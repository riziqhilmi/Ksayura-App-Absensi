<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->decimal('daily_rate', 15, 2)->default(0)->after('end_date');
            $table->decimal('paid_days', 5, 2)->default(0)->after('daily_rate');
            $table->integer('holiday_days')->default(0)->after('leave_days');
        });
    }

    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn(['daily_rate', 'paid_days', 'holiday_days']);
        });
    }
};
