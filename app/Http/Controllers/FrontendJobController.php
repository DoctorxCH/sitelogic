<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobChecklist;
use App\Models\JobChecklistItem;
use App\Models\JobFieldSetting;
use App\Models\User;
use App\Models\JobChecklistItemPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FrontendJobController extends Controller
{
    private const MAX_PHOTOS_PER_ITEM = 5;

    public function index(Request $request)
    {
        $user = Auth::user();
        $isManager = $user->hasRole('manager') || $user->hasRole('super_admin');

        $myJobsQuery = Job::query();
        $generalJobsQuery = Job::query();

        if ($isManager) {
            // Managers see their own started jobs as "Meine Jobs" and all pending jobs as "Jobs Allgemein"
            $myJobsQuery->where('user_id', $user->id)
                        ->where('status', 'in_progress');
            
            $generalJobsQuery->where('status', 'pending');
        } else {
            // Technicians see their assigned jobs as "Meine Jobs" and unassigned pending jobs as "Jobs Allgemein"
            $myJobsQuery->where('technician_id', $user->id)
                        ->whereIn('status', ['pending', 'in_progress']);
                        
            $generalJobsQuery->whereNull('technician_id')
                             ->where('status', 'pending');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $searchClosure = function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('custom_fields', 'like', "%{$search}%");
            };
            $myJobsQuery->where($searchClosure);
            $generalJobsQuery->where($searchClosure);
        }

        $myJobs = $myJobsQuery->orderBy('created_at', 'desc')->paginate(12, ['*'], 'my_jobs_page');
        $generalJobs = $generalJobsQuery->orderBy('created_at', 'desc')->paginate(12, ['*'], 'general_jobs_page');

        // Map data uses both
        $allMapJobsQuery = Job::query();
        if ($isManager) {
            $allMapJobsQuery->where(function ($q) use ($user) {
                $q->where('status', 'pending')
                    ->orWhere(function ($sq) use ($user) {
                        $sq->where('user_id', $user->id)
                            ->where('status', 'in_progress');
                    });
            });
        } else {
            $allMapJobsQuery->where(function($q) use ($user) {
                $q->whereNull('technician_id')->where('status', 'pending')
                  ->orWhere(function($sq) use ($user) {
                      $sq->where('technician_id', $user->id)->whereIn('status', ['pending', 'in_progress']);
                  });
            });
        }
        $allMapJobs = $allMapJobsQuery->get();

        $pendingReviews = [];
        if ($isManager) {
            $pendingReviews = JobChecklist::where('status', 'submitted')
                ->whereHas('job', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->with('job')
                ->orderBy('submitted_at', 'asc')
                ->get();
        }

        return view('frontend.dashboard', compact('myJobs', 'generalJobs', 'pendingReviews', 'isManager', 'allMapJobs'));
    }

    public function show(Job $job)
    {
        $user = Auth::user();
        $isManager = $user->hasRole('manager') || $user->hasRole('super_admin');

        if (!$isManager && $job->technician_id && $job->technician_id !== $user->id) {
            abort(403);
        }

        $job->load(['checklists.items.photos', 'checklists.reviewer:id,name,email', 'user:id,name,email', 'technician:id,name,email']);
        $fieldLabels = JobFieldSetting::query()->pluck('label', 'key');
        $editorIds = $job->checklists
            ->flatMap(function (JobChecklist $checklist) {
                $ids = $checklist->edited_by_user_ids ?? [];
                if ($checklist->reviewer_id) {
                    $ids[] = $checklist->reviewer_id;
                }

                return $ids;
            })
            ->filter()
            ->unique()
            ->values();
        $editorEmails = User::query()
            ->whereIn('id', $editorIds)
            ->pluck('email', 'id');

        return view('frontend.job-detail', compact('job', 'isManager', 'fieldLabels', 'editorEmails'));
    }

    public function updateStatus(Job $job, Request $request)
    {
        $request->validate(['status' => 'required|in:in_progress,completed']);
        if ($request->status === 'in_progress') {
            $job->technician_id = Auth::id();
            $job->status = 'in_progress';
            if (!$job->started_at) $job->started_at = now();
        } else {
            $job->status = 'completed';
            $job->completed_at = now();
        }
        $job->save();
        return redirect()->back()->with('success', 'Job status updated.');
    }

    public function disableChecklist(JobChecklist $checklist)
    {
        if ($checklist->status !== 'pending') {
            return redirect()->back()->with('error', 'This checklist is locked and cannot be changed anymore.');
        }

        $checklist->update(['status' => 'disabled']);
        $checklist->items()->update(['status' => 'disabled']);
        $this->trackChecklistEditor($checklist);

        return redirect()->back()->with('success', 'Checklist disabled.');
    }

    public function submitChecklist(JobChecklist $checklist)
    {
        if ($checklist->status !== 'pending') {
            return redirect()->back()->with('error', 'This checklist is locked and cannot be submitted again.');
        }

        $unresolvedCount = $checklist->items()->whereIn('status', ['pending', 'rejected'])->count();
        if ($unresolvedCount > 0) {
            return redirect()->back()->with('error', 'All items must be filled or disabled before submitting.');
        }

        $checklist->update(['status' => 'submitted', 'submitted_at' => now()]);
        $this->trackChecklistEditor($checklist);

        return redirect()->route('frontend.dashboard')->with('success', 'Checklist submitted for review.');
    }

    // AJAX OPERATIONS FOR TECHNICIAN
    public function saveItem(JobChecklistItem $item, Request $request)
    {
        if ($item->checklist->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This checklist is locked and cannot be edited anymore.'
            ], 423);
        }

        // Punkt deaktivieren via AJAX
        if ($request->has('disable')) {
            foreach ($item->photos as $photo) {
                Storage::disk('public')->delete($photo->photo_path);
            }
            $item->photos()->delete();

            $item->update([
                'status' => 'disabled', 
                'photo_path' => null,
                'technician_comment' => $request->input('technician_comment')
            ]);
            return response()->json([
                'success' => true, 
                'status' => 'disabled',
                'photos' => [],
                'comment' => $item->technician_comment
            ]);
        }

        // Foto & Kommentar speichern via AJAX
        $request->validate([
            'photo' => 'nullable|image|max:10240',
            'photos' => 'nullable|array',
            'photos.*' => 'image|max:10240',
            'technician_comment' => 'nullable|string'
        ]);

        if ($item->photos()->count() === 0 && $item->photo_path) {
            $legacyPhotoData = ['photo_path' => $item->photo_path];
            if ($this->hasPhotoSortOrderColumn()) {
                $legacyPhotoData['sort_order'] = 1;
            }
            $item->photos()->create($legacyPhotoData);
        }

        $incomingPhotoFiles = [];
        if ($request->hasFile('photos')) {
            $incomingPhotoFiles = array_merge($incomingPhotoFiles, $request->file('photos'));
        }
        if ($request->hasFile('photo')) {
            $incomingPhotoFiles[] = $request->file('photo');
        }

        $existingPhotoCount = $item->photos()->count();
        if ($existingPhotoCount === 0 && $item->photo_path) {
            $existingPhotoCount = 1;
        }

        if (($existingPhotoCount + count($incomingPhotoFiles)) > self::MAX_PHOTOS_PER_ITEM) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum ' . self::MAX_PHOTOS_PER_ITEM . ' photos per checkpoint allowed.'
            ], 422);
        }

        $uploadedPaths = [];
        $useSortOrder = $this->hasPhotoSortOrderColumn();
        $nextSortOrder = $useSortOrder ? ((int) $item->photos()->max('sort_order') + 1) : null;

        if (!empty($incomingPhotoFiles)) {
            foreach ($incomingPhotoFiles as $photoFile) {
                $path = $photoFile->store('checklists/photos', 'public');
                $photoData = ['photo_path' => $path];
                if ($useSortOrder) {
                    $photoData['sort_order'] = $nextSortOrder++;
                }

                $item->photos()->create($photoData);
                $uploadedPaths[] = $path;
            }
        }

        if (!empty($uploadedPaths)) {
            // Keep first photo_path for backward compatibility with older UI/resource code.
            $item->photo_path = $uploadedPaths[0];
            $item->status = 'submitted';
        }

        $item->technician_comment = $request->input('technician_comment');
        $item->save();

        $this->trackChecklistEditor($item->checklist);

        $photosPayload = $this->buildPhotosPayload($item);
        $photoUrls = collect($photosPayload)->pluck('url')->values();

        return response()->json([
            'success' => true,
            'status' => $item->status,
            'photo_url' => $item->photo_path ? asset('storage/' . $item->photo_path) : null,
            'photo_urls' => $photoUrls,
            'photos' => $photosPayload,
            'comment' => $item->technician_comment
        ]);
    }

    public function deleteItemPhoto(JobChecklistItem $item, JobChecklistItemPhoto $photo)
    {
        if ($photo->job_checklist_item_id !== $item->id) {
            abort(404);
        }

        if ($item->checklist->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This checklist is locked and cannot be edited anymore.'
            ], 423);
        }

        Storage::disk('public')->delete($photo->photo_path);
        $photo->delete();

        if ($this->hasPhotoSortOrderColumn()) {
            $remainingPhotos = $item->photos()->get();
            foreach ($remainingPhotos as $index => $remainingPhoto) {
                $remainingPhoto->update(['sort_order' => $index + 1]);
            }
        }

        $firstPhotoPath = $item->photos()->value('photo_path');
        $item->update(['photo_path' => $firstPhotoPath]);
        $this->trackChecklistEditor($item->checklist);

        $photosPayload = $this->buildPhotosPayload($item);

        return response()->json([
            'success' => true,
            'photos' => $photosPayload,
            'photo_urls' => collect($photosPayload)->pluck('url')->values(),
        ]);
    }

    public function reorderItemPhotos(JobChecklistItem $item, Request $request)
    {
        if (!$this->hasPhotoSortOrderColumn()) {
            return response()->json([
                'success' => false,
                'message' => 'Photo sorting is not available until database migration is applied.'
            ], 409);
        }

        if ($item->checklist->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This checklist is locked and cannot be edited anymore.'
            ], 423);
        }

        $data = $request->validate([
            'photo_ids' => 'required|array|min:1',
            'photo_ids.*' => 'required|integer',
        ]);

        $existingIds = $item->photos()->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $requestedIds = collect($data['photo_ids'])->map(fn ($id) => (int) $id)->values()->all();

        sort($existingIds);
        $sortedRequested = $requestedIds;
        sort($sortedRequested);

        if ($existingIds !== $sortedRequested) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid photo order payload.'
            ], 422);
        }

        foreach ($requestedIds as $index => $photoId) {
            $item->photos()->whereKey($photoId)->update(['sort_order' => $index + 1]);
        }

        $firstPhotoPath = $item->photos()->value('photo_path');
        $item->update(['photo_path' => $firstPhotoPath]);
        $this->trackChecklistEditor($item->checklist);

        $photosPayload = $this->buildPhotosPayload($item);

        return response()->json([
            'success' => true,
            'photos' => $photosPayload,
            'photo_urls' => collect($photosPayload)->pluck('url')->values(),
        ]);
    }

    // SEAMLESS REVIEWS FOR MANAGERS
    public function reviewChecklist(JobChecklist $checklist, Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('manager') && !$user->hasRole('super_admin')) abort(403);
        if ($checklist->job->user_id !== $user->id && !$user->hasRole('super_admin')) {
            abort(403);
        }
        if ($checklist->status !== 'submitted') {
            return redirect()->back()->with('error', 'Only submitted checklists can be reviewed.');
        }

        $request->validate([
            'items' => 'required|array',
            'items.*.status' => 'required|in:approved,rejected',
            'items.*.manager_comment' => 'required_if:items.*.status,rejected|nullable|string',
        ]);

        $allApproved = true;

        foreach ($request->items as $itemId => $data) {
            $item = JobChecklistItem::findOrFail($itemId);
            if ($item->status === 'disabled') continue;

            $item->status = $data['status'];
            $item->manager_comment = $data['manager_comment'] ?? null;
            if ($data['status'] === 'rejected') {
                $allApproved = false;
            }
            $item->save();
        }

        $checklist->status = $allApproved ? 'approved' : 'rejected';
        $checklist->reviewer_id = $user->id;
        $checklist->save();
        $this->trackChecklistEditor($checklist);

        return redirect()->route('frontend.dashboard')->with('success', $allApproved ? 'Checklist completely approved and closed.' : 'Checklist rejected and closed.');
    }

    private function trackChecklistEditor(JobChecklist $checklist): void
    {
        $userId = Auth::id();
        if (!$userId) {
            return;
        }

        $editedBy = $checklist->edited_by_user_ids ?? [];
        if (!in_array($userId, $editedBy, true)) {
            $editedBy[] = $userId;
            $checklist->update(['edited_by_user_ids' => array_values($editedBy)]);
        }
    }

    private function buildPhotosPayload(JobChecklistItem $item): array
    {
        $useSortOrder = $this->hasPhotoSortOrderColumn();
        $columns = $useSortOrder ? ['id', 'photo_path', 'sort_order'] : ['id', 'photo_path'];

        $photos = $item->photos()
            ->get($columns)
            ->map(function (JobChecklistItemPhoto $photo, int $index) use ($useSortOrder) {
                return [
                    'id' => $photo->id,
                    'url' => asset('storage/' . $photo->photo_path),
                    'sort_order' => $useSortOrder ? $photo->sort_order : ($index + 1),
                ];
            })
            ->values()
            ->all();

        if (empty($photos) && $item->photo_path) {
            return [[
                'id' => null,
                'url' => asset('storage/' . $item->photo_path),
                'sort_order' => 1,
            ]];
        }

        return $photos;
    }

    private function hasPhotoSortOrderColumn(): bool
    {
        return JobChecklistItem::hasPhotoSortOrderColumn();
    }
}
