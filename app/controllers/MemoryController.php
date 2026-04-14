<?php

class MemoryController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->requireRole('admin', 'editor');

        $memoryService = new ContentMemoryService();
        $memoryModel = new ContentMemory();
        $clientId = $GLOBALS['client_id'];

        $memories = $memoryModel->getByClient($clientId);
        $topics = $memoryModel->getTopics($clientId);
        $recentAngles = $memoryModel->getRecentAngles($clientId);

        $this->view('memory/index', [
            'pageTitle' => 'Content Memory',
            'memories' => $memories,
            'topics' => $topics,
            'recentAngles' => $recentAngles,
        ]);
    }
}
