<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider'); // 'hr_system', 'payroll_system', etc
            $table->string('name'); // User-friendly name
            $table->string('status')->default('disconnected'); // 'connected', 'disconnected', 'error'
            $table->string('api_url');
            $table->string('api_email');
            $table->text('api_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->json('sync_settings')->nullable(); // Auto-sync preferences
            $table->json('metadata')->nullable(); // Extra info (company_id, etc)
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'provider']);
            $table->index(['status', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_integrations');
    }
};
