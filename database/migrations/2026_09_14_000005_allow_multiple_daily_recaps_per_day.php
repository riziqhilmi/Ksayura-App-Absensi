<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_recaps', function (Blueprint $table) {
            $table->index('employee_id');
            $table->dropUnique(['employee_id', 'recap_date']);
            $table->index(['employee_id', 'recap_date']);
        });
    }

    public function down(): void
    {
        Schema::table('daily_recaps', function (Blueprint $table) {
            $table->dropIndex(['employee_id', 'recap_date']);
            $table->unique(['employee_id', 'recap_date']);
            $table->dropIndex(['employee_id']);
        });
    }
};
