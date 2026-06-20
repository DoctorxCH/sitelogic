<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('job_checklist_item_photos') && !Schema::hasColumn('job_checklist_item_photos', 'sort_order')) {
            Schema::table('job_checklist_item_photos', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->default(0)->after('photo_path');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('job_checklist_item_photos') && Schema::hasColumn('job_checklist_item_photos', 'sort_order')) {
            Schema::table('job_checklist_item_photos', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};
