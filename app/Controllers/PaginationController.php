<?php

namespace App\Controllers;

class PaginationController extends Controller
{

    private $offset = 0;
    private $step = 10;

    public function data($taskstatusid)
    {
        $totalRows = $this->container->helper->getTaskCount($taskstatusid);
        $currentPage = isset($_GET['page']) ? $_GET['page'] : 0;

        $rowsOnPage = $totalRows / $this->step;
        $limit = $this->step;

        if ($currentPage > 0) {
            $limit = $currentPage * $this->step;
            $this->offset = $limit - $this->step;
        }

        $off = explode(' ', $this->offset);
        $lim = explode(' ', $limit);

        $value = array_merge($off, $lim);

        return $value;

    }

    public static function pageSwitcher()
    {

        $currentPage = isset($_GET['page']) ? $_GET['page'] : 0;

        if ($currentPage == 0 || $currentPage == 1) {

            $start = 1;

        } else {

            $start = $currentPage;
        }

        return (int) $start;

    }

}
