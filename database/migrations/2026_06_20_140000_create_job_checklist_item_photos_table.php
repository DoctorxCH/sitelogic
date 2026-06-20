<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_checklist_item_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_checklist_item_id')->constrained('job_checklist_items')->cascadeOnDelete();
            $table->string('photo_path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_checklist_item_photos');
    }
};
