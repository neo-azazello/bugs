<?php

namespace App\Controllers;

use App\Models\User;
use App\Controllers\Controller;
use Respect\Validation\Validator as v;

class AuthController extends Controller {
    
    //Renders Login page
    public function getSignIn($request, $response) {
        
        return $this->view->render($response, 'auth/signin.twig');
    
    }

    //Here we check if user eneter valid cde
    public function postSignIn($request, $response) {
        

        //Here we create rules that check data received from form before sending to database.    
        $validation = $this->validator->validate($request, [
            'code' => v::noWhitespace()->notEmpty(),

        ]);
        
        if($validation->failed()){
            
            $this->flash->addMessage('error', 'Field can not be empty');
        }

        $auth = $this->auth->attempt(
            
            $request->getParam('code')
        
        );

        if(!$auth){
            
            $this->flash->addMessage('error', 'Entered code is invalid');
            return $response->withRedirect($this->router->pathFor('auth.signin'));

        }
        
            
        //var_dump($_SERVER['REQUEST_URI']); die();
        $redirect = $response->withRedirect($this->router->pathFor('home'));

        return $redirect;

    }

    //Redirect user to signin page if he signed out from the system
    public function getSignOut($request, $response) {

        $this->auth->logout();
        return $response->withRedirect($this->router->pathFor('auth.signin'));
    }
    
}
