<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('project_types', function (Blueprint $table) {
            $table->string('description')->nullable()->after('name');
        });

        Schema::table('checklist_templates', function (Blueprint $table) {
            $table->foreignId('project_type_id')->nullable()->after('id')->constrained('project_types')->nullOnDelete();
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->foreignId('project_type_id')->nullable()->after('id')->constrained('project_types')->nullOnDelete();
        });

        Schema::table('checklist_items', function (Blueprint $table) {
            $table->boolean('is_checked')->default(false)->after('question');
        });

        Schema::dropIfExists('checklist_template_project_type');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('checklist_template_project_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_type_id')->constrained('project_types')->cascadeOnDelete();
            $table->foreignId('checklist_template_id')->constrained('checklist_templates')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::table('checklist_items', function (Blueprint $table) {
            $table->dropColumn('is_checked');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->dropForeign(['project_type_id']);
            $table->dropColumn('project_type_id');
        });

        Schema::table('checklist_templates', function (Blueprint $table) {
            $table->dropForeign(['project_type_id']);
            $table->dropColumn('project_type_id');
        });

        Schema::table('project_types', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
