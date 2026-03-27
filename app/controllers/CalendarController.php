<?php

class CalendarController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->view('calendar/index', ['pageTitle' => 'Calendar']);
    }

    public function events(): void
    {
        $this->requireAuth();

        $month = (int) ($_GET['month'] ?? date('n'));
        $year = (int) ($_GET['year'] ?? date('Y'));

        $start = "{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01 00:00:00";
        $end = date('Y-m-t 23:59:59', strtotime($start));

        $postModel = new Post();
        $posts = $postModel->getByDateRange($GLOBALS['client_id'], $start, $end);

        $this->json(['posts' => $posts]);
    }
}
