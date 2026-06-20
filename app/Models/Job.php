<?php

namespace App\Models;

use App\Observers\JobObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([JobObserver::class])]
class Job extends Model
{
    // Hebt die Mass-Assignment-Blockade auf, damit der CSV-Import alle Felder speichern darf
    protected $guarded = [];

    protected $casts = [
        'custom_fields' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function checklists()
    {
        return $this->hasMany(JobChecklist::class);
    }

    public function bepType()
    {
        return $this->belongsTo(BepType::class);
    }
}
