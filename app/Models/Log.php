<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'action',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // وظيفة لتسجيل السجل
    public static function addLog($action, $description, $userId = null)
    {
        self::create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
        ]);
    }

}