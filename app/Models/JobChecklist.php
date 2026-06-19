<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobChecklist extends Model
{
    protected $table = 'job_checklists';
    protected $fillable = ['job_id', 'name', 'is_completed'];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(JobChecklistItem::class, 'job_checklist_id');
    }
}
