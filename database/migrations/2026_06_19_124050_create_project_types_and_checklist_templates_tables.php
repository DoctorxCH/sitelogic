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
        Schema::create('project_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // z.B. "FTTH"
            $table->timestamps();
        });

        Schema::create('checklist_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // z.B. "Inhouse" oder "Manhole"
            $table->timestamps();
        });

        Schema::create('checklist_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_template_id')->constrained('checklist_templates')->cascadeOnDelete();
            $table->string('question'); // Die eigentliche Frage, z.B. "OTO-Dose korrekt beschriftet?"
            $table->timestamps();
        });

        Schema::create('checklist_template_project_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_type_id')->constrained('project_types')->cascadeOnDelete();
            $table->foreignId('checklist_template_id')->constrained('checklist_templates')->cascadeOnDelete();
            $table->timestamps();
        });

        // Add name fields to existing checklists and checklist_items tables if they don't exist
        Schema::table('checklists', function (Blueprint $table) {
            $table->string('name')->nullable()->after('auftragskartei_id');
        });

        Schema::table('checklist_items', function (Blueprint $table) {
            $table->string('question')->nullable()->after('checklist_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checklist_items', function (Blueprint $table) {
            $table->dropColumn('question');
        });

        Schema::table('checklists', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        Schema::dropIfExists('checklist_template_project_type');
        Schema::dropIfExists('checklist_template_items');
        Schema::dropIfExists('checklist_templates');
        Schema::dropIfExists('project_types');
    }
};
