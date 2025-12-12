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
        Schema::create('erasmus_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('call_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('event_type', ['apertura', 'cierre', 'entrevista', 'publicacion_provisional', 'publicacion_definitivo', 'reunion_informativa', 'otro']);
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_public')->default(true);
            $table->foreignId('created_by')->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['program_id', 'start_date']);
            $table->index(['call_id', 'start_date']);
            $table->index(['is_public', 'start_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erasmus_events');
    }
};
