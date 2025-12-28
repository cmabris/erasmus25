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
        Schema::table('academic_years', function (Blueprint $table) {
            // Add index on is_current for faster queries when filtering current academic year
            $table->index('is_current', 'academic_years_is_current_index');

            // Add index on deleted_at for faster soft delete queries
            $table->index('deleted_at', 'academic_years_deleted_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropIndex('academic_years_is_current_index');
            $table->dropIndex('academic_years_deleted_at_index');
        });
    }
};
