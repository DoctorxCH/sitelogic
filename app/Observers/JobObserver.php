<?php

namespace App\Observers;

use App\Models\Job;
use App\Models\ChecklistTemplate;

class JobObserver
{
    /**
     * Wird automatisch gefeuert, NACHDEM ein Job in der Datenbank erstellt wurde.
     */
    public function created(Job $job): void
    {
        // Sucht alle Templates, die den Typ des neuen Jobs (z.B. 'ftth') in ihrem Array haben
        $templates = ChecklistTemplate::all()->filter(function ($template) use ($job) {
            return is_array($template->job_types) && in_array($job->type, $template->job_types);
        });

        foreach ($templates as $template) {
            // 1. Erstelle die Haupt-Checkliste für den Job
            $checklist = $job->checklists()->create([
                'name' => $template->name,
            ]);

            // 2. Kopiere alle Unteraufgaben aus dem Template in den Job
            foreach ($template->items as $item) {
                $checklist->items()->create([
                    'task' => $item->task,
                    'is_checked' => false,
                ]);
            }
        }
    }
}
