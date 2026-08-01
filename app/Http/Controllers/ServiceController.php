<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Service;
use App\Models\SolarPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
   public function mainServices()
{
    $services = Service::whereNull('parent_id')->get();

    return response()->json(
        $services,
        200,
        [],
        JSON_UNESCAPED_UNICODE
    );
}

public function subServices($id)
{
    $services = Service::where('parent_id', $id)->get();

    return response()->json(
        $services,
        200,
        [],
        JSON_UNESCAPED_UNICODE
    );
}

 public function getDevices($serviceId)
{
    $devices = Device::where(
        'service_id',
        $serviceId
    )->get();

    return response()->json(
        $devices,
        200,
        [],
        JSON_UNESCAPED_UNICODE
    );
}


public function getSolarSolutions($installationRequestId)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    // التأكد أن الطلب يخص المستخدم
    $requestExists = DB::table('solar_installation_requests')
        ->where('id', $installationRequestId)
        ->where('user_id', $user->id)
        ->first();

    if (!$requestExists) {
        return response()->json([
            'message' => 'Installation request not found'
        ], 404);
    }

    // حساب الاستهلاك الخاص بهذا الطلب فقط
    $totalConsumption = DB::table('device_user')
        ->where('installation_request_id', $installationRequestId)
        ->sum('consumption');

    if ($totalConsumption == 0) {
        return response()->json([
            'message' => 'No consumption found'
        ], 404);
    }

    // جلب الباقات
    $packages = SolarPackage::all();

    // فلترة الباقات المناسبة
    $filtered = $packages->filter(function ($package) use ($totalConsumption) {
        return $package->capacity_watt >= $totalConsumption;
    });

    // ترتيب حسب السعر
    $sorted = $filtered->sortBy('price')->values();

    return response()->json([
        'installation_request_id' => $installationRequestId,
        'user_consumption' => $totalConsumption,
        'recommended_packages' => $sorted
    ]);
}


public function getSolarPackages(Request $request)
{
    $user = $request->user();

    if (!$user || $user->role !== 'super_admin') {
        return response()->json([
            'message' => 'Unauthorized'
        ], 403);
    }

    $packages = SolarPackage::select(
        'id',
        'name',
        'Inverter_watt',
        'battery',
        'Panels',
        'Price',
        'Capacity_watt'
    )->get();

    return response()->json(
        $packages,
        200,
        [],
        JSON_UNESCAPED_UNICODE
    );



}
public function maintenanceItems()
{
    // 🔥 نجيب "طلب صيانة"
    $maintenance = Service::where('title', 'طلب صيانة')->first();

    if (!$maintenance) {
        return response()->json([
            'message' => 'Maintenance service not found'
        ], 404);
    }

    // 🔥 نجيب الأبناء تبعه
    $items = Service::where('parent_id', $maintenance->id)->get();

    return response()->json([
        'parent' => $maintenance->title,
        'data' => $items
    ]);
}
public function industrialGeneratorServices()
{
    $parent = Service::where('title','خدمات الكهرباء الصناعية')->first();

    if (!$parent) {
        return response()->json([
            'message' => 'Industrial electricity service not found'
        ], 404);
    }

    $services = Service::where('parent_id', $parent->id)
        ->select('id', 'title')
        ->get();

    return response()->json([
        'message' => 'Industrial generator services retrieved successfully',
        'services' => $services
    ]);
}
}
