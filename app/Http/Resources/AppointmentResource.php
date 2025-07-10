<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'scheduled_at' => $this->scheduled_at->toDateTimeString(),
            'description' => $this->description,
            'status' => $this->statusObject,
            'market' =>[
                'id' => $this->market->id, 
                'name' => $this->market->name,
            ],
            'user' => $this->whenLoaded('user', function () {
            return [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ];
        }),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}