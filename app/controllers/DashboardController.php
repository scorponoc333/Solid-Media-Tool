<?php

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $postModel = new Post();
        $clientId = $GLOBALS['client_id'];

        $stats = $postModel->getStats($clientId);
        $recentPosts = $postModel->getRecent($clientId, 8);
        $scheduledPosts = $postModel->getScheduled($clientId);
        $topicStats = $postModel->getTopicDistribution($clientId);

        $this->view('dashboard/index', [
            'stats' => $stats,
            'recentPosts' => $recentPosts,
            'scheduledPosts' => $scheduledPosts,
            'topicStats' => $topicStats,
            'pageTitle' => 'Dashboard',
        ]);
    }

    public function apiStats(): void
    {
        $this->requireAuth();

        $postModel = new Post();
        $clientId = $GLOBALS['client_id'];
        $stats = $postModel->getStats($clientId);

        $this->json(['stats' => $stats]);
    }
}
