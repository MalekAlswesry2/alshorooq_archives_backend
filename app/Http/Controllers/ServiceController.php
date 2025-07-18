<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function userServices(Request $request)
{
    $user = $request->user();

    $services = $user->services()->where('is_active', true)->get();

    return response()->json([
        'services' => $services
    ]);
}

}
