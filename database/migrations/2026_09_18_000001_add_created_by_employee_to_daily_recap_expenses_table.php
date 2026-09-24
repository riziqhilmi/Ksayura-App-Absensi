<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_recap_expenses', function (Blueprint $table) {
            $table->foreignId('created_by_employee_id')
                ->nullable()
                ->after('daily_recap_expense_session_id')
                ->constrained('employees')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('daily_recap_expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by_employee_id');
        });
    }
};
