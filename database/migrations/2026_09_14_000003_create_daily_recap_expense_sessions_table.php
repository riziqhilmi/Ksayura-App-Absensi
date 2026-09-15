<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_recap_expense_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_recap_id')->constrained()->onDelete('cascade');
            $table->date('recap_date');
            $table->foreignId('opened_by_employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('closed_by_employee_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['recap_date', 'opened_at']);
        });

        Schema::table('daily_recap_expenses', function (Blueprint $table) {
            $table->foreignId('daily_recap_expense_session_id')
                ->nullable()
                ->after('daily_recap_id')
                ->constrained('daily_recap_expense_sessions')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('daily_recap_expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('daily_recap_expense_session_id');
        });

        Schema::dropIfExists('daily_recap_expense_sessions');
    }
};
