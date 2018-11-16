<?php

namespace App\Misc;

use App\Models\User;
use \Datetime;
use \DateTimeZone;

class Auth {

    public function user() {
        
        if(isset($_SESSION['user'])) {
            
            return User::find($_SESSION['user']);
        
        }
    }

    public function check() {

        return isset($_SESSION['user']);

    }

    public function attempt($code) {
        
        $user = User::where('code', $code)->where('is_active', 1)->first();
        
        if($user) {
             
             $_SESSION['user'] = $user->id;
             $_SESSION['is_admin'] = $user->is_admin;
             $_SESSION['name'] = $user->name;
             
             return true;
        
        } else {
             
             return false;
        }
        
    }

    public function logout() {

        unset($_SESSION['user']);
        unset($_SESSION['is_admin']);
        unset($_SESSION['name']);

    }
}