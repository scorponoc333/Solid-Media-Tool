<?php

class ReportingController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $postModel = new Post();
        $clientId = $GLOBALS['client_id'];

        $stats = $postModel->getStats($clientId);
        $posts = $postModel->getByClient($clientId);
        $topicDist = $postModel->getTopicDistribution($clientId);
        $platformDist = $postModel->getPlatformDistribution($clientId);

        $this->view('reporting/index', [
            'pageTitle' => 'Reports',
            'stats' => $stats,
            'posts' => $posts,
            'topicDist' => $topicDist,
            'platformDist' => $platformDist,
        ]);
    }
}
