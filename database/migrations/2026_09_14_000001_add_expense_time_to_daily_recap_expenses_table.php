<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_recap_expenses', function (Blueprint $table) {
            $table->time('expense_time')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('daily_recap_expenses', function (Blueprint $table) {
            $table->dropColumn('expense_time');
        });
    }
};
