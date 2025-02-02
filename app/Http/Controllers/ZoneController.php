<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $user = Auth::user();
        $zone = Zone::create($validated);
        Log::addLog(
            'إضافة خط سير جديد',
            "تم إضافة خط سير {$zone->name} بواسطة {$user->name}",
            $user->id
        );
        return response()->json([
            'message' => 'Zone created successfully',
            'zone' => $zone,
        ], 200);
    }
}
