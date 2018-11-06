<?php

use Respect\Validation\Validator as v;

//Starting new session in aour application
session_start();

// require the main Slim 3 Framework file
require __DIR__ . "/../vendor/autoload.php";

// require the main Slim 3 Framework file
require __DIR__ . "/settings.php";

//We need attach things to container. So we firstly grab the container
$container = $app->getContainer();

$container['upload_directory'] =  __DIR__ . '/../assets/uploads';

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

$container['telegram'] = function ($container) {
    return new \App\Misc\Telegram;
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


//Attach validator to our container
$container['validator'] = function ($container) {
    return new \App\Validation\Validator;
};

$container['AuthController'] = function ($container) {
    return new \App\Controllers\AuthController($container);
}; 

$container['TaskController'] = function ($container) {
    return new \App\Controllers\TaskController($container);
};

$container['CommentController'] = function ($container) {
    return new \App\Controllers\CommentController($container);
}; 


// require the routing file
require __DIR__ . "/../app/routes.php";