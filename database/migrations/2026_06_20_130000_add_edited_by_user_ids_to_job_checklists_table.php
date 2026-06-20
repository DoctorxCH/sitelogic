<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_checklists', function (Blueprint $table) {
            $table->json('edited_by_user_ids')->nullable()->after('reviewer_id');
        });
    }

    public function down(): void
    {
        Schema::table('job_checklists', function (Blueprint $table) {
            $table->dropColumn('edited_by_user_ids');
        });
    }
};
