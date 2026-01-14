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
        Schema::table('newsletter_subscriptions', function (Blueprint $table) {
            // Índice para filtros de verificación
            $table->index('verified_at', 'newsletter_subscriptions_verified_at_index');

            // Índice para ordenación por fecha de suscripción
            $table->index('subscribed_at', 'newsletter_subscriptions_subscribed_at_index');

            // Índice compuesto para consultas frecuentes: estado + verificación
            $table->index(['is_active', 'verified_at'], 'newsletter_subscriptions_status_verification_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('newsletter_subscriptions', function (Blueprint $table) {
            $table->dropIndex('newsletter_subscriptions_verified_at_index');
            $table->dropIndex('newsletter_subscriptions_subscribed_at_index');
            $table->dropIndex('newsletter_subscriptions_status_verification_index');
        });
    }
};
