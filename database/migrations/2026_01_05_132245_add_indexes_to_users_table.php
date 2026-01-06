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
        Schema::table('users', function (Blueprint $table) {
            // Index for deleted_at to optimize SoftDeletes queries
            $table->index('deleted_at', 'users_deleted_at_index');

            // Index for name to optimize search queries
            $table->index('name', 'users_name_index');

            // Composite index for common query pattern: deleted_at + name (for sorting and filtering)
            $table->index(['deleted_at', 'name'], 'users_deleted_at_name_index');

            // Composite index for search queries: name + email
            // Note: email already has a unique index, but this composite helps with LIKE queries
            $table->index(['name', 'email'], 'users_name_email_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_deleted_at_index');
            $table->dropIndex('users_name_index');
            $table->dropIndex('users_deleted_at_name_index');
            $table->dropIndex('users_name_email_index');
        });
    }
};
