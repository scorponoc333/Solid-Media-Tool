<?php

class ContentMemoryService
{
    private ContentMemory $model;

    public function __construct()
    {
        $this->model = new ContentMemory();
    }

    public function generateHash(string $topic, string $keywords, string $angle): string
    {
        return md5(strtolower(trim($topic) . '|' . trim($keywords) . '|' . trim($angle)));
    }

    public function isDuplicate(string $topic, string $keywords, string $angle, int $clientId): bool
    {
        $hash = $this->generateHash($topic, $keywords, $angle);
        return $this->model->hashExists($hash, $clientId);
    }

    public function remember(int $postId, string $topic, string $keywords, string $angle, int $clientId): void
    {
        $hash = $this->generateHash($topic, $keywords, $angle);
        if (!$this->model->hashExists($hash, $clientId)) {
            $this->model->create([
                'client_id' => $clientId,
                'post_id' => $postId,
                'topic' => $topic,
                'keywords' => $keywords,
                'angle' => $angle,
                'content_hash' => $hash,
            ]);
        }
    }

    public function getContext(int $clientId): array
    {
        return [
            'topics' => $this->model->getTopics($clientId),
            'recent_angles' => $this->model->getRecentAngles($clientId),
        ];
    }
}
