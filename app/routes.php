<?php

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;


//If guest go to signin
$app->group('', function () {
    
    //to signin
    $this->get('/signin', 'AuthController:getSignIn')->setName('auth.signin');
    $this->post('/signin', 'AuthController:postSignIn');
    
})->add(new GuestMiddleware($container));



$app->group('', function () {
    
    //Shows all bug lists in home page
    $this->get('/', 'TaskController:all')->setName('home');
    
    //These list are all fixed ones
    $this->get('/fixed', 'TaskController:finished')->setName('fixed');
    
    //Logging out user
    $this->get('/signout', 'AuthController:getSignOut')->setName('auth.signout');
    
    //Create link for a new bug
    $this->get('/create', 'TaskController:createPage')->setName('create');
     
     //Post new data
    $this->post('/create', 'TaskController:createTask');
    
    //When bug created or user clicks on list it open detail view
    $this->get('/view/{id}', 'TaskController:details')->setName('task');
    
     //Delete task
    $this->get('/delete/{id}', 'TaskController:daleteTask');
    
    //Edit tasks page
    $this->get('/edit/{id}', 'TaskController:editTaskPageDatas')->setName('edit');
    
    //Post updates
    $this->post('/edit', 'TaskController:updateTask')->setName('postedit');
    
    //status updates
    $this->post('/status', 'TaskController:updateStatus')->setName('status');
    
    //Publish status update
    $this->post('/is_draft', 'TaskController:updatePublishStatus')->setName('is_draft');
    
    //remove files
    $this->post('/files', 'TaskController:deleteTaskFile')->setName('files');

    $this->post('/addcomment', 'CommentController:addNewComment');
    
    $this->post('/deletecomment', 'CommentController:deleteComment');
    
    $this->get('/profile/{id}', 'ProfileController:getProfileDetails')->setName('profile');
    
    $this->post('/updateprofile', 'ProfileController:updateProfile')->setName('updateprofile');
    
    $this->get('/drafts', 'TaskController:getDraftTasks')->setName('drafts');
    
    $this->get('/tested', 'TaskController:getTestedTasks')->setName('tested');
    
    $this->post('/updatenotify', 'NotificationController:updateNotification');
    $this->post('/markallread', 'NotificationController:markAllRead')->setName('markallread');

    $this->get('/settings', 'SettingsController:getAllSettings')->setName('settings');
    
    $this->post('/addproject', 'SettingsController:newProjectAdd')->setName('addproject');
    $this->post('/addtasktype', 'SettingsController:newTaskType')->setName('addtasktype');
    $this->post('/addtaskstatus', 'SettingsController:newTaskStatus')->setName('addtaskstatus');
    $this->post('/adduser', 'ProfileController:addNewUser')->setName('adduser');
    
    $this->get('/delete/{table}/{column}/{id}', 'SettingsController:deleteSettingsData');
    
    $this->post('/settingsprofile', 'ProfileController:settingsProfile')->setName('settingsprofile');
    
    $this->post('/addcheklist', 'ChecklistController:addNewChecklist');
    $this->post('/deletechecklist', 'ChecklistController:deleteChecklist');
    $this->post('/done', 'ChecklistController:markAsDone');

    $this->post('/finished', 'TaskController:markTaskChecklist');
    $this->post('/addnewtaskcheklist', 'TaskController:addNewTaskChecklist')->setName('addnewtaskcheklist');

})->add(new AuthMiddleware($container));