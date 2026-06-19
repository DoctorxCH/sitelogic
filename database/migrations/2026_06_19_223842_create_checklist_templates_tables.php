<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Templates for Checklists
        Schema::create('checklist_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('job_types'); // Stores e.g., ["ftth", "ftto"]
            $table->timestamps();
        });

        // 2. Templates for Checklist Items
        Schema::create('checklist_item_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_template_id')->constrained('checklist_templates')->onDelete('cascade');
            $table->string('task');
            $table->timestamps();
        });

        // 3. Add 'type' column to jobs table if it doesn't exist
        if (Schema::hasTable('jobs') && !Schema::hasColumn('jobs', 'type')) {
            Schema::table('jobs', function (Blueprint $table) {
                $table->string('type')->default('ftth')->after('status');
            });
        }

        // 4. Drop old dynamic tables cleanly
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('checklist_items');
        Schema::dropIfExists('checklists');
        Schema::enableForeignKeyConstraints();

        // 5. Create new Job-bound Checklist instances
        Schema::create('job_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs')->onDelete('cascade');
            $table->string('name');
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });

        Schema::create('job_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_checklist_id')->constrained('job_checklists')->onDelete('cascade');
            $table->string('task');
            $table->boolean('is_checked')->default(false);
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_checklist_items');
        Schema::dropIfExists('job_checklists');
        Schema::dropIfExists('checklist_item_templates');
        Schema::dropIfExists('checklist_templates');
    }
};
