<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChecklistTemplate extends Model
{
    protected $fillable = ['name', 'job_types'];
    protected $casts = ['job_types' => 'array'];

    public function items(): HasMany
    {
        return $this->hasMany(ChecklistItemTemplate::class, 'checklist_template_id');
    }
}
