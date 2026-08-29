<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Tambahkan kolom untuk auto checkout
            $table->boolean('is_auto_checkout')->default(false)->after('status');
            
            // Tambahkan kolom untuk mencatat waktu auto checkout
            $table->timestamp('auto_checkout_at')->nullable()->after('is_auto_checkout');
            
            // Ubah enum status untuk menambahkan auto_checkout
            // Note: MySQL tidak support modify enum langsung, jadi kita buat baru
            // Atau kita bisa menggunakan DB::statement untuk alter enum
            DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present', 'absent', 'late', 'half_day', 'leave', 'auto_checkout') DEFAULT 'absent'");
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['is_auto_checkout', 'auto_checkout_at']);
            DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present', 'absent', 'late', 'half_day', 'leave') DEFAULT 'absent'");
        });
    }
};