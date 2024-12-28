<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function index()
    {
        $zones = Zone::all();
        return response()->json(['zones' => $zones], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:zones,code',
        ]);

        $zone = Zone::create($validated);

        return response()->json([
            'message' => 'Zone created successfully',
            'zone' => $zone,
        ], 200);
    }
}
