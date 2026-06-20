<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class JobChecklistItem extends Model
{
    protected $guarded = [];

    private static ?bool $hasPhotoSortOrderColumn = null;

    public function checklist()
    {
        return $this->belongsTo(JobChecklist::class, 'job_checklist_id');
    }

    public function photos()
    {
        $relation = $this->hasMany(JobChecklistItemPhoto::class, 'job_checklist_item_id');

        if (self::hasPhotoSortOrderColumn()) {
            return $relation->orderBy('sort_order')->orderBy('id');
        }

        return $relation->orderBy('id');
    }

    public static function hasPhotoSortOrderColumn(): bool
    {
        if (self::$hasPhotoSortOrderColumn === null) {
            self::$hasPhotoSortOrderColumn = Schema::hasTable('job_checklist_item_photos')
                && Schema::hasColumn('job_checklist_item_photos', 'sort_order');
        }

        return self::$hasPhotoSortOrderColumn;
    }
}
