<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_recaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('recap_date');
            $table->unsignedBigInteger('total_expense_amount')->default(0);
            $table->unsignedBigInteger('remaining_cash_amount')->default(0);
            $table->unsignedBigInteger('total_qris_amount')->default(0);
            $table->unsignedBigInteger('capital_amount')->default(0);
            $table->timestamps();

            $table->unique(['employee_id', 'recap_date']);
            $table->index('recap_date');
        });

        Schema::create('daily_recap_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_recap_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->unsignedBigInteger('amount')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('daily_recap_qris_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_recap_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('amount')->default(0);
            $table->string('evidence_path')->nullable();
            $table->string('evidence_original_name')->nullable();
            $table->string('evidence_mime_type')->nullable();
            $table->unsignedInteger('evidence_size')->nullable();
            $table->timestamp('evidence_uploaded_at')->nullable();
            $table->timestamp('evidence_expires_at')->nullable();
            $table->timestamp('evidence_deleted_at')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('evidence_expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_recap_qris_transactions');
        Schema::dropIfExists('daily_recap_expenses');
        Schema::dropIfExists('daily_recaps');
    }
};
