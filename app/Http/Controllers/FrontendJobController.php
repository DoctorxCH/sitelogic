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
        // Jobs die entweder frei sind oder dem Techniker gehören
        $jobs = Job::where(function($query) {
                $query->whereNull('technician_id')
                      ->where('status', 'pending');
            })
            ->orWhere(function($query) {
                $query->where('technician_id', Auth::id())
                      ->whereIn('status', ['pending', 'in_progress']);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('frontend.dashboard', compact('jobs'));
    }

    public function show(Job $job)
    {
        if ($job->technician_id && $job->technician_id !== Auth::id()) {
            abort(403, 'Unauthorized.');
        }

        $job->load('checklists.items');

        return view('frontend.job-detail', compact('job'));
    }

    public function toggleItem(ChecklistItem $item)
    {
        if ($item->checklist->job->technician_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
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
        $request->validate(['status' => 'required|in:in_progress,completed,aborted']);

        if ($request->status === 'in_progress') {
            // Selbstzuweisung des Technikers beim Starten
            if ($job->technician_id && $job->technician_id !== Auth::id()) {
                abort(403);
            }
            $job->technician_id = Auth::id();
            $job->status = 'in_progress';
            if (!$job->started_at) {
                $job->started_at = now();
            }
        } else {
            // Absichern für Abschluss
            if ($job->technician_id !== Auth::id()) {
                abort(403);
            }
            $job->status = $request->status;
            $job->completed_at = now();
        }

        $job->save();

        return redirect()->back()->with('success', 'Status updated.');
    }
}
