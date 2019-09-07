<?php

namespace App\Controllers;

use App\Models\Tasks;

class HelperController extends Controller
{
    /*
    This method gets task counts by taskstatus and auth user.
    IF USER is admin returns all data;
     */
    public function getTaskCount($taskstatusid)
    {
        $userid = $this->container->auth->user()->id;
        $is_admin = $this->container->auth->user()->is_admin;

        return Tasks::where(array(
            'tasks.taskstatus' => $taskstatusid,
            'tasks.is_draft' => 'false',
            'taskassigns.userid' => $userid,
        ))->join('taskassigns', 'taskassigns.taskid', '=', 'tasks.taskid')
            ->get()
            ->count();
    }

}
