<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

class Notifications extends Model {
    
        protected $table = 'notifications';

        protected $fillable = [
            'taskid',
            'authorid',
            'assignedid',
            'has_read',
            'text',
        ];
    
    public function getNotifications() {
        
        if(isset($_SESSION['user'])) {
            
            $userid = $_SESSION['user'];
            return $select = DB::select("
            SELECT
             N.id,
             N.taskid,
             U.photo,
             N.created_at,
             N.text
            FROM notifications N
            INNER JOIN users U ON N.authorid = U.id
            WHERE assignedid = $userid AND has_read = 'false'
            ORDER BY N.id DESC
            ");
        } 
        
        return false;
    
    }
    
    
}