<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_recaps', function (Blueprint $table) {
            $table->timestamp('expense_opened_at')->nullable()->after('capital_amount');
            $table->timestamp('expense_closed_at')->nullable()->after('expense_opened_at');
        });
    }

    public function down(): void
    {
        Schema::table('daily_recaps', function (Blueprint $table) {
            $table->dropColumn(['expense_opened_at', 'expense_closed_at']);
        });
    }
};
