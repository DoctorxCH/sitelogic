<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistTemplate extends Model
{
    protected $fillable = ['name'];

    public function items()
    {
        return $this->hasMany(ChecklistTemplateItem::class);
    }

    public function projectTypes()
    {
        return $this->belongsToMany(ProjectType::class, 'checklist_template_project_type');
    }
}
