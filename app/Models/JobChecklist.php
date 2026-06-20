<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobChecklist extends Model
{
    protected $guarded = [];
    protected $casts = [
        'submitted_at' => 'datetime',
        'edited_by_user_ids' => 'array',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function items()
    {
        return $this->hasMany(JobChecklistItem::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
