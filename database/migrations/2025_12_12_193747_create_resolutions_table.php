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
        Schema::create('resolutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('call_id')->constrained()->cascadeOnDelete();
            $table->foreignId('call_phase_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['provisional', 'definitivo', 'alegaciones']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('evaluation_procedure')->nullable();
            $table->date('official_date');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['call_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resolutions');
    }
};
