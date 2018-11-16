<?php

// As added to composer.json file an autoload of app dir, this namespace will serve
// as include once or require once;
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

class User extends Model {

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
        
        public function getUserDetails($userId) {
            
            $id = implode('', $userId);
            
            return $select = DB::select("SELECT * FROM users WHERE id = $id");

        }


}