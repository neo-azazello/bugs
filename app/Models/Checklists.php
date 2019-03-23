<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

class Checklists extends Model {
    
        protected $table = 'checklist';

        protected $fillable = [
            'done',
            'taskid',
            'userid',
            'text',
            'created_at',
        ];
    
    public static function getTaskChecklist($id, $userid) {
        
           $taskid = implode('', $id);
            
            return $select = DB::select(
                "SELECT
                  CH.id,
                  CH.done,
                  CH.text,
                  U.name,
                  U.photo
                  FROM checklist CH
                  INNER JOIN users U On U.id = CH.userid
                  WHERE CH.taskid = $taskid
                  AND CH.userid = $userid"
                );
        
        }
        
    
    public static function getTaskChecklistById($id) {
        
         return $select = DB::select(
             "SELECT
              done,
              text,
              id
              FROM checklist
              WHERE id = $id"
            );
    }
        
        
        

}