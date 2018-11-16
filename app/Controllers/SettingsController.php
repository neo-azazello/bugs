<?php

namespace App\Controllers;

use App\Models\Settings;

class SettingsController extends Controller {
    
    public function getAllSettings($request, $response) {
        
       if(isset($_SESSION['is_admin'])) {
            if($_SESSION['is_admin'] == "false" ){
               return $response->withRedirect($this->router->pathFor('home'));
            }
        }
        
        return $this->view->render($response, 'settings/view.twig', array (
          'users' => Settings::query('users'),
          'statuses' => Settings::query('taskstatus'),
          'types' => Settings::query('tasktypes'),
          'projects' => Settings::query('projects')
        ));
    }
    
    
    //NEW PROJECT ADD
    public function newProjectAdd($request, $response) {
    
        $old_date_timestamp = strtotime($request->getParam('projectsartdate'));
        $new_date = date('Y-m-d H:i:s', $old_date_timestamp);
        
        $this->container->db->table('projects')->insert([
            'projectname' => $request->getParam('projectname'),
            'projectlogo' =>  $request->getParam('projectlogo'),
            'projectsartdate' =>  $new_date,
        ]);
        
        $this->container->flash->addMessage('success', 'New Project has been added.');
        
        return $response->withRedirect($this->router->pathFor('settings'));
    }
    
    public function newTaskType($request, $response) {
        
        $this->container->db->table('tasktypes')->insert([
            'tasktypename' => $request->getParam('tasktypename'),
            'tasktypecolor' =>  $request->getParam('tasktypecolor'),
        ]);
        
      $this->container->flash->addMessage('success', 'New task type has been added.');
        
      return $response->withRedirect($this->router->pathFor('settings'));
    }
    
    public function newTaskStatus($request, $response) {
        
        $this->container->db->table('taskstatus')->insert([
            'statusname' => $request->getParam('statusname'),
            'statuscolor' =>  $request->getParam('statuscolor'),
        ]);
        
      $this->container->flash->addMessage('success', 'New task status has been added.');
        
      return $response->withRedirect($this->router->pathFor('settings'));
    }
    
    public function deleteSettingsData($request, $response, $id) {
        
        if(isset($_SESSION['is_admin'])) {
            if($_SESSION['is_admin'] == "false" ){
               $this->container->flash->addMessage('error', 'You are not allowed to delete.');
               return $response->withRedirect($this->router->pathFor('home'));
            }
        }
        
        $this->container->db->table($id['table'])->where($id['column'], $id['id'])->delete();
        $this->container->flash->addMessage('success', 'Entity has been deleted successfully.');
        return $response->withRedirect($this->router->pathFor('settings'));
    
        
    }
    
    
}