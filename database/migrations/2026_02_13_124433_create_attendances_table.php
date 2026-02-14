<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->enum('status', ['Present','Absent','Late','Remote'])->default('Present');
            $table->decimal('hours', 5, 2)->default(0); // total work hours
            $table->decimal('overtime', 5, 2)->default(0); // overtime hours
            $table->timestamps();

            $table->unique(['employee_id', 'date']); // prevent duplicate
            $table->index(['date','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
