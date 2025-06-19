<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;

class AppointmentController extends Controller
{

    public function index(Request $request)
{
    $user = auth()->user();

    if ($user->role === 'admin' && $request->has('user_id')) {
        $appointments = Appointment::with(['user:id,name', 'market:id,name'])
            ->where('user_id', $request->user_id)
            ->latest()
            ->get();
    } else {
        $appointments = $user->appointments()->with('market:id,name')->latest()->get();
    }

    return response()->json([
        'appointments' => $appointments,
    ]);
}

    public function store(Request $request)
{
    $request->validate([
        'market_id' => 'required|exists:markets,id',
        'scheduled_at' => 'required|date',
        'description' => 'nullable|string',
    ]);

    $appointment = Appointment::create([
        'user_id' => auth()->id(),
        'market_id' => $request->market_id,
        'scheduled_at' => $request->scheduled_at,
        'description' => $request->description,
    ]);

    return response()->json([
        'message' => 'تم إضافة الموعد بنجاح',
        'appointment' => $appointment,
    ]);
}

public function updateStatus(Request $request, $id)
{
    $user = auth()->user();

    $request->validate([
        'status' => 'required|in:upcoming,completed,not_completed,canceled',
    ]);

    $appointment = Appointment::find($id);

    if (!$appointment) {
        return response()->json(['error' => true, 'message' => 'Appointment not found'], 404);
    }

    if ($user->role === 'user' && $appointment->user_id !== $user->id) {
        return response()->json(['error' => true, 'message' => 'Unauthorized'], 403);
    }

    $appointment->status = $request->status;
    $appointment->save();

    return response()->json([
        'message' => 'تم تحديث حالة الموعد بنجاح',
        'appointment' => $appointment,
    ]);
}
public function cancelAppointment($id)
{
    $user = auth()->user();

    $appointment = Appointment::find($id);

    if (!$appointment) {
        return response()->json([
            'error' => true,
            'message' => 'Appointment not found',
        ], 404);
    }

    if ($user->role === 'user' && $appointment->user_id !== $user->id) {
        return response()->json([
            'error' => true,
            'message' => 'Unauthorized',
        ], 403);
    }

    $appointment->status = 'canceled';
    $appointment->save();

    return response()->json([
        'message' => 'تم الغاء الموعد بنجاح',
        'appointment' => $appointment,
    ]);
}

// public function update(Request $request, Appointment $appointment)
// {
//     $user = auth()->user();

//     if ($user->role !== 'admin' && $appointment->user_id !== $user->id) {
//         return response()->json(['error' => 'Unauthorized'], 403);
//     }

//     $request->validate([
//         'market_id' => 'required|exists:markets,id',
//         'scheduled_at' => 'required|date',
//         'description' => 'nullable|string',
//     ]);

//     $appointment->update($request->only('market_id', 'scheduled_at', 'description'));

//     return response()->json([
//         'message' => 'تم تحديث الموعد بنجاح',
//         'appointment' => $appointment,
//     ]);
// }

}
