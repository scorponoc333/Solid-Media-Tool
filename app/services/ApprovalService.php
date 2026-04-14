<?php

class ApprovalService
{
    private ApprovalSetting $settingModel;
    private PostReview $reviewModel;

    public function __construct()
    {
        $this->settingModel = new ApprovalSetting();
        $this->reviewModel = new PostReview();
    }

    public function getSettings(int $clientId): array
    {
        $settings = $this->settingModel->getByClient($clientId);
        return $settings ?: ['approval_required' => 0, 'min_approvals' => 1];
    }

    public function saveSettings(int $clientId, array $data): void
    {
        $this->settingModel->upsertByClient($clientId, [
            'approval_required' => !empty($data['approval_required']) ? 1 : 0,
            'min_approvals' => max(1, min(5, (int)($data['min_approvals'] ?? 1))),
        ]);
    }

    public function isApprovalRequired(int $clientId): bool
    {
        $settings = $this->getSettings($clientId);
        return !empty($settings['approval_required']);
    }

    public function submitForReview(int $postId): void
    {
        $postModel = new Post();
        $postModel->update($postId, ['status' => 'pending_review']);
    }

    public function approve(int $postId, int $reviewerId, ?string $comment = null): array
    {
        $this->reviewModel->upsertReview($postId, $reviewerId, 'approved', $comment);

        $clientId = $GLOBALS['client_id'];
        $settings = $this->getSettings($clientId);
        $approvalCount = $this->reviewModel->countApprovals($postId);
        $minRequired = (int)($settings['min_approvals'] ?? 1);

        if ($approvalCount >= $minRequired) {
            // Enough approvals — move back to draft so editor can schedule/publish
            $postModel = new Post();
            $postModel->update($postId, ['status' => 'draft']);
            return ['fully_approved' => true, 'approvals' => $approvalCount, 'required' => $minRequired];
        }

        return ['fully_approved' => false, 'approvals' => $approvalCount, 'required' => $minRequired];
    }

    public function requestChanges(int $postId, int $reviewerId, string $comment): void
    {
        $this->reviewModel->upsertReview($postId, $reviewerId, 'changes_requested', $comment);
    }

    public function getReviewStatus(int $postId, int $clientId): array
    {
        $reviews = $this->reviewModel->getByPost($postId);
        $settings = $this->getSettings($clientId);
        $approvalCount = $this->reviewModel->countApprovals($postId);

        return [
            'reviews' => $reviews,
            'approval_count' => $approvalCount,
            'min_required' => (int)($settings['min_approvals'] ?? 1),
            'is_fully_approved' => $approvalCount >= (int)($settings['min_approvals'] ?? 1),
        ];
    }

    public function getPendingReviews(int $clientId): array
    {
        return Database::fetchAll(
            "SELECT p.*
             FROM posts p
             WHERE p.client_id = :cid AND p.status = 'pending_review'
             ORDER BY p.created_at DESC",
            ['cid' => $clientId]
        );
    }
}
