<?php

namespace App\Controllers;

use App\Models\User;
use Respect\Validation\Validator as v;

class ProfileController extends Controller {
    
    public function getProfileDetails($request, $response, $id) {
        
        return $this->view->render($response, 'auth/profile.twig', array (
                'user' => User::getUserDetails($id)
            ));
        
    }
    
    public function updateProfile($request, $response) {
    
    
        if (empty($request->getParam('photo'))) {
            
            $photo = $this->container->db->table('users')->select('photo')->where('id', $_SESSION['user'])->value('photo');
            
        } else {
            
            $photo = $request->getParam('photo');
        }
        
        $validation = $this->validator->validate($request, [
            'email' => v::noWhitespace()->notEmpty()->email(),
            'name' => v::notEmpty(),
            'code' => v::noWhitespace()->notEmpty(),
            'telegramname' => v::noWhitespace()->notEmpty(),

        ]);
        
        if($validation->failed()){
            
             $this->container->flash->addMessage('error', 'Some data was invalid. Profile has not been updated.');

            return $response->withRedirect($this->router->pathFor('profile', ['id' => $_SESSION['user']]));

        }
        
        
       $profile = User::where('id', $_SESSION['user'])->update([
                'name' => $request->getParam('name'),
                'code' =>  $request->getParam('code'),
                'email' =>  $request->getParam('email'),
                'telegramname' =>  $request->getParam('telegramname'),
                'photo' =>  $photo,
        ]);
        
        
        
        $this->container->flash->addMessage('success', 'Your profile has been updated successfully.');
        return $response->withRedirect($this->router->pathFor('profile', ['id' => $_SESSION['user']]));
        
    }
    
    
    public function settingsProfile($request, $response) {
    
    
        if (empty($request->getParam('photo'))) {
            
            $photo = $this->container->db->table('users')->select('photo')->where('id', $request->getParam('id'))->value('photo');
            
        } else {
            
            $photo = $request->getParam('photo');
        }
        
       $profile = User::where('id', $request->getParam('id'))->update([
                'name' => $request->getParam('name'),
                'code' =>  $request->getParam('code'),
                'email' =>  $request->getParam('email'),
                'telegramname' =>  $request->getParam('telegramname'),
                'is_active' =>  $request->getParam('is_active'),
                'photo' =>  $photo,
        ]);

        $this->container->flash->addMessage('success', 'Profile has been updated successfully.');
        return $response->withRedirect($this->router->pathFor('settings'));
        
    }
    
    
    public function addNewUser($request, $response){
            
            
       $validation = $this->validator->validate($request, [
            'email' => v::noWhitespace()->notEmpty()->email(),
            'name' => v::notEmpty(),
            'code' => v::noWhitespace()->notEmpty(),
            'telegramname' => v::noWhitespace()->notEmpty(),

        ]);
        
        if($validation->failed()){
            
            $this->container->flash->addMessage('error', 'Some data was invalid. User can not be created.');
            return $response->withRedirect($this->router->pathFor('settings'));

        }
        
        $user = User::create([
            'photo' => $request->getParam('photo'),
            'name' =>  $request->getParam('name'),
            'email' =>  $request->getParam('email'),
            'code' =>  $request->getParam('code'),
            'telegramname' =>  $request->getParam('telegramname'),
        ]);
       
       $this->container->flash->addMessage('success', 'New user has been successfully added.');
       return $response->withRedirect($this->router->pathFor('settings'));
        
    }

    
}