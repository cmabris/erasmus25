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
        Schema::table('translations', function (Blueprint $table) {
            // Index for language_id to optimize filtering by language
            $table->index('language_id', 'translations_language_id_index');

            // Index for field to optimize filtering and sorting by field
            $table->index('field', 'translations_field_index');

            // Index for created_at to optimize sorting by creation date
            $table->index('created_at', 'translations_created_at_index');

            // Composite index for common query pattern: translatable_type + language_id (for filtering)
            $table->index(['translatable_type', 'language_id'], 'translations_type_language_index');

            // Composite index for sorting: created_at + translatable_type (for sorting with type filter)
            $table->index(['created_at', 'translatable_type'], 'translations_created_type_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('translations', function (Blueprint $table) {
            $table->dropIndex('translations_language_id_index');
            $table->dropIndex('translations_field_index');
            $table->dropIndex('translations_created_at_index');
            $table->dropIndex('translations_type_language_index');
            $table->dropIndex('translations_created_type_index');
        });
    }
};
