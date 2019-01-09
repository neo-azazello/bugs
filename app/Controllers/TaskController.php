<?php

namespace App\Controllers;

use App\Models\Tasks;
use App\Models\User;

use Slim\Http\UploadedFile;
use App\Controllers\CommentController as comment;
use App\Controllers\ChecklistController as checks;

use App\Misc\Pagination;

class TaskController extends Controller {

    
    //Loads tasks. If it is admin all if it is user only his tasks
    public function all($request, $response) {

        if(isset($_SESSION['is_admin'])) {
            if($_SESSION['is_admin'] == "true" ){

                $view = array ('tasks' => Tasks::getAllTasks());

            } else {
            
                $userid = $_SESSION['user'];
                $view = array ('tasks' => Tasks::getUsersTasks($userid));
            }
        }
        
        
        return $this->view->render($response, 'tasks/all.twig', $view);
    }
    
    //Load all fulfilled tasks
    public function finished($request, $response) {
        
        if(isset($_SESSION['is_admin'])) {
            if($_SESSION['is_admin'] == "true" ){

                $view = array ('tasks' => Tasks::getFinishedTasks());

            } else {
            
                $userid = $_SESSION['user'];
                $view = array ('tasks' => Tasks::getFinishedTasks($userid));
            }
        }

        return $this->view->render($response, 'tasks/finished.twig', $view);
    }
    
    //Displays the create page 
    public function createPage($request, $response) {

        return $this->view->render($response, 'tasks/new.twig', array (
            'users' => Tasks::getUsers(), 
            'types' => Tasks::getTaskTypes(),
            'statuses' => Tasks::getTaskStatus(),
            'projects' => Tasks::getProjects()
        ));
    }
    
    public function moveUploadedFile($directory, $uploadedFile) {
            $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
            $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
            $filename = sprintf('%s.%0.8s', $basename, $extension);
        
            $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
        
            return $filename;
    }
    
    //Here we create tasks
    public function createTask($request, $response) {
        
        $task = Tasks::create([
            'tasktitle' => $request->getParam('tasktitle'),
            'tasktext' =>  $request->getParam('tasktext'),
            'taskauthor' =>  $request->getParam('taskauthor'),
            'tasktypeid' =>  $request->getParam('tasktypeid'),
            'taskstatus' =>  $request->getParam('taskstatus'),
            'taskproject' =>  $request->getParam('taskproject'),
            'is_draft' =>  $request->getParam('is_draft'),
        ]); 
        
         //Adding task checklist
        if(!empty($request->getParam('text'))) {
            
            foreach($request->getParam('text') as $key=>$text) {
                $bulk[] = array(
                    'taskid' => $task->id,
                    'text' => $text,
                    'byuser' => (int)$request->getParam('byuser')[$key]
                    );
            }
    
            $this->container->db->table('taskchecklist')->insert($bulk);
        }
        
        
        //Adding task assigned users
        if(!empty($request->getParam('assigned'))) {
            
            foreach($request->getParam('assigned') as $userId) {
                $insert_bulk[] = array(
                    'taskid' => $task->id,
                    'userid' => $userId
                    );
            }
    
            $this->container->db->table('taskassigns')->insert($insert_bulk);
        }
        
        //Adding files to task if avvailable
        if(!empty($request->getUploadedFiles()['taskfiles'][0]->getClientFilename())) {
          
          $directory = $this->container->get('upload_directory');
          $uploadedFiles = $request->getUploadedFiles();
          
          foreach ($uploadedFiles['taskfiles'] as $uploadedFile) {
             if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                 $filename = $this->moveUploadedFile($directory, $uploadedFile);
                 $insert_bulk_files[] = array('taskid' => $task->id, 'filename' => $filename );
             }
            
         }
          
            $this->container->db->table('taskfiles')->insert($insert_bulk_files);
        }
        
        if($request->getParam('is_draft') == 'false') {
            
            $this->container->NotificationController->setNotification(
                $task->id, 
                $request->getParam('taskauthor'), 
                $request->getParam('assigned'), 
                'false', 
                $_SESSION['name'] . ' created a task and assigned it to you');
            
            $users = $this->container->db->table('users')->select('telegramname')->whereIn('id', $request->getParam('assigned'))->implode('telegramname', ', ');
           $this->telegram->tg_msg($_SESSION['name'] . " has added new task for " . $users . "\nLink: https://" . $_SERVER['SERVER_NAME']. "/view/" . $task->id);
        }
        
        
        return $response->withRedirect($this->router->pathFor('task', ['id' => $task->id])); 
    }
    
    
    public function daleteTask($request, $response, $id){
        
         if(isset($_SESSION['is_admin'])) {
            if($_SESSION['is_admin'] == "true" ) {
                $taskid = implode('', $id);
                $this->telegram->tg_msg($_SESSION['name'] . " deleted task # " . $taskid);
                $this->container->db->table('tasks')->where('taskid', $id)->delete();
                
            } else {
                
                $this->container->flash->addMessage('error', 'You can not delete tasks.');
            }
         }

        return $response->withRedirect($this->router->pathFor('home'));
    }
    
    
    public function details($request, $response, $id) {
        
        $tasktype = $this->container->db->table('tasks')->select('tasktypeid')->where('taskid', $id)->value('tasktypeid');
        
        return $this->view->render($response, 'tasks/view.twig', array (
            'task' => Tasks::getConcreteTask($id),
            'taskcheklist' => Tasks::getTaskChecklist($id),
            'files' => Tasks::getTaskFiles($id),
            'assigned' => Tasks::getAssignedUsers($id), 
            'statuses' => Tasks::getTaskStatus(),
            'comments' => comment::viewTaskComments($id),
            'checks' => checks::viewTaskChecklist($id, $_SESSION['user']),
            'anothertasks' => Tasks::getUsersAnotherTasks($_SESSION['user'], $tasktype, $id)
        ));
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

    
    public function editTaskPageDatas($request, $response, $id) {
        
      if(isset($_SESSION['is_admin'])) {
        if($_SESSION['is_admin'] == "true" ) {

                return $this->view->render($response, 'tasks/edit.twig', array (
                    'edit' => Tasks::getConcreteTask($id), 
                    'assigned' => Tasks::getAssignedUsers($id),
                    'files' => Tasks::getTaskFiles($id),
                    'taskcheklist' => Tasks::getTaskChecklist($id),
                    'users' => Tasks::getUsers(), 
                    'types' => Tasks::getTaskTypes(),
                    'projects' => Tasks::getProjects(),
                    'statuses' => Tasks::getTaskStatus()
                    )
                );
                
            } else {
                
                $this->container->flash->addMessage('error', 'You can not edit this task.');
            }
         }

        return $response->withRedirect($this->router->pathFor('home'));
        
    }
    
    public function updateTask($request, $response) {
        
       $task = Tasks::where('taskid', $request->getParam('taskid'))->update([
                'tasktitle' => $request->getParam('tasktitle'),
                'tasktext' =>  $request->getParam('tasktext'),
                'tasktypeid' =>  $request->getParam('tasktypeid'),
                'taskstatus' =>  $request->getParam('taskstatus'),
                'taskproject' =>  $request->getParam('taskproject'),
                'is_draft' =>  $request->getParam('is_draft'),
        ]);
        
        $currentassigned = $this->container->db->table('taskassigns')->where('taskid', $request->getParam('taskid'))->delete();
        
       foreach($request->getParam('assigned') as $userId) {
            $insert_bulk[] = array(
                'taskid' => $request->getParam('taskid'),
                'userid' => $userId
                );
        }
            
        $this->container->db->table('taskassigns')->insert($insert_bulk);
        
        
        if(!empty($request->getParam('text'))) {
            
            $current = $this->container->db->table('taskchecklist')->where('taskid', $request->getParam('taskid'))->delete();
            
            foreach($request->getParam('text') as $key=>$text) {
                $bulk[] = array(
                    'taskid' => $request->getParam('taskid'),
                    'text' => $text,
                    'is_done' => $request->getParam('is_done')[$key],
                    'byuser' => (int)$request->getParam('byuser')[$key]
                    );
            }
            
            $this->container->db->table('taskchecklist')->insert($bulk);
    
        }
        
        //Adding files to task if avvailable
        if(!empty($request->getUploadedFiles()['taskfiles'][0]->getClientFilename())) {
          
          $directory = $this->container->get('upload_directory');
          $uploadedFiles = $request->getUploadedFiles();
          
          foreach ($uploadedFiles['taskfiles'] as $uploadedFile) {
             if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                 $filename = $this->moveUploadedFile($directory, $uploadedFile);
                 $insert_bulk_files[] = array('taskid' => $request->getParam('taskid'), 'filename' => $filename );
             }
            
         }
          
            $this->container->db->table('taskfiles')->insert($insert_bulk_files);
        }
        
        if($request->getParam('is_draft') == 'false') {
            
            $this->container->NotificationController->setNotification(
                $request->getParam('taskid'), 
                $request->getParam('taskauthor'), 
                $request->getParam('assigned'), 
                'false', 
                $_SESSION['name'] . ' updated task where you are assigned');
            
            $users = $this->container->db->table('users')->select('telegramname')->whereIn('id', $request->getParam('assigned'))->implode('telegramname', ', ');
            $this->telegram->tg_msg($_SESSION['name'] . " just updated task # " . $request->getParam('taskid') . "\nTitle: " . $request->getParam('tasktitle') . "\nAssigned: " .  $users . "\nLink: https://" . $_SERVER['SERVER_NAME']. "/view/" . $request->getParam('taskid'));
        }
        
        $this->container->flash->addMessage('success', 'Task has been updated successfully.');
        return $response->withRedirect($this->router->pathFor('task', ['id' => $request->getParam('taskid')]));
        
        
    }
    
    public function updateStatus($request, $response){
        
        $current = $this->container->db->table('tasks')->select('taskstatus')->where('taskid', $request->getParam('task'))->value('taskstatus');
        
        if($current != $request->getParam('status')) {
            
            $this->container->db->table('tasks')->where('taskid', $request->getParam('task'))->update(['taskstatus' => $request->getParam('status'),]);
            
            $taskstatus = $this->container->db->table('taskstatus')->select('statusname')->where('statusid', $request->getParam('status'))->value('statusname');
            $tasktitle = $this->container->db->table('tasks')->select('tasktitle')->where('taskid', $request->getParam('task'))->value('tasktitle');
            
            $this->telegram->tg_msg($_SESSION['name'] . " updated task status to → " . $taskstatus . "\nTitle: " . $tasktitle . "\nLink: http://" . $_SERVER['SERVER_NAME']. "/view/" .$request->getParam('task'));
        
        } else {
            
            echo "The same status";
        }
    }
    
    
    public function deleteTaskFile($request, $response){
        
        $filename = $this->container->db->table('taskfiles')->select('filename')->where('fileid', $request->getParam('file'))->value('filename');
        $directory = $this->container->get('upload_directory');
        unlink($directory . "/" . $filename);
        
        $this->container->db->table('taskfiles')->where('fileid', $request->getParam('file'))->delete();
    }
    
    public function getDraftTasks($request, $response) {
        
        return $this->view->render($response, 'tasks/drafts.twig', array ('drafts' => Tasks::getDraftTasks()));
    }
    
    
    public function getTestedTasks($request, $response) {
        
        $page = $this->container->pagination->data(4);
  
        if(isset($_SESSION['is_admin'])) {
            if($_SESSION['is_admin'] == "true" ){

                $view = array (
                    'tested' => Tasks::getTestedTasks(null, $page),
                    'paginator' => Pagination::pageSwitcher(4));

            } else {
            
                $userid = $_SESSION['user'];
                $view = array (
                    'tested' => Tasks::getTestedTasks($userid, $page),
                    'paginator' => Pagination::pageSwitcher(4));
            }
        }
        
        return $this->view->render($response, 'tasks/tested.twig', $view);
    }
    

    
}