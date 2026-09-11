<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'gps_accuracy_in')) {
                $table->unsignedSmallInteger('gps_accuracy_in')->nullable()->after('longitude_in');
            }

            if (!Schema::hasColumn('attendances', 'gps_accuracy_out')) {
                $table->unsignedSmallInteger('gps_accuracy_out')->nullable()->after('longitude_out');
            }

            if (!Schema::hasColumn('attendances', 'location_recorded_at_in')) {
                $table->timestamp('location_recorded_at_in')->nullable()->after('gps_accuracy_out');
            }

            if (!Schema::hasColumn('attendances', 'location_recorded_at_out')) {
                $table->timestamp('location_recorded_at_out')->nullable()->after('location_recorded_at_in');
            }

            if (!Schema::hasColumn('attendances', 'device_fingerprint_in')) {
                $table->string('device_fingerprint_in', 64)->nullable()->after('location_recorded_at_out');
            }

            if (!Schema::hasColumn('attendances', 'device_fingerprint_out')) {
                $table->string('device_fingerprint_out', 64)->nullable()->after('device_fingerprint_in');
            }

            if (!Schema::hasColumn('attendances', 'timezone_in')) {
                $table->string('timezone_in', 64)->nullable()->after('device_fingerprint_out');
            }

            if (!Schema::hasColumn('attendances', 'timezone_out')) {
                $table->string('timezone_out', 64)->nullable()->after('timezone_in');
            }

            if (!Schema::hasColumn('attendances', 'client_time_offset_in')) {
                $table->integer('client_time_offset_in')->nullable()->after('timezone_out');
            }

            if (!Schema::hasColumn('attendances', 'client_time_offset_out')) {
                $table->integer('client_time_offset_out')->nullable()->after('client_time_offset_in');
            }

            if (!Schema::hasColumn('attendances', 'fraud_flags')) {
                $table->json('fraud_flags')->nullable()->after('client_time_offset_out');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $columns = [
                'gps_accuracy_in',
                'gps_accuracy_out',
                'location_recorded_at_in',
                'location_recorded_at_out',
                'device_fingerprint_in',
                'device_fingerprint_out',
                'timezone_in',
                'timezone_out',
                'client_time_offset_in',
                'client_time_offset_out',
                'fraud_flags',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('attendances', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
