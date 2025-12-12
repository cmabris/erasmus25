<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration adds the foreign key constraint to the media table.
     * It should be run after Laravel Media Library is installed and its migrations have been executed.
     */
    public function up(): void
    {
        // Only add the foreign key if the media table exists (Media Library is installed)
        if (Schema::hasTable('media')) {
            Schema::table('media_consents', function (Blueprint $table) {
                $table->foreign('media_id')
                    ->references('id')
                    ->on('media')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_consents', function (Blueprint $table) {
            $table->dropForeign(['media_id']);
        });
    }
};
