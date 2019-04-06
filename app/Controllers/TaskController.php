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
        
        $userid = $this->container->auth->user()->id;
        $is_admin = $this->container->auth->user()->is_admin;

        if($is_admin == 'true') {
           $view = array ('tasks' => Tasks::getAllTasks());
        } else {
           $view = array ('tasks' => Tasks::getUsersTasks($userid));
        }
        
        return $this->view->render($response, 'tasks/all.twig', $view);
    }
    
    //Load all fulfilled tasks
    public function finished($request, $response) {
        
        $userid = $this->container->auth->user()->id;
        $is_admin = $this->container->auth->user()->is_admin;
        $page = $this->container->pagination->data(4);

        if($is_admin == 'true') {
           $view = array ('tasks' => Tasks::getFinishedTasks(),
                          'paginator' => Pagination::pageSwitcher(4));
        } else {
           $view = array ('tasks' => Tasks::getFinishedTasks($userid),
                          'paginator' => Pagination::pageSwitcher(4));
        }

        return $this->view->render($response, 'tasks/finished.twig', $view);
    }
    
    //Get all tested Tasks
    public function getTestedTasks($request, $response) {
        
        $userid = $this->container->auth->user()->id;
        $is_admin = $this->container->auth->user()->is_admin;
        $page = $this->container->pagination->data(4);

        if($is_admin == 'true') {
            $view = array (
                'tested' => Tasks::getTestedTasks(null, $page),
                'paginator' => Pagination::pageSwitcher(4));
         } else {
            $view = array (
                'tested' => Tasks::getTestedTasks($userid, $page),
                'paginator' => Pagination::pageSwitcher(4));
         }
  
        
        return $this->view->render($response, 'tasks/tested.twig', $view);
    }
 
    //Displays the create page 
    public function createPage($request, $response) {
        $args['users'] = Tasks::getUsers();
        $args['types'] = Tasks::getTaskTypes();
        $args['statuses'] = Tasks::getTaskStatus();
        $args['projects'] = Tasks::getProjects();
        
        return $this->view->render($response, 'tasks/new.twig', $args);
    }
    
    public function moveUploadedFile($directory, $uploadedFile) {
            $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
            $basename = bin2hex(random_bytes(8)); 
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
            
            $users = $this->container->db->table('users')->select('telegramname')->whereIn('id', $request->getParam('assigned'))->implode('telegramname', ', ');
           $this->telegram->tg_msg($_SESSION['name'] . " has added new task for " . $users . "\nLink: https://" . $_SERVER['SERVER_NAME']. "/view/" . $task->id);
        }
        
        
        return $response->withRedirect($this->router->pathFor('task', ['id' => $task->id])); 
    } 
    
    public function daleteTask($request, $response, $id){

        $is_admin = $this->container->auth->user()->is_admin;
        
            if($is_admin == "true" ) {
                $this->telegram->tg_msg($_SESSION['name'] . " deleted task # " . $id['id']);
                $this->container->db->table('tasks')->where('taskid', $id)->delete();
                
            } else {
                $this->container->flash->addMessage('error', 'You can not delete tasks.');
                return $response->withRedirect($this->router->pathFor('all'));
            }

        return $response->withRedirect($this->router->pathFor('all'));
    }
       
    public function details($request, $response, $id) {
        $userid = $this->container->auth->user()->id;
        $tasktype = $this->container->db->table('tasks')->select('tasktypeid')->where('taskid', $id)->value('tasktypeid');

        $args['task'] = Tasks::getConcreteTask($id);
        $args['taskcheklist'] = Tasks::getTaskChecklist($id);
        $args['files'] = Tasks::getTaskFiles($id);
        $args['assigned'] = Tasks::getAssignedUsers($id); 
        $args['statuses'] = Tasks::getTaskStatus();
        $args['comments'] = comment::viewTaskComments($id);
        $args['users'] = Tasks::getUsers();
        $args['checks'] = checks::viewTaskChecklist($id, $userid);
        $args['anothertasks'] = Tasks::getUsersAnotherTasks($userid, $tasktype, $id);
        $args['cheklistcomments'] = Tasks::getTaskChecklistComment($id['id']);

        return $this->view->render($response, 'tasks/view.twig', $args);
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
        
        $is_admin = $this->container->auth->user()->is_admin;
        if($is_admin == "true" ) {
            
            $args['edit'] = Tasks::getConcreteTask($id);
            $args['assigned'] = Tasks::getAssignedUsers($id);
            $args['files'] = Tasks::getTaskFiles($id);
            $args['users'] = Tasks::getUsers();
            $args['types'] = Tasks::getTaskTypes();
            $args['projects'] = Tasks::getProjects();
            $args['statuses'] = Tasks::getTaskStatus();
            
            return $this->view->render($response, 'tasks/edit.twig', $args);
                
        } else {
                
            $this->container->flash->addMessage('error', 'You can not edit this task.');
            return $response->withRedirect($this->router->pathFor('all'));
        }
        
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
    
    public function updatePublishStatus($request, $response){
        
        $current = $this->container->db->table('tasks')->select('is_draft')->where('taskid', $request->getParam('taskid'))->value('is_draft');
        
        if($current != $request->getParam('is_draft')) {
            
        $this->container->db->table('tasks')->where('taskid', $request->getParam('taskid'))->update(['is_draft' => $request->getParam('is_draft'),]);
        
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

}