<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Enums\AppointmentStatus;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Carbon;
use App\Models\User;



class AppointmentController extends Controller
{

    public function index(Request $request)
{
    $user = auth()->user();
    $now = Carbon::now();

    if ($user->role === 'admin') {
        // $appointments = Appointment::with(['user:id,name', 'market:id,name'])
        //     ->where('user_id', $request->user_id)
        //     ->latest()
        //     ->get();
        $futureAppointments = Appointment::with(['market', 'user'])
            ->where('scheduled_at', '>=', $now)
            ->orderBy('scheduled_at');

        $pastAppointments = Appointment::with(['market', 'user'])
            ->where('scheduled_at', '<', $now)
            ->orderBy('scheduled_at');

        $appointments = $futureAppointments->get()->concat($pastAppointments->get());
    } else {
        // $appointments = $user->appointments()->with('market:id,name')->latest()->get();
        // $appointments = AppointmentResource::collection(
        //     Appointment::where('user_id', auth()->id())
        // ->orderBy('scheduled_at')
        // ->get(),
        // );
        $now = Carbon::today(); // or now() if you want time too

    $futureAppointments = Appointment::with('market:id,name')
        ->where('user_id', $user->id)
        ->whereDate('scheduled_at', '>=', $now)
        ->orderBy('scheduled_at', 'asc');

    $pastAppointments = Appointment::with('market:id,name')
        ->where('user_id', $user->id)
        ->whereDate('scheduled_at', '<', $now)
        ->orderBy('scheduled_at', 'asc');

    // Combine both lists
    $appointments = $futureAppointments->get()->concat($pastAppointments->get());
    }

    return response()->json([
        'appointments' => AppointmentResource::collection($appointments),
    ]);
}

public function getUserAppointments($user_id)
{
    $requestingUser = auth()->user();

    // Optional: prevent normal users from viewing others' data
    if ($requestingUser->role === 'user' && $requestingUser->id != $user_id) {
        return response()->json([
            'error' => true,
            'message' => 'Unauthorized access to user appointments'
        ], 403);
    }

    // Make sure the user exists
    $user = User::find($user_id);
    if (!$user) {
        return response()->json([
            'error' => true,
            'message' => 'User not found'
        ], 404);
    }

    // Retrieve upcoming first, past later
    $now = now();

    $futureAppointments = Appointment::with('market')
        ->where('user_id', $user_id)
        ->where('scheduled_at', '>=', $now)
        ->orderBy('scheduled_at', 'asc');

    $pastAppointments = Appointment::with('market')
        ->where('user_id', $user_id)
        ->where('scheduled_at', '<', $now)
        ->orderBy('scheduled_at', 'asc');

    $appointments = $futureAppointments->get()->concat($pastAppointments->get());

    return response()->json([
        'appointments' => AppointmentResource::collection($appointments),
    ]);
}

    public function store(Request $request)
{
    $data = $request->validate([
        'market_id' => 'required|exists:markets,id',
        'scheduled_at' => 'required|date',
        'description' => 'nullable|string',
        'status' => ['nullable', new Enum(AppointmentStatus::class)],
    ]);

    $appointment = Appointment::create([
        'user_id' => auth()->id(),
        'market_id' => $data['market_id'],
        'scheduled_at' => $data['scheduled_at'],
        'description' => $data['description'],
        'status' => $data['status'] ?? AppointmentStatus::Upcoming,
    ]);

    return response()->json([
        'message' => 'تم إضافة الموعد بنجاح',
        'appointment' => new AppointmentResource($appointment),
    ]);
}

public function updateStatus(Request $request, $id)
{
    $user = auth()->user();

    $request->validate([
        'status' => ['required', new Enum(AppointmentStatus::class)],
    ]);

    $appointment = Appointment::find($id);

    if (!$appointment) {
        return response()->json(['error' => true, 'message' => 'Appointment not found'], 404);
    }

    if ($user->role === 'user' && $appointment->user_id !== $user->id) {
        return response()->json(['error' => true, 'message' => 'Unauthorized'], 403);
    }

    $appointment->status = AppointmentStatus::from($request->status);
    $appointment->save();

    return response()->json([
        'message' => 'تم تحديث حالة الموعد بنجاح',
        'appointment' => new AppointmentResource($appointment),
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

    $appointment->status = AppointmentStatus::Canceled;
    $appointment->save();

    return response()->json([
        'message' => 'تم الغاء الموعد بنجاح',
        'appointment' => new AppointmentResource($appointment),
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
