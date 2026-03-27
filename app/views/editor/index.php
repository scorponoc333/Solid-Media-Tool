<?php
$csrfToken = $_SESSION['csrf_token'] ?? '';
$posts = $posts ?? [];
$firstName = $_SESSION['first_name'] ?? '';
$brand = (new BrandingService())->get($GLOBALS['client_id']);
$companyName = htmlspecialchars($brand['company_name'] ?? 'your company');
?>

<input type="hidden" id="csrf-token" value="<?= htmlspecialchars($csrfToken) ?>">

<!-- Section Header -->
<div class="section-header">
    <h3 class="section-title">All Posts</h3>
    <a href="<?= BASE_URL ?>/generator" class="btn btn-primary">
        <i class="fas fa-magic"></i> New Post
    </a>
</div>

<!-- Filter Bar -->
<div class="card mb-3" style="padding:16px 20px">
    <div class="flex-center gap-2" style="flex-wrap:wrap">
        <div class="form-group" style="margin-bottom:0;flex:1;min-width:160px">
            <select id="filter-status" class="form-select" onchange="filterPosts()">
                <option value="all">All Statuses</option>
                <option value="draft">Draft</option>
                <option value="scheduled">Scheduled</option>
                <option value="published">Published</option>
                <option value="failed">Failed</option>
            </select>
        </div>
        <div class="form-group" style="margin-bottom:0;flex:1;min-width:160px">
            <select id="filter-platform" class="form-select" onchange="filterPosts()">
                <option value="all">All Platforms</option>
                <option value="instagram">Instagram</option>
                <option value="facebook">Facebook</option>
                <option value="linkedin">LinkedIn</option>
                <option value="twitter">Twitter</option>
            </select>
        </div>
        <div class="form-group" style="margin-bottom:0;flex:2;min-width:200px">
            <input type="text" id="filter-search" class="form-input" placeholder="Search posts..." oninput="filterPosts()">
        </div>
    </div>
</div>

<!-- Posts Table -->
<?php if (empty($posts)): ?>
    <div class="card">
        <div class="empty-state">
            <i class="fas fa-feather-alt"></i>
            <p><?= $firstName ? "Hey {$firstName}, there" : 'There' ?> are no posts for <?= $companyName ?> yet. Head over to the AI Generator to create your first one.</p>
            <a href="<?= BASE_URL ?>/generator" class="btn btn-primary">
                <i class="fas fa-magic"></i> Generate Your First Post
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="card" style="padding:0;overflow:hidden">
        <div class="table-wrapper">
            <table id="posts-table">
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
                    <?php foreach ($posts as $post): ?>
                    <tr data-status="<?= htmlspecialchars($post['status']) ?>"
                        data-platform="<?= htmlspecialchars($post['platform']) ?>"
                        data-search="<?= htmlspecialchars(strtolower($post['title'] . ' ' . ($post['topic'] ?? ''))) ?>">
                        <td>
                            <div style="font-weight:600;max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                <?= htmlspecialchars($post['title']) ?>
                            </div>
                            <?php if (!empty($post['topic'])): ?>
                                <div class="text-muted text-small"><?= htmlspecialchars($post['topic']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-<?= htmlspecialchars($post['platform']) ?>"><?= ucfirst(htmlspecialchars($post['platform'])) ?></span></td>
                        <td><span class="text-small" style="text-transform:capitalize"><?= str_replace('_', ' ', htmlspecialchars($post['post_type'])) ?></span></td>
                        <td><span class="badge badge-<?= htmlspecialchars($post['status']) ?>"><?= ucfirst(htmlspecialchars($post['status'])) ?></span></td>
                        <td>
                            <?php if (!empty($post['scheduled_at'])): ?>
                                <span class="text-small"><?= date('M j, g:ia', strtotime($post['scheduled_at'])) ?></span>
                            <?php else: ?>
                                <span class="text-muted text-small">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="flex-center gap-1">
                                <a href="<?= BASE_URL ?>/posts/edit/<?= (int)$post['id'] ?>" class="btn btn-ghost btn-sm btn-icon" title="Edit">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <button class="btn btn-ghost btn-sm btn-icon" title="Delete" onclick="deletePost(<?= (int)$post['id'] ?>, this)" style="color:var(--danger)">
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

    <div class="text-muted text-small mt-2" id="filter-count">
        Showing <?= count($posts) ?> post<?= count($posts) !== 1 ? 's' : '' ?>
    </div>
<?php endif; ?>

<script>
const BASE = '<?= rtrim(BASE_URL, '/') ?>';
const csrfToken = () => document.getElementById('csrf-token').value;

function filterPosts() {
    const status = document.getElementById('filter-status').value;
    const platform = document.getElementById('filter-platform').value;
    const search = document.getElementById('filter-search').value.toLowerCase().trim();
    const rows = document.querySelectorAll('#posts-table tbody tr');
    let visible = 0;

    rows.forEach(row => {
        const matchStatus = status === 'all' || row.dataset.status === status;
        const matchPlatform = platform === 'all' || row.dataset.platform === platform;
        const matchSearch = !search || row.dataset.search.includes(search);

        if (matchStatus && matchPlatform && matchSearch) {
            row.style.display = '';
            visible++;
        } else {
            row.style.display = 'none';
        }
    });

    const countEl = document.getElementById('filter-count');
    if (countEl) {
        countEl.textContent = 'Showing ' + visible + ' post' + (visible !== 1 ? 's' : '');
    }
}

function deletePost(id, btnEl) {
    confirmModal('Delete Post', 'Are you sure you want to delete this post? This action cannot be undone.', async () => {
        try {
            const formData = new FormData();
            formData.append('csrf_token', csrfToken());

            const res = await fetch(BASE + '/posts/delete/' + id, {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Delete failed');

            const row = btnEl.closest('tr');
            if (row) {
                row.style.transition = 'opacity 0.3s ease';
                row.style.opacity = '0';
                setTimeout(() => {
                    row.remove();
                    filterPosts();
                }, 300);
            }
            showToast('Post deleted.', 'success');
        } catch (err) {
            showToast(err.message, 'error');
        }
    });
}
</script>
