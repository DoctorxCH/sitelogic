<?php

namespace App\Observers;

use App\Models\Job;
use App\Models\ProjectType;
use App\Models\Checklist;
use App\Models\ChecklistItem;

class JobObserver
{
    /**
     * Handle the Job "created" event.
     */
    public function created(Job $job): void
    {
        $projectTypeId = $job->project_type_id;
        if (!$projectTypeId && $job->projekt_typ) {
            $projectType = \App\Models\ProjectType::where('name', $job->projekt_typ)->first();
            $projectTypeId = $projectType?->id;
        }

        if (!$projectTypeId) return;

        $templates = \App\Models\ChecklistTemplate::where('project_type_id', $projectTypeId)->get();

        foreach ($templates as $template) {
            $checklist = $job->checklists()->create([
                'name' => $template->name,
                'status' => 'open',
                'hauptschalter' => true,
            ]);

            foreach ($template->items as $item) {
                $checklist->items()->create([
                    'question' => $item->question,
                    'is_checked' => false,
                    'kriterien_ausgeschaltet' => false,
                ]);
            }
        }
    }

    /**
     * Handle the Job "updated" event.
     */
    public function updated(Job $job): void
    {
        //
    }

    /**
     * Handle the Job "deleted" event.
     */
    public function deleted(Job $job): void
    {
        //
    }

    /**
     * Handle the Job "restored" event.
     */
    public function restored(Job $job): void
    {
        //
    }

    /**
     * Handle the Job "force deleted" event.
     */
    public function forceDeleted(Job $job): void
    {
        //
    }
}
