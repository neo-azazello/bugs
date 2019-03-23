<?php

namespace App\Controllers;

use App\Models\Comments;
use App\Models\User;
use App\Models\Tasks;

class CommentController extends Controller {
    
    public static function viewTaskComments($id) {
        $getcomments = Comments::getTaskComments($id);
            return $getcomments;
        
    }
    
    public function addNewComment($request, $response) {
        
           $receivers = []; 
        
            $yell = Comments::create([
                'commenttext' => $request->getParam('commenttext'),
                'taskid' => $request->getParam('taskid'),
                'authorid'=> $_SESSION['user']
            ]);
            
           $getcomment = Comments::getCommentById($yell->id);
           $tasktitle = $this->container->db->table('tasks')->select('tasktitle')->where('taskid', $request->getParam('taskid'))->value('tasktitle');
           $this->telegram->tg_msg($_SESSION['name'] . " added new comment for task: " . $tasktitle . "\nLink: https://" . $_SERVER['SERVER_NAME']. "/view/" . $request->getParam('taskid'));
            
           $taskassigned = Tasks::getTaskAssigners($request->getParam('taskid'), $_SESSION['user']);
           $receivers[] = $this->container->db->table('tasks')->select('taskauthor')->where('taskid', $request->getParam('taskid'))->value('taskauthor');
            
           $notifiers = array_merge($taskassigned, $receivers);
            
            $this->container->NotificationController->setNotification(
                $request->getParam('taskid'), 
                $_SESSION['user'], 
                $notifiers, 
                'false', 
                $_SESSION['name'] . ' left a comment to task: ' . $tasktitle);
        
        return json_encode(array_shift($getcomment)); die();
        
    }
    
    
    public function deleteComment($request, $response) {
        
        $this->container->db->table('taskcomments')->where('commentid', $request->getParam('commentid'))->delete();
        
    }
    
}