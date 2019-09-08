<?php

namespace App\Controllers;

use App\Controllers\ChecklistController as checks;
use App\Controllers\CommentController as comment;
use App\Misc\Pagination;
use App\Models\Tasks;
use App\Models\User;

class TaskController extends Controller
{

    //Displays the create page
    public function createPage($request, $response)
    {
        $args['users'] = Tasks::getUsers();
        $args['types'] = Tasks::getTaskTypes();
        $args['statuses'] = Tasks::getTaskStatus();
        $args['projects'] = Tasks::getProjects();

        return $this->view->render($response, 'tasks/new.twig', $args);
    }

    //Loads tasks. If it is admin all if it is user only his tasks
    public function all($request, $response)
    {

        $userid = $this->container->auth->user()->id;
        $is_admin = $this->container->auth->user()->is_admin;

        if ($is_admin == 'true') {
            $view['tasks'] = $this->getAllTasks();
        } else {
            $view['tasks'] = $this->getAllTasks($userid);

        }

        return $this->view->render($response, 'tasks/all.twig', $view);
    }

    //This method is retriving data for all method
    private function getAllTasks($userid = null)
    {
        $data = Tasks::getTasks([], $userid, true);
        $result = array();

        foreach ($data as $key => $element) {
            $result[$element->projectname]["tasks"][] = [
                "taskid" => $element->taskid,
                "tasktitle" => $element->tasktitle,
                "created_at" => $element->created_at,
                "name" => $element->name,
                "tasktypename" => $element->tasktypename,
                "tasktypecolor" => $element->tasktypecolor,
                "statusname" => $element->statusname,
                "statuscolor" => $element->statuscolor,
                "projectname" => $element->projectname,
                "projectsartdate" => $element->projectsartdate,
                "is_draft" => $element->is_draft,
                "assignedUsers" => $element->assignedUsers,
                "commentcount" => $element->commentcount,
                "checklist" => $element->checklist,
                "checklistdone" => $element->checklistdone,
            ];

        }

        return $result;

    }

    //Load all fulfilled tasks
    public function finished($request, $response)
    {

        $userid = $this->container->auth->user()->id;
        $is_admin = $this->container->auth->user()->is_admin;
        $page = $this->container->pagination->data(3);

        if ($is_admin == 'true') {
            $view = array(
                'tasks' => Tasks::getTasks($page, null, false, true),
                'count' => $this->container->helper->getTaskCount(4),
                'paginator' => $this->container->pagination->pageSwitcher());
        } else {
            $view = array(
                'tasks' => Tasks::getTasks($page, $userid, false, true), // $page = array, userid, isCurrent = false, isDone = true
                'count' => $this->container->helper->getTaskCount(3),
                'paginator' => $this->container->pagination->pageSwitcher());
        }

        return $this->view->render($response, 'tasks/finished.twig', $view);
    }

    //Get all tested Tasks
    public function getTestedTasks($request, $response)
    {

        $userid = $this->container->auth->user()->id;
        $is_admin = $this->container->auth->user()->is_admin;
        $page = $this->container->pagination->data(4);

        if ($is_admin == 'true') {
            $view = array(
                'tested' => Tasks::getTasks($page, null, false, false, true),
                'count' => $this->container->helper->getTaskCount(4),
                'paginator' => $this->container->pagination->pageSwitcher());
        } else {
            $view = array(
                'tested' => Tasks::getTasks($page, $userid, false, false, true),
                'count' => $this->container->helper->getTaskCount(4),
                'paginator' => $this->container->pagination->pageSwitcher());
        }

        return $this->view->render($response, 'tasks/tested.twig', $view);
    }

    //Here we create tasks
    public function createTask($request, $response)
    {

        $task = Tasks::create([
            'tasktitle' => $request->getParam('tasktitle'),
            'tasktext' => $request->getParam('tasktext'),
            'taskauthor' => $request->getParam('taskauthor'),
            'tasktypeid' => $request->getParam('tasktypeid'),
            'taskstatus' => $request->getParam('taskstatus'),
            'taskproject' => $request->getParam('taskproject'),
            'is_draft' => $request->getParam('is_draft'),
        ]);

        //Adding task assigned users
        if (!empty($request->getParam('assigned'))) {

            foreach ($request->getParam('assigned') as $userId) {
                $insert_bulk[] = array(
                    'taskid' => $task->id,
                    'userid' => $userId,
                );
            }

            $this->container->db->table('taskassigns')->insert($insert_bulk);
        }

        //Adding files to task if avvailable
        $this->container->file->addFiles($request, $response);

        if ($request->getParam('is_draft') == 'false') {
            $this->container->telegram->newTask($request->getParam('assigned'), $task->id);
        }

        return $response->withRedirect($this->router->pathFor('task', ['id' => $task->id]));
    }

    public function daleteTask($request, $response, $id)
    {

        $is_admin = $this->container->auth->user()->is_admin;

        if ($is_admin == "true") {
            $this->telegram->tg_msg($_SESSION['name'] . " deleted task # " . $id['id']);
            $this->container->db->table('tasks')->where('taskid', $id)->delete();

        } else {
            $this->container->flash->addMessage('error', 'You can not delete tasks.');
            return $response->withRedirect($this->router->pathFor('all'));
        }

        return $response->withRedirect($this->router->pathFor('all'));
    }

    public function details($request, $response, $id)
    {
        $userid = $this->container->auth->user()->id;
        $tasktype = $this->container->db->table('tasks')->select('tasktypeid')->where('taskid', $id)->value('tasktypeid');

        $args['task'] = Tasks::getConcreteTask($id);
        $args['taskcheklist'] = Tasks::getTaskChecklist($id);
        $args['files'] = Tasks::getTaskFiles($id);
        $args['assigned'] = Tasks::getAssignedUsers($id);
        $args['statuses'] = Tasks::getTaskStatus();
        $args['comments'] = comment::viewTaskComments($id);
        $args['users'] = Tasks::getUsers();
        $args['checks'] = checks::viewTaskChecklist($id, $userid);
        $args['cheklistcomments'] = Tasks::getTaskChecklistComment($id['id']);

        return $this->view->render($response, 'tasks/view.twig', $args);
    }

    public function markTaskChecklist($request, $response)
    {

        $is_done = $this->container->db->table('taskchecklist')->select('is_done')->where('id', $request->getParam('id'))->value('is_done');

        if ($is_done == '1') {
            $task = $this->container->db->table('taskchecklist')->where('id', $request->getParam('id'))->update(['is_done' => '2']);
            $status = 'true';

        } else {
            $task = $this->container->db->table('taskchecklist')->where('id', $request->getParam('id'))->update(['is_done' => '1']);
            $status = 'false';
        }
        return json_encode(array("status" => $status));die();
    }

    public function editTaskPageDatas($request, $response, $id)
    {

        $is_admin = $this->container->auth->user()->is_admin;
        if ($is_admin == "true") {

            $args['edit'] = Tasks::getConcreteTask($id);
            $args['assigned'] = Tasks::getAssignedUsers($id);
            $args['files'] = Tasks::getTaskFiles($id);
            $args['users'] = Tasks::getUsers();
            $args['types'] = Tasks::getTaskTypes();
            $args['projects'] = Tasks::getProjects();
            $args['statuses'] = Tasks::getTaskStatus();

            return $this->view->render($response, 'tasks/edit.twig', $args);

        } else {

            $this->container->flash->addMessage('error', 'You can not edit this task.');
            return $response->withRedirect($this->router->pathFor('all'));
        }

    }

    public function updateTask($request, $response)
    {

        $task = Tasks::where('taskid', $request->getParam('taskid'))->update([
            'tasktitle' => $request->getParam('tasktitle'),
            'tasktext' => $request->getParam('tasktext'),
            'tasktypeid' => $request->getParam('tasktypeid'),
            'taskstatus' => $request->getParam('taskstatus'),
            'taskproject' => $request->getParam('taskproject'),
            'is_draft' => $request->getParam('is_draft'),
        ]);

        $currentassigned = $this->container->db->table('taskassigns')->where('taskid', $request->getParam('taskid'))->delete();

        foreach ($request->getParam('assigned') as $userId) {
            $insert_bulk[] = array(
                'taskid' => $request->getParam('taskid'),
                'userid' => $userId,
            );
        }

        $this->container->db->table('taskassigns')->insert($insert_bulk);

        //Adding files to task if avvailable
        $this->container->file->addFiles($request, $response);

        if ($request->getParam('is_draft') == 'false') {

            $users = $this->container->db->table('users')->select('telegramname')->whereIn('id', $request->getParam('assigned'))->implode('telegramname', ', ');
            $this->telegram->tg_msg($_SESSION['name'] . " just updated task # " . $request->getParam('taskid') . "\nTitle: " . $request->getParam('tasktitle') . "\nAssigned: " . $users . "\nLink: https://" . $_SERVER['SERVER_NAME'] . "/view/" . $request->getParam('taskid'));
        }

        $this->container->flash->addMessage('success', 'Task has been updated successfully.');
        return $response->withRedirect($this->router->pathFor('task', ['id' => $request->getParam('taskid')]));

    }

    public function getDraftTasks($request, $response)
    {
        return $this->view->render($response, 'tasks/drafts.twig', array('drafts' => Tasks::getDraftTasks()));
    }

}
