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
        Schema::create('media_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->enum('consent_type', ['imagen', 'video', 'audio']);
            $table->string('person_name')->nullable();
            $table->string('person_email')->nullable();
            $table->boolean('consent_given');
            $table->date('consent_date');
            $table->foreignId('consent_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->date('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['media_id']);
            $table->index(['consent_type', 'consent_given']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_consents');
    }
};
