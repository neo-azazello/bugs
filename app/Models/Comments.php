<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

class Comments extends Model {
    
        protected $table = 'taskcomments';

        protected $fillable = [
            'taskid',
            'authorid',
            'commenttext',
            'commentid',
        ];
    
    public static function getTaskComments($id) {
        
           $taskid = implode('', $id);
            
            return $select = DB::select(
                "SELECT
                TC.commentid,
                TC.commenttext,
                TC.created_at,
                U.name,
                U.photo
                FROM taskcomments TC
                INNER JOIN users U On U.id = TC.authorid
                WHERE TC.taskid = $taskid"
                );
        
    }
    
    public static function getCommentById($id){
        
            return $select = DB::select(
                "SELECT
                TC.commentid,
                TC.commenttext AS text,
                TC.created_at AS date,
                U.name,
                U.photo
                FROM taskcomments TC
                INNER JOIN users U On U.id = TC.authorid
                WHERE commentid = $id");
    }
}