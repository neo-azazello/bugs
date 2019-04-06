<?php

namespace App\Controllers;

class LandingController extends Controller {

    public function index($request, $response){
        $args['backenddone'] = $this->getCounts('taskchecklist', array('type'=>'backend', 'is_done'=>2));
        $args['backendnotdone'] = $this->getCounts('taskchecklist', array('type'=>'backend','is_done'=>1));
        $args['frontenddone'] = $this->getCounts('taskchecklist', array('type'=>'frontend', 'is_done'=>2));
        $args['frontendnotdone'] = $this->getCounts('taskchecklist', array('type'=>'frontend', 'is_done'=>1));
        return $this->view->render($response, 'landing/index.twig', $args);
    }

    private function getCounts($table, $where=array()){
        $db = $this->container->db;
        $query = $db->table($table);
            if(!empty($where)){
                $query->where($where);
            }
        $result = $query->get()->count();
        return $result;
    }

}