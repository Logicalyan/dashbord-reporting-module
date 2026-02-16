<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_api_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('access_token');
            $table->timestamp('expired_at');
            $table->timestamps();

            $table->index(['user_id', 'expired_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_api_tokens');
    }
};
