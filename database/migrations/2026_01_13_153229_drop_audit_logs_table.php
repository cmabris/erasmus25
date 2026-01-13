<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Elimina la tabla audit_logs ya que se ha migrado a usar
     * Spatie Laravel Activitylog (tabla activity_log).
     * La tabla audit_logs estaba vacía (0 registros).
     */
    public function up(): void
    {
        Schema::dropIfExists('audit_logs');
    }

    /**
     * Reverse the migrations.
     *
     * Recrea la tabla audit_logs en caso de rollback.
     * Nota: Esta migración no restaura datos, solo la estructura.
     */
    public function down(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('action', ['create', 'update', 'delete', 'publish', 'archive', 'restore']);
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->json('changes')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at');

            $table->index(['user_id', 'created_at']);
            $table->index(['model_type', 'model_id']);
        });
    }
};
