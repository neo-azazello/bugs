<?php

namespace App\Controllers;

use App\Models\Notifications;

class NotificationController extends Controller {
    
    
    public function getUnreadNotifications(){
        
        $notifications = Notifications::getNotifications();
        
        return $notifications;
    }
    
    public function setNotification($taskid, $authorid, $assigned = array(null), $has_read, $text){
        
        foreach($assigned as $userId) {
            $insert_bulk[] = array(
                'taskid' => $taskid,
                'authorid' => $authorid,
                'assignedid'=> $userId,
                'has_read' => $has_read,
                'text' => $text
            );
        }
    
                
       $this->container->db->table('notifications')->insert($insert_bulk);
        
    }
    
    public function updateNotification($request, $response) {
        
        $this->container->db->table('notifications')->where('id', $request->getParam('noty'))->update([
            'has_read' => 'true',
        ]);
        
    }
    
    public function markAllRead($request, $response) {
        
        if(isset($_SESSION['user'])){
            
          $this->container->db->table('notifications')->where('assignedid', $_SESSION['user'])->update([
            'has_read' => 'true',
          ]);
            
        }
        
        $this->container->flash->addMessage('info', 'All notifications are marked as read.');
        return $response->withRedirect($this->router->pathFor('home'));
    }
    
    
}