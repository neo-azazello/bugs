<?php

// As added to composer.json file an autoload of app dir, this namespace will serve
// as include once or require once;
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

class Tasks extends Model {

        protected $table = 'tasks';

        protected $fillable = [
            'tasktitle',
            'tasktext',
            'taskauthor',
            'tasktypeid',
            'taskstatus',
            'taskproject',
            'is_draft',
        ];
        
        public static function getAllTasks() {
            
            return $select = DB::select(
                "SELECT
                  T.taskid,
                  T.tasktitle,
                  T.created_at,
                  U.name,
                  TT.tasktypename,
                  TT.tasktypecolor,
                  TS.statusname,
                  TS.statuscolor,
                  P.projectname,
                  P.projectsartdate,
                  T.is_draft,
                GROUP_CONCAT(AU.name separator ', ') assignedUsers,
                (SELECT  COUNT(*) FROM taskcomments tc WHERE tc.taskid = T.taskid) AS commentcount,
                (SELECT  COUNT(*) FROM taskchecklist tch WHERE tch.taskid = T.taskid) AS checklist,
                (SELECT  COUNT(*) FROM taskchecklist tch WHERE tch.taskid = T.taskid AND is_done = 2) AS checklistdone
                FROM tasks T
                INNER JOIN tasktypes TT ON T.tasktypeid = TT.tasktypeid
                INNER JOIN users U ON U.id = T.taskauthor
                INNER JOIN projects P ON P.projectid = T.taskproject
                INNER JOIN taskstatus TS ON TS.statusid = T.taskstatus
                LEFT JOIN taskassigns TA ON TA.taskid = T.taskid
                LEFT JOIN  users AU ON TA.userid = AU.id
                WHERE T.taskstatus != 3 
                AND T.taskstatus != 4 
                AND T.is_draft = 'false'
                GROUP BY T.taskid
                ORDER BY T.taskid DESC"
                );
        }
        
        
          public static function getUsersTasks($userid){
            
            return $select = DB::select(
                "SELECT
                  T.taskid,
                  T.tasktitle,
                  T.taskauthor,
                  T.created_at,
                  U.photo,
                  TT.tasktypename,
                  TT.tasktypecolor,
                  TS.statusname,
                  TS.statuscolor,
                  P.projectname,
                  P.projectsartdate,
                GROUP_CONCAT(AU.name separator ', ') assignedUsers,
                (SELECT  COUNT(*) FROM taskcomments tc WHERE tc.taskid = T.taskid) AS commentcount,
                (SELECT  COUNT(*) FROM taskchecklist tch WHERE tch.taskid = T.taskid AND tch.byuser = $userid) AS checklist,
                (SELECT  COUNT(*) FROM taskchecklist tch WHERE tch.taskid = T.taskid AND is_done = 2  AND tch.byuser = $userid) AS checklistdone
                FROM tasks T
                INNER JOIN tasktypes TT ON T.tasktypeid = TT.tasktypeid
                INNER JOIN users U ON U.id = T.taskauthor
                INNER JOIN projects P ON P.projectid = T.taskproject
                INNER JOIN taskstatus TS ON TS.statusid = T.taskstatus
                LEFT JOIN taskassigns TA ON TA.taskid = T.taskid
                LEFT JOIN  users AU ON TA.userid = AU.id
                WHERE TA.userid = $userid 
                AND T.taskstatus != 3 
                AND T.taskstatus != 4 
                AND T.is_draft = 'false'
                GROUP BY T.taskid
                ORDER BY T.taskid DESC"
                );
        } 
        
        public static function getUsersAnotherTasks($userid, $tasktype, $taskid){
            
            $id = implode(', ', $taskid);
            
            return $select = DB::select(
                "SELECT
                  T.taskid,
                  T.tasktitle,
                (SELECT  COUNT(*) FROM taskchecklist tch WHERE tch.taskid = T.taskid AND tch.byuser = $userid) AS checklist,
                (SELECT  COUNT(*) FROM taskchecklist tch WHERE tch.taskid = T.taskid AND is_done = 2 AND tch.byuser = $userid) AS checklistdone
                FROM tasks T
                INNER JOIN users U ON U.id = T.taskauthor
                INNER JOIN projects P ON P.projectid = T.taskproject
                LEFT JOIN taskassigns TA ON TA.taskid = T.taskid
                LEFT JOIN  users AU ON TA.userid = AU.id
                WHERE TA.userid = $userid
                AND T.tasktypeid = $tasktype
                AND T.taskid != $id
                AND T.taskstatus != 3 
                AND T.taskstatus != 4 
                AND T.is_draft = 'false'
                GROUP BY T.taskid
                ORDER BY T.taskid DESC"
                );
        }  
        
        
        public static function getDraftTasks() {
            
            return $select = DB::select(
                "SELECT
                  T.taskid,
                  T.tasktitle,
                  T.created_at,
                  U.photo,
                  TT.tasktypename,
                  TT.tasktypecolor,
                  TS.statusname,
                  TS.statuscolor,
                  P.projectname,
                  P.projectsartdate,
                GROUP_CONCAT(AU.name separator ', ') assignedUsers
                FROM tasks T
                INNER JOIN tasktypes TT ON T.tasktypeid = TT.tasktypeid
                INNER JOIN users U ON U.id = T.taskauthor
                INNER JOIN projects P ON P.projectid = T.taskproject
                INNER JOIN taskstatus TS ON TS.statusid = T.taskstatus
                LEFT JOIN taskassigns TA ON TA.taskid = T.taskid
                LEFT JOIN  users AU ON TA.userid = AU.id
                WHERE T.taskstatus != 3 
                AND T.is_draft = 'true'
                GROUP BY T.taskid
                ORDER BY T.taskid DESC"
                );
        }
        
        public static function getFinishedTasks($userid = null) {
            
            return $select = DB::select(
                "SELECT
                  T.taskid,
                  T.tasktitle,
                  T.created_at,
                  U.photo,
                  TT.tasktypename,
                  TT.tasktypecolor,
                  TS.statusname,
                  TS.statuscolor,
                  P.projectname,
                  P.projectsartdate,
                GROUP_CONCAT(AU.name separator ', ') assignedUsers,
                (SELECT  COUNT(*) FROM taskcomments tc WHERE tc.taskid = T.taskid) AS commentcount,
                (SELECT  COUNT(*) FROM taskchecklist tch WHERE tch.taskid = T.taskid) AS checklist,
                (SELECT  COUNT(*) FROM taskchecklist tch WHERE tch.taskid = T.taskid AND is_done = 2) AS checklistdone
                FROM tasks T
                INNER JOIN tasktypes TT ON T.tasktypeid = TT.tasktypeid
                INNER JOIN users U ON U.id = T.taskauthor
                INNER JOIN projects P ON P.projectid = T.taskproject
                INNER JOIN taskstatus TS ON TS.statusid = T.taskstatus
                LEFT JOIN taskassigns TA ON TA.taskid = T.taskid
                LEFT JOIN  users AU ON TA.userid = AU.id
                WHERE T.taskstatus = 3 
                AND T.is_draft = 'false'
                AND (TA.userid = ? OR ? IS NULL)
                GROUP BY T.taskid
                ORDER BY T.taskid DESC", [$userid, $userid]
                );
        }
        
        public static function getTestedTasks($userid = null, $page = array()) {
            
            $limitation = implode(', ', $page);
            
            return $select = DB::select(
                "SELECT
                  T.taskid,
                  T.tasktitle,
                  T.created_at,
                  U.photo,
                  TT.tasktypename,
                  TT.tasktypecolor,
                  TS.statusname,
                  TS.statuscolor,
                  P.projectname,
                  P.projectsartdate,
                  T.is_draft,
                GROUP_CONCAT(AU.name separator ', ') assignedUsers,
                (SELECT  COUNT(*) FROM taskcomments tc WHERE tc.taskid = T.taskid) AS commentcount,
                (SELECT  COUNT(*) FROM taskchecklist tch WHERE tch.taskid = T.taskid) AS checklist,
                (SELECT  COUNT(*) FROM taskchecklist tch WHERE tch.taskid = T.taskid AND is_done = 2) AS checklistdone
                FROM tasks T
                INNER JOIN tasktypes TT ON T.tasktypeid = TT.tasktypeid
                INNER JOIN users U ON U.id = T.taskauthor
                INNER JOIN projects P ON P.projectid = T.taskproject
                INNER JOIN taskstatus TS ON TS.statusid = T.taskstatus
                LEFT JOIN taskassigns TA ON TA.taskid = T.taskid
                LEFT JOIN  users AU ON TA.userid = AU.id
                WHERE T.taskstatus = 4 
                AND T.is_draft = 'false'
                AND (TA.userid = ? OR ? IS NULL)
                GROUP BY T.taskid
                ORDER BY T.taskid DESC
                LIMIT $limitation ", [$userid, $userid]
                );
        }
        
        
        public static function getConcreteTask($id){
            
            $taskid = implode('', $id);
            
            return $select = DB::select(
                "SELECT
                  T.taskid,
                  T.taskauthor,
                  T.tasktitle,
                  T.tasktext,
                  T.taskstatus,
                  T.tasktypeid,
                  T.created_at,
                  P.projectname,
                  U.name,
                  TT.tasktypename,
                  TT.tasktypecolor,
                  TS.statusname,
                  T.is_draft,
                GROUP_CONCAT(AU.name separator ', ') assignedUsers
                FROM tasks T
                INNER JOIN tasktypes TT ON T.tasktypeid = TT.tasktypeid
                INNER JOIN users U ON U.id = T.taskauthor
                INNER JOIN projects P ON P.projectid = T.taskproject
                INNER JOIN taskstatus TS ON TS.statusid = T.taskstatus
                LEFT JOIN taskassigns TA ON TA.taskid = T.taskid
                LEFT JOIN  users AU ON TA.userid = AU.id
                WHERE T.taskid = $taskid
                GROUP BY T.taskid"
                );
            
        }
        
        public static function getTaskChecklist($id) {
            
        $taskid = implode('', $id);
            
           return $select = DB::select(
                "SELECT
                  TC.id,
                  TC.byuser,
                  TC.text,
                  TC.is_done,
                  U.name
                  FROM taskchecklist TC
                  INNER JOIN users U ON TC.byuser = U.id
                  WHERE TC.taskid = $taskid
                  ORDER BY TC.is_done ASC"
                );
        }
        
        
        public static function getTaskTypes() {
            
            return $select = DB::select("SELECT tasktypeid, tasktypename FROM tasktypes ORDER BY tasktypeid ASC");
        }
        
        public static function getTaskStatus() {
            
            return $select = DB::select("SELECT statusid, statusname FROM taskstatus ORDER BY statusid ASC");
        }
        
        public static function getProjects() {
            
            return $select = DB::select("SELECT projectid, projectname FROM projects ORDER BY projectid ASC");
        }
        
        public static function getUsers(){
            
            return $select = DB::select(
                "SELECT id, name, photo, is_active FROM users WHERE is_active = true"
                );
        }
        
        public static function getAssignedUsers($id){
            
            $taskid = implode('', $id);
            
            return $select = DB::select(
                "SELECT U.id, U.name, U.photo 
                 FROM users U 
                 INNER JOIN taskassigns T ON T.userid = U.id 
                 WHERE T.taskid = $taskid"
                );
        }

        public static function getTaskFiles($id){
            
            $taskid = implode('', $id);
            return $select = DB::select("SELECT fileid, filename FROM taskfiles WHERE taskid = $taskid");
        }
        
        
        public static function getTaskAssigners($taskid, $userid){
            
             $select = DB::select("SELECT userid FROM taskassigns WHERE taskid = $taskid AND userid != $userid ");
                
                foreach($select as $key => $value) {
                    $new[] = $value->userid;
                }
            
            
            return $new;
        }

        public static function getTaskIdByTaskChecklist($id){
          return DB::select("SELECT taskid FROM taskchecklist WHERE id = $id");
        }

        public static function getTaskIdByTaskChecklistComment($id){
          return DB::select("SELECT taskid FROM taskchecklistcomments WHERE id = $id");
        }

        public static function getTaskChecklistComment($taskid){
            return $select = DB::select(
              "SELECT
                TCHC.id,
                TCHC.commenttext,
                TCHC.checklistid,
                U.name,
                U.photo
                FROM taskchecklistcomments TCHC
                INNER JOIN users U ON TCHC.userid = U.id
                WHERE TCHC.taskid = $taskid
                ORDER BY TCHC.created_at ASC"
              );
        }


}