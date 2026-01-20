<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Performance indexes for query optimization (Paso 3.9.1 - Fase 4).
     * These indexes improve performance for common filtering and sorting operations.
     */
    public function up(): void
    {
        // Calls table indexes
        Schema::table('calls', function (Blueprint $table) {
            // Index for soft deletes filtering (used in all queries with SoftDeletes)
            $table->index('deleted_at', 'calls_deleted_at_index');

            // Index for type filtering (alumnado/personal)
            $table->index('type', 'calls_type_index');

            // Index for modality filtering
            $table->index('modality', 'calls_modality_index');
        });

        // Resolutions table indexes
        Schema::table('resolutions', function (Blueprint $table) {
            // Index for published_at filtering (public resolutions)
            $table->index('published_at', 'resolutions_published_at_index');

            // Composite index for call + published (used in Public\Calls\Show)
            $table->index(['call_id', 'published_at'], 'resolutions_call_published_index');
        });

        // Programs table indexes
        Schema::table('programs', function (Blueprint $table) {
            // Index for is_active filtering (used in getCachedActive and filters)
            $table->index('is_active', 'programs_is_active_index');

            // Composite index for active programs ordered (used in dropdowns)
            $table->index(['is_active', 'order'], 'programs_active_order_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->dropIndex('calls_deleted_at_index');
            $table->dropIndex('calls_type_index');
            $table->dropIndex('calls_modality_index');
        });

        Schema::table('resolutions', function (Blueprint $table) {
            $table->dropIndex('resolutions_published_at_index');
            $table->dropIndex('resolutions_call_published_index');
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->dropIndex('programs_is_active_index');
            $table->dropIndex('programs_active_order_index');
        });
    }
};
