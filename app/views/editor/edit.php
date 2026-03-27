<?php
$csrfToken = $_SESSION['csrf_token'] ?? '';
$post = $post ?? [];
$postId = (int)($post['id'] ?? 0);

// Parse platforms from JSON column, fall back to single platform
$selectedPlatforms = [];
if (!empty($post['platforms'])) {
    $decoded = json_decode($post['platforms'], true);
    if (is_array($decoded)) {
        $selectedPlatforms = $decoded;
    }
}
if (empty($selectedPlatforms) && !empty($post['platform'])) {
    $selectedPlatforms = [$post['platform']];
}

$brandingService = new BrandingService();
$branding = $brandingService->get($GLOBALS['client_id']);
$companyName = htmlspecialchars($branding['company_name'] ?? 'Your Company');
$firstName = htmlspecialchars($_SESSION['first_name'] ?? '');
?>

<input type="hidden" id="csrf-token" value="<?= htmlspecialchars($csrfToken) ?>">
<input type="hidden" id="post-id" value="<?= $postId ?>">

<style>
    .editor-layout { display: grid; grid-template-columns: 3fr 2fr; gap: 24px; }
    .editor-image-preview {
        width: 100%;
        aspect-ratio: 1;
        background: var(--bg-input);
        border-radius: var(--radius-md);
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        margin-bottom: 20px;
        position: relative;
    }
    .editor-image-preview img { width: 100%; height: 100%; object-fit: cover; }
    .editor-image-preview .placeholder-icon {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }
    .editor-image-preview .placeholder-icon i { font-size: 40px; opacity: 0.25; }
    .editor-image-preview .placeholder-icon span { font-size: 13px; }

    /* Platform checkboxes */
    .platform-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .platform-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        cursor: pointer;
        transition: all 0.15s ease;
        user-select: none;
    }
    .platform-option:hover {
        border-color: var(--primary);
        background: rgba(var(--primary-rgb), 0.03);
    }
    .platform-option.selected {
        border-color: var(--primary);
        background: rgba(var(--primary-rgb), 0.08);
    }
    .platform-option input[type="checkbox"] { display: none; }
    .platform-option .platform-check {
        width: 18px;
        height: 18px;
        border-radius: 4px;
        border: 2px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease;
        flex-shrink: 0;
    }
    .platform-option.selected .platform-check {
        background: var(--primary);
        border-color: var(--primary);
    }
    .platform-option.selected .platform-check::after {
        content: '\f00c';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        font-size: 10px;
        color: #fff;
    }
    .platform-option .platform-icon {
        font-size: 16px;
        width: 20px;
        text-align: center;
    }
    .platform-option .platform-label {
        font-size: 13px;
        font-weight: 500;
        color: var(--text);
    }
    .platform-option.disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
    .platform-option.disabled:hover {
        border-color: var(--border);
        background: transparent;
    }

    /* Action bar */
    .editor-action-bar {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 20px 0 0;
        border-top: 1px solid var(--border);
        margin-top: 24px;
        flex-wrap: wrap;
    }
    .editor-action-bar .btn-danger { margin-left: auto; }

    /* Post logs section */
    .post-logs { margin-top: 24px; }
    .log-entry {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid var(--border-light);
        font-size: 13px;
    }
    .log-entry:last-child { border-bottom: none; }
    .log-platform-badge {
        padding: 3px 10px;
        border-radius: 100px;
        font-size: 11px;
        font-weight: 600;
        text-transform: capitalize;
    }
    .log-status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .log-status-dot.success { background: var(--success); }
    .log-status-dot.failed { background: var(--danger); }
    .log-status-dot.pending { background: var(--warning); }

    @media (max-width: 768px) {
        .editor-layout { grid-template-columns: 1fr; }
        .platform-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="section-header">
    <h3 class="section-title">
        <a href="<?= BASE_URL ?>/posts" style="color:var(--text-muted);margin-right:8px" title="Back to posts"><i class="fas fa-arrow-left"></i></a>
        Edit Post
    </h3>
    <span class="badge badge-<?= htmlspecialchars($post['status'] ?? 'draft') ?>"><?= ucfirst(htmlspecialchars($post['status'] ?? 'draft')) ?></span>
</div>

<div class="editor-layout">
    <!-- Left Column: Image + Content -->
    <div>
        <div class="card">
            <div class="editor-image-preview" id="image-preview">
                <?php if (!empty($post['image_url'])): ?>
                    <img src="<?= htmlspecialchars($post['image_url']) ?>" alt="Post image" id="preview-img">
                <?php else: ?>
                    <div class="placeholder-icon">
                        <i class="fas fa-image"></i>
                        <span>No image</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="edit-title">Title</label>
                <input type="text" id="edit-title" class="form-input" value="<?= htmlspecialchars($post['title'] ?? '') ?>" placeholder="Post title">
            </div>

            <div class="form-group" style="margin-bottom:0">
                <label class="form-label" for="edit-content">Caption / Content</label>
                <textarea id="edit-content" class="form-textarea" rows="8" placeholder="Write your post content..."><?= htmlspecialchars($post['content'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Right Column: Settings -->
    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cog" style="margin-right:8px;color:var(--text-muted)"></i>Settings</h3>
            </div>

            <!-- Platform Multi-Select -->
            <div class="form-group">
                <label class="form-label">Publish To</label>
                <div class="platform-grid" id="platform-grid">
                    <label class="platform-option <?= in_array('facebook', $selectedPlatforms) ? 'selected' : '' ?>" data-platform="facebook">
                        <input type="checkbox" name="platforms[]" value="facebook" <?= in_array('facebook', $selectedPlatforms) ? 'checked' : '' ?>>
                        <div class="platform-check"></div>
                        <i class="fab fa-facebook platform-icon" style="color:#1877F2"></i>
                        <span class="platform-label">Facebook</span>
                    </label>
                    <label class="platform-option <?= in_array('linkedin', $selectedPlatforms) ? 'selected' : '' ?>" data-platform="linkedin">
                        <input type="checkbox" name="platforms[]" value="linkedin" <?= in_array('linkedin', $selectedPlatforms) ? 'checked' : '' ?>>
                        <div class="platform-check"></div>
                        <i class="fab fa-linkedin platform-icon" style="color:#0A66C2"></i>
                        <span class="platform-label">LinkedIn</span>
                    </label>
                    <label class="platform-option disabled" data-platform="instagram" title="Coming soon">
                        <input type="checkbox" name="platforms[]" value="instagram" disabled>
                        <div class="platform-check"></div>
                        <i class="fab fa-instagram platform-icon" style="color:#E4405F"></i>
                        <span class="platform-label">Instagram</span>
                    </label>
                    <label class="platform-option disabled" data-platform="twitter" title="Coming soon">
                        <input type="checkbox" name="platforms[]" value="twitter" disabled>
                        <div class="platform-check"></div>
                        <i class="fab fa-x-twitter platform-icon" style="color:var(--text)"></i>
                        <span class="platform-label">X / Twitter</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="edit-post-type">Post Type</label>
                <select id="edit-post-type" class="form-select">
                    <option value="educational" <?= ($post['post_type'] ?? '') === 'educational' ? 'selected' : '' ?>>Educational</option>
                    <option value="promotional" <?= ($post['post_type'] ?? '') === 'promotional' ? 'selected' : '' ?>>Promotional</option>
                    <option value="engagement" <?= ($post['post_type'] ?? '') === 'engagement' ? 'selected' : '' ?>>Engagement</option>
                    <option value="storytelling" <?= ($post['post_type'] ?? '') === 'storytelling' ? 'selected' : '' ?>>Storytelling</option>
                    <option value="behind_the_scenes" <?= ($post['post_type'] ?? '') === 'behind_the_scenes' ? 'selected' : '' ?>>Behind the Scenes</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="edit-status">Status</label>
                <select id="edit-status" class="form-select">
                    <option value="draft" <?= ($post['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="scheduled" <?= ($post['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                    <option value="published" <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="failed" <?= ($post['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="edit-scheduled-at">Schedule Date &amp; Time</label>
                <input type="datetime-local" id="edit-scheduled-at" class="form-input" value="<?= !empty($post['scheduled_at']) ? date('Y-m-d\TH:i', strtotime($post['scheduled_at'])) : '' ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="edit-topic">Topic</label>
                <input type="text" id="edit-topic" class="form-input" value="<?= htmlspecialchars($post['topic'] ?? '') ?>" placeholder="Post topic">
            </div>

            <div class="form-group">
                <label class="form-label" for="edit-keywords">Keywords</label>
                <input type="text" id="edit-keywords" class="form-input" value="<?= htmlspecialchars($post['keywords'] ?? '') ?>" placeholder="Comma-separated keywords">
            </div>

            <div class="form-group" style="margin-bottom:0">
                <label class="form-label" for="edit-angle">Angle</label>
                <input type="text" id="edit-angle" class="form-input" value="<?= htmlspecialchars($post['angle'] ?? '') ?>" placeholder="Content angle or hook">
            </div>
        </div>

        <!-- Post Logs (if any) -->
        <div class="card post-logs" id="post-logs-card" style="display:none">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history" style="margin-right:8px;color:var(--text-muted)"></i>Posting History</h3>
            </div>
            <div id="post-logs-list"></div>
        </div>
    </div>
</div>

<!-- Action Bar -->
<div class="editor-action-bar">
    <button class="btn btn-primary" id="btn-save" onclick="savePost()">
        <i class="fas fa-save"></i> Save Changes
    </button>
    <button class="btn btn-ghost" id="btn-schedule" onclick="schedulePost()">
        <i class="fas fa-clock"></i> Schedule
    </button>
    <button class="btn btn-primary" id="btn-post-now" onclick="postNow()" style="background:var(--success);border-color:var(--success)">
        <i class="fas fa-paper-plane"></i> Post Now
    </button>
    <?php if (($post['status'] ?? '') === 'failed'): ?>
    <button class="btn btn-ghost" id="btn-retry" onclick="retryPost()" style="color:var(--warning)">
        <i class="fas fa-redo"></i> Retry Failed
    </button>
    <?php endif; ?>
    <button class="btn btn-danger" id="btn-delete" onclick="deletePost()">
        <i class="fas fa-trash-alt"></i> Delete
    </button>
</div>

<script>
const BASE = '<?= rtrim(BASE_URL, '/') ?>';
const postId = document.getElementById('post-id').value;
const csrfToken = () => document.getElementById('csrf-token').value;

// Platform checkbox toggle
document.querySelectorAll('.platform-option:not(.disabled)').forEach(label => {
    label.addEventListener('click', function(e) {
        // Prevent the native label→checkbox toggle so we control it ourselves
        e.preventDefault();
        const cb = this.querySelector('input[type="checkbox"]');
        cb.checked = !cb.checked;
        this.classList.toggle('selected', cb.checked);
    });
});

function getSelectedPlatforms() {
    const checked = document.querySelectorAll('#platform-grid input[type="checkbox"]:checked');
    return Array.from(checked).map(cb => cb.value);
}

function getPostData() {
    const platforms = getSelectedPlatforms();
    return {
        title: document.getElementById('edit-title').value,
        content: document.getElementById('edit-content').value,
        image_url: '<?= htmlspecialchars($post['image_url'] ?? '', ENT_QUOTES) ?>',
        platform: platforms[0] || 'facebook',
        platforms: JSON.stringify(platforms),
        post_type: document.getElementById('edit-post-type').value,
        status: document.getElementById('edit-status').value,
        scheduled_at: document.getElementById('edit-scheduled-at').value || '',
        topic: document.getElementById('edit-topic').value,
        keywords: document.getElementById('edit-keywords').value,
        angle: document.getElementById('edit-angle').value,
    };
}

function setLoading(btn, loading, label) {
    if (loading) {
        btn.dataset.origLabel = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + (label || 'Loading...');
        btn.disabled = true;
        btn.style.opacity = '0.7';
    } else {
        btn.innerHTML = btn.dataset.origLabel || btn.innerHTML;
        btn.disabled = false;
        btn.style.opacity = '';
    }
}

async function savePost() {
    const btn = document.getElementById('btn-save');
    setLoading(btn, true, 'Saving...');

    try {
        const data = getPostData();
        const formData = new FormData();
        formData.append('csrf_token', csrfToken());
        Object.entries(data).forEach(([k, v]) => formData.append(k, v));

        const res = await fetch(BASE + '/posts/update/' + postId, {
            method: 'POST',
            body: formData
        });
        const result = await res.json();
        if (!res.ok || result.error) throw new Error(result.error || 'Save failed');

        showToast('Post saved successfully!', 'success');
    } catch (err) {
        showToast(err.message, 'error');
    } finally {
        setLoading(btn, false);
    }
}

async function schedulePost() {
    const scheduledAt = document.getElementById('edit-scheduled-at').value;
    if (!scheduledAt) {
        showToast('Please set a date and time first.', 'warning');
        document.getElementById('edit-scheduled-at').focus();
        return;
    }

    // Prevent scheduling in the past
    const scheduledDate = new Date(scheduledAt);
    const now = new Date();
    if (scheduledDate <= now) {
        showToast('Scheduled time must be in the future. Please pick a later date/time.', 'warning');
        document.getElementById('edit-scheduled-at').focus();
        return;
    }

    const platforms = getSelectedPlatforms();
    if (platforms.length === 0) {
        showToast('Please select at least one platform.', 'warning');
        return;
    }

    const btn = document.getElementById('btn-schedule');
    setLoading(btn, true, 'Scheduling...');

    try {
        // Save the post with status=scheduled — the cron job will handle
        // posting it to the platform(s) when the scheduled time arrives.
        const data = getPostData();
        data.status = 'scheduled';
        const formData = new FormData();
        formData.append('csrf_token', csrfToken());
        Object.entries(data).forEach(([k, v]) => formData.append(k, v));

        const saveRes = await fetch(BASE + '/posts/update/' + postId, {
            method: 'POST',
            body: formData
        });
        const saveResult = await saveRes.json();
        if (!saveRes.ok || saveResult.error) throw new Error(saveResult.error || 'Save failed');

        document.getElementById('edit-status').value = 'scheduled';

        const dateStr = scheduledDate.toLocaleDateString('en-US', {
            month: 'short', day: 'numeric', year: 'numeric',
            hour: 'numeric', minute: '2-digit'
        });
        showToast('Scheduled for ' + platforms.join(' & ') + ' on ' + dateStr, 'success');
    } catch (err) {
        showToast(err.message, 'error');
    } finally {
        setLoading(btn, false);
    }
}

async function postNow() {
    const platforms = getSelectedPlatforms();
    if (platforms.length === 0) {
        showToast('Please select at least one platform to post to.', 'warning');
        return;
    }

    const content = document.getElementById('edit-content').value.trim();
    if (!content) {
        showToast('Post content cannot be empty.', 'warning');
        return;
    }

    confirmModal(
        'Post Now',
        'This will immediately publish to <strong>' + platforms.join(' &amp; ') + '</strong>. Are you sure?',
        async () => {
            const btn = document.getElementById('btn-post-now');
            setLoading(btn, true, 'Posting...');

            try {
                // Save first to ensure latest content is stored
                const data = getPostData();
                const saveFormData = new FormData();
                saveFormData.append('csrf_token', csrfToken());
                Object.entries(data).forEach(([k, v]) => saveFormData.append(k, v));

                await fetch(BASE + '/posts/update/' + postId, {
                    method: 'POST',
                    body: saveFormData
                });

                // Now post
                const formData = new FormData();
                formData.append('csrf_token', csrfToken());

                const res = await fetch(BASE + '/posts/post-now/' + postId, {
                    method: 'POST',
                    body: formData
                });
                const result = await res.json();

                if (result.success) {
                    document.getElementById('edit-status').value = result.new_status || 'published';
                    let msg = 'Posted successfully to ' + platforms.join(' & ') + '!';
                    if (result.results) {
                        const failed = Object.entries(result.results).filter(([k, v]) => !v.success);
                        if (failed.length > 0) {
                            msg = 'Posted to some platforms. ' + failed.length + ' failed.';
                            showToast(msg, 'warning');
                        } else {
                            showToast(msg, 'success');
                        }
                    } else {
                        showToast(msg, 'success');
                    }
                } else {
                    throw new Error(result.error || 'Posting failed');
                }

                loadPostLogs();
            } catch (err) {
                showToast(err.message, 'error');
            } finally {
                setLoading(btn, false);
            }
        }
    );
}

async function retryPost() {
    const btn = document.getElementById('btn-retry');
    if (!btn) return;
    setLoading(btn, true, 'Retrying...');

    try {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken());

        const res = await fetch(BASE + '/posts/retry/' + postId, {
            method: 'POST',
            body: formData
        });
        const result = await res.json();

        if (result.success) {
            showToast('Retry completed!', 'success');
            loadPostLogs();
        } else {
            throw new Error(result.error || 'Retry failed');
        }
    } catch (err) {
        showToast(err.message, 'error');
    } finally {
        setLoading(btn, false);
    }
}

function deletePost() {
    confirmModal('Delete Post', 'Are you sure you want to delete this post? This action cannot be undone.', async () => {
        const btn = document.getElementById('btn-delete');
        setLoading(btn, true, 'Deleting...');

        try {
            const formData = new FormData();
            formData.append('csrf_token', csrfToken());

            const res = await fetch(BASE + '/posts/delete/' + postId, {
                method: 'POST',
                body: formData
            });
            const result = await res.json();
            if (!res.ok) throw new Error(result.error || 'Delete failed');

            showToast('Post deleted.', 'success');
            setTimeout(() => window.location.href = BASE + '/posts', 800);
        } catch (err) {
            showToast(err.message, 'error');
            setLoading(btn, false);
        }
    });
}

// Load posting logs
async function loadPostLogs() {
    try {
        const res = await fetch(BASE + '/posts/logs/' + postId);
        const result = await res.json();

        if (result.logs && result.logs.length > 0) {
            const card = document.getElementById('post-logs-card');
            const list = document.getElementById('post-logs-list');
            card.style.display = 'block';

            list.innerHTML = result.logs.map(log => {
                const date = new Date(log.created_at).toLocaleString();
                const statusClass = log.status === 'success' ? 'success' : (log.status === 'failed' ? 'failed' : 'pending');
                return `<div class="log-entry">
                    <div class="log-status-dot ${statusClass}"></div>
                    <span class="log-platform-badge" style="background:var(--bg-input)">${log.platform}</span>
                    <span style="flex:1;color:var(--text-secondary);font-size:13px">${log.status === 'failed' ? (log.error_message || 'Failed') : 'Published'}</span>
                    <span style="color:var(--text-muted);font-size:12px">${date}</span>
                </div>`;
            }).join('');
        }
    } catch (e) {
        // Silent fail for logs
    }
}

// Load logs on page load
loadPostLogs();
</script>
