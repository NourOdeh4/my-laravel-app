<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
   use App\Models\SolarPackage;
//use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSolarController extends Controller
{


public function store(Request $request)
{
    $user = Auth::user();

    if (!$user || $user->role !== 'super_admin') {
        return response()->json(['message' => 'Forbidden'], 403);
    }

    $request->validate([
        'name' => 'required|string',
        'inverter_watt' => 'required|integer',
        'battery' => 'required|string',
        'panels' => 'required|integer',
        'price' => 'required|integer',
        'capacity_watt' => 'required|integer',
    ]);

    $package = SolarPackage::create($request->all());

    return response()->json([
        'message' => 'Package created',
        'data' => $package
    ]);
}
public function update(Request $request, $id)
{
    $user = Auth::user();

    if (!$user || $user->role !== 'super_admin') {
        return response()->json(['message' => 'Forbidden'], 403);
    }

    $package = SolarPackage::find($id);

    if (!$package) {
        return response()->json(['message' => 'Not found'], 404);
    }

    $package->update($request->all());

    return response()->json([
        'message' => 'Package updated',
        'data' => $package
    ]);
}
public function destroy($id)
{
    $user = Auth::user();

    if (!$user || $user->role !== 'super_admin') {
        return response()->json(['message' => 'Forbidden'], 403);
    }

    $package = SolarPackage::find($id);

    if (!$package) {
        return response()->json(['message' => 'Not found'], 404);
    }

    $package->delete();

    return response()->json([
        'message' => 'Package deleted'
    ]);
}
}
