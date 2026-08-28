<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanySettingController extends Controller
{
    public function index()
    {
        $settings = CompanySetting::all();
        $office = CompanySetting::getOfficeLocation();
        
        return view('owner.settings.index', compact('settings', 'office'));
    }

    public function updateOffice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|numeric|min:10|max:5000',
            'address' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        CompanySetting::set('office_latitude', $request->latitude);
        CompanySetting::set('office_longitude', $request->longitude);
        CompanySetting::set('office_radius', $request->radius, 'number');
        CompanySetting::set('office_address', $request->address ?? 'Alamat kantor belum diatur');

        return redirect()->route('owner.settings.index')
            ->with('success', 'Pengaturan lokasi kantor berhasil diperbarui!');
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

        $isWithin = CompanySetting::isWithinOfficeRadius(
            $request->latitude,
            $request->longitude
        );

        $office = CompanySetting::getOfficeLocation();
        $distance = CompanySetting::calculateDistance(
            (float) $request->latitude,
            (float) $request->longitude,
            (float) $office['latitude'],
            (float) $office['longitude']
        );

        return response()->json([
            'is_within_radius' => $isWithin,
            'distance' => round($distance),
            'radius' => $office['radius'],
            'office_location' => $office,
            'user_location' => [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude
            ]
        ]);
    }
}