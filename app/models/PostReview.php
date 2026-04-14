<?php

class PostReview extends Model
{
    protected string $table = 'post_reviews';

    public function getByPost(int $postId): array
    {
        return Database::fetchAll(
            "SELECT pr.*, u.first_name, u.username, u.email
             FROM {$this->table} pr
             JOIN users u ON u.id = pr.reviewer_id
             WHERE pr.post_id = :pid
             ORDER BY pr.created_at DESC",
            ['pid' => $postId]
        );
    }

    public function countApprovals(int $postId): int
    {
        $row = Database::fetch(
            "SELECT COUNT(*) as cnt FROM {$this->table} WHERE post_id = :pid AND status = 'approved'",
            ['pid' => $postId]
        );
        return (int)($row['cnt'] ?? 0);
    }

    public function hasReviewed(int $postId, int $reviewerId): ?array
    {
        return Database::fetch(
            "SELECT * FROM {$this->table} WHERE post_id = :pid AND reviewer_id = :rid",
            ['pid' => $postId, 'rid' => $reviewerId]
        );
    }

    public function upsertReview(int $postId, int $reviewerId, string $status, ?string $comment): void
    {
        $existing = $this->hasReviewed($postId, $reviewerId);
        if ($existing) {
            $this->update($existing['id'], ['status' => $status, 'comment' => $comment]);
        } else {
            $this->create([
                'post_id' => $postId,
                'reviewer_id' => $reviewerId,
                'status' => $status,
                'comment' => $comment,
            ]);
        }
    }
}
