<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    protected $fillable = [
        'auftragskartei_id',
        'status',
        'hauptschalter',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class, 'auftragskartei_id');
    }
}
