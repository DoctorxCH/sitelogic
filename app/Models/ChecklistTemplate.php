<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistTemplate extends Model
{
    protected $fillable = ['project_type_id', 'name'];

    public function items()
    {
        return $this->hasMany(ChecklistTemplateItem::class);
    }

    public function projectType()
    {
        return $this->belongsTo(ProjectType::class);
    }
}
