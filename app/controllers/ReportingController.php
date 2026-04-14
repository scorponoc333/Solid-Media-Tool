<?php

class ReportingController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->requireRole('admin', 'editor');

        $postModel = new Post();
        $clientId = $GLOBALS['client_id'];

        $stats = $postModel->getStats($clientId);
        $posts = $postModel->getByClient($clientId);
        $topicDist = $postModel->getTopicDistribution($clientId);
        $platformDist = $postModel->getPlatformDistribution($clientId);

        // Get failed posts with their error messages from posting logs
        $failedPosts = [];
        $db = Database::connect();
        $stmt = $db->prepare(
            "SELECT p.id, p.title, p.platform, p.platforms, p.created_at,
                    l.error_message, l.platform AS failed_platform, l.created_at AS failed_at
             FROM posts p
             LEFT JOIN social_post_logs l ON p.id = l.post_id AND l.status = 'failed'
             WHERE p.client_id = :cid AND p.status = 'failed'
             ORDER BY COALESCE(l.created_at, p.created_at) DESC"
        );
        $stmt->execute(['cid' => $clientId]);
        $failedPosts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('reporting/index', [
            'pageTitle' => 'Reports',
            'stats' => $stats,
            'posts' => $posts,
            'topicDist' => $topicDist,
            'platformDist' => $platformDist,
            'failedPosts' => $failedPosts,
        ]);
    }

    public function exportCsv(): void
    {
        $this->requireAuth();
        $this->requireRole('admin', 'editor');

        $postModel = new Post();
        $clientId = $GLOBALS['client_id'];
        $posts = $postModel->getByClient($clientId);

        $filename = 'posts-export-' . date('Y-m-d') . '.csv';

        // Release session lock before streaming output
        session_write_close();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // CSV header row
        fputcsv($output, ['Title', 'Content', 'Platform', 'Post Type', 'Status', 'Scheduled At', 'Created At']);

        foreach ($posts as $post) {
            // Determine platform(s)
            $platforms = '';
            if (!empty($post['platforms'])) {
                $decoded = json_decode($post['platforms'], true);
                if (is_array($decoded)) {
                    $platforms = implode(', ', array_map('ucfirst', $decoded));
                }
            }
            if (empty($platforms)) {
                $platforms = ucfirst($post['platform'] ?? 'facebook');
            }

            // Truncate content to first 100 characters
            $content = $post['content'] ?? '';
            if (mb_strlen($content) > 100) {
                $content = mb_substr($content, 0, 100) . '...';
            }

            fputcsv($output, [
                $post['title'] ?? '',
                $content,
                $platforms,
                ucfirst(str_replace('_', ' ', $post['post_type'] ?? '')),
                ucfirst($post['status'] ?? ''),
                $post['scheduled_at'] ?? '',
                $post['created_at'] ?? '',
            ]);
        }

        fclose($output);
        exit;
    }
}
