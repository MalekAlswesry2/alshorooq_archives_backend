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
    // public function getZones(Request $request)
    // {
    //     $user = auth()->user();
    
    //     if ($user->role === 'admin') {
    //         $branchIds = $user->branches()->pluck('branches.id')->toArray();
    //         if (empty($branchIds)) {
    //             $branchIds[] = $user->branch_id;
    //         }
    
    //         $zones = Zone::whereIn('branch_id', $branchIds)->get();
    //     } else {
    //         // users only see zones for their branch
    //         $zones = Zone::where('branch_id', $user->branch_id)->get();
    //     }
    
    //     return response()->json([
    //         'zones' => $zones
    //     ]);
    // }
    
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
            'message' => 'تم اضافة خط سير',
            'zone' => $zone,
        ], 200);
    }
}
