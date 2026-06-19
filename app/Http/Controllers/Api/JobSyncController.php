<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Job;
use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\JobAsset;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class JobSyncController extends Controller
{
    /**
     * Create a new job with assets (needed for JobApiTest).
     */
    public function createJob(Request $request)
    {
        $validatedData = $request->validate([
            'pid' => 'required|string',
            'adresse' => 'required|string',
            'projekt_typ' => 'required|string',
            'bauleiter' => 'required|string',
            'technologie' => 'required|string',
            'asset_ids' => 'nullable|array',
            'flat_ids' => 'nullable|array',
            'kabel_bep_muffentypen' => 'nullable|array',
            'asset_metadaten' => 'nullable|array',
        ]);

        $job = Job::create($request->only(['pid', 'adresse', 'projekt_typ', 'bauleiter', 'technologie']));

        $jobAsset = JobAsset::create([
            'job_id' => $job->id,
            'asset_ids' => $request->input('asset_ids'),
            'flat_ids' => $request->input('flat_ids'),
            'kabel_bep_muffentypen' => $request->input('kabel_bep_muffentypen'),
            'asset_metadaten' => $request->input('asset_metadaten'),
        ]);

        $job->load('jobAssets');

        return response()->json([
            'message' => 'Job created successfully',
            'job' => $job,
        ], 201);
    }

    /**
     * Get all jobs relevant for the user.
     */
    public function getJobs(Request $request)
    {
        // Filter jobs by the authenticated user's name
        $user = $request->user();
        if ($user && $user->hasRole('bauleiter')) {
            $jobs = Job::with('jobAssets')->where('bauleiter', $user->name)->get();
        } else {
             $jobs = Job::with('jobAssets')->get();
        }
        return response()->json($jobs);
    }

    /**
     * Get checklists for offline syncing.
     */
    public function getChecklists(Request $request)
    {
        return response()->json(Checklist::all());
    }

    /**
     * Get checklist items for offline syncing.
     */
    public function getChecklistItems(Request $request)
    {
        return response()->json(ChecklistItem::all());
    }

    /**
     * Sync checklist updates from offline to online.
     */
    public function syncChecklist(Request $request, $id)
    {
        // Add authorization check. For now, require auth user to be able to access.
        if (!$request->user()) {
            abort(403, 'Unauthorized');
        }

        // This endpoint expects a partial payload to update the checklist.
        $checklist = Checklist::findOrFail($id);

        $validatedData = $request->validate([
            'status' => 'sometimes|string',
            'hauptschalter' => 'sometimes|boolean',
            'reject_comment' => 'sometimes|string|nullable',
        ]);

        $checklist->update($validatedData);

        return response()->json(['success' => true, 'checklist' => $checklist]);
    }

    /**
     * Upload photo from the offline queue.
     */
    public function uploadPhoto(Request $request)
    {
         if (!$request->user()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'checklist_item_id' => 'required|exists:checklist_items,id',
            'file_data' => 'required|string',
            'file_name' => 'required|string',
        ]);

        // file_data is assumed to be a base64 encoded string
        $fileData = $request->input('file_data');
        $fileName = basename($request->input('file_name')); // Prevent path traversal

        // Remove the data URI scheme if present
        if (preg_match('/^data:image\/(\w+);base64,/', $fileData, $type)) {
            $fileData = substr($fileData, strpos($fileData, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, gif

            $fileData = base64_decode($fileData);

            if ($fileData === false) {
                return response()->json(['error' => 'Base64 decode failed'], 400);
            }
        } else {
            return response()->json(['error' => 'Invalid image format'], 400);
        }

        // Save file to storage
        $path = 'photos/' . uniqid() . '_' . Str::slug(pathinfo($fileName, PATHINFO_FILENAME)) . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
        Storage::disk('public')->put($path, $fileData);

        // Fetch checklist to associate photo to JobAsset
        $checklistItem = ChecklistItem::with('checklist')->findOrFail($request->input('checklist_item_id'));
        $jobId = $checklistItem->checklist->auftragskartei_id;

        JobAsset::updateOrCreate(
             ['job_id' => $jobId],
             ['asset_metadaten' => \DB::raw("jsonb_set(COALESCE(asset_metadaten, '{}'::jsonb), '{photos}', COALESCE(asset_metadaten->'photos', '[]'::jsonb) || '\"$path\"'::jsonb)")]
        );

        return response()->json(['success' => true, 'path' => $path]);
    }
}
