<?php

namespace App\Observers;

use App\Models\Job;
use App\Models\ChecklistTemplate;
use App\Models\JobChecklist;
use App\Models\JobChecklistItem;

class JobObserver
{
    public function created(Job $job): void
    {
        // Finde alle Vorlagen, die für den Typ des Jobs registriert sind
        $templates = ChecklistTemplate::whereJsonContains('job_types', $job->type)->get();

        foreach ($templates as $template) {
            // Erstelle die Live-Checkliste für den Job
            $jobChecklist = JobChecklist::create([
                'job_id' => $job->id,
                'name' => $template->name,
            ]);

            // Kopiere alle vordefinierten Punkte in den Job
            foreach ($template->items as $itemTemplate) {
                JobChecklistItem::create([
                    'job_checklist_id' => $jobChecklist->id,
                    'task' => $itemTemplate->task,
                ]);
            }
        }
    }
}
