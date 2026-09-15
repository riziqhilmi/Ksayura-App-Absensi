<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_recap_shift_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('recap_date')->unique();
            $table->unsignedBigInteger('capital_amount')->default(0);
            $table->unsignedBigInteger('remaining_cash_amount')->default(0);
            $table->foreignId('updated_by_employee_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_recap_shift_summaries');
    }
};
