<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GeneratorMaintenanceRequest;
use App\Models\Generator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GeneratorMaintenanceRequestController extends Controller
{
   public function store(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message'=>'Unauthenticated'
        ],401);
    }


    $request->validate([

        'generator_name'=>'required|string',

        'problem_description'=>'required|string',

        'generator_power'=>'required|integer',

        'working_hours'=>'required|integer|min:1|max:24',

        'priority'=>'required|in:urgent,normal,low'

    ]);


    // البحث عن المولد حسب الاسم
    $generator = Generator::where('name',$request->generator_name)
        ->first();


    if(!$generator){

        return response()->json([
            'message'=>'Generator not found'
        ],404);

    }


    $maintenanceRequest = GeneratorMaintenanceRequest::create([

        'user_id'=>$user->id,

        'generator_id'=>$generator->id,

        'generator_name'=>$generator->name,

        'problem_description'=>$request->problem_description,

        'generator_power'=>$request->generator_power,

        'working_hours'=>$request->working_hours,

        'priority'=>$request->priority,

        'status'=>'pending'

    ]);


    return response()->json([

        'message'=>'Generator maintenance request created successfully',

        'request_id'=>$maintenanceRequest->id,

        'generator'=>$generator->name,

        'status'=>$maintenanceRequest->status

    ],201);
}
public function showMyGeneratorMaintenanceRequest($id)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }


    $request = GeneratorMaintenanceRequest::with([
        'user',
        'generator',
        'technician'
    ])
    ->where('id', $id)
    ->where('user_id', $user->id)
    ->first();


    if (!$request) {
        return response()->json([
            'message' => 'Generator maintenance request not found'
        ], 404);
    }


    return response()->json([
        'message' => 'Generator maintenance request retrieved successfully',
        'request' => $request
    ]);
}
public function myGeneratorMaintenanceRequests()
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message'=>'Unauthenticated'
        ],401);
    }


    $requests = GeneratorMaintenanceRequest::with([
        'generator',
        'technician'
    ])
    ->where('user_id',$user->id)
    ->latest()
    ->get();


    return response()->json([
        'message'=>'My generator maintenance requests retrieved successfully',
        'requests'=>$requests
    ]);
}
public function updateGeneratorMaintenanceStatus(Request $request,$id)
{
    $user = Auth::user();

    if (!$user || $user->role !== 'super_admin') {
        return response()->json([
            'message'=>'Unauthorized'
        ],403);
    }


    $request->validate([
        'status'=>'required|in:accepted,rejected',
        'technician_name'=>'nullable|string',
        'technician_last_name'=>'nullable|string'
    ]);



    $maintenanceRequest =
        GeneratorMaintenanceRequest::findOrFail($id);



    $maintenanceRequest->status = $request->status;



    if($request->status == 'accepted'){


        if(!$request->technician_name || !$request->technician_last_name){

            return response()->json([
                'message'=>'Technician name and last name are required'
            ],422);

        }


        $technician = User::where('role','technician')
            ->where('name',$request->technician_name)
            ->where('last_name',$request->technician_last_name)
            ->first();



        if(!$technician){

            return response()->json([
                'message'=>'Technician not found'
            ],404);

        }


        $maintenanceRequest->technician_id = $technician->id;

    }


    if($request->status == 'rejected'){

        $maintenanceRequest->technician_id = null;

    }



    $maintenanceRequest->save();



    return response()->json([
        'message'=>'Generator maintenance request updated successfully',
        'request'=>$maintenanceRequest
    ]);
}
public function adminGeneratorMaintenanceRequests()
{
    $user = Auth::user();

    if (!$user || $user->role !== 'super_admin') {
        return response()->json([
            'message' => 'Unauthorized'
        ],403);
    }


    $requests = GeneratorMaintenanceRequest::with([
        'user',
        'generator',
        'technician'
    ])
    ->latest()
    ->get();


    return response()->json([
        'message'=>'All generator maintenance requests retrieved successfully',
        'requests'=>$requests
    ]);
}
public function completeGeneratorMaintenanceRequest($id)
{
    $user = Auth::user();


    if(!$user || $user->role != 'technician'){

        return response()->json([
            'message'=>'Unauthorized'
        ],403);

    }


    $request = GeneratorMaintenanceRequest::where('id',$id)
        ->where('technician_id',$user->id)
        ->first();



    if(!$request){

        return response()->json([
            'message'=>'Request not found'
        ],404);

    }


    $request->status='completed';

    $request->save();



    return response()->json([

        'message'=>'Generator maintenance request completed successfully'

    ]);
}

public function technicianGeneratorMaintenanceRequests()
{
    $user = Auth::user();


    if(!$user || $user->role != 'technician'){

        return response()->json([
            'message'=>'Unauthorized'
        ],403);

    }


    $requests = GeneratorMaintenanceRequest::with([
        'user',
        'generator'
    ])
    ->where('technician_id',$user->id)
    ->latest()
    ->get();



    return response()->json([

        'message'=>'Technician generator maintenance requests retrieved successfully',

        'requests'=>$requests

    ]);
}
public function getGeneratorMaintenanceTechnician($id)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message'=>'Unauthenticated'
        ],401);
    }


    $request = GeneratorMaintenanceRequest::with('technician')
        ->where('id',$id)
        ->where('user_id',$user->id)
        ->first();


    if(!$request){

        return response()->json([
            'message'=>'Generator maintenance request not found'
        ],404);

    }


    if(!$request->technician){

        return response()->json([
            'message'=>'No technician assigned yet'
        ],404);

    }


    return response()->json([

        'technician'=>[
            'id'=>$request->technician->id,
            'name'=>$request->technician->name,
            'last_name'=>$request->technician->last_name,
            'email'=>$request->technician->email,
            'phone'=>$request->technician->phone,
            'address'=>$request->technician->address,
            'avatar'=>$request->technician->avatar,
        ]

    ]);
}
public function deleteGeneratorMaintenanceRequest($id)
{
    $user = Auth::user();


    if (!$user) {

        return response()->json([
            'message'=>'Unauthenticated'
        ],401);

    }


    $request = GeneratorMaintenanceRequest::where('id',$id)
        ->where('user_id',$user->id)
        ->where('status','pending')
        ->first();



    if (!$request) {

        return response()->json([
            'message'=>'Request not found or cannot be deleted'
        ],404);

    }



    $request->delete();



    return response()->json([

        'message'=>'Generator maintenance request deleted successfully'

    ]);
}
}
