<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('company_settings')->insert([
            [
                'key' => 'office_latitude',
                'value' => '-8.180305',
                'type' => 'string',
                'description' => 'Latitude lokasi kantor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'office_longitude',
                'value' => '113.725896',
                'type' => 'string',
                'description' => 'Longitude lokasi kantor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'office_radius',
                'value' => '100',
                'type' => 'number',
                'description' => 'Radius absensi dalam meter',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'office_address',
                'value' => 'Jl. Moh. Yamin, Karanganyar, Tegal Besar, Kec. Kaliwates, Kab. Jember',
                'type' => 'string',
                'description' => 'Alamat lengkap kantor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};