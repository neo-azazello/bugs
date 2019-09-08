<?php

namespace App\Controllers;

class TelegramController extends Controller
{

    protected $tokenid = "647912647:AAHfJtWUbme5Y9O_iUY9nleQAohT_RsSVA0";
    protected $channel = "@smartbugs";

    public function tg_msg($txt)
    {
        $token = $this->tokenid;
        $query = ["chat_id" => $this->channel, "text" => $txt];
        $url = "https://api.telegram.org/bot" . $token;
        $url .= "/sendMessage?" . http_build_query($query);

        file_get_contents($url, true);

    }

    public function newTask($assigned, $taskid)
    {
        $username = $this->container->auth->user()->name;
        $users = $this->container->db->table('users')
            ->select('telegramname')
            ->whereIn('id', $assigned)
            ->implode('telegramname', ', ');

        $this->tg_msg($username . " has added new task for " . $users . "\nLink: https://" . $_SERVER['SERVER_NAME'] . "/view/" . $taskid);
    }

    public function updateStatus($taskstatus, $tasktitle, $taskid)
    {
        $username = $this->container->auth->user()->name;
        $this->telegram->tg_msg($username . " updated task status to → " . $taskstatus . "\nTitle: " . $tasktitle . "\nLink: http://" . $_SERVER['SERVER_NAME'] . "/view/" . $taskid);
    }

    public function addNewTaskChecklistComment($taskid, $checklistid)
    {
        $username = $this->container->auth->user()->name;
        $task = $this->container->db->table('tasks')->where('taskid', $taskid)->first();
        $this->telegram->tg_msg($username . " added new comment to task # " . $taskid . "\nTitle: " . $task->tasktitle . "\nLink: https://" . $_SERVER['SERVER_NAME'] . "/view/" . $taskid . "#" . $checklistid);
    }

}
