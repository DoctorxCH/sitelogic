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
        // Find the matching project type by name (case-insensitive ideally, but exact match for now)
        $projectType = ProjectType::where('name', $job->projekt_typ)->first();

        if ($projectType) {
            // Get templates attached to this project type
            $templates = $projectType->checklistTemplates()->with('items')->get();

            foreach ($templates as $template) {
                // Create a concrete checklist for this job
                $checklist = Checklist::create([
                    'auftragskartei_id' => $job->id,
                    'name' => $template->name,
                    'status' => 'open',
                    'hauptschalter' => true,
                ]);

                // Create concrete checklist items based on template questions
                foreach ($template->items as $templateItem) {
                    ChecklistItem::create([
                        'checklist_id' => $checklist->id,
                        'question' => $templateItem->question,
                        'kriterien_ausgeschaltet' => false,
                    ]);
                }
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
