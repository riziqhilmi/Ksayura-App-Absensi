<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'type', 'description'];

    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set($key, $value, $type = 'string', $description = null)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'description' => $description]
        );
    }

    // Get office location
    public static function getOfficeLocation()
    {
        return [
            'latitude' => self::get('office_latitude', '-8.180305'),
            'longitude' => self::get('office_longitude', '113.725896'),
            'radius' => (float) self::get('office_radius', 100),
            'address' => self::get('office_address', 'Alamat kantor belum diatur'),
        ];
    }

    // Check if location is within office radius
    public static function isWithinOfficeRadius($latitude, $longitude)
    {
        $office = self::getOfficeLocation();
        $distance = self::calculateDistance(
            (float) $latitude,
            (float) $longitude,
            (float) $office['latitude'],
            (float) $office['longitude']
        );
        
        return $distance <= $office['radius'];
    }

    // Calculate distance between two points using Haversine formula
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meters
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) + 
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c; // returns distance in meters
    }
}