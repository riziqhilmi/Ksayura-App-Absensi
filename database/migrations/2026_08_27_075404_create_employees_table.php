<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('employee_code')->unique();
            $table->string('position')->nullable();
            $table->decimal('base_salary', 15, 2)->default(0);
            $table->enum('salary_type', ['daily', 'weekly', 'monthly'])->default('monthly');
            $table->decimal('daily_rate', 15, 2)->nullable();
            $table->decimal('hourly_rate', 15, 2)->nullable();
            $table->enum('status', ['active', 'inactive', 'resigned'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};