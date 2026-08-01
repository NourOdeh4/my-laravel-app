<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
  use App\Models\SolarRequest;
use App\Models\SolarRequestDevice;
use App\Models\SolarPackage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class SolarRequestController extends Controller
{


public function store(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    $request->validate([
        'installation_request_id' => 'required|exists:solar_installation_requests,id',
        'solar_package_id' => 'required|exists:solar_packages,id',
    ]);

    // التأكد أن طلب التركيب يخص هذا المستخدم
    $installationRequest = DB::table('solar_installation_requests')
        ->where('id', $request->installation_request_id)
        ->where('user_id', $user->id)
        ->first();

    if (!$installationRequest) {
        return response()->json([
            'message' => 'Installation request not found'
        ], 404);
    }

    // جلب الأجهزة الخاصة بهذا الطلب فقط
    $selectedDevices = DB::table('device_user')
        ->where('installation_request_id', $request->installation_request_id)
        ->get();

    if ($selectedDevices->isEmpty()) {
        return response()->json([
            'message' => 'No selected devices found'
        ], 400);
    }

    DB::beginTransaction();

    try {

        // إنشاء طلب تركيب الطاقة الشمسية
        $solarRequest = SolarRequest::create([
            'user_id' => $user->id,
            'solar_package_id' => $request->solar_package_id,
            'status' => 'pending'
        ]);

        // ربط الأجهزة بالطلب
        foreach ($selectedDevices as $device) {

            SolarRequestDevice::create([
                'solar_request_id' => $solarRequest->id,
                'device_user_id'   => $device->id,
                'working_hours'    => $device->hours,
            ]);
        }

        DB::commit();

        return response()->json([
            'message' => 'Request created successfully',
            'installation_request_id' => $request->installation_request_id,
            'request' => SolarRequest::with('solarPackage')->find($solarRequest->id)
        ], 201);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'message' => $e->getMessage()
        ], 500);
    }
}

public function updateStatus(Request $request, $id)
{
    $user = Auth::user();

    if (!$user || $user->role !== 'super_admin') {
        return response()->json([
            'message' => 'Unauthorized'
        ], 403);
    }

    $request->validate([
        'status' => 'required|in:accepted,rejected',
        'technician_name' => 'nullable|string',
        'technician_last_name' => 'nullable|string'
    ]);

    $solarRequest = SolarRequest::findOrFail($id);

    $solarRequest->status = $request->status;

    // 🔥 إذا تم القبول نجيب الفني بالاسم
    if ($request->status === 'accepted') {

        if (!$request->technician_name || !$request->technician_last_name) {
            return response()->json([
                'message' => 'Technician name and last name are required'
            ], 422);
        }

        $technician = DB::table('users')
            ->where('role', 'technician')
            ->where('name', $request->technician_name)
            ->where('last_name', $request->technician_last_name)
            ->first();

        if (!$technician) {
            return response()->json([
                'message' => 'Technician not found'
            ], 404);
        }

        $solarRequest->technician_id = $technician->id;
    }

    // ❌ إذا رفض نحذف الفني
    if ($request->status === 'rejected') {
        $solarRequest->technician_id = null;
    }

    $solarRequest->save();

    return response()->json([
        'message' => 'Request updated successfully',
        'data' => $solarRequest
    ]);
}
public function technicianRequests()
{
    $user = Auth::user();

    if ($user->role != 'technician') {
        return response()->json([
            'message' => 'Unauthorized'
        ], 403);
    }

    $requests = SolarRequest::with([
        'user',
        'solarPackage',
        'devices.device'
    ])
    ->where('technician_id', $user->id)
    ->get();

    return response()->json($requests);
}
public function show()
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    $requests = SolarRequest::with([
        'user',
        'technician',
        'solarPackage',
        'devices.device'
    ])
    ->where('user_id', $user->id)
    ->orderBy('id', 'desc')
    ->get();

    if ($requests->isEmpty()) {
        return response()->json([
            'message' => 'No requests found'
        ], 404);
    }

    return response()->json([
        'message' => 'Solar requests retrieved successfully',
        'requests' => $requests
    ]);
}
public function showSolarRequestById($id)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    $request = SolarRequest::with([
        'user',
        'technician',
        'solarPackage',
        'devices.device'
    ])
    ->where('id', $id)
    ->where('user_id', $user->id)
    ->first();

    if (!$request) {
        return response()->json([
            'message' => 'Solar request not found'
        ], 404);
    }

    return response()->json([
        'message' => 'Solar request retrieved successfully',
        'request' => $request
    ]);
}
public function getTechnicianProfile($id)
{
    $user = Auth::user();

    $solarRequest = SolarRequest::with('technician')
        ->findOrFail($id);

    // نتأكد أن الطلب يعود لهذا المستخدم
    if ($solarRequest->user_id != $user->id) {
        return response()->json([
            'message' => 'Unauthorized'
        ], 403);
    }

    if (!$solarRequest->technician) {
        return response()->json([
            'message' => 'No technician assigned yet'
        ], 404);
    }

    return response()->json([
        'technician' => [
            'id' => $solarRequest->technician->id,
            'name' => $solarRequest->technician->name,
            'last_name' => $solarRequest->technician->last_name,
            'email' => $solarRequest->technician->email,
            'phone' => $solarRequest->technician->phone,
            'address' => $solarRequest->technician->address,
            'avatar' => $solarRequest->technician->avatar,
        ]
    ]);
}
public function completeRequest($id)
{
    $user = Auth::user();

    if ($user->role !== 'technician') {
        return response()->json([
            'message' => 'Unauthorized'
        ], 403);
    }

    $request = SolarRequest::find($id);

    if (!$request) {
        return response()->json([
            'message' => 'Request not found'
        ], 404);
    }

    // 🔥 فقط الطلبات المخصصة له
    if ($request->technician_id !== $user->id) {
        return response()->json([
            'message' => 'Not your request'
        ], 403);
    }

    $request->status = 'completed';
    $request->save();

    return response()->json([
        'message' => 'Request marked as completed',
        'data' => $request
    ]);
}
public function adminSolarInstallationRequests(){$user = Auth::user();

if (!$user || $user->role !== 'super_admin') {
    return response()->json([
        'message' => 'Unauthorized'
    ], 403);
}

$requests = SolarRequest::with([
    'user',
    'technician',
    'solarPackage',
    'devices.device'
])
    ->latest()
    ->get();

return response()->json([
    'message' => 'All solar installation requests retrieved successfully',
    'requests' => $requests
]);

}
}
