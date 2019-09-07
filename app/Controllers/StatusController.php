<?php

namespace App\Controllers;

use App\Models\Tasks;

class StatusController extends Controller
{

    private function selectQuery($table, $what, $where = array())
    {
        return $this->container->db
            ->table($table)
            ->select($what)
            ->where($where)
            ->value($what);
    }

    public function updateStatus($request, $response)
    {
        $current = $this->selectQuery('tasks', 'taskstatus', array('taskid' => $request->getParam('task')));

        if ($current != $request->getParam('status')) {

            Tasks::where(
                'taskid', $request->getParam('task')
            )->update([
                'taskstatus' => $request->getParam('status'),
            ]);

            $status = $this->selectQuery('taskstatus', 'statusname', array('statusid' => $request->getParam('status')));
            $tasktitle = $this->selectQuery('tasks', 'tasktitle', array('taskid' => $request->getParam('task')));
            $this->container->telegram->updateStatus($status, $tasktitle, $request->getParam('task'));

        } else {

            echo "The same status";
        }
    }

    public function updatePublishStatus($request, $response)
    {
        $current = $this->selectQuery('tasks', 'is_draft', array('taskid' => $request->getParam('taskid')));

        if ($current != $request->getParam('is_draft')) {

            Tasks::where(
                'taskid', $request->getParam('taskid')
            )->update([
                'is_draft' => $request->getParam('is_draft'),
            ]);

            echo "Status has been updated!";

        } else {

            echo "The same status";
        }
    }

}
