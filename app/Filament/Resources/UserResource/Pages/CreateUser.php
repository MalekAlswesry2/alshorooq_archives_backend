<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Livewire\Attributes\Title;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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

    protected function afterCreate(): void
    {
        $this->syncUserPermissions($this->record);

        Notification::make()
            ->title('✅ تم إنشاء المستخدم وتحديث صلاحياته بنجاح')
            ->success()
            ->send();
    }
    protected function syncUserPermissions(User $user): void
    {
        DB::table('user_permissions')->where('user_id', $user->id)->delete();

        $permissions = DB::table('role_permission')
            ->where('role_id', $user->role_id)
            ->pluck('permission_id');

        foreach ($permissions as $permissionId) {
            DB::table('user_permissions')->insert([
                'user_id' => $user->id,
                'permission_id' => $permissionId,
            ]);
        }
    }
}
