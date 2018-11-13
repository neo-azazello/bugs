<?php

namespace App\Controllers;

use App\Models\Settings;

class SettingsController extends Controller {
    
    public function getAllSettings($request, $response) {

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
        
        $this->container->flash->addMessage('project', 'New Project has been added.');
        
        return $response->withRedirect($this->router->pathFor('settings'));
    }
    
    
}