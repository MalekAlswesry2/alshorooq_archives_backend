<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Livewire\Attributes\Title;
use App\Models\User;
use App\Notifications\SendPasswordToUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;
protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
                ->success()
                ->title('انشاء مستخدم')
                ->body('تم انشاء المستخدم بنجاح');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
{
    $password = Str::random(8);
    $data['password'] = bcrypt($password);

    // خزّن الباسورد مؤقتًا لإرساله بعد الإنشاء
    $this->tempPassword = $password;

    return $data;
}
    protected function afterCreate(): void
    {
        $this->syncUserPermissions($this->record);

        Notification::make()
            ->title('✅ تم إنشاء المستخدم وتحديث صلاحياته بنجاح')
            ->success()
            ->send();

                $this->record->notify(new SendPasswordToUser($this->tempPassword));

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
