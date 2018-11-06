<?php

namespace App\Controllers;

use App\Models\Comments;

class CommentController extends Controller {
    
    public function viewTaskComments($id) {
        $getcomments = Comments::getTaskComments($id);
            return $getcomments;
        
    }
    
    public function addNewComment($request, $response) {
        
            $yell = Comments::create([
                'commenttext' => $request->getParam('commenttext'),
                'taskid' => $request->getParam('taskid'),
                'authorid'=> $_SESSION['user']
            ]);
            
            $getcomment = Comments::getCommentById($yell->id);
        
        return json_encode(array_shift($getcomment)); die();
        
    }
    
    
    public function deleteComment($request, $response) {
        
        $this->container->db->table('taskcomments')->where('commentid', $request->getParam('commentid'))->delete();
        
    }
    
}