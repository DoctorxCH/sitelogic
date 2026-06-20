<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BepType extends Model
{
    protected $fillable = ['name', 'number_of_units', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'number_of_units' => 'integer',
    ];

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }
}
