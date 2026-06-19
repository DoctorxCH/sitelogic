<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $fillable = [
        'pid',
        'adresse',
        'project_type_id',
        'projekt_typ',
        'bauleiter',
        'technologie',
    ];

    public function projectType()
    {
        return $this->belongsTo(ProjectType::class);
    }

    public function jobAssets()
    {
        return $this->hasMany(JobAsset::class);
    }

    public function checklists()
    {
        return $this->hasMany(Checklist::class, 'auftragskartei_id');
    }
}
