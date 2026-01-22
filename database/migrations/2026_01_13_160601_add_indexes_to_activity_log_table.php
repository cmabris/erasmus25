<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            // Para MySQL: especificar longitud para columnas TEXT
            // Para SQLite: no se puede especificar longitud, se omite el índice en description
            $connection = DB::connection(config('activitylog.database_connection'));
            $driver = $connection->getDriverName();
            $tableName = config('activitylog.table_name');

            if ($driver === 'mysql') {
                try {
                    $connection->statement("ALTER TABLE `{$tableName}` ADD INDEX `activity_log_description_index` (`description`(255))");
                } catch (\Exception $e) {
                    // Si el índice ya existe, ignorar el error
                    if (str_contains($e->getMessage(), 'Duplicate key name')) {
                        // Índice ya existe, continuar
                    } else {
                        throw $e;
                    }
                }
            }
            // SQLite no soporta índices en columnas TEXT con longitud, se omite
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
