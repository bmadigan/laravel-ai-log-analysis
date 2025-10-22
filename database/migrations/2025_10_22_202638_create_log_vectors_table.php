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
        Schema::create('log_vectors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('log_entry_id')->constrained()->cascadeOnDelete();
            $table->json('embedding');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_vectors');
    }
};
