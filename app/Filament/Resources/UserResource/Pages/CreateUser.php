<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Livewire\Attributes\Title;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;


    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
                ->success()
                ->title('User Created')
                ->body('The user creatd successfully!');
    }

}
