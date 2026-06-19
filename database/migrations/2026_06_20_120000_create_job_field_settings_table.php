<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_field_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->string('type')->default('text');
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });

        if (Schema::hasTable('jobs') && !Schema::hasColumn('jobs', 'custom_fields')) {
            Schema::table('jobs', function (Blueprint $table) {
                $table->json('custom_fields')->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('job_field_settings');
    }
};
