<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->string('reason')->nullable();
            $table->enum('type', ['annual', 'sick', 'personal', 'company', 'other'])->default('company');
            $table->boolean('is_paid')->default(true);
            $table->enum('status', ['scheduled', 'taken', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_holidays');
    }
};