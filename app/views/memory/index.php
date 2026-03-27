<?php
$memories = $memories ?? [];
$topics = $topics ?? [];
$recentAngles = $recentAngles ?? [];
$totalMemories = count($memories);
$totalTopics = count($topics);
$totalAngles = count($recentAngles);
$firstName = $_SESSION['first_name'] ?? '';
$brand = (new BrandingService())->get($GLOBALS['client_id']);
$companyName = htmlspecialchars($brand['company_name'] ?? 'your company');
?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card stat-primary">
        <div class="stat-icon"><i class="fas fa-brain"></i></div>
        <div class="stat-value"><?= $totalMemories ?></div>
        <div class="stat-label">Total Memories</div>
    </div>
    <div class="stat-card stat-info">
        <div class="stat-icon"><i class="fas fa-tags"></i></div>
        <div class="stat-value"><?= $totalTopics ?></div>
        <div class="stat-label">Unique Topics</div>
    </div>
    <div class="stat-card stat-success">
        <div class="stat-icon"><i class="fas fa-lightbulb"></i></div>
        <div class="stat-value"><?= $totalAngles ?></div>
        <div class="stat-label">Angles Used</div>
    </div>
</div>

<?php if (empty($memories)): ?>
    <!-- Empty State -->
    <div class="card">
        <div class="empty-state">
            <i class="fas fa-brain"></i>
            <p><?= $firstName ? "{$firstName}, there are" : 'There are' ?> no content memories yet. As you generate posts for <?= $companyName ?>, topics, keywords, and angles are tracked here to keep your content fresh.</p>
            <a href="<?= BASE_URL ?>/generator" class="btn btn-primary">
                <i class="fas fa-magic"></i> Generate Content
            </a>
        </div>
    </div>
<?php else: ?>

    <!-- Topics Used -->
    <?php if (!empty($topics)): ?>
    <div class="section-header">
        <h3 class="section-title">Topics Used</h3>
    </div>
    <div class="card" style="margin-bottom:28px">
        <div class="flex gap-2" style="flex-wrap:wrap">
            <?php foreach ($topics as $topic): ?>
                <div style="background:var(--bg-input);border-radius:100px;padding:6px 14px;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px">
                    <?= htmlspecialchars($topic['topic']) ?>
                    <span style="background:rgba(var(--primary-rgb),0.15);color:var(--primary);border-radius:100px;padding:1px 8px;font-size:11px;font-weight:700"><?= $topic['count'] ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Angles -->
    <?php if (!empty($recentAngles)): ?>
    <div class="section-header">
        <h3 class="section-title">Recent Angles</h3>
    </div>
    <div class="card" style="margin-bottom:28px">
        <div style="display:flex;flex-direction:column;gap:10px">
            <?php foreach ($recentAngles as $angle): ?>
                <div style="display:flex;align-items:center;gap:10px;padding:8px 12px;background:var(--bg-input);border-radius:var(--radius-sm)">
                    <i class="fas fa-lightbulb" style="color:var(--warning);font-size:14px;flex-shrink:0"></i>
                    <span style="font-size:14px;color:var(--text)"><?= htmlspecialchars($angle['angle'] ?? $angle) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Memory Log Table -->
    <div class="section-header">
        <h3 class="section-title">Memory Log</h3>
    </div>
    <div class="card" style="padding:0;overflow:hidden">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Topic</th>
                        <th>Keywords</th>
                        <th>Angle</th>
                        <th>Linked Post</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($memories as $memory): ?>
                    <tr>
                        <td>
                            <span style="background:rgba(var(--primary-rgb),0.1);color:var(--primary);border-radius:100px;padding:2px 10px;font-size:12px;font-weight:600">
                                <?= htmlspecialchars($memory['topic'] ?? '') ?>
                            </span>
                        </td>
                        <td>
                            <span class="text-small" style="color:var(--text-secondary)">
                                <?= htmlspecialchars($memory['keywords'] ?? '') ?>
                            </span>
                        </td>
                        <td>
                            <span class="text-small"><?= htmlspecialchars($memory['angle'] ?? '') ?></span>
                        </td>
                        <td>
                            <?php if (!empty($memory['post_id'])): ?>
                                <a href="<?= BASE_URL ?>/posts/edit/<?= (int)$memory['post_id'] ?>" style="color:var(--primary);font-size:13px;font-weight:500;text-decoration:none">
                                    <?= htmlspecialchars($memory['post_title'] ?? 'View Post') ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted text-small">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($memory['created_at'])): ?>
                                <span class="text-small" style="color:var(--text-secondary)"><?= date('M j, Y', strtotime($memory['created_at'])) ?></span>
                            <?php else: ?>
                                <span class="text-muted text-small">&mdash;</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php endif; ?>
