<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('sync_type'); // 'auto', 'manual'
            $table->string('entity'); // 'attendance', 'employee', etc
            $table->date('sync_date_from');
            $table->date('sync_date_to');
            $table->string('status'); // 'pending', 'processing', 'completed', 'failed'
            $table->json('stats')->nullable(); // {created: 10, updated: 5, skipped: 2}
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['entity', 'status', 'created_at']);
        });

        // Add sync metadata to attendances table
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('source')->default('manual')->after('overtime'); // 'manual', 'api_sync'
            $table->timestamp('synced_at')->nullable()->after('source');
            $table->string('external_id')->nullable()->after('synced_at'); // ID from external system

            $table->index(['source', 'synced_at']);
            $table->index('external_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_sync_logs');

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['source', 'synced_at', 'external_id']);
        });
    }
};
