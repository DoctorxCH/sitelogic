<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\ChecklistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FrontendJobController extends Controller
{
    public function index()
    {
        $jobs = Job::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('frontend.dashboard', compact('jobs'));
    }

    public function show(Job $job)
    {
        if ($job->user_id !== Auth::id()) {
            abort(403, 'Zugriff verweigert.');
        }

        $job->load('checklists.items');

        return view('frontend.job-detail', compact('job'));
    }

    public function toggleItem(ChecklistItem$item)
    {
        if ($item->checklist->job->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unautorisiert'], 403);
        }

        $item->is_checked = !$item->is_checked;
        $item->checked_at = $item->is_checked ? now() : null;
        $item->save();

        return response()->json([
            'success' => true,
            'is_checked' => $item->is_checked
        ]);
    }

    public function updateStatus(Job $job, Request $request)
    {
        if ($job->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate(['status' => 'required|in:in_progress,completed,aborted']);
        
        $job->status = $request->status;
        if ($request->status === 'in_progress' && !$job->started_at) {
            $job->started_at = now();
        } elseif (in_array($request->status, ['completed', 'aborted'])) {
            $job->completed_at = now();
        }
        $job->save();

        return redirect()->back()->with('success', 'Status aktualisiert.');
    }
}
