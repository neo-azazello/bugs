<?php

use Odan\Slim\Session\Adapter\PhpSessionAdapter;
use Odan\Slim\Session\Session;

//Starting new session in aour application
$lifetime = 86400;
session_start();
setcookie(session_name(), session_id(), time() + $lifetime);

// require the main Slim 3 Framework file
require __DIR__ . "/../vendor/autoload.php";

// require the main Slim 3 Framework file
require __DIR__ . "/settings.php";

//We need attach things to container. So we firstly grab the container
$container = $app->getContainer();

$container['upload_directory'] = __DIR__ . '/../assets/uploads';

//Using Eloquent db component we try to connect to our mysql db.
//Firs we instantiate DB class and later we call cnnector.
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

//Adding db to container in order to access it from controllers
$container['db'] = function ($container) use ($capsule) {
    return $capsule;
};

$container['auth'] = function ($container) {
    return new \App\Misc\Auth;
};

$container['session'] = function ($container) {
    $settings = $container->get('settings');
    $adapter = new PhpSessionAdapter();
    $session = new Session($adapter);
    $session->setOptions($settings['session']);

    return $session;
};

//Adding to container flash messages
$container['flash'] = function ($container) {
    return new \Slim\Flash\Messages;
};

//Starting to bind everything with container
//First the view
$container['view'] = function ($container) { //when we would use view it will resolve from container

    //Creating new Twig View Instance, then specify the folder and give options
    $view = new \Slim\Views\Twig(__DIR__ . '/../views', [
        //An array of options
        'cache' => false,
    ]);

    //Extension helps us to generate urls to different routes within our views
    $view->addExtension(new \Slim\Views\TwigExtension(
        //We need router because we are going to generate urls for links
        $container->router,
        //Pull in the current url
        $container->request->geturi()
    ));

    //Make available Auth class inside of our templates
    $view->getEnvironment()->addGlobal('auth', [
        //Here we are essentaily doing is we are calling the database once but store it inside of our view variables
        'check' => $container->auth->check(),
        'user' => $container->auth->user(),
    ]);

    //Giving availibiity of flash functionality to our twig views
    $view->getEnvironment()->addGlobal('flash', $container->flash);

    $markdown = new Twig_SimpleFunction('markdown', function ($text) {
        $mkparser = new \Parsedown();
        return $mkparser->text($text);
    });

    $view->getEnvironment()->addFunction($markdown);

    return $view;
};

$container['LandingController'] = function ($container) {
    return new \App\Controllers\LandingController($container);
};

//Attach validator to our container
$container['validator'] = function ($container) {
    return new \App\Validation\Validator;
};

$container['ProfileController'] = function ($container) {
    return new \App\Controllers\ProfileController($container);
};

$container['AuthController'] = function ($container) {
    return new \App\Controllers\AuthController($container);
};

$container['TaskController'] = function ($container) {
    return new \App\Controllers\TaskController($container);
};

$container['TaskChecklistController'] = function ($container) {
    return new \App\Controllers\TaskChecklistController($container);
};

$container['CommentController'] = function ($container) {
    return new \App\Controllers\CommentController($container);
};

$container['ChecklistController'] = function ($container) {
    return new \App\Controllers\ChecklistController($container);
};

$container['SettingsController'] = function ($container) {
    return new \App\Controllers\SettingsController($container);
};

$container['WikiController'] = function ($container) {
    return new \App\Controllers\WikiController($container);
};

$container['helper'] = function ($container) {
    return new \App\Controllers\HelperController($container);
};

$container['pagination'] = function ($container) {
    return new \App\Controllers\PaginationController($container);
};

$container['telegram'] = function ($container) {
    return new \App\Controllers\TelegramController($container);
};

$container['file'] = function ($container) {
    return new \App\Controllers\FileController($container);
};

$container['StatusController'] = function ($container) {
    return new \App\Controllers\StatusController($container);
};

// require the routing file
require __DIR__ . "/../app/routes.php";
