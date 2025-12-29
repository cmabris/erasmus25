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
        Schema::table('call_phases', function (Blueprint $table) {
            // Index for deleted_at to optimize SoftDeletes queries
            $table->index('deleted_at', 'call_phases_deleted_at_index');

            // Index for order to optimize sorting queries
            $table->index('order', 'call_phases_order_index');

            // Index for phase_type to optimize filtering queries
            $table->index('phase_type', 'call_phases_phase_type_index');

            // Composite index for common query pattern: call_id + order (for sorting)
            $table->index(['call_id', 'order'], 'call_phases_call_id_order_index');

            // Composite index for filtering by call_id + phase_type
            $table->index(['call_id', 'phase_type'], 'call_phases_call_id_phase_type_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('call_phases', function (Blueprint $table) {
            $table->dropIndex('call_phases_deleted_at_index');
            $table->dropIndex('call_phases_order_index');
            $table->dropIndex('call_phases_phase_type_index');
            $table->dropIndex('call_phases_call_id_order_index');
            $table->dropIndex('call_phases_call_id_phase_type_index');
        });
    }
};
