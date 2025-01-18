<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Zone;
use Illuminate\Http\Request;

class AreaController extends Controller
{

    public function allAreas(Request $request)
    {
        $query = Area::query();
    
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%$search%")
                ->orWhereHas('zone', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
                });
        }
    
        $areas = $query->with('zone')->get();
    
        return response()->json([
            'message' => 'Areas retrieved successfully',
            'areas' => $areas,
        ], 200);
    }
    

    // عرض جميع المناطق تحت زون معين
    public function index($zoneId)
    {
        $zone = Zone::with('areas')->find($zoneId);

        if (!$zone) {
            return response()->json([
                'error' => true,
                'message' => 'Zone not found',
            ], 404);
        }

        return response()->json([
            'zone' => $zone,
            'areas' => $zone->areas,
        ], 200);
    }

    // إنشاء منطقة جديدة
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'zone_id' => 'required|exists:zones,id',
        ]);

        $area = Area::create($validated);

        return response()->json([
            'message' => 'Area created successfully',
            'area' => $area,
        ], 200);
    }


    public function getAreasDebOnZone(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            // إذا كان المستخدم admin، احضر جميع المناطق
            $areas = Area::all();
        } else {
            // إذا كان المستخدم user، احضر المناطق بناءً على zone_id للمستخدم
            $areas = Area::where('zone_id', $user->zone_id)->get();
        }

        return response()->json($areas);
    }
}
