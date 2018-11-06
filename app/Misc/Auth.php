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
        
        try {
             
             $user = User::where('code', $code)->first();
             
             $date = new DateTime(null, new DateTimeZone('Asia/Baku'));
             $time = ($date->getTimestamp() + $date->getOffset());
             
             $_SESSION['user'] = $user->id;
             $_SESSION['is_admin'] = $user->is_admin;
             $_SESSION['name'] = $user->name;
             
             setcookie("start", $time, $time+86400); 
             
             return true;
            
            } catch (Exception $e) {
            
             return false;
        }
        
    }

    public function logout() {

        unset($_SESSION['user']);
        unset($_SESSION['is_admin']);
        unset($_SESSION['name']);

    }
}