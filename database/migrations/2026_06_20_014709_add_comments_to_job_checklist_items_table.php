<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_checklist_items', function (Blueprint $table) {
            $table->text('technician_comment')->nullable()->after('status');
            $table->text('manager_comment')->nullable()->after('technician_comment');
            if (Schema::hasColumn('job_checklist_items', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('job_checklist_items', function (Blueprint $table) {
            $table->dropColumn(['technician_comment', 'manager_comment']);
            $table->text('rejection_reason')->nullable();
        });
    }
};
