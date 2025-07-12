<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function afterSave(): void
    {
        $roleId = $this->record->id;

        // جلب كل المستخدمين الذين يملكون هذا الدور
        $users = User::where('role_id', $roleId)->get();

        foreach ($users as $user) {
            $this->syncUserPermissions($user);
        }

        Notification::make()
            ->title('✅ تم تحديث صلاحيات جميع المستخدمين المرتبطين بهذا الدور')
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
