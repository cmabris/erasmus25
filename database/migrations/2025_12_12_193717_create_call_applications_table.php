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
        Schema::create('call_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('call_id')->constrained()->cascadeOnDelete();
            $table->string('applicant_name');
            $table->string('applicant_email');
            $table->string('applicant_phone')->nullable();
            $table->enum('status', ['pendiente', 'admitida', 'rechazada', 'renunciada'])->default('pendiente');
            $table->decimal('score', 5, 2)->nullable();
            $table->integer('position')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['call_id', 'status']);
            $table->index(['call_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_applications');
    }
};
