<?php

// As added to composer.json file an autoload of app dir, this namespace will serve
// as include once or require once;
namespace App\Models;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{

    protected $table = 'users';

    protected $fillable = [
        'name',
        'code',
        'photo',
        'email',
        'telegramname',
        'updated_at',
        'created_at',
    ];

    public static function getUserDetails($userId)
    {
        return DB::select("SELECT * FROM users WHERE id = $userId");
    }

}
