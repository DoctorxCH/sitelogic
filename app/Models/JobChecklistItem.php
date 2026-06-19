<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobChecklistItem extends Model
{
    protected $table = 'job_checklist_items';
    protected $fillable = ['job_checklist_id', 'task', 'is_checked', 'checked_at'];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(JobChecklist::class, 'job_checklist_id');
    }
}
