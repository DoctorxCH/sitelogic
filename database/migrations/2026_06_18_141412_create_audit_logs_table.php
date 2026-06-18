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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('wer')->nullable();
            $table->timestamp('wann')->useCurrent();
            $table->string('punkt_deaktiviert')->nullable();
            $table->string('status_geaendert')->nullable();
            $table->string('action_type_local')->nullable();
            $table->string('details')->nullable();
            $table->unsignedBigInteger('entity_id_local')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
