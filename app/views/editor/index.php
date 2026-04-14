<?php
$csrfToken = $_SESSION['csrf_token'] ?? '';
$posts = $posts ?? [];
$firstName = $_SESSION['first_name'] ?? '';
$brand = (new BrandingService())->get($GLOBALS['client_id']);
$companyName = htmlspecialchars($brand['company_name'] ?? 'your company');
$tPrimary = htmlspecialchars($brand['primary_color'] ?? '#6366f1');
$tLogo = $brand['logo_url'] ?? '';
?>

<input type="hidden" id="csrf-token" value="<?= htmlspecialchars($csrfToken) ?>">

<!-- Page Transition Portal -->
<div id="pageTransition" style="position:fixed;inset:0;z-index:99995;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0);backdrop-filter:blur(0px);opacity:0;visibility:hidden;transition:opacity 0.4s ease,background 0.5s ease,backdrop-filter 0.5s ease">
    <div id="pageTransContent" style="position:relative;width:280px;height:280px;display:flex;flex-direction:column;align-items:center;justify-content:center;transform:scale(0.6);opacity:0;transition:transform 0.6s cubic-bezier(0.34,1.56,0.64,1),opacity 0.4s ease">
        <!-- Rings -->
        <div style="position:absolute;width:240px;height:240px;border:1px solid rgba(255,255,255,0.06);border-radius:50%;animation:ptSpin 8s linear infinite"></div>
        <div style="position:absolute;width:180px;height:180px;border:1px dashed rgba(255,255,255,0.08);border-radius:50%;animation:ptSpin 5s linear infinite reverse"></div>
        <div style="position:absolute;width:120px;height:120px;border:2px solid rgba(255,255,255,0.06);border-top-color:rgba(255,255,255,0.5);border-radius:50%;animation:ptSpin 2.5s linear infinite;box-shadow:0 0 24px rgba(255,255,255,0.06)"></div>
        <!-- Orbiting dot -->
        <div style="position:absolute;width:160px;height:160px;animation:ptSpin 4s linear infinite">
            <div style="position:absolute;width:5px;height:5px;background:#fff;border-radius:50%;top:-2px;left:calc(50% - 2px);box-shadow:0 0 10px rgba(255,255,255,0.7)"></div>
        </div>
        <!-- Logo -->
        <div style="position:relative;z-index:5;width:64px;height:64px;border-radius:50%;background:rgba(255,255,255,0.1);backdrop-filter:blur(6px);display:flex;align-items:center;justify-content:center">
            <?php if ($tLogo): ?>
                <img src="<?= htmlspecialchars($tLogo) ?>" style="max-width:42px;max-height:42px;object-fit:contain;filter:brightness(0) invert(1)" alt="">
            <?php else: ?>
                <div style="font-size:24px;font-weight:800;color:#fff"><?= strtoupper(substr($brand['company_name'] ?? 'S', 0, 1)) ?></div>
            <?php endif; ?>
        </div>
        <div style="position:relative;z-index:5;margin-top:16px;font-size:14px;font-weight:600;color:#fff" id="pageTransStatus">Loading...</div>
        <div style="position:relative;z-index:5;margin-top:4px;font-size:11px;color:rgba(255,255,255,0.4)">Please wait a moment</div>
        <!-- Particles -->
        <div style="position:absolute;inset:0;overflow:hidden;pointer-events:none">
            <?php for ($pi = 0; $pi < 10; $pi++): ?>
            <div style="position:absolute;left:<?= 5 + $pi * 9.5 ?>%;width:<?= 2 + ($pi % 3) ?>px;height:<?= 2 + ($pi % 3) ?>px;border-radius:50%;background:rgba(255,255,255,0.5);animation:ptFloat <?= 3 + ($pi % 3) ?>s ease-in-out infinite;animation-delay:-<?= round($pi * 0.3, 1) ?>s;opacity:0"></div>
            <?php endfor; ?>
        </div>
    </div>
</div>
<style>
@keyframes ptSpin{to{transform:rotate(360deg)}}
@keyframes ptFloat{0%{bottom:-10px;opacity:0;transform:scale(.4)}15%{opacity:.5}85%{opacity:.2}100%{bottom:110%;opacity:0;transform:scale(1)}}
</style>

<!-- Section Header -->
<div class="section-header">
    <h3 class="section-title">All Posts</h3>
    <div style="display:flex;gap:8px;align-items:center">
        <!-- View Toggle -->
        <div style="display:flex;border:1px solid var(--border);border-radius:var(--radius-sm);overflow:hidden">
            <button class="btn btn-sm" id="viewTableBtn" onclick="switchView('table')" style="border:none;border-radius:0;background:var(--primary);color:#fff;padding:8px 14px">
                <i class="fas fa-list"></i>
            </button>
            <button class="btn btn-sm" id="viewKanbanBtn" onclick="switchView('kanban')" style="border:none;border-radius:0;background:var(--bg-input);color:var(--text-muted);padding:8px 14px">
                <i class="fas fa-columns"></i>
            </button>
        </div>
        <a href="#" class="btn btn-primary btn-shine" onclick="navigateWithTransition(event, '<?= BASE_URL ?>/generator', 'Loading Generator...')">
            <i class="fas fa-magic"></i> New Post
        </a>
    </div>
</div>

<!-- Table View Filters -->
<div id="tableFilters" class="card mb-3" style="padding:16px 20px">
    <div class="flex-center gap-2" style="flex-wrap:wrap">
        <div class="form-group" style="margin-bottom:0;flex:1;min-width:160px">
            <select id="filter-status" class="form-select" onchange="filterPosts()">
                <option value="all">All Statuses</option>
                <option value="draft">Draft</option>
                <option value="pending_review">Pending Review</option>
                <option value="scheduled">Scheduled</option>
                <option value="published">Published</option>
                <option value="failed">Failed</option>
            </select>
        </div>
        <div class="form-group" style="margin-bottom:0;flex:1;min-width:160px">
            <select id="filter-platform" class="form-select" onchange="filterPosts()">
                <option value="all">All Platforms</option>
                <option value="facebook">Facebook</option>
                <option value="linkedin">LinkedIn</option>
            </select>
        </div>
        <div class="form-group" style="margin-bottom:0;flex:2;min-width:200px">
            <input type="text" id="filter-search" class="form-input" placeholder="Search posts..." oninput="filterPosts()">
        </div>
    </div>
</div>

<!-- Kanban View Filters -->
<div id="kanbanFilters" class="card mb-3" style="padding:16px 20px;display:none">
    <div class="flex-center gap-2" style="flex-wrap:wrap">
        <div class="form-group" style="margin-bottom:0;flex:1;min-width:160px">
            <select id="kanban-month" class="form-select" onchange="renderKanban()">
                <?php
                $now = new DateTime();
                for ($m = -1; $m <= 2; $m++) {
                    $d = (clone $now)->modify("{$m} months");
                    $val = $d->format('Y-m');
                    $label = $d->format('F Y');
                    $sel = $m === 0 ? ' selected' : '';
                    echo "<option value=\"{$val}\"{$sel}>{$label}</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group" style="margin-bottom:0;flex:1;min-width:160px">
            <select id="kanban-status" class="form-select" onchange="renderKanban()">
                <option value="all">All Statuses</option>
                <option value="draft">Draft</option>
                <option value="scheduled">Scheduled</option>
                <option value="published">Published</option>
                <option value="failed">Failed</option>
            </select>
        </div>
    </div>
</div>

<!-- Kanban Board -->
<div id="kanbanView" style="display:none">
    <div id="kanbanBoard" style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
        <!-- Rendered by JS -->
    </div>
</div>

<!-- Posts Table View -->
<div id="tableView">
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
                    <?php
                        $postPlatforms = [];
                        if (!empty($post['platforms'])) {
                            $decoded = json_decode($post['platforms'], true);
                            if (is_array($decoded)) $postPlatforms = $decoded;
                        }
                        if (empty($postPlatforms)) $postPlatforms = [$post['platform'] ?? 'facebook'];
                    ?>
                    <tr data-post-id="<?= (int)$post['id'] ?>"
                        data-status="<?= htmlspecialchars($post['status']) ?>"
                        data-platform="<?= htmlspecialchars(implode(',', $postPlatforms)) ?>"
                        data-search="<?= htmlspecialchars(strtolower($post['title'] . ' ' . ($post['topic'] ?? ''))) ?>">
                        <td>
                            <div style="font-weight:600;max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                <?= htmlspecialchars($post['title']) ?>
                            </div>
                            <?php if (!empty($post['topic'])): ?>
                                <div class="text-muted text-small"><?= htmlspecialchars($post['topic']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php foreach ($postPlatforms as $p): ?>
                                <span class="badge badge-<?= htmlspecialchars($p) ?>"><?= ucfirst(htmlspecialchars($p)) ?></span>
                            <?php endforeach; ?>
                        </td>
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
</div><!-- /tableView -->

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
        const matchPlatform = platform === 'all' || row.dataset.platform.split(',').includes(platform);
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

// --- Row Highlight on redirect ---
(function() {
    var params = new URLSearchParams(window.location.search);
    var highlightId = params.get('highlight');
    if (!highlightId) return;

    // Clean URL without reload
    var cleanUrl = window.location.pathname;
    window.history.replaceState({}, '', cleanUrl);

    // Find the row
    var row = document.querySelector('tr[data-post-id="' + highlightId + '"]');
    if (!row) return;

    // Clear any active filters so the row is visible
    document.querySelectorAll('.filter-select').forEach(function(s) { s.value = 'all'; });
    var searchInput = document.querySelector('.filter-search');
    if (searchInput) searchInput.value = '';
    document.querySelectorAll('tbody tr').forEach(function(r) { r.style.display = ''; });

    // Scroll to row
    setTimeout(function() {
        row.scrollIntoView({ behavior: 'smooth', block: 'center' });

        // Apply glow effect
        row.style.transition = 'box-shadow 0.6s ease, background 0.6s ease';
        row.style.position = 'relative';
        row.style.zIndex = '5';
        row.style.boxShadow = '0 0 0 2px var(--primary), 0 0 24px rgba(var(--primary-rgb), 0.35)';
        row.style.background = 'rgba(var(--primary-rgb), 0.08)';

        // Pulse the glow
        setTimeout(function() {
            row.style.boxShadow = '0 0 0 3px var(--primary), 0 0 40px rgba(var(--primary-rgb), 0.5)';
            row.style.background = 'rgba(var(--primary-rgb), 0.12)';
        }, 600);

        // Fade back to normal
        setTimeout(function() {
            row.style.transition = 'box-shadow 1.2s ease, background 1.2s ease';
            row.style.boxShadow = 'none';
            row.style.background = '';
        }, 2200);

        // Clean up
        setTimeout(function() {
            row.style.position = '';
            row.style.zIndex = '';
            row.style.transition = '';
        }, 3500);
    }, 300);
})();

// ---- View Toggle ----
var currentView = 'table';
var ALL_POSTS = <?= json_encode(array_map(function($p) {
    $plats = json_decode($p['platforms'] ?? '[]', true) ?: [$p['platform'] ?? 'facebook'];
    // Filter to active platforms only
    $plats = array_values(array_filter($plats, function($pl) { return in_array($pl, ['facebook','linkedin']); }));
    return [
        'id' => (int)$p['id'],
        'title' => $p['title'],
        'topic' => $p['topic'] ?? '',
        'post_type' => $p['post_type'] ?? '',
        'status' => $p['status'],
        'platforms' => $plats,
        'scheduled_at' => $p['scheduled_at'] ?? null,
        'created_at' => $p['created_at'] ?? null,
    ];
}, $posts)) ?>;

function switchView(view) {
    currentView = view;
    var tableBtn = document.getElementById('viewTableBtn');
    var kanbanBtn = document.getElementById('viewKanbanBtn');
    var tableView = document.getElementById('tableView');
    var kanbanView = document.getElementById('kanbanView');
    var tableFilters = document.getElementById('tableFilters');
    var kanbanFilters = document.getElementById('kanbanFilters');

    if (view === 'table') {
        tableBtn.style.background = 'var(--primary)'; tableBtn.style.color = '#fff';
        kanbanBtn.style.background = 'var(--bg-input)'; kanbanBtn.style.color = 'var(--text-muted)';
        tableView.style.display = '';
        kanbanView.style.display = 'none';
        tableFilters.style.display = '';
        kanbanFilters.style.display = 'none';
    } else {
        kanbanBtn.style.background = 'var(--primary)'; kanbanBtn.style.color = '#fff';
        tableBtn.style.background = 'var(--bg-input)'; tableBtn.style.color = 'var(--text-muted)';
        tableView.style.display = 'none';
        kanbanView.style.display = '';
        tableFilters.style.display = 'none';
        kanbanFilters.style.display = '';
        renderKanban();
    }
}

// ---- Kanban Board ----
var PLATFORM_CONFIG = {
    facebook: { label: 'Facebook', color: '#1877F2', icon: 'fab fa-facebook-f' },
    linkedin: { label: 'LinkedIn', color: '#0A66C2', icon: 'fab fa-linkedin-in' },
};

var STATUS_COLORS = {
    draft: 'var(--text-muted)',
    pending_review: 'var(--warning)',
    scheduled: 'var(--info)',
    published: 'var(--success)',
    failed: 'var(--danger)',
};

function renderKanban() {
    var board = document.getElementById('kanbanBoard');
    var monthFilter = document.getElementById('kanban-month').value; // "2026-04"
    var statusFilter = document.getElementById('kanban-status').value;
    var monthParts = monthFilter.split('-');
    var filterYear = parseInt(monthParts[0]);
    var filterMonth = parseInt(monthParts[1]);

    board.innerHTML = '';

    Object.keys(PLATFORM_CONFIG).forEach(function(platform) {
        var conf = PLATFORM_CONFIG[platform];

        // Filter posts for this platform and month
        var posts = ALL_POSTS.filter(function(p) {
            if (!p.platforms.includes(platform)) return false;
            if (statusFilter !== 'all' && p.status !== statusFilter) return false;
            var dateStr = p.scheduled_at || p.created_at;
            if (dateStr) {
                var d = new Date(dateStr);
                return d.getFullYear() === filterYear && (d.getMonth() + 1) === filterMonth;
            }
            // Posts without dates — show in current month
            var now = new Date();
            return now.getFullYear() === filterYear && (now.getMonth() + 1) === filterMonth;
        });

        // Sort by date (next up first)
        posts.sort(function(a, b) {
            var da = new Date(a.scheduled_at || a.created_at || 0);
            var db = new Date(b.scheduled_at || b.created_at || 0);
            return da - db;
        });

        // Build column
        var col = document.createElement('div');
        col.style.cssText = 'background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;animation:contentReveal 0.5s ease both;animation-delay:' + (platform === 'facebook' ? '0.1' : '0.2') + 's';

        // Column header
        col.innerHTML = '<div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;background:linear-gradient(135deg,' + conf.color + '12,transparent)">'
            + '<div style="width:32px;height:32px;border-radius:8px;background:' + conf.color + ';display:flex;align-items:center;justify-content:center"><i class="' + conf.icon + '" style="color:#fff;font-size:14px"></i></div>'
            + '<div>'
            + '<div style="font-size:14px;font-weight:700;color:var(--text)">' + conf.label + '</div>'
            + '<div style="font-size:11px;color:var(--text-muted)">' + posts.length + ' post' + (posts.length !== 1 ? 's' : '') + '</div>'
            + '</div></div>';

        // Cards container
        var cardsHtml = '<div style="padding:12px;display:flex;flex-direction:column;gap:8px;min-height:100px">';

        if (posts.length === 0) {
            cardsHtml += '<div style="text-align:center;padding:24px 12px;color:var(--text-muted);font-size:12px">No posts this month</div>';
        } else {
            posts.forEach(function(p, idx) {
                var statusColor = STATUS_COLORS[p.status] || 'var(--text-muted)';
                var dateLabel = '';
                if (p.scheduled_at) {
                    var d = new Date(p.scheduled_at);
                    dateLabel = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ', ' + d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
                }
                var typeLabel = (p.post_type || '').replace(/_/g, ' ');

                cardsHtml += '<a href="' + BASE + '/posts/edit/' + p.id + '" class="kanban-card" style="animation-delay:' + (0.1 + idx * 0.06) + 's" '
                    + 'onmouseenter="showKanbanTip(this,event)" onmouseleave="hideKanbanTip()">'
                    + '<div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">'
                    + '<div style="width:6px;height:6px;border-radius:50%;background:' + statusColor + ';flex-shrink:0"></div>'
                    + '<span style="font-size:12px;font-weight:600;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1">' + escHtml(p.title || 'Untitled') + '</span>'
                    + '</div>'
                    + (dateLabel ? '<div style="font-size:10px;color:var(--text-muted);padding-left:14px">' + dateLabel + '</div>' : '')
                    + '<div data-tip-type="' + escAttr(typeLabel) + '" data-tip-status="' + escAttr(p.status) + '" data-tip-topic="' + escAttr(p.topic || '') + '" style="display:none"></div>'
                    + '</a>';
            });
        }
        cardsHtml += '</div>';
        col.innerHTML += cardsHtml;
        board.appendChild(col);
    });
}

// Animated tooltip for kanban cards
var kanbanTipEl = null;
function showKanbanTip(card, e) {
    hideKanbanTip();
    var meta = card.querySelector('[data-tip-type]');
    if (!meta) return;

    var tip = document.createElement('div');
    tip.id = 'kanbanTip';
    tip.style.cssText = 'position:fixed;z-index:9999;background:rgba(15,23,42,0.95);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.08);border-radius:10px;padding:12px 16px;max-width:220px;pointer-events:none;animation:tipIn 0.25s cubic-bezier(0.34,1.56,0.64,1)';

    var lines = [];
    if (meta.dataset.tipType) lines.push('<div style="font-size:11px;color:rgba(255,255,255,0.5);text-transform:capitalize;margin-bottom:2px">' + meta.dataset.tipType + '</div>');
    if (meta.dataset.tipTopic) lines.push('<div style="font-size:12px;color:#fff;font-weight:600">' + meta.dataset.tipTopic + '</div>');
    lines.push('<div style="font-size:10px;color:rgba(255,255,255,0.4);margin-top:4px;text-transform:capitalize">' + meta.dataset.tipStatus.replace(/_/g, ' ') + '</div>');
    tip.innerHTML = lines.join('');

    document.body.appendChild(tip);
    kanbanTipEl = tip;

    var rect = card.getBoundingClientRect();
    tip.style.left = (rect.right + 8) + 'px';
    tip.style.top = rect.top + 'px';

    // Keep in viewport
    var tipRect = tip.getBoundingClientRect();
    if (tipRect.right > window.innerWidth - 10) {
        tip.style.left = (rect.left - tipRect.width - 8) + 'px';
    }
}

function hideKanbanTip() {
    var t = document.getElementById('kanbanTip');
    if (t) t.remove();
    kanbanTipEl = null;
}

// Page transition for navigation
function navigateWithTransition(e, url, statusText) {
    e.preventDefault();
    var portal = document.getElementById('pageTransition');
    var content = document.getElementById('pageTransContent');
    var status = document.getElementById('pageTransStatus');
    var primary = '<?= $tPrimary ?>';

    status.textContent = statusText || 'Loading...';
    portal.style.background = 'linear-gradient(165deg,' + primary + ' 0%,#0a0a0a 60%,#000 100%)';
    portal.style.opacity = '1';
    portal.style.visibility = 'visible';
    portal.style.backdropFilter = 'blur(10px)';
    content.style.transform = 'scale(1)';
    content.style.opacity = '1';

    setTimeout(function() {
        window.location.href = url;
    }, 1500);
}
</script>
<style>
.kanban-card {
    display: block;
    padding: 10px 12px;
    background: var(--bg-input);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
    animation: kanbanCardIn 0.3s ease both;
}
.kanban-card:hover {
    border-color: var(--primary);
    box-shadow: 0 2px 8px rgba(var(--primary-rgb), 0.12);
    transform: translateY(-1px);
}
@keyframes kanbanCardIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes tipIn {
    from { opacity: 0; transform: scale(0.9) translateY(4px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.btn-shine{position:relative;overflow:hidden}
.btn-shine::after{content:'';position:absolute;top:-50%;left:-60%;width:40%;height:200%;background:linear-gradient(105deg,transparent 40%,rgba(255,255,255,0.35) 45%,rgba(255,255,255,0.1) 50%,transparent 55%);opacity:0;pointer-events:none;transition:opacity .2s}
.btn-shine:hover::after{opacity:1;animation:btnShine .7s ease forwards}
@keyframes btnShine{0%{left:-60%}100%{left:120%}}
</style>
