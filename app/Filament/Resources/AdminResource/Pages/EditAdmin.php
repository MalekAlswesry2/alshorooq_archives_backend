<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use App\Models\User;

class EditAdmin extends EditRecord
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
        protected function afterSave(): void
    {
        $this->syncUserPermissions($this->record);

        Notification::make()
            ->title('✅ تم تحديث صلاحيات المستخدم بنجاح')
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
