<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use App\Services\OpenRouterService;

class DeviceController extends Controller
{
    // جلب الأجهزة
    public function index()
    {
        return response()->json(Device::all());
    }
public function selectDevices(Request $request, OpenRouterService $ai)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    $request->validate([
        'service_id' => 'nullable|exists:services,id',
        'devices' => 'required|array|min:1',
        'devices.*.name' => 'required|string',
        'devices.*.hours' => 'required|integer|min:1|max:24',
    ]);

    // إنشاء طلب تركيب جديد تلقائياً
    $installationRequestId = DB::table('solar_installation_requests')->insertGetId([
        'user_id' => $user->id,
        'service_id' => $request->service_id,
        'status' => 'pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $result = [];
    $totalConsumption = 0;

    foreach ($request->devices as $item) {

        // تحليل الجهاز بواسطة AI
        $aiResult = $ai->getDevicePower($item['name']);

        if (
            !$aiResult ||
            !is_array($aiResult) ||
            !isset($aiResult['power'])
        ) {
            return response()->json([
                'message' => 'AI failed to resolve device',
                'input' => $item['name'],
                'debug' => $aiResult
            ], 422);
        }

        $title = $aiResult['device'];
        $watt = (int) $aiResult['power'];

        // التحقق من الاستطاعة
        if ($watt < 1 || $watt > 5000) {
            return response()->json([
                'message' => 'Invalid watt detected',
                'device' => $title,
                'watt' => $watt
            ], 422);
        }

        // حساب الاستهلاك
        $consumption = $watt * $item['hours'];
        $totalConsumption += $consumption;

        // تخزين الجهاز وربطه بطلب التركيب
        DB::table('device_user')->insert([
            'user_id' => $user->id,
            'service_id' => $request->service_id,
            'installation_request_id' => $installationRequestId,
            'title' => $title,
            'hours' => $item['hours'],
            'watt_per_hour' => $watt,
            'consumption' => $consumption,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result[] = [
            'title' => $title,
            'hours' => $item['hours'],
            'watt_per_hour' => $watt,
            'consumption' => $consumption,
        ];
    }

    return response()->json([
        'message' => 'Saved successfully',
        'installation_request_id' => $installationRequestId,
        'user_id' => $user->id,
        'service_id' => $request->service_id,
        'selected_devices' => $result,
        'total_consumption_watt' => $totalConsumption
    ]);
}

public function deleteUserInstallationRequest($id)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    // التأكد أن الطلب موجود ويخص المستخدم وحالته pending
    $installationRequest = DB::table('solar_installation_requests')
        ->where('id', $id)
        ->where('user_id', $user->id)
        ->where('status', 'pending')
        ->first();

    if (!$installationRequest) {
        return response()->json([
            'message' => 'Request not found or cannot be deleted'
        ], 404);
    }

    DB::beginTransaction();

    try {

<<<<<<< HEAD
        // حذف الأجهزة المرتبطة بالطلب
=======
        // حذف الأجهزة المرتبطة بهذا الطلب فقط
>>>>>>> 1f07254a9c81854a5a6b734cb8f37e452278f2e7
        DB::table('device_user')
            ->where('installation_request_id', $id)
            ->delete();

<<<<<<< HEAD
        // حذف الطلب النهائي إذا كان موجوداً
        DB::table('solar_requests')
            ->where('installation_request_id', $id)
            ->delete();

        // حذف طلب التركيب
=======
        // حذف طلب التركيب نفسه
>>>>>>> 1f07254a9c81854a5a6b734cb8f37e452278f2e7
        DB::table('solar_installation_requests')
            ->where('id', $id)
            ->delete();

        DB::commit();

        return response()->json([
            'message' => 'Installation request deleted successfully'
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'message' => $e->getMessage()
        ], 500);
    }
}
public function updateUserDevices(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'Unauthenticated'], 401);
    }

    $request->validate([
        'devices' => 'required|array|min:1',
        'devices.*.id' => 'required|exists:devices,id',
        'devices.*.hours' => 'nullable|integer|min:1|max:24',
        'devices.*.action' => 'required|in:add,update,delete',
    ]);

    foreach ($request->devices as $item) {

        $deviceId = $item['id'];

        //  حذف جهاز من الطلب
        if ($item['action'] === 'delete') {
            DB::table('device_user')
                ->where('user_id', $user->id)
                ->where('device_id', $deviceId)
                ->delete();

            continue;
        }

        $device = Device::find($deviceId);

        $watt = match ($device->title) {
            'براد' => 200,
            'غسالة' => 1400,
            'مكيف' => 2500,
            'انارة' => 500,
            'ادوات كهربائية بسيطة' => 2000,
            default => 0,
        };

        // ➕ إضافة جهاز جديد
        if ($item['action'] === 'add') {

            DB::table('device_user')->insert([
                'user_id' => $user->id,
                'device_id' => $deviceId,
                'hours' => $item['hours'],
                'watt_per_hour' => $watt,
                'consumption' => $watt * $item['hours'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ✏️ تعديل جهاز موجود
        if ($item['action'] === 'update') {

            DB::table('device_user')
                ->where('user_id', $user->id)
                ->where('device_id', $deviceId)
                ->update([
                    'hours' => $item['hours'],
                    'consumption' => $watt * $item['hours'],
                    'updated_at' => now(),
                ]);
        }
    }

    // 🔥 إعادة حساب الكل
    $all = DB::table('device_user')
        ->where('user_id', $user->id)
        ->get();

    $total = 0;

    foreach ($all as $row) {
        $total += $row->watt_per_hour * $row->hours;
    }

    return response()->json([
        'user_id' => $user->id,
        'devices' => $all,
        'total_consumption_watt' => $total,
        'message' => 'Updated successfully (add/update/delete supported)'
    ]);
}public function addIndustrialGeneratorDevices(Request $request, OpenRouterService $openRouterService)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    $request->validate([
        'devices' => 'required|array|min:1',
        'devices.*.device_name' => 'required|string|max:255',
        'devices.*.hours' => 'required|integer|min:1|max:24',
    ]);

    $service = Service::where('title', 'تركيب مولدات صناعية')->first();

    if (!$service) {
        return response()->json([
            'message' => 'Service not found'
        ], 404);
    }

    // إنشاء طلب جديد تلقائياً
    $installationRequestId = DB::table('solar_installation_requests')->insertGetId([
        'user_id' => $user->id,
        'service_id' => $service->id,
        'status' => 'pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $results = [];
    $totalConsumption = 0;

    foreach ($request->devices as $item) {

        $aiResult = $openRouterService->getDevicePower($item['device_name']);

        if (isset($aiResult['error']) && $aiResult['error'] === true) {
            return response()->json([
                'message' => 'AI error for device: ' . $item['device_name'],
                'details' => $aiResult
            ], 422);
        }

        $wattPerHour = (int) $aiResult['power'];
        $consumption = $wattPerHour * $item['hours'];

        DB::table('device_user')->insert([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'installation_request_id' => $installationRequestId,
            'title' => $aiResult['device'] ?? $item['device_name'],
            'hours' => $item['hours'],
            'watt_per_hour' => $wattPerHour,
            'consumption' => $consumption,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $results[] = [
            'name' => $aiResult['device'] ?? $item['device_name'],
            'watt_per_hour' => $wattPerHour,
            'hours' => $item['hours'],
            'consumption' => $consumption,
        ];

        $totalConsumption += $consumption;
    }

    return response()->json([
        'message' => 'Devices added successfully',
        'installation_request_id' => $installationRequestId,
        'devices' => $results,
        'total_consumption' => $totalConsumption
    ], 201);
}
}
