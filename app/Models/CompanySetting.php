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
        $office = [
            'key' => 'office',
            'name' => 'Kantor',
            'latitude' => self::get('office_latitude', '-8.180305'),
            'longitude' => self::get('office_longitude', '113.725896'),
            'radius' => (float) self::get('office_radius', 100),
            'address' => self::get('office_address', 'Alamat kantor belum diatur'),
        ];

        return array_merge($office, [
            'locations' => self::getAttendanceLocations(),
        ]);
    }

    public static function getAttendanceLocations()
    {
        $locations = [
            [
                'key' => 'office',
                'name' => 'Kantor',
                'latitude' => self::get('office_latitude', '-8.180305'),
                'longitude' => self::get('office_longitude', '113.725896'),
                'radius' => (float) self::get('office_radius', 100),
                'address' => self::get('office_address', 'Alamat kantor belum diatur'),
            ],
        ];

        $marketLatitude = self::get('market_latitude');
        $marketLongitude = self::get('market_longitude');

        if ($marketLatitude !== null && $marketLongitude !== null && $marketLatitude !== '' && $marketLongitude !== '') {
            $locations[] = [
                'key' => 'market',
                'name' => 'Pasar',
                'latitude' => $marketLatitude,
                'longitude' => $marketLongitude,
                'radius' => (float) self::get('market_radius', self::get('office_radius', 100)),
                'address' => self::get('market_address', 'Alamat pasar belum diatur'),
            ];
        }

        return $locations;
    }

    public static function findNearestAttendanceLocation($latitude, $longitude)
    {
        $nearest = null;

        foreach (self::getAttendanceLocations() as $location) {
            $distance = self::calculateDistance(
                (float) $latitude,
                (float) $longitude,
                (float) $location['latitude'],
                (float) $location['longitude']
            );

            $candidate = array_merge($location, [
                'distance' => $distance,
                'is_within_radius' => $distance <= (float) $location['radius'],
            ]);

            if (!$nearest || $candidate['distance'] < $nearest['distance']) {
                $nearest = $candidate;
            }
        }

        return $nearest;
    }

    // Check if location is within any attendance radius
    public static function isWithinOfficeRadius($latitude, $longitude)
    {
        $nearest = self::findNearestAttendanceLocation($latitude, $longitude);

        return $nearest ? $nearest['is_within_radius'] : false;
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
