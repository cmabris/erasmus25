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
            $table->softDeletes();

            // Add index on deleted_at for faster soft delete queries
            $table->index('deleted_at', 'erasmus_events_deleted_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erasmus_events', function (Blueprint $table) {
            $table->dropIndex('erasmus_events_deleted_at_index');
            $table->dropSoftDeletes();
        });
    }
};
