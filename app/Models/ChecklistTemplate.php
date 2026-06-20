<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistTemplate extends Model
{
    protected $guarded = [];

    protected $casts = [
        'job_types' => 'array',
    ];

    public function items()
    {
        return $this->hasMany(ChecklistItemTemplate::class);
    }
}
