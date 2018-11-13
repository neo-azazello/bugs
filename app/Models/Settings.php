<?php

namespace App\Models;

use Illuminate\Database\Capsule\Manager as DB;

class Settings {
    
    public function query($tablename) {
        
        $selectTable = DB::select("SELECT * FROM $tablename");
        
        return $selectTable;
    }

}