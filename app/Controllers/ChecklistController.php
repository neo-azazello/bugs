<?php

namespace App\Controllers;

use App\Models\Checklists;

class ChecklistController extends Controller {
    
    public static function viewTaskChecklist($id, $userid) {
        $checklist = Checklists::getTaskChecklist($id, $userid);
            return $checklist;
        
    }
    
    public function addNewChecklist($request, $response){
        
        $yell = Checklists::create([
            'text' => $request->getParam('text'),
            'taskid' => $request->getParam('taskid'),
            'userid'=> $_SESSION['user']
        ]);
        
        $getcheklist = Checklists::getTaskChecklistById($yell->id);
        
        return json_encode(array_shift($getcheklist)); die();
    }

    
    
    public function deleteChecklist($request, $response) {
        
        $this->container->db->table('checklist')->where('id', $request->getParam('id'))->delete();
        
    }
    
    
    public function markAsDone ($request, $response){
        
        $is_done = $this->container->db->table('checklist')->select('done')->where('id', $request->getParam('id'))->value('done');
        
        if($is_done == 'false') {
            
            $task = Checklists::where('id', $request->getParam('id'))->update([
                    'done' => 'true',
            ]);
            
            $status = 'true';
        
       } else {
           
            $task = Checklists::where('id', $request->getParam('id'))->update([
                    'done' => 'false',
            ]);
            
            $status = 'false';
       }
    
        return json_encode(array("status" => $status)); die();
    }
    
    
}