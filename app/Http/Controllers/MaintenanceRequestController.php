<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaintenanceRequestController extends Controller
{
    public function show($id)
{
    $request = MaintenanceRequest::with([
        'user',
        'service'
    ])->find($id);

    if (!$request) {
        return response()->json([
            'message' => 'Request not found'
        ], 404);
    }

    return response()->json([
        'data' => $request
    ]);
}
public function store(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    $request->validate([
        'problem_description' => 'required|string',
        'location' => 'required|string',
        'priority' => 'required|in:urgent,normal,low',
        'damaged_panels_count' => 'nullable|integer',
    ]);

    $service = Service::where('title', 'ألواح طاقة شمسية')->first();

    if (!$service) {
        return response()->json([
            'message' => 'Service not found in system'
        ], 404);
    }

    $requestData = [
        'user_id' => $user->id,
        'service_id' => $service->id,
        'problem_description' => $request->problem_description,
        'location' => $request->location,
        'priority' => $request->priority,
        'status' => 'pending',
        'created_at' => now(),
        'updated_at' => now(),
    ];

    if ($request->has('damaged_panels_count')) {
        $requestData['damaged_panels_count'] = $request->damaged_panels_count;
    }

    // إنشاء الطلب وإرجاع رقم الطلب
    $maintenanceRequestId = DB::table('maintenance_requests')
        ->insertGetId($requestData);

    return response()->json([
        'message' => 'Request created successfully',
        'request_id' => $maintenanceRequestId,
        'user_id' => $user->id,
        'service' => $service->title,
        'status' => 'pending'
    ], 201);
}
public function updateStatus(Request $request, $id) { $user = Auth::user();
if (!$user || $user->role !== 'super_admin') { return response()->json([ 'message' => 'Unauthorized' ], 403); } $request->validate([ 'status' => 'required|in:accepted,rejected', 'technician_name' => 'required_if:status,accepted|string|max:255', 'technician_last_name' => 'required_if:status,accepted|string|max:255', ]); $maintenanceRequest = MaintenanceRequest::find($id); if (!$maintenanceRequest) { return response()->json([ 'message' => 'Maintenance request not found' ], 404); } if ($request->status === 'rejected') { $maintenanceRequest->update([ 'status' => 'rejected', 'technician_id' => null, ]); return response()->json([ 'message' => 'Request rejected successfully', 'request_id' => $maintenanceRequest->id, 'status' => $maintenanceRequest->status, ]); } $technician = User::where('role', 'technician') ->where('name', $request->technician_name) ->where('last_name', $request->technician_last_name) ->first(); if (!$technician) { return response()->json([ 'message' => 'Technician not found' ], 404); } $maintenanceRequest->update([ 'status' => 'accepted', 'technician_id' => $technician->id, ]); return response()->json([ 'message' => 'Request accepted and technician assigned successfully', 'request_id' => $maintenanceRequest->id, 'status' => $maintenanceRequest->status, 'technician' => [ 'id' => $technician->id, 'name' => $technician->name, 'last_name' => $technician->last_name, ], ]);
}
public function technicianSolarPanelRequests()
{
    $user = Auth::user();

    if (!$user || $user->role !== 'technician') {
        return response()->json([
            'message' => 'Unauthorized'
        ], 403);
    }

    $requests = MaintenanceRequest::with([
        'user',
        'service'
    ])
        ->where('technician_id', $user->id)
        ->where('status', 'accepted')
        ->whereHas('service', function ($query) {
            $query->where('title', 'ألواح طاقة شمسية');
        })
        ->get();

    return response()->json([
        'message' => 'Solar panel maintenance requests retrieved successfully',
        'technician_id' => $user->id,
        'requests' => $requests
    ]);
}
public function completeRequest($id)
{
    $user = Auth::user();

    if (!$user || $user->role !== 'technician') {
        return response()->json([
            'message' => 'Unauthorized'
        ], 403);
    }

    $maintenanceRequest = MaintenanceRequest::where('id', $id)
        ->where('technician_id', $user->id)
        ->where('status', 'accepted')
        ->whereHas('service', function ($query) {
            $query->where('title', 'ألواح طاقة شمسية');
        })
        ->first();

    if (!$maintenanceRequest) {
        return response()->json([
            'message' => 'Request not found, not assigned to you, or not accepted yet'
        ], 404);
    }

    $maintenanceRequest->update([
        'status' => 'completed'
    ]);

    return response()->json([
        'message' => 'Request completed successfully',
        'request_id' => $maintenanceRequest->id,
        'status' => $maintenanceRequest->status
    ]);
}
public function adminSolarPanelRequests()
{
$user = Auth::user();
if (!$user || $user->role !== 'super_admin') {
    return response()->json([ 'message' => 'Unauthorized' ], 403);
     }
     $requests = MaintenanceRequest::with([ 'user', 'technician', 'service' ]) ->whereHas('service', function ($query) {
        $query->where('title', 'ألواح طاقة شمسية'); }
        ) ->latest() ->get(); return response()->json([ 'message' => 'All solar panel maintenance requests retrieved successfully', 'requests' => $requests ]);
}
public function showMySolarPanelRequest($id){$user = Auth::user();

if (!$user) {
    return response()->json([
        'message' => 'Unauthenticated'
    ], 401);
}

$request = MaintenanceRequest::with([
    'user',
    'technician',
    'service'
])
    ->where('id', $id)
    ->where('user_id', $user->id)
    ->whereHas('service', function ($query) {
        $query->where('title', 'ألواح طاقة شمسية');
    })
    ->first();

if (!$request) {
    return response()->json([
        'message' => 'Solar panel maintenance request not found'
    ], 404);
}

return response()->json([
    'message' => 'Solar panel maintenance request retrieved successfully',
    'request' => $request
]);

}
public function storeBatteryRequest(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    $request->validate([
        'battery_type' => 'required|string|max:255',
        'problem_description' => 'required|string',
        'ownership_duration' => 'required|integer|min:1',
        'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
    ]);

    $service = Service::where('title', 'البطاريات')->first();

    if (!$service) {
        return response()->json([
            'message' => 'Battery service not found'
        ], 404);
    }


    $imagePath = $request->file('image')
        ->store('maintenance/batteries', 'public');


    $maintenanceRequest = MaintenanceRequest::create([
        'user_id' => $user->id,
        'service_id' => $service->id,
        'battery_type' => $request->battery_type,
        'problem_description' => $request->problem_description,
        'ownership_duration' => $request->ownership_duration,
        'image' => $imagePath,
        'location' => 'Not required for battery maintenance',
        'status' => 'pending',
    ]);


    return response()->json([
        'message' => 'Battery maintenance request created successfully',
        'request' => $maintenanceRequest,
        'request_id' => $maintenanceRequest->id,
    'status' => $maintenanceRequest->status
    ], 201);
}



public function showMyBatteryRequest($id)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }


    $request = MaintenanceRequest::with([
        'user',
        'technician',
        'service'
    ])
    ->where('id', $id)
    ->where('user_id', $user->id)
    ->whereHas('service', function ($query) {
        $query->where('title', 'البطاريات');
    })
    ->first();


    if (!$request) {
        return response()->json([
            'message' => 'Battery maintenance request not found'
        ], 404);
    }


    return response()->json([
        'message' => 'Battery maintenance request retrieved successfully',
        'request' => $request
    ]);
}

public function showSolarPanelTechnician($id)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    $request = MaintenanceRequest::with('technician')
        ->where('id', $id)
        ->where('user_id', $user->id)
        ->whereHas('service', function ($query) {
            $query->where('title', 'ألواح طاقة شمسية');
        })
        ->first();

    if (!$request) {
        return response()->json([
            'message' => 'Solar panel request not found'
        ], 404);
    }

    if (!$request->technician) {
        return response()->json([
            'message' => 'No technician has been assigned yet'
        ], 404);
    }

    return response()->json([
        'message' => 'Technician profile retrieved successfully',
        'request_id' => $request->id,
        'technician' => [
            'id' => $request->technician->id,
            'name' => $request->technician->name,
            'last_name' => $request->technician->last_name,
            'phone' => $request->technician->phone,
            'email' => $request->technician->email,
        ]
    ]);
}

public function showBatteryTechnician($id)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }


    $request = MaintenanceRequest::with('technician')
        ->where('id', $id)
        ->where('user_id', $user->id)
        ->whereHas('service', function ($query) {
            $query->where('title', 'البطاريات');
        })
        ->first();


    if (!$request) {
        return response()->json([
            'message' => 'Battery request not found'
        ], 404);
    }


    if (!$request->technician) {
        return response()->json([
            'message' => 'No technician has been assigned yet'
        ], 404);
    }


    return response()->json([
        'message' => 'Technician profile retrieved successfully',
        'request_id' => $request->id,
        'technician' => [
            'id' => $request->technician->id,
            'name' => $request->technician->name,
            'last_name' => $request->technician->last_name,
            'phone' => $request->technician->phone,
            'email' => $request->technician->email,
        ]
    ]);
}

public function adminBatteryRequests()
{
$user = Auth::user();
if (!$user || $user->role !== 'super_admin') { return response()->json([ 'message' => 'Unauthorized' ], 403); } $requests = MaintenanceRequest::with([ 'user', 'technician', 'service' ]) ->whereHas('service', function ($query) { $query->where('title', 'البطاريات'); }) ->latest() ->get(); return response()->json([ 'message' => 'All battery maintenance requests retrieved successfully', 'requests' => $requests ]);
}
// 5) السوبر أدمن: قبول أو رفض طلب بطارية وتعيين الفني بالاسم والكنية
public function updateBatteryRequestStatus(Request $request, $id)
{
$user = Auth::user();
if (!$user || $user->role !== 'super_admin') { return response()->json([ 'message' => 'Unauthorized' ], 403); } $request->validate([ 'status' => 'required|in:accepted,rejected', 'technician_name' => 'required_if:status,accepted|string|max:255', 'technician_last_name' => 'required_if:status,accepted|string|max:255', ]); $maintenanceRequest = MaintenanceRequest::where('id', $id) ->whereHas('service', function ($query) { $query->where('title', 'البطاريات'); }) ->first(); if (!$maintenanceRequest) { return response()->json([ 'message' => 'Battery maintenance request not found' ], 404); } if ($request->status === 'rejected') { $maintenanceRequest->update([ 'status' => 'rejected', 'technician_id' => null, ]); return response()->json([ 'message' => 'Battery request rejected successfully', 'request_id' => $maintenanceRequest->id, 'status' => 'rejected' ]); } $technician = User::where('role', 'technician') ->where('name', $request->technician_name) ->where('last_name', $request->technician_last_name) ->first(); if (!$technician) { return response()->json([ 'message' => 'Technician not found' ], 404); } $maintenanceRequest->update([ 'status' => 'accepted', 'technician_id' => $technician->id, ]); return response()->json([ 'message' => 'Battery request accepted and technician assigned successfully', 'request_id' => $maintenanceRequest->id, 'status' => 'accepted', 'technician' => [ 'id' => $technician->id, 'name' => $technician->name, 'last_name' => $technician->last_name, ] ]);
}
public function technicianBatteryRequests(){$user = Auth::user();

if (!$user || $user->role !== 'technician') {
    return response()->json([
        'message' => 'Unauthorized'
    ], 403);
}

$requests = MaintenanceRequest::with([
    'user',
    'technician',
    'service'
])
    ->where('technician_id', $user->id)
    ->where('status', 'accepted')
    ->whereHas('service', function ($query) {
        $query->where('title', 'البطاريات');
    })
    ->latest()
    ->get();

return response()->json([
    'message' => 'Battery requests retrieved successfully',
    'technician_id' => $user->id,
    'requests' => $requests
]);

}

 public function completeBatteryRequest($id){$user = Auth::user();

if (!$user || $user->role !== 'technician') {
    return response()->json([
        'message' => 'Unauthorized'
    ], 403);
}

$maintenanceRequest = MaintenanceRequest::where('id', $id)
    ->where('technician_id', $user->id)
    ->where('status', 'accepted')
    ->whereHas('service', function ($query) {
        $query->where('title', 'البطاريات');
    })
    ->first();

if (!$maintenanceRequest) {
    return response()->json([
        'message' => 'Battery request not found, not assigned to you, or not accepted yet'
    ], 404);
}

$maintenanceRequest->update([
    'status' => 'completed'
]);

return response()->json([
    'message' => 'Battery request completed successfully',
    'request_id' => $maintenanceRequest->id,
    'status' => 'completed'
]);


}public function storeInverterRequest(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    $request->validate([
        'inverter_code' => 'required|string|max:255',
        'input_voltage' => 'required|numeric',
        'output_voltage' => 'required|numeric',
        'notes' => 'nullable|string',
        'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
    ]);

    $service = Service::where('title', 'الانفيرتر')->first();

    if (!$service) {
        return response()->json([
            'message' => 'Inverter service not found'
        ], 404);
    }

   $imagePath = $request->file('image')->store('maintenance/inverters', 'public');

$maintenanceRequest = MaintenanceRequest::create([
    'user_id' => $user->id,
    'service_id' => $service->id,
    'problem_description' => $request->notes ?? 'Inverter issue',
    'location' => $request->location ?? 'Not specified',
    'inverter_code' => $request->inverter_code,
    'input_voltage' => $request->input_voltage,
    'output_voltage' => $request->output_voltage,
    'notes' => $request->notes,
    'image' => $imagePath,
    'status' => 'pending',
]);

return response()->json([
    'message' => 'Inverter maintenance request created successfully',
    'request_id' => $maintenanceRequest->id,
    'status' => $maintenanceRequest->status,
], 201);
}
public function showMyInverterRequest($id)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    $request = MaintenanceRequest::with([
        'user',
        'technician',
        'service'
    ])
    ->where('id', $id)
    ->where('user_id', $user->id)
    ->whereHas('service', function ($query) {
        $query->where('title', 'الانفيرتر');
    })
    ->first();

    if (!$request) {
        return response()->json([
            'message' => 'Inverter request not found'
        ], 404);
    }

    return response()->json([
        'message' => 'Inverter request retrieved successfully',
        'request' => $request
    ]);
}
public function showInverterTechnician($id)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    $request = MaintenanceRequest::with('technician')
        ->where('id', $id)
        ->where('user_id', $user->id)
        ->whereHas('service', function ($query) {
            $query->where('title', 'الانفيرتر');
        })
        ->first();

    if (!$request) {
        return response()->json([
            'message' => 'Inverter request not found'
        ], 404);
    }

    if (!$request->technician) {
        return response()->json([
            'message' => 'No technician has been assigned yet'
        ], 404);
    }

    return response()->json([
        'message' => 'Technician profile retrieved successfully',
        'request_id' => $request->id,
        'technician' => [
            'id' => $request->technician->id,
            'name' => $request->technician->name,
            'last_name' => $request->technician->last_name,
            'phone' => $request->technician->phone,
            'email' => $request->technician->email,
        ]
    ]);
}
public function adminInverterRequests()
{
    $requests = DB::table('maintenance_requests')
        ->join('users', 'maintenance_requests.user_id', '=', 'users.id')
        ->join('services', 'maintenance_requests.service_id', '=', 'services.id')
        ->leftJoin('users as tech', 'maintenance_requests.technician_id', '=', 'tech.id')
        ->where('services.title', 'الانفيرتر')
        ->select(
            'maintenance_requests.*',
            'users.name as user_name',
            'users.last_name as user_last_name',
            'tech.name as technician_name',
            'tech.last_name as technician_last_name'
        )
        ->get();

    return response()->json([
        'message' => 'All inverter requests retrieved successfully',
        'requests' => $requests
    ]);
}
public function updateInverterRequestStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:accepted,rejected',
        'technician_name' => 'nullable|string',
        'technician_last_name' => 'nullable|string',
    ]);

    $tech = null;

    if ($request->status == 'accepted') {
        $tech = DB::table('users')
            ->where('name', $request->technician_name)
            ->where('last_name', $request->technician_last_name)
            ->first();

        if (!$tech) {
            return response()->json([
                'message' => 'Technician not found'
            ], 404);
        }
    }

    DB::table('maintenance_requests')
        ->where('id', $id)
        ->update([
            'status' => $request->status,
            'technician_id' => $tech->id ?? null,
            'updated_at' => now()
        ]);

    return response()->json([
        'message' => 'Inverter request updated successfully',
        'status' => $request->status,
        'technician' => $tech ? [
            'id' => $tech->id,
            'name' => $tech->name,
            'last_name' => $tech->last_name
        ] : null
    ]);
}
public function technicianInverterRequests()
{
    $user = Auth::user();

    $requests = DB::table('maintenance_requests')
        ->join('users', 'maintenance_requests.user_id', '=', 'users.id')
        ->join('services', 'maintenance_requests.service_id', '=', 'services.id')
        ->where('maintenance_requests.technician_id', $user->id)
        ->where('services.title', 'الانفيرتر')
        ->where('maintenance_requests.status', 'accepted')
        ->select(
            'maintenance_requests.*',
            'users.name as user_name',
            'users.last_name as user_last_name'
        )
        ->get();

    return response()->json([
        'message' => 'Technician inverter requests retrieved successfully',
        'technician_id' => $user->id,
        'requests' => $requests
    ]);
}
public function completeInverterRequest($id)
{
    $user = Auth::user();

    $request = DB::table('maintenance_requests')
        ->where('id', $id)
        ->where('technician_id', $user->id)
        ->first();

    if (!$request) {
        return response()->json([
            'message' => 'Request not found or not assigned to you'
        ], 404);
    }

    DB::table('maintenance_requests')
        ->where('id', $id)
        ->update([
            'status' => 'completed',
            'updated_at' => now()
        ]);

    return response()->json([
        'message' => 'Request marked as completed',
        'request_id' => $id,
        'status' => 'completed'
    ]);
}public function deleteMySolarPanelRequest($id)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    $maintenanceRequest = MaintenanceRequest::where('id', $id)
        ->where('user_id', $user->id)
        ->whereHas('service', function ($query) {
            $query->where('title', 'ألواح طاقة شمسية');
        })
        ->first();

    if (!$maintenanceRequest) {
        return response()->json([
            'message' => 'Solar panel maintenance request not found'
        ], 404);
    }

    if ($maintenanceRequest->status !== 'pending') {
        return response()->json([
            'message' => 'You can only delete a pending request'
        ], 403);
    }

    $maintenanceRequest->delete();

    return response()->json([
        'message' => 'Solar panel maintenance request deleted successfully',
        'request_id' => $maintenanceRequest->id
    ]);
}
public function deleteMyInverterRequest($id)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    $maintenanceRequest = MaintenanceRequest::where('id', $id)
        ->where('user_id', $user->id)
        ->whereHas('service', function ($query) {
            $query->where('title', 'الانفيرتر');
        })
        ->first();

    if (!$maintenanceRequest) {
        return response()->json([
            'message' => 'Inverter maintenance request not found'
        ], 404);
    }

    if ($maintenanceRequest->status !== 'pending') {
        return response()->json([
            'message' => 'You can only delete a pending request'
        ], 403);
    }

    $maintenanceRequest->delete();

    return response()->json([
        'message' => 'Inverter maintenance request deleted successfully',
        'request_id' => $maintenanceRequest->id
    ]);
}
public function deleteMyBatteryRequest($id)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    $maintenanceRequest = MaintenanceRequest::where('id', $id)
        ->where('user_id', $user->id)
        ->whereHas('service', function ($query) {
            $query->where('title', 'البطاريات');
        })
        ->first();

    if (!$maintenanceRequest) {
        return response()->json([
            'message' => 'Battery maintenance request not found'
        ], 404);
    }

    if ($maintenanceRequest->status !== 'pending') {
        return response()->json([
            'message' => 'You can only delete a pending request'
        ], 403);
    }

    $maintenanceRequest->delete();

    return response()->json([
        'message' => 'Battery maintenance request deleted successfully',
        'request_id' => $maintenanceRequest->id
    ]);
}
public function showAllSolarPanelRequests()
{
    $requests = MaintenanceRequest::with([
        'user',
        'technician',
        'service'
    ])
    ->whereHas('service', function ($query) {
        $query->where('title', 'ألواح طاقة شمسية');
    })
    ->get();

    return response()->json([
        'message' => 'All solar panel requests retrieved successfully',
        'requests' => $requests
    ]);
}
public function showAllBatteryRequests()
{
    $requests = MaintenanceRequest::with([
        'user',
        'technician',
        'service'
    ])
    ->whereHas('service', function ($query) {
        $query->where('title', 'البطاريات');
    })
    ->get();

    return response()->json([
        'message' => 'All battery requests retrieved successfully',
        'requests' => $requests
    ]);
}
public function showAllInverterRequests()
{
    $requests = MaintenanceRequest::with([
        'user',
        'technician',
        'service'
    ])
    ->whereHas('service', function ($query) {
        $query->where('title', 'الانفيرتر');
    })
    ->get();

    return response()->json([
        'message' => 'All inverter requests retrieved successfully',
        'requests' => $requests
    ]);
}
}
