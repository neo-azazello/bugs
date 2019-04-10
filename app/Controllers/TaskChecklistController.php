<?php

namespace App\Controllers;

use App\Models\Tasks;
use App\Models\User;

class TaskChecklistController extends Controller {
    
    public function addNewTaskChecklist($request, $response) {
        
        $taskid = $request->getParam('taskid');
        $text = $request->getParam('text');
        $byuser = $request->getParam('byuser');
        $type = $request->getParam('type');
        
        $this->container->db->table('taskchecklist')->insert(array('taskid'=>$taskid, 'text'=>$text, 'byuser'=>$byuser, 'type'=>$type));
        
        return $response->withRedirect($this->router->pathFor('task', ['id' => $taskid])); 
    }

    public function editTaskChecklistModal($request, $response){
        $args['users'] = Tasks::getUsers();
        $args['id'] = $request->getParam('id');
        $args['item'] = $this->container->db->table('taskchecklist')->where('id', $args['id'])->first();
        return $this->view->render($response, 'tasks/taskcheklisteditform.twig', $args);
    }

    public function editTaskChecklist($request, $response){
        
        $checklistid = $request->getParam('checklistid');
        $taskid = $request->getParam('taskid');
        $text = $request->getParam('text');
        $byuser = $request->getParam('byuser');
        $type = $request->getParam('type');
        
        $this->container->db->table('taskchecklist')->where('id', $checklistid)->update(array('text'=>$text, 'byuser'=>$byuser, 'type'=>$type));
        
        return $response->withRedirect($this->router->pathFor('task', ['id' => $taskid]). "#" . $checklistid); 
    }

    public function markTaskChecklist($request, $response) {
       
        $is_done = $this->container->db->table('taskchecklist')->select('is_done')->where('id', $request->getParam('id'))->value('is_done');
         
         if($is_done == '1') {
            $task = $this->container->db->table('taskchecklist')->where('id', $request->getParam('id'))->update(['is_done' => '2',]);
            $status = 'true';
             
         } else {
            $task = $this->container->db->table('taskchecklist')->where('id', $request->getParam('id'))->update(['is_done' => '1',]);
            $status = 'false';
         }
             return json_encode(array("status" => $status)); die();
    }

    public function addNewTaskChecklistCommentModal($request, $response){
        $args['cheklistid'] = $request->getParam('id');
        $args['userid'] = $this->container->auth->user()->id;
        $args['taskid'] = Tasks::getTaskIdByTaskChecklist($args['cheklistid'])[0]->taskid;
        return $this->view->render($response, 'tasks/taskchecklistcommentadd.twig', $args);
    }

    public function addNewTaskChecklistComment($request, $response) {
        
        $commenttext = $request->getParam('commenttext');
        $taskid = $request->getParam('taskid');
        $userid = $request->getParam('userid');
        $checklistid = $request->getParam('checklistid');
        
        $new_comment = $this->container->db->table('taskchecklistcomments')->insert(array(
            'taskid'=>$taskid, 
            'commenttext'=>$commenttext, 
            'userid'=>$userid,
            'checklistid'=>$checklistid)
        );

        $username = $this->container->auth->user()->name;
        $task = $this->container->db->table('tasks')->where('taskid', $taskid)->first();

        $this->telegram->tg_msg($username . " added new comment to task # " . $taskid . "\nTitle: " . $task->tasktitle . "\nLink: https://" . $_SERVER['SERVER_NAME']. "/view/" . $taskid. "#" . $checklistid);

        if($new_comment) {
            return $response->withRedirect($this->router->pathFor('task', ['id' => $taskid]) . "#" . $checklistid); 
        }
        
    }

    public function editTaskChecklistComment($request, $response){
        $args['id'] = $request->getParam('id');
        $args['text'] = $this->container->db->table('taskchecklistcomments')->where('id', $args['id'])->first();
        return $this->view->render($response, 'tasks/taskchecklistcommentedit.twig', $args);
    }

    public function editChecklistComment($request, $response){
        $id = $request->getParam('id');
        $commenttext = $request->getParam('commenttext');
        $taskid = $request->getParam('taskid');
        $checklistid = $request->getParam('checklistid');
        
        $update_comment = $this->container->db->table('taskchecklistcomments')->where('id', $id)->update(array(
            'commenttext'=>$commenttext)
        );

        if($update_comment) {
            return $response->withRedirect($this->router->pathFor('task', ['id' => $taskid]) . "#" . $checklistid); 
        }
    }

    public function deleteChecklistComment($request, $response, $id){
        $taskid = Tasks::getTaskIdByTaskChecklistComment($id['id']);
        $this->container->db->table('taskchecklistcomments')->where(array('id'=>$id['id'], 'userid'=> $userid = $this->container->auth->user()->id))->delete();
        return $response->withRedirect($this->router->pathFor('task', ['id' => $taskid[0]->taskid]));

    }
 
}