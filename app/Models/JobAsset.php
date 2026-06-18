<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobAsset extends Model
{
    protected $fillable = [
        'job_id',
        'asset_ids',
        'flat_ids',
        'kabel_bep_muffentypen',
        'asset_metadaten',
    ];

    protected $casts = [
        'asset_ids' => 'array',
        'flat_ids' => 'array',
        'kabel_bep_muffentypen' => 'array',
        'asset_metadaten' => 'array',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
