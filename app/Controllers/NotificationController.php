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
    
    
}