<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_checklists', function (Blueprint $table) {
            $table->string('status')->default('pending'); // pending, disabled, submitted, approved, rejected
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
        });

        Schema::table('job_checklist_items', function (Blueprint $table) {
            $table->string('photo_path')->nullable();
            $table->string('status')->default('pending'); // pending, disabled, submitted, approved, rejected
            $table->text('rejection_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('job_checklists', function (Blueprint $table) {
            $table->dropForeign(['reviewer_id']);
            $table->dropColumn(['status', 'reviewer_id', 'submitted_at']);
        });

        Schema::table('job_checklist_items', function (Blueprint $table) {
            $table->dropColumn(['photo_path', 'status', 'rejection_reason']);
        });
    }
};
