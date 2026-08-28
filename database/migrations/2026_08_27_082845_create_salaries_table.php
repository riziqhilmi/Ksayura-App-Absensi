<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('period'); // misal: 2024-01
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('base_salary', 15, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('overtime_pay', 15, 2)->default(0);
            $table->decimal('attendance_bonus', 15, 2)->default(0);
            $table->decimal('performance_bonus', 15, 2)->default(0);
            $table->decimal('deductions', 15, 2)->default(0);
            $table->decimal('total_salary', 15, 2)->default(0);
            $table->integer('working_days')->default(0);
            $table->integer('present_days')->default(0);
            $table->integer('late_days')->default(0);
            $table->integer('absent_days')->default(0);
            $table->integer('leave_days')->default(0);
            $table->enum('status', ['draft', 'calculated', 'paid'])->default('draft');
            $table->date('paid_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};