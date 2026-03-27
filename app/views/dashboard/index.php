<?php
$firstName = $_SESSION['first_name'] ?? '';
$brandingService = new BrandingService();
$brand = $brandingService->get($GLOBALS['client_id']);
$companyName = $brand['company_name'] ?? 'your company';

// Time-based greeting
$hour = (int) date('G');
if ($hour < 12) {
    $timeGreeting = 'Good morning';
} elseif ($hour < 17) {
    $timeGreeting = 'Good afternoon';
} else {
    $timeGreeting = 'Good evening';
}
$greeting = $firstName ? "{$timeGreeting}, " . htmlspecialchars($firstName) : $timeGreeting;
$companyHtml = htmlspecialchars($companyName);
?>

<!-- Greeting -->
<div style="margin-bottom:28px">
    <h2 style="font-size:22px;font-weight:700;color:var(--text);margin-bottom:4px"><?= $greeting ?>.</h2>
    <p style="font-size:14px;color:var(--text-muted);margin:0">Here's an overview of your content for <?= $companyHtml ?>.</p>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card stat-primary">
        <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
        <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
        <div class="stat-label">Total Posts</div>
    </div>
    <div class="stat-card stat-info">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-value"><?= $stats['scheduled'] ?? 0 ?></div>
        <div class="stat-label">Scheduled</div>
    </div>
    <div class="stat-card stat-success">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-value"><?= $stats['published'] ?? 0 ?></div>
        <div class="stat-label">Published</div>
    </div>
    <div class="stat-card stat-warning">
        <div class="stat-icon"><i class="fas fa-pen-fancy"></i></div>
        <div class="stat-value"><?= $stats['draft'] ?? 0 ?></div>
        <div class="stat-label">Drafts</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="section-header">
    <h3 class="section-title">Quick Actions</h3>
</div>
<div class="quick-actions-grid">
    <a href="<?= BASE_URL ?>/generator" class="quick-action-card">
        <div class="quick-action-icon"><i class="fas fa-magic"></i></div>
        <div class="quick-action-text">
            <div class="quick-action-title">Generate Content</div>
            <div class="quick-action-desc">Create posts for <?= $companyHtml ?></div>
        </div>
    </a>
    <a href="<?= BASE_URL ?>/calendar" class="quick-action-card">
        <div class="quick-action-icon"><i class="fas fa-calendar-alt"></i></div>
        <div class="quick-action-text">
            <div class="quick-action-title">View Calendar</div>
            <div class="quick-action-desc">See your upcoming schedule</div>
        </div>
    </a>
    <a href="<?= BASE_URL ?>/reporting" class="quick-action-card">
        <div class="quick-action-icon"><i class="fas fa-chart-bar"></i></div>
        <div class="quick-action-text">
            <div class="quick-action-title">View Reports</div>
            <div class="quick-action-desc">Track <?= $companyHtml ?>'s performance</div>
        </div>
    </a>
</div>

<!-- Recent Posts -->
<div class="section-header">
    <h3 class="section-title">Recent Posts</h3>
    <a href="<?= BASE_URL ?>/posts" class="btn btn-ghost btn-sm">View All</a>
</div>

<?php if (empty($recentPosts)): ?>
    <div class="card">
        <div class="empty-state">
            <i class="fas fa-feather-alt"></i>
            <p><?= $firstName ? "Hey {$firstName}, no" : 'No' ?> posts yet — let's get <?= $companyHtml ?>'s content rolling. Fire up the AI Generator and create your first post in seconds.</p>
            <a href="<?= BASE_URL ?>/generator" class="btn btn-primary">
                <i class="fas fa-magic"></i> Generate Your First Post
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="card" style="padding:0;overflow:hidden">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Platform</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Scheduled</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentPosts as $post): ?>
                    <tr>
                        <td>
                            <div style="font-weight:600;max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                <?= htmlspecialchars($post['title']) ?>
                            </div>
                            <?php if ($post['topic']): ?>
                                <div class="text-muted text-small"><?= htmlspecialchars($post['topic']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-<?= $post['platform'] ?>"><?= ucfirst($post['platform']) ?></span></td>
                        <td><span class="text-small" style="text-transform:capitalize"><?= str_replace('_', ' ', $post['post_type']) ?></span></td>
                        <td><span class="badge badge-<?= $post['status'] ?>"><?= ucfirst($post['status']) ?></span></td>
                        <td>
                            <?php if ($post['scheduled_at']): ?>
                                <span class="text-small"><?= date('M j, g:ia', strtotime($post['scheduled_at'])) ?></span>
                            <?php else: ?>
                                <span class="text-muted text-small">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/posts/edit/<?= $post['id'] ?>" class="btn btn-ghost btn-sm btn-icon" title="Edit">
                                <i class="fas fa-pen"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($topicStats)): ?>
<!-- Topic Distribution -->
<div class="section-header mt-3">
    <h3 class="section-title">Topics Used</h3>
</div>
<div class="card">
    <div class="flex gap-2" style="flex-wrap:wrap">
        <?php foreach ($topicStats as $topic): ?>
            <div style="background:var(--bg-input);border-radius:100px;padding:6px 14px;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px">
                <?= htmlspecialchars($topic['topic']) ?>
                <span style="background:rgba(var(--primary-rgb),0.15);color:var(--primary);border-radius:100px;padding:1px 8px;font-size:11px;font-weight:700"><?= $topic['count'] ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
