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
        Schema::connection(config('activitylog.database_connection'))->table(config('activitylog.table_name'), function (Blueprint $table) {
            // Índice compuesto para búsquedas por subject (modelo afectado)
            $table->index(['subject_type', 'subject_id'], 'activity_log_subject_index');

            // Índice compuesto para búsquedas por causer (usuario que realizó la acción)
            $table->index(['causer_type', 'causer_id'], 'activity_log_causer_index');

            // Índice en created_at para ordenación y filtros de fecha
            $table->index('created_at', 'activity_log_created_at_index');

            // Índice en description para búsquedas
            $table->index('description', 'activity_log_description_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(config('activitylog.database_connection'))->table(config('activitylog.table_name'), function (Blueprint $table) {
            $table->dropIndex('activity_log_subject_index');
            $table->dropIndex('activity_log_causer_index');
            $table->dropIndex('activity_log_created_at_index');
            $table->dropIndex('activity_log_description_index');
        });
    }
};
