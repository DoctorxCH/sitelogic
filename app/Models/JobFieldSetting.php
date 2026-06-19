<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobFieldSetting extends Model
{
    protected $table = 'job_field_settings';
    
    protected $fillable = ['key', 'label', 'type', 'options', 'is_required'];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
    ];
}
