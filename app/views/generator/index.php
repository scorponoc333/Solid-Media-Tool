<?php
$csrfToken = $_SESSION['csrf_token'] ?? '';
$firstName = $_SESSION['first_name'] ?? '';
$brandingService = new BrandingService();
$brand = $brandingService->get($GLOBALS['client_id']);
$companyName = htmlspecialchars($brand['company_name'] ?? 'your company');
$nameGreet = $firstName ? htmlspecialchars($firstName) . ', you' : 'You';
$logoUrl = $brand['logo_url'] ?? '';
$primaryColor = $brand['primary_color'] ?? '#6366f1';
$secondaryColor = $brand['secondary_color'] ?? '#8b5cf6';
?>

<input type="hidden" id="csrf-token" value="<?= htmlspecialchars($csrfToken) ?>">

<!-- AI Generation Lightbox -->
<div id="ai-lightbox" class="ai-lightbox">
    <div class="ai-lightbox-content">
        <!-- Orbiting ring behind the logo -->
        <div class="ai-orbit-ring">
            <div class="ai-orbit-dot"></div>
            <div class="ai-orbit-dot" style="animation-delay:-1.2s"></div>
            <div class="ai-orbit-dot" style="animation-delay:-2.4s"></div>
        </div>

        <!-- Logo -->
        <div class="ai-lightbox-logo">
            <?php if ($logoUrl): ?>
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="<?= $companyName ?>">
            <?php else: ?>
                <div class="ai-logo-fallback"><?= strtoupper(substr($brand['company_name'] ?? 'S', 0, 1)) ?></div>
            <?php endif; ?>
        </div>

        <!-- Pulse ring -->
        <div class="ai-pulse-ring"></div>
        <div class="ai-pulse-ring" style="animation-delay:0.6s"></div>

        <!-- Status text -->
        <h3 class="ai-lightbox-title" id="ai-lightbox-title">AI is generating your content</h3>
        <p class="ai-lightbox-subtitle" id="ai-lightbox-subtitle">Crafting a week of posts for <?= $companyName ?></p>

        <!-- Progress bar -->
        <div class="ai-progress-track">
            <div class="ai-progress-bar" id="ai-progress-bar"></div>
        </div>

        <!-- Floating particles -->
        <div class="ai-particles">
            <span></span><span></span><span></span><span></span><span></span>
            <span></span><span></span><span></span><span></span><span></span>
        </div>
    </div>
</div>

<style>
/* ---- AI Generation Lightbox ---- */
.ai-lightbox {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0,0,0,0.65);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity 0.4s ease, visibility 0.4s ease;
}
.ai-lightbox.visible {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
}
.ai-lightbox-content {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 60px 48px 48px;
    border-radius: 24px;
    background: linear-gradient(
        165deg,
        <?= htmlspecialchars($primaryColor) ?> 0%,
        <?= htmlspecialchars($secondaryColor) ?> 100%
    );
    box-shadow:
        0 0 0 1px rgba(0,0,0,0.1),
        0 24px 80px rgba(0,0,0,0.3),
        0 0 120px -20px <?= htmlspecialchars($primaryColor) ?>88;
    max-width: 440px;
    width: 90%;
    overflow: hidden;
}
[data-theme="dark"] .ai-lightbox-content {
    background: linear-gradient(
        165deg,
        <?= htmlspecialchars($primaryColor) ?> 0%,
        <?= htmlspecialchars($secondaryColor) ?> 100%
    );
}
/* Top accent stripe — lighter shimmer on branded bg */
.ai-lightbox-content::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, rgba(255,255,255,0.1), rgba(255,255,255,0.5), rgba(255,255,255,0.1));
    background-size: 200% 100%;
    animation: ai-stripe 2.4s ease infinite;
}
@keyframes ai-stripe {
    0%,100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

/* ---- Logo ---- */
.ai-lightbox-logo {
    position: relative;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(4px);
    margin-bottom: 12px;
    z-index: 2;
}
.ai-lightbox-logo img {
    max-width: 68px;
    max-height: 68px;
    object-fit: contain;
    border-radius: 8px;
    filter: brightness(0) invert(1);
}
.ai-logo-fallback {
    font-size: 40px;
    font-weight: 800;
    color: #fff;
}

/* ---- Orbiting ring ---- */
.ai-orbit-ring {
    position: absolute;
    top: 60px;
    width: 140px;
    height: 140px;
    border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.15);
    animation: ai-orbit-spin 3.6s linear infinite;
    z-index: 1;
}
@keyframes ai-orbit-spin { to { transform: rotate(360deg); } }

.ai-orbit-dot {
    position: absolute;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #fff;
    top: -5px;
    left: calc(50% - 5px);
    box-shadow: 0 0 12px rgba(255,255,255,0.6);
    animation: ai-orbit-glow 1.2s ease-in-out infinite alternate;
}
.ai-orbit-dot:nth-child(2) { top: auto; bottom: -5px; }
.ai-orbit-dot:nth-child(3) { top: calc(50% - 5px); left: -5px; }
@keyframes ai-orbit-glow {
    0% { opacity: 0.5; transform: scale(0.8); }
    100% { opacity: 1; transform: scale(1.2); }
}

/* ---- Pulse rings ---- */
.ai-pulse-ring {
    position: absolute;
    top: 60px;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.5);
    opacity: 0;
    z-index: 0;
    animation: ai-pulse 2.4s ease-out infinite;
    pointer-events: none;
}
@keyframes ai-pulse {
    0%   { transform: scale(1); opacity: 0.5; }
    100% { transform: scale(2.2); opacity: 0; }
}

/* ---- Text ---- */
.ai-lightbox-title {
    font-size: 20px;
    font-weight: 700;
    color: #fff;
    margin: 20px 0 8px;
    letter-spacing: -0.3px;
}
.ai-lightbox-subtitle {
    font-size: 14px;
    color: rgba(255,255,255,0.75);
    margin: 0 0 28px;
    line-height: 1.5;
}

/* ---- Progress bar ---- */
.ai-progress-track {
    width: 100%;
    height: 6px;
    border-radius: 3px;
    background: rgba(0,0,0,0.2);
    overflow: hidden;
    position: relative;
    z-index: 2;
}
.ai-progress-bar {
    height: 100%;
    width: 0%;
    border-radius: 3px;
    background: linear-gradient(90deg, rgba(255,255,255,0.8), #fff);
    transition: width 0.4s ease;
    position: relative;
}
.ai-progress-bar::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.5), transparent);
    animation: ai-shimmer 1.8s ease infinite;
}
@keyframes ai-shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

/* ---- Floating particles ---- */
.ai-particles {
    position: absolute;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
    z-index: 0;
}
.ai-particles span {
    position: absolute;
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background: rgba(255,255,255,0.7);
    opacity: 0;
    animation: ai-float 4s ease-in-out infinite;
}
.ai-particles span:nth-child(1)  { left:10%; animation-delay:0s; }
.ai-particles span:nth-child(2)  { left:20%; animation-delay:0.5s; }
.ai-particles span:nth-child(3)  { left:35%; animation-delay:1s; }
.ai-particles span:nth-child(4)  { left:50%; animation-delay:1.5s; }
.ai-particles span:nth-child(5)  { left:65%; animation-delay:2s; }
.ai-particles span:nth-child(6)  { left:75%; animation-delay:0.3s; }
.ai-particles span:nth-child(7)  { left:85%; animation-delay:0.8s; }
.ai-particles span:nth-child(8)  { left:45%; animation-delay:1.2s; }
.ai-particles span:nth-child(9)  { left:58%; animation-delay:1.8s; }
.ai-particles span:nth-child(10) { left:90%; animation-delay:2.2s; }

@keyframes ai-float {
    0%   { bottom: -10px; opacity: 0; transform: scale(0.5); }
    20%  { opacity: 0.6; }
    80%  { opacity: 0.3; }
    100% { bottom: 110%; opacity: 0; transform: scale(1.2); }
}
</style>

<!-- Generator Controls -->
<div class="grid-2 mb-3">
    <!-- Generate Full Week -->
    <div class="card" style="display:flex;flex-direction:column">
        <div class="card-header">
            <div>
                <h3 class="card-title"><i class="fas fa-calendar-week" style="color:var(--primary);margin-right:8px"></i>Generate Full Week</h3>
                <p class="card-subtitle">A full week of content in one click</p>
            </div>
        </div>
        <p style="color:var(--text-secondary);font-size:14px;line-height:1.7;margin-bottom:20px">
            <?= $nameGreet ?> can automatically generate a full week of varied social media posts for <?= $companyName ?>. Each post is crafted with a unique angle, and the content memory engine ensures nothing feels repetitive.
        </p>
        <div style="margin-top:auto">
            <button class="btn btn-primary w-full" id="btn-generate-week" onclick="generateWeek()">
                <i class="fas fa-magic"></i> Generate Full Week
            </button>
        </div>
    </div>

    <!-- Generate Single Post -->
    <div class="card" style="display:flex;flex-direction:column">
        <div class="card-header">
            <div>
                <h3 class="card-title"><i class="fas fa-feather-alt" style="color:var(--secondary);margin-right:8px"></i>Generate Single Post</h3>
                <p class="card-subtitle">One focused post for <?= $companyName ?></p>
            </div>
        </div>
        <form id="single-post-form" onsubmit="generateSingle(event)" style="flex:1;display:flex;flex-direction:column">
            <div class="form-group">
                <label class="form-label" for="topic">Topic</label>
                <input type="text" id="topic" name="topic" class="form-input" placeholder="e.g. Benefits of cloud computing" required>
            </div>
            <div class="form-group" style="margin-bottom:16px">
                <label class="form-label" for="post_type">Post Type</label>
                <select id="post_type" name="post_type" class="form-select">
                    <option value="educational">Educational</option>
                    <option value="promotional">Promotional</option>
                    <option value="engagement">Engagement</option>
                    <option value="storytelling">Storytelling</option>
                    <option value="behind_the_scenes">Behind the Scenes</option>
                </select>
            </div>
            <div style="margin-top:auto">
                <button type="submit" class="btn btn-primary w-full" id="btn-generate-single">
                    <i class="fas fa-bolt"></i> Generate Post
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Results Area -->
<div class="section-header">
    <h3 class="section-title">Generated Content</h3>
    <span class="text-muted text-small" id="results-count"></span>
</div>

<div id="generator-results">
    <div class="card">
        <div class="empty-state">
            <i class="fas fa-wand-magic-sparkles"></i>
            <p><?= $firstName ? "Hey {$firstName}, your" : 'Your' ?> generated posts for <?= $companyName ?> will appear here. Use the controls above to get started.</p>
        </div>
    </div>
</div>

<style>
    /* 2x2 grid for generated result cards */
    .results-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    @media (max-width: 900px) {
        .results-grid { grid-template-columns: 1fr; }
    }

    .result-card { }
    .result-card .post-card {
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .result-card .post-card-image-wrap {
        width: 100%;
        aspect-ratio: 1/1;
        background: var(--bg-input);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        font-size: 14px;
        position: relative;
        overflow: hidden;
        border-radius: var(--radius-md) var(--radius-md) 0 0;
    }
    .result-card .post-card-image-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .result-card .post-card-image-wrap .placeholder-icon {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }
    .result-card .post-card-image-wrap .placeholder-icon i { font-size: 32px; opacity: 0.3; }
    .result-card .editable-title {
        width: 100%;
        border: none;
        background: transparent;
        font-size: 15px;
        font-weight: 600;
        color: var(--text);
        font-family: inherit;
        padding: 0;
        margin-bottom: 8px;
        outline: none;
        border-bottom: 1px dashed transparent;
        transition: border-color var(--transition);
    }
    .result-card .editable-title:focus { border-bottom-color: var(--primary); }
    .result-card .editable-content {
        width: 100%;
        border: none;
        background: var(--bg-input);
        border-radius: var(--radius-sm);
        font-size: 13px;
        color: var(--text-secondary);
        font-family: inherit;
        padding: 10px 12px;
        line-height: 1.6;
        resize: vertical;
        min-height: 72px;
        outline: none;
        transition: border-color var(--transition);
        border: 1px solid transparent;
    }
    .result-card .editable-content:focus { border-color: var(--primary); }
    .result-card .post-card-actions {
        flex-wrap: wrap;
        gap: 6px;
    }
    .btn-loading { pointer-events: none; opacity: 0.7; }
    .btn-loading i { animation: spin 1s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .img-loading-overlay {
        position: absolute;
        inset: 0;
        background: rgba(var(--primary-rgb), 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

<script>
const BASE = '<?= rtrim(BASE_URL, '/') ?>';
const csrfToken = () => document.getElementById('csrf-token').value;

function setLoading(btn, loading, label) {
    if (loading) {
        btn.dataset.origLabel = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner"></i> ' + (label || 'Generating...');
        btn.classList.add('btn-loading');
    } else {
        btn.innerHTML = btn.dataset.origLabel || btn.innerHTML;
        btn.classList.remove('btn-loading');
    }
}

// --- AI Lightbox helpers ---
const aiLightbox = document.getElementById('ai-lightbox');
const aiProgressBar = document.getElementById('ai-progress-bar');
const aiTitle = document.getElementById('ai-lightbox-title');
const aiSubtitle = document.getElementById('ai-lightbox-subtitle');
let aiProgressInterval = null;

const aiMessages = [
    'Analyzing your brand voice\u2026',
    'Selecting trending topics\u2026',
    'Crafting unique angles\u2026',
    'Checking content memory\u2026',
    'Writing compelling captions\u2026',
    'Fine-tuning for engagement\u2026',
    'Polishing the final drafts\u2026',
];

function showAILightbox(title, subtitle) {
    aiTitle.textContent = title || 'AI is generating your content';
    aiSubtitle.textContent = subtitle || 'Crafting posts for <?= addslashes($companyName) ?>';
    aiProgressBar.style.width = '0%';
    // Force reflow so transition triggers cleanly
    void aiLightbox.offsetWidth;
    aiLightbox.classList.add('visible');
    document.body.style.overflow = 'hidden';

    // Animate progress bar to ~90% over time
    let progress = 0;
    let msgIndex = 0;
    aiProgressInterval = setInterval(() => {
        const remaining = 90 - progress;
        progress += remaining * 0.04 + Math.random() * 1.5;
        if (progress > 90) progress = 90;
        aiProgressBar.style.width = progress + '%';

        // Cycle through status messages
        if (progress > (msgIndex + 1) * (85 / aiMessages.length) && msgIndex < aiMessages.length - 1) {
            msgIndex++;
            aiSubtitle.textContent = aiMessages[msgIndex];
        }
    }, 300);
}

function hideAILightbox() {
    clearInterval(aiProgressInterval);
    // Snap to 100%
    aiProgressBar.style.width = '100%';
    aiSubtitle.textContent = 'Done! Here\u2019s your content.';

    setTimeout(() => {
        aiLightbox.classList.remove('visible');
        document.body.style.overflow = '';
    }, 600);
}

async function generateWeek() {
    const btn = document.getElementById('btn-generate-week');
    setLoading(btn, true, 'Generating week...');
    showAILightbox(
        'AI is generating your content',
        'Crafting a full week of posts for <?= addslashes($companyName) ?>'
    );

    try {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken());

        const res = await fetch(BASE + '/generator/week', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Generation failed');

        hideAILightbox();
        // Small delay so the user sees the 100% state
        setTimeout(() => {
            renderResults(data.posts || []);
            showToast('Week of content generated!', 'success');
        }, 800);
    } catch (err) {
        hideAILightbox();
        showToast(err.message, 'error');
    } finally {
        setLoading(btn, false);
    }
}

async function generateSingle(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-generate-single');
    setLoading(btn, true, 'Generating...');
    showAILightbox(
        'AI is crafting your post',
        'Building a tailored post for <?= addslashes($companyName) ?>'
    );

    try {
        const formData = new FormData(document.getElementById('single-post-form'));
        formData.append('csrf_token', csrfToken());

        const res = await fetch(BASE + '/generator/single', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Generation failed');

        hideAILightbox();
        setTimeout(() => {
            renderResults(data.post ? [data.post] : [], true);
            showToast('Post generated!', 'success');
        }, 800);
    } catch (err) {
        hideAILightbox();
        showToast(err.message, 'error');
    } finally {
        setLoading(btn, false);
    }
}

function renderResults(posts, append = false) {
    const container = document.getElementById('generator-results');
    if (!append) container.innerHTML = '';

    if (!posts.length && !append) {
        container.innerHTML = `
            <div class="card">
                <div class="empty-state">
                    <i class="fas fa-wand-magic-sparkles"></i>
                    <p>No content was generated. Please try again.</p>
                </div>
            </div>`;
        return;
    }

    // Get or create the grid wrapper
    let grid = container.querySelector('.results-grid');
    if (!grid || !append) {
        container.innerHTML = '';
        grid = document.createElement('div');
        grid.className = 'results-grid';
        container.appendChild(grid);
    }

    posts.forEach((post, idx) => {
        const uid = Date.now() + '_' + idx;
        const typeLabel = (post.post_type || 'educational').replace(/_/g, ' ');
        const imageHtml = post.image_url
            ? `<img src="${escHtml(post.image_url)}" alt="Post image">`
            : `<div class="placeholder-icon"><i class="fas fa-image"></i><span>Image will be generated</span></div>`;

        const card = document.createElement('div');
        card.className = 'result-card card';
        card.innerHTML = `
            <div class="post-card">
                <div class="post-card-image-wrap" id="img-wrap-${uid}">
                    ${imageHtml}
                </div>
                <div class="post-card-body">
                    <input type="text" class="editable-title" id="title-${uid}" value="${escAttr(post.title || 'Untitled Post')}">
                    <textarea class="editable-content" id="content-${uid}" rows="3">${escHtml(post.content || '')}</textarea>
                    <div class="post-card-meta mt-1">
                        <span class="badge badge-draft" style="text-transform:capitalize">${escHtml(typeLabel)}</span>
                        ${post.topic ? `<span class="text-muted text-small">${escHtml(post.topic)}</span>` : ''}
                    </div>
                </div>
                <div class="post-card-actions">
                    <button class="btn btn-ghost btn-sm" onclick="regenerateText('${uid}')">
                        <i class="fas fa-sync-alt"></i> Regen Text
                    </button>
                    <button class="btn btn-ghost btn-sm" onclick="regenerateImage('${uid}')">
                        <i class="fas fa-image"></i> Regen Image
                    </button>
                    <button class="btn btn-primary btn-sm" style="margin-left:auto" onclick="saveDraft('${uid}', '${escAttr(post.post_type || 'educational')}', '${escAttr(post.topic || '')}', '${escAttr(post.keywords || '')}', '${escAttr(post.angle || '')}')">
                        <i class="fas fa-save"></i> Save Draft
                    </button>
                </div>
            </div>`;

        card.dataset.imageUrl = post.image_url || '';
        card.dataset.postType = post.post_type || 'educational';
        card.dataset.topic = post.topic || '';
        card.dataset.keywords = post.keywords || '';
        card.dataset.angle = post.angle || '';
        card.id = 'card-' + uid;

        grid.appendChild(card);
    });

    updateResultsCount();
}

function updateResultsCount() {
    const count = document.querySelectorAll('.result-card').length;
    const el = document.getElementById('results-count');
    el.textContent = count ? count + ' post' + (count !== 1 ? 's' : '') + ' generated' : '';
}

async function regenerateText(uid) {
    const contentEl = document.getElementById('content-' + uid);
    const card = document.getElementById('card-' + uid);
    const btn = event.currentTarget;
    setLoading(btn, true, 'Rewriting...');

    try {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken());
        formData.append('content', contentEl.value);
        formData.append('instructions', 'Rewrite this with a fresh angle');

        const res = await fetch(BASE + '/generator/regenerate-text', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Regeneration failed');

        contentEl.value = data.content || contentEl.value;
        showToast('Text regenerated!', 'success');
    } catch (err) {
        showToast(err.message, 'error');
    } finally {
        setLoading(btn, false);
    }
}

async function regenerateImage(uid) {
    const contentEl = document.getElementById('content-' + uid);
    const imgWrap = document.getElementById('img-wrap-' + uid);
    const card = document.getElementById('card-' + uid);
    const btn = event.currentTarget;
    setLoading(btn, true, 'Generating...');

    imgWrap.innerHTML += '<div class="img-loading-overlay"><i class="fas fa-spinner fa-spin" style="font-size:24px;color:var(--primary)"></i></div>';

    try {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken());
        formData.append('prompt', contentEl.value);

        const res = await fetch(BASE + '/generator/regenerate-image', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Image generation failed');

        if (data.image_url) {
            imgWrap.innerHTML = `<img src="${escHtml(data.image_url)}" alt="Post image">`;
            card.dataset.imageUrl = data.image_url;
        }
        showToast('Image regenerated!', 'success');
    } catch (err) {
        showToast(err.message, 'error');
        const overlay = imgWrap.querySelector('.img-loading-overlay');
        if (overlay) overlay.remove();
    } finally {
        setLoading(btn, false);
    }
}

async function saveDraft(uid, postType, topic, keywords, angle) {
    const titleEl = document.getElementById('title-' + uid);
    const contentEl = document.getElementById('content-' + uid);
    const card = document.getElementById('card-' + uid);
    const btn = event.currentTarget;
    setLoading(btn, true, 'Saving...');

    try {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken());
        formData.append('title', titleEl.value);
        formData.append('content', contentEl.value);
        formData.append('image_url', card.dataset.imageUrl || '');
        formData.append('post_type', card.dataset.postType || postType);
        formData.append('platform', 'facebook');
        formData.append('platforms', JSON.stringify(['facebook']));
        formData.append('status', 'draft');
        formData.append('topic', card.dataset.topic || topic);
        formData.append('keywords', card.dataset.keywords || keywords);
        formData.append('angle', card.dataset.angle || angle);

        const res = await fetch(BASE + '/posts/save', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Save failed');

        showToast('Saved as draft!', 'success');
        btn.innerHTML = '<i class="fas fa-check"></i> Saved';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-primary');
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-save"></i> Save Draft';
            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
        }, 2000);
    } catch (err) {
        showToast(err.message, 'error');
    } finally {
        setLoading(btn, false);
    }
}

function escHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function escAttr(str) {
    return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/'/g,'&#39;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>
