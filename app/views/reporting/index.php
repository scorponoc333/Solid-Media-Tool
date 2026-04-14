<?php
$csrfToken = $_SESSION['csrf_token'] ?? '';
if (empty($csrfToken)) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); $csrfToken = $_SESSION['csrf_token']; }
?>
<input type="hidden" id="csrf-token" value="<?= htmlspecialchars($csrfToken) ?>">
<style>
.filter-bar { display:flex; flex-wrap:wrap; align-items:flex-end; gap:12px; margin-bottom:24px; }
.filter-bar .form-group { margin-bottom:0; min-width:140px; }
.filter-bar .form-label { margin-bottom:4px; }
.platform-bars { display:flex; flex-direction:column; gap:12px; }
.platform-bar-row { display:flex; align-items:center; gap:12px; }
.platform-bar-label { min-width:90px; font-size:13px; font-weight:600; color:var(--text); text-transform:capitalize; }
.platform-bar-track { flex:1; height:28px; background:var(--bg-input); border-radius:100px; overflow:hidden; position:relative; }
.platform-bar-fill { height:100%; border-radius:100px; display:flex; align-items:center; padding:0 10px; font-size:11px; font-weight:700; color:#fff; min-width:32px; transition:width 0.4s ease; }
.platform-bar-fill.bar-instagram { background:linear-gradient(135deg,#f09433,#dc2743,#bc1888); }
.platform-bar-fill.bar-facebook { background:#1877f2; }
.platform-bar-fill.bar-linkedin { background:#0a66c2; }
.platform-bar-fill.bar-twitter { background:#1da1f2; }
.platform-bar-fill.bar-all { background:var(--primary); }
.platform-bar-count { min-width:36px; text-align:right; font-size:13px; font-weight:700; color:var(--text); }
.stat-clickable { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.stat-clickable:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.12); }
.stat-clickable.stat-active { ring: 2px; box-shadow: 0 0 0 3px rgba(var(--primary-rgb),0.35), 0 6px 20px rgba(0,0,0,0.1); transform: translateY(-2px); }
</style>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card stat-primary stat-clickable" data-filter-status="" role="button" tabindex="0" aria-label="Filter: All posts" style="cursor:pointer">
        <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
        <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
        <div class="stat-label">Total Posts</div>
    </div>
    <div class="stat-card stat-info stat-clickable" data-filter-status="scheduled" role="button" tabindex="0" aria-label="Filter: Scheduled posts" style="cursor:pointer">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-value"><?= $stats['scheduled'] ?? 0 ?></div>
        <div class="stat-label">Scheduled</div>
    </div>
    <div class="stat-card stat-success stat-clickable" data-filter-status="published" role="button" tabindex="0" aria-label="Filter: Published posts" style="cursor:pointer">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-value"><?= $stats['published'] ?? 0 ?></div>
        <div class="stat-label">Published</div>
    </div>
    <div class="stat-card stat-warning stat-clickable" data-filter-status="draft" role="button" tabindex="0" aria-label="Filter: Draft posts" style="cursor:pointer">
        <div class="stat-icon"><i class="fas fa-pen-fancy"></i></div>
        <div class="stat-value"><?= $stats['draft'] ?? 0 ?></div>
        <div class="stat-label">Drafts</div>
    </div>
    <?php if (($stats['failed'] ?? 0) > 0): ?>
    <div class="stat-card stat-danger stat-clickable" data-filter-status="failed" role="button" tabindex="0" aria-label="Filter: Failed posts" style="cursor:pointer">
        <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-value"><?= $stats['failed'] ?></div>
        <div class="stat-label">Failed</div>
    </div>
    <?php endif; ?>
</div>

<!-- Failed Posts -->
<?php if (!empty($failedPosts)): ?>
<div class="section-header" id="failed-posts">
    <h3 class="section-title" style="color:var(--danger)"><i class="fas fa-exclamation-triangle" style="margin-right:8px"></i>Failed Posts</h3>
</div>
<div class="card" style="padding:0;overflow:hidden;border:1px solid rgba(239,68,68,0.2);margin-bottom:24px">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Post</th>
                    <th>Platform</th>
                    <th>Error</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($failedPosts as $fp): ?>
                <tr style="cursor:pointer" onclick="if(!event.target.closest('button'))window.location.href='<?= BASE_URL ?>/posts/edit/<?= (int)$fp['id'] ?>'" id="failed-row-<?= (int)$fp['id'] ?>">
                    <td>
                        <div style="font-weight:600;color:var(--text);max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= htmlspecialchars($fp['title']) ?>">
                            <?= htmlspecialchars($fp['title']) ?>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-<?= htmlspecialchars($fp['failed_platform'] ?? $fp['platform'] ?? 'draft') ?>">
                            <?= ucfirst(htmlspecialchars($fp['failed_platform'] ?? $fp['platform'] ?? 'Unknown')) ?>
                        </span>
                    </td>
                    <td>
                        <span class="text-small" style="color:var(--danger);max-width:300px;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= htmlspecialchars($fp['error_message'] ?? 'Unknown error') ?>">
                            <?= htmlspecialchars($fp['error_message'] ?? 'Unknown error') ?>
                        </span>
                    </td>
                    <td>
                        <span class="text-small text-muted">
                            <?= !empty($fp['failed_at']) ? date('M j, g:ia', strtotime($fp['failed_at'])) : (!empty($fp['created_at']) ? date('M j, g:ia', strtotime($fp['created_at'])) : '—') ?>
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;align-items:center">
                            <a href="<?= BASE_URL ?>/posts/edit/<?= (int)$fp['id'] ?>" class="btn btn-ghost btn-sm" style="white-space:nowrap" onclick="event.stopPropagation()">
                                <i class="fas fa-redo"></i> Retry
                            </a>
                            <button class="btn btn-ghost btn-sm btn-icon" title="Delete" onclick="event.stopPropagation();deleteFailedPost(<?= (int)$fp['id'] ?>, this)" style="color:var(--danger)">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Filter Bar -->
<div class="section-header" id="posts-section">
    <h3 class="section-title">Posts</h3>
</div>
<div class="card mb-3">
    <div class="filter-bar" id="filter-bar">
        <div class="form-group">
            <label class="form-label">From</label>
            <input type="date" class="form-input" id="filter-date-from">
        </div>
        <div class="form-group">
            <label class="form-label">To</label>
            <input type="date" class="form-input" id="filter-date-to">
        </div>
        <div class="form-group">
            <label class="form-label">Platform</label>
            <select class="form-select" id="filter-platform">
                <option value="">All Platforms</option>
                <option value="facebook">Facebook</option>
                <option value="linkedin">LinkedIn</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Status</label>
            <select class="form-select" id="filter-status">
                <option value="">All Statuses</option>
                <option value="draft">Draft</option>
                <option value="scheduled">Scheduled</option>
                <option value="published">Published</option>
                <option value="failed">Failed</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Type</label>
            <select class="form-select" id="filter-type">
                <option value="">All Types</option>
                <option value="image">Image</option>
                <option value="video">Video</option>
                <option value="carousel">Carousel</option>
                <option value="story">Story</option>
                <option value="reel">Reel</option>
                <option value="text">Text</option>
            </select>
        </div>
        <div class="form-group">
            <button class="btn btn-primary btn-sm" id="apply-filters"><i class="fas fa-filter"></i> Apply</button>
        </div>
        <div class="form-group" style="margin-left:auto">
            <a href="<?= BASE_URL ?>/reporting/export-csv" class="btn btn-ghost btn-sm"><i class="fas fa-download"></i> Download CSV</a>
        </div>
    </div>
</div>

<!-- Posts Table -->
<?php if (empty($posts)): ?>
    <div class="card">
        <div class="empty-state">
            <i class="fas fa-chart-bar"></i>
            <p>No posts to report on yet. Start creating content to see your reports.</p>
            <a href="<?= BASE_URL ?>/generator" class="btn btn-primary"><i class="fas fa-magic"></i> Generate Content</a>
        </div>
    </div>
<?php else: ?>
    <div class="card" style="padding:0;overflow:hidden">
        <div class="table-wrapper">
            <table id="report-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Platform</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Scheduled</th>
                        <th>Topic</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post):
                        $rptPlatforms = [];
                        if (!empty($post['platforms'])) {
                            $decoded = json_decode($post['platforms'], true);
                            if (is_array($decoded)) $rptPlatforms = $decoded;
                        }
                        if (empty($rptPlatforms)) $rptPlatforms = [$post['platform'] ?? 'facebook'];
                    ?>
                    <tr data-platform="<?= htmlspecialchars(implode(',', $rptPlatforms)) ?>"
                        data-status="<?= htmlspecialchars($post['status'] ?? '') ?>"
                        data-type="<?= htmlspecialchars($post['post_type'] ?? '') ?>"
                        data-scheduled="<?= htmlspecialchars($post['scheduled_at'] ?? '') ?>"
                        data-topic="<?= htmlspecialchars($post['topic'] ?? '') ?>">
                        <td>
                            <div style="font-weight:600;max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                <?= htmlspecialchars($post['title']) ?>
                            </div>
                        </td>
                        <td>
                            <?php foreach ($rptPlatforms as $rp): ?>
                                <span class="badge badge-<?= htmlspecialchars($rp) ?>"><?= ucfirst(htmlspecialchars($rp)) ?></span>
                            <?php endforeach; ?>
                        </td>
                        <td><span class="text-small" style="text-transform:capitalize"><?= str_replace('_', ' ', $post['post_type'] ?? '') ?></span></td>
                        <td><span class="badge badge-<?= $post['status'] ?>"><?= ucfirst($post['status']) ?></span></td>
                        <td>
                            <?php if (!empty($post['scheduled_at'])): ?>
                                <span class="text-small"><?= date('M j, g:ia', strtotime($post['scheduled_at'])) ?></span>
                            <?php else: ?>
                                <span class="text-muted text-small">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($post['topic'])): ?>
                                <span class="text-small"><?= htmlspecialchars($post['topic']) ?></span>
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

<!-- Topic Distribution -->
<?php if (!empty($topicDist)): ?>
<div class="section-header mt-3">
    <h3 class="section-title">Topics</h3>
</div>
<div class="card">
    <div class="flex gap-2" style="flex-wrap:wrap">
        <?php foreach ($topicDist as $topic): ?>
            <div style="background:var(--bg-input);border-radius:100px;padding:6px 14px;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px">
                <?= htmlspecialchars($topic['topic']) ?>
                <span style="background:rgba(var(--primary-rgb),0.15);color:var(--primary);border-radius:100px;padding:1px 8px;font-size:11px;font-weight:700"><?= $topic['count'] ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Platform Breakdown -->
<?php if (!empty($platformDist)): ?>
<div class="section-header mt-3">
    <h3 class="section-title">Platform Breakdown</h3>
</div>
<div class="card">
    <div class="platform-bars">
        <?php
            $maxCount = max(array_column($platformDist, 'count'));
            foreach ($platformDist as $plat):
                $pct = $maxCount > 0 ? round(($plat['count'] / $maxCount) * 100) : 0;
        ?>
        <div class="platform-bar-row">
            <div class="platform-bar-label"><?= ucfirst(htmlspecialchars($plat['platform'])) ?></div>
            <div class="platform-bar-track">
                <div class="platform-bar-fill bar-<?= htmlspecialchars($plat['platform']) ?>" style="width:<?= max($pct, 5) ?>%">
                    <?= $plat['count'] ?>
                </div>
            </div>
            <div class="platform-bar-count"><?= $plat['count'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<script>
(function() {
    var applyBtn = document.getElementById('apply-filters');
    if (!applyBtn) return;

    applyBtn.addEventListener('click', function() {
        var platform = document.getElementById('filter-platform').value;
        var status = document.getElementById('filter-status').value;
        var postType = document.getElementById('filter-type').value;
        var dateFrom = document.getElementById('filter-date-from').value;
        var dateTo = document.getElementById('filter-date-to').value;

        var table = document.getElementById('report-table');
        if (!table) return;
        var rows = table.querySelectorAll('tbody tr');
        var visible = 0;

        rows.forEach(function(row) {
            var show = true;

            if (platform && !row.getAttribute('data-platform').split(',').includes(platform)) show = false;
            if (status && row.getAttribute('data-status') !== status) show = false;
            if (postType && row.getAttribute('data-type') !== postType) show = false;

            if (dateFrom || dateTo) {
                var scheduled = row.getAttribute('data-scheduled');
                if (!scheduled) {
                    show = false;
                } else {
                    var d = scheduled.substring(0, 10);
                    if (dateFrom && d < dateFrom) show = false;
                    if (dateTo && d > dateTo) show = false;
                }
            }

            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (visible === 0) {
            showToast('No posts match the selected filters.', 'info');
        } else {
            showToast(visible + ' post' + (visible !== 1 ? 's' : '') + ' found.', 'success');
        }
    });
})();

// Handle hash-based navigation (e.g. #failed-posts)
(function() {
    var hash = window.location.hash;
    if (hash === '#failed-posts') {
        var failedCard = document.querySelector('.stat-clickable[data-filter-status="failed"]');
        if (failedCard) {
            setTimeout(function() { failedCard.click(); }, 100);
        }
    }
})();

// Stat card click → filter + smooth scroll
document.querySelectorAll('.stat-clickable').forEach(function(card) {
    card.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); this.click(); }
    });
    card.addEventListener('click', function() {
        var status = this.getAttribute('data-filter-status');
        var statusSelect = document.getElementById('filter-status');
        if (statusSelect) statusSelect.value = status;

        // Clear other filters for a clean view
        var platformSelect = document.getElementById('filter-platform');
        var typeSelect = document.getElementById('filter-type');
        var dateFrom = document.getElementById('filter-date-from');
        var dateTo = document.getElementById('filter-date-to');
        if (platformSelect) platformSelect.value = '';
        if (typeSelect) typeSelect.value = '';
        if (dateFrom) dateFrom.value = '';
        if (dateTo) dateTo.value = '';

        // Apply filter
        var applyBtn = document.getElementById('apply-filters');
        if (applyBtn) applyBtn.click();

        // Highlight active card
        document.querySelectorAll('.stat-clickable').forEach(function(c) { c.classList.remove('stat-active'); });
        this.classList.add('stat-active');

        // Smooth scroll to the "Posts" heading with a small offset
        var target = document.getElementById('posts-section');
        if (target) {
            var y = target.getBoundingClientRect().top + window.pageYOffset - 20;
            window.scrollTo({ top: y, behavior: 'smooth' });
        }
    });
});

function deleteFailedPost(id, btnEl) {
    confirmModal('Delete Failed Post', 'Are you sure you want to delete this post? This cannot be undone.', async function() {
        try {
            var formData = new FormData();
            formData.append('csrf_token', document.getElementById('csrf-token').value);

            var res = await fetch('<?= BASE_URL ?>/posts/delete/' + id, {
                method: 'POST',
                body: formData
            });
            var data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Delete failed');

            var row = document.getElementById('failed-row-' + id);
            if (row) {
                row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';
                setTimeout(function() { row.remove(); }, 300);
            }
            showToast('Post deleted.', 'success');
        } catch (err) {
            showToast(err.message, 'error');
        }
    });
}
</script>
