<?php

namespace App\Http\Controllers;
use App\Models\Generator;
use App\Models\GeneratorRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class GeneratorController extends Controller
{
    public function getSuitableGenerators($installationRequestId)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    // التأكد أن الطلب يخص المستخدم
    $request = DB::table('solar_installation_requests')
        ->where('id', $installationRequestId)
        ->where('user_id', $user->id)
        ->first();

    if (!$request) {
        return response()->json([
            'message' => 'Installation request not found'
        ], 404);
    }

    // حساب الاستهلاك
    $totalConsumption = DB::table('device_user')
        ->where('installation_request_id', $installationRequestId)
        ->sum('consumption');

    if ($totalConsumption == 0) {
        return response()->json([
            'message' => 'No consumption found'
        ], 404);
    }

    $generators = Generator::where(
        'capacity_watt',
        '>=',
        $totalConsumption
    )
    ->orderBy('capacity_watt')
    ->get();

    return response()->json([
        'installation_request_id' => $installationRequestId,
        'total_consumption' => $totalConsumption,
        'generators' => $generators
    ]);
}
public function storeGeneratorRequest(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ],401);
    }

    $request->validate([
        'installation_request_id'=>'required|exists:solar_installation_requests,id',
        'generator_id'=>'required|exists:generators,id'
    ]);

    // نتأكد أن الطلب يخص المستخدم
    $installation = DB::table('solar_installation_requests')
        ->where('id',$request->installation_request_id)
        ->where('user_id',$user->id)
        ->first();

    if(!$installation){
        return response()->json([
            'message'=>'Installation request not found'
        ],404);
    }

    $generatorRequest = GeneratorRequest::create([

        'user_id'=>$user->id,

        'installation_request_id'=>$request->installation_request_id,

        'generator_id'=>$request->generator_id,

        'status'=>'pending'

    ]);

    return response()->json([

        'message'=>'Generator request created successfully',

        'request'=>$generatorRequest

    ],201);

}
public function showGeneratorRequest($id)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    $request = GeneratorRequest::with([
        'generator',
        'technician'
    ])
    ->where('id', $id)
    ->where('user_id', $user->id)
    ->first();

    if (!$request) {
        return response()->json([
            'message' => 'Generator request not found'
        ], 404);
    }

    return response()->json([
        'message' => 'Generator request retrieved successfully',
        'request' => $request
    ]);
}
public function myGeneratorRequests()
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    $requests = GeneratorRequest::with([
        'generator',
        'technician'
    ])
    ->where('user_id', $user->id)
    ->latest()
    ->get();

    return response()->json([
        'message' => 'Generator requests retrieved successfully',
        'requests' => $requests
    ]);
}
public function adminGeneratorRequests()
{
    $user = Auth::user();

    if (!$user || $user->role !== 'super_admin') {
        return response()->json([
            'message' => 'Unauthorized'
        ], 403);
    }

    $requests = GeneratorRequest::with([
        'user',
        'generator',
        'technician'
    ])
    ->latest()
    ->get();

    return response()->json([
        'message' => 'Generator requests retrieved successfully',
        'requests' => $requests
    ]);
}
public function updateGeneratorRequestStatus(Request $request, $id)
{
    $user = Auth::user();

    if (!$user || $user->role != 'super_admin') {
        return response()->json([
            'message' => 'Unauthorized'
        ], 403);
    }

    $request->validate([
        'status' => 'required|in:accepted,rejected',
        'technician_name' => 'nullable|string',
        'technician_last_name' => 'nullable|string',
    ]);

    $generatorRequest = GeneratorRequest::findOrFail($id);

    $generatorRequest->status = $request->status;

    if ($request->status == 'accepted') {

        if (!$request->technician_name || !$request->technician_last_name) {
            return response()->json([
                'message' => 'Technician name and last name are required'
            ], 422);
        }

        $technician = User::where('role', 'technician')
            ->where('name', $request->technician_name)
            ->where('last_name', $request->technician_last_name)
            ->first();

        if (!$technician) {
            return response()->json([
                'message' => 'Technician not found'
            ], 404);
        }

        $generatorRequest->technician_id = $technician->id;
    }

    if ($request->status == 'rejected') {
        $generatorRequest->technician_id = null;
    }

    $generatorRequest->save();

    return response()->json([
        'message' => 'Generator request updated successfully',
        'request' => $generatorRequest
    ]);
}
public function generatorTechnicianProfile($id)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    $request = GeneratorRequest::with('technician')
        ->where('id', $id)
        ->where('user_id', $user->id)
        ->first();

    if (!$request) {
        return response()->json([
            'message' => 'Request not found'
        ], 404);
    }

    if (!$request->technician) {
        return response()->json([
            'message' => 'No technician assigned yet'
        ], 404);
    }

    return response()->json([
        'technician' => $request->technician
    ]);
}
public function technicianGeneratorRequests()
{
    $user = Auth::user();

    if (!$user || $user->role != 'technician') {
        return response()->json([
            'message' => 'Unauthorized'
        ], 403);
    }

    $requests = GeneratorRequest::with([
        'user',
        'generator'
    ])
    ->where('technician_id', $user->id)
    ->latest()
    ->get();

    return response()->json([
        'requests' => $requests
    ]);
}
public function completeGeneratorRequest($id)
{
    $user = Auth::user();

    if (!$user || $user->role != 'technician') {
        return response()->json([
            'message' => 'Unauthorized'
        ], 403);
    }

    $request = GeneratorRequest::where('id', $id)
        ->where('technician_id', $user->id)
        ->first();

    if (!$request) {
        return response()->json([
            'message' => 'Request not found'
        ], 404);
    }

    $request->status = 'completed';
    $request->save();

    return response()->json([
        'message' => 'Generator request completed successfully'
    ]);
}
public function deleteGeneratorRequest($id)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    $request = GeneratorRequest::where('id', $id)
        ->where('user_id', $user->id)
        ->where('status', 'pending')
        ->first();

    if (!$request) {
        return response()->json([
            'message' => 'Request not found or cannot be deleted'
        ], 404);
    }

    $request->delete();

    return response()->json([
        'message' => 'Generator request deleted successfully'
    ]);
}

}
