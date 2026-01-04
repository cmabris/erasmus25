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
        Schema::table('document_categories', function (Blueprint $table) {
            // Add index on name for faster search queries
            $table->index('name', 'document_categories_name_index');

            // Add index on order for faster sorting queries
            $table->index('order', 'document_categories_order_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_categories', function (Blueprint $table) {
            $table->dropIndex('document_categories_name_index');
            $table->dropIndex('document_categories_order_index');
        });
    }
};
