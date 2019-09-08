<?php

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;

$app->get('/', 'LandingController:index')->setName('home');

//If guest go to signin
$app->group('', function () {

    //to signin
    $this->get('/signin', 'AuthController:getSignIn')->setName('auth.signin');
    $this->post('/signin', 'AuthController:postSignIn');

})->add(new GuestMiddleware($container));

$app->group('', function () {

    //Logging out user
    $this->get('/signout', 'AuthController:getSignOut')->setName('auth.signout');

    //General Task Routes
    $this->get('/all', 'TaskController:all')->setName('all');
    $this->get('/create', 'TaskController:createPage')->setName('create');
    $this->post('/create', 'TaskController:createTask');
    $this->get('/view/{id}', 'TaskController:details')->setName('task');
    $this->get('/edit/{id}', 'TaskController:editTaskPageDatas')->setName('edit');
    $this->post('/edit', 'TaskController:updateTask')->setName('postedit');
    $this->get('/delete/{id}', 'TaskController:daleteTask');

    $this->post('/files', 'file:deleteTaskFile')->setName('files');
    $this->get('/drafts', 'TaskController:getDraftTasks')->setName('drafts');
    $this->get('/fixed', 'TaskController:finished')->setName('fixed');
    $this->get('/tested', 'TaskController:getTestedTasks')->setName('tested');

    //Controll Status
    $this->post('/status', 'StatusController:updateStatus')->setName('status');
    $this->post('/is_draft', 'StatusController:updatePublishStatus')->setName('is_draft');

    //Task Comments Routes
    $this->post('/addcomment', 'CommentController:addNewComment');
    $this->post('/deletecomment', 'CommentController:deleteComment');

    //Task checklists routes
    $this->post('/finished', 'TaskChecklistController:markTaskChecklist');
    $this->post('/addnewtaskcheklist', 'TaskChecklistController:addNewTaskChecklist')->setName('addnewtaskcheklist');
    $this->get('/editchecklist/', 'TaskChecklistController:editTaskChecklistModal');
    $this->post('/edittaskcheklist', 'TaskChecklistController:editTaskChecklist')->setName('edittaskcheklist');
    $this->get('/addchecklistcomment/', 'TaskChecklistController:addNewTaskChecklistCommentModal');
    $this->post('/addnewtaskcheklistcomment', 'TaskChecklistController:addNewTaskChecklistComment')->setName('addnewtaskcheklistcomment');
    $this->get('/editchecklistcomment/', 'TaskChecklistController:editTaskChecklistComment');
    $this->post('/editnewtaskcheklistcomment', 'TaskChecklistController:editChecklistComment')->setName('editnewtaskcheklistcomment');
    $this->get('/deletechecklistcomment/{id}', 'TaskChecklistController:deleteChecklistComment');

    //User Own checklists
    $this->post('/addcheklist', 'ChecklistController:addNewChecklist');
    $this->post('/deletechecklist', 'ChecklistController:deleteChecklist');
    $this->post('/done', 'ChecklistController:markAsDone');

    //Routes for Profile settings
    $this->post('/adduser', 'ProfileController:addNewUser')->setName('adduser');
    $this->post('/settingsprofile', 'ProfileController:settingsProfile')->setName('settingsprofile');
    $this->get('/profile', 'ProfileController:getProfileDetails')->setName('profile');
    $this->post('/updateprofile', 'ProfileController:updateProfile')->setName('updateprofile');

    //Routes for Settings
    $this->get('/settings', 'SettingsController:getAllSettings')->setName('settings');
    $this->post('/addproject', 'SettingsController:newProjectAdd')->setName('addproject');
    $this->post('/addtasktype', 'SettingsController:newTaskType')->setName('addtasktype');
    $this->post('/addtaskstatus', 'SettingsController:newTaskStatus')->setName('addtaskstatus');
    $this->get('/delete/{table}/{column}/{id}', 'SettingsController:deleteSettingsData');

})->add(new AuthMiddleware($container));
