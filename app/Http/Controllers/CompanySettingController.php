<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class CompanySettingController extends Controller
{
    public function index(): Response
    {
        $office = CompanySetting::getOfficeLocation();
        $locations = CompanySetting::getAttendanceLocations();
        
        return Inertia::render('Owner/Settings/Index', [
            'office' => $office,
            'market' => [
                'latitude' => CompanySetting::get('market_latitude', ''),
                'longitude' => CompanySetting::get('market_longitude', ''),
                'radius' => CompanySetting::get('market_radius', $office['radius']),
                'address' => CompanySetting::get('market_address', ''),
            ],
            'locations' => $locations,
            'links' => [
                'updateOffice' => route('owner.settings.update-office'),
            ],
        ]);
    }

    public function updateOffice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|numeric|min:10|max:5000',
            'address' => 'nullable|string|max:500',
            'market_latitude' => 'nullable|numeric|between:-90,90',
            'market_longitude' => 'nullable|numeric|between:-180,180',
            'market_radius' => 'nullable|numeric|min:10|max:5000',
            'market_address' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        CompanySetting::set('office_latitude', $request->latitude);
        CompanySetting::set('office_longitude', $request->longitude);
        CompanySetting::set('office_radius', $request->radius, 'number');
        CompanySetting::set('office_address', $request->address ?? 'Alamat kantor belum diatur');
        CompanySetting::set('market_latitude', $request->market_latitude);
        CompanySetting::set('market_longitude', $request->market_longitude);
        CompanySetting::set('market_radius', $request->market_radius ?: $request->radius, 'number');
        CompanySetting::set('market_address', $request->market_address ?? 'Alamat pasar belum diatur');

        return redirect()->route('owner.settings.index')
            ->with('success', 'Pengaturan lokasi absensi berhasil diperbarui!');
    }

    // Get office location for map
    public function getOfficeLocation()
    {
        $office = CompanySetting::getOfficeLocation();
        return response()->json($office);
    }

    // Validate location
    public function validateLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $nearestLocation = CompanySetting::findNearestAttendanceLocation(
            $request->latitude,
            $request->longitude
        );

        $office = CompanySetting::getOfficeLocation();
        $distance = $nearestLocation ? $nearestLocation['distance'] : 0;

        return response()->json([
            'is_within_radius' => (bool) ($nearestLocation['is_within_radius'] ?? false),
            'distance' => round($distance),
            'radius' => $nearestLocation['radius'] ?? $office['radius'],
            'location_name' => $nearestLocation['name'] ?? 'Lokasi absensi',
            'nearest_location' => $nearestLocation,
            'office_location' => $office,
            'attendance_locations' => CompanySetting::getAttendanceLocations(),
            'user_location' => [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude
            ]
        ]);
    }
}
