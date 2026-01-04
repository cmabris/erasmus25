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
        Schema::table('erasmus_events', function (Blueprint $table) {
            // Index for event_type to optimize filtering queries
            $table->index('event_type', 'erasmus_events_event_type_index');

            // Index for start_date to optimize date range queries and sorting
            $table->index('start_date', 'erasmus_events_start_date_index');

            // Index for end_date to optimize date range queries
            $table->index('end_date', 'erasmus_events_end_date_index');

            // Composite index for common query pattern: event_type + start_date (for filtering and sorting)
            $table->index(['event_type', 'start_date'], 'erasmus_events_event_type_start_date_index');

            // Composite index for date range queries: start_date + end_date
            $table->index(['start_date', 'end_date'], 'erasmus_events_start_end_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erasmus_events', function (Blueprint $table) {
            $table->dropIndex('erasmus_events_event_type_index');
            $table->dropIndex('erasmus_events_start_date_index');
            $table->dropIndex('erasmus_events_end_date_index');
            $table->dropIndex('erasmus_events_event_type_start_date_index');
            $table->dropIndex('erasmus_events_start_end_date_index');
        });
    }
};
