<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobChecklistItemPhoto extends Model
{
    protected $guarded = [];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function item()
    {
        return $this->belongsTo(JobChecklistItem::class, 'job_checklist_item_id');
    }
}
