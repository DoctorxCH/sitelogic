<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectType extends Model
{
    protected $fillable = ['name'];

    public function checklistTemplates()
    {
        return $this->belongsToMany(ChecklistTemplate::class, 'checklist_template_project_type');
    }
}
