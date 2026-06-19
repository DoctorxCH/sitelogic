<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    protected $fillable = [
        'checklist_id',
        'question',
        'kriterien_ausgeschaltet',
    ];

    public function checklist()
    {
        return $this->belongsTo(Checklist::class);
    }
}
