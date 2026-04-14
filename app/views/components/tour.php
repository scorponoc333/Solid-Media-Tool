<?php
$tourRole = $_SESSION['role'] ?? 'admin';
$tourSteps = [];

// Define tour steps per role
$adminSteps = [
    ['target' => '.nav-item[href*="/dashboard"]', 'title' => 'Dashboard', 'text' => 'Your command center. See post stats, recent activity, and quick actions at a glance.', 'position' => 'right'],
    ['target' => '.nav-item[href*="/generator"]', 'title' => 'Content Generator', 'text' => 'Plan and generate social media posts with AI. Choose days, themes, and let AI craft the content.', 'position' => 'right'],
    ['target' => '.nav-item[href*="/posts"]', 'title' => 'Posts', 'text' => 'Manage all your posts here. Edit, schedule, publish, or delete content.', 'position' => 'right'],
    ['target' => '.nav-item[href*="/calendar"]', 'title' => 'Calendar', 'text' => 'Visual overview of your content schedule. See what\'s planned for each day.', 'position' => 'right'],
    ['target' => '.nav-item[href*="/content-strategy"]', 'title' => 'Content Strategy', 'text' => 'Define content themes, assign them to days, and provide sample posts for AI to mimic.', 'position' => 'right'],
    ['target' => '.nav-item[href*="/art-direction"]', 'title' => 'Art Direction', 'text' => 'Control how AI-generated images look — realism, contrast, mood, watermarks, and more.', 'position' => 'right'],
    ['target' => '.nav-item[href*="/branding"]', 'title' => 'Branding', 'text' => 'Set your company logo, colors, favicon, and visual identity. Run the Setup Wizard from here.', 'position' => 'right'],
    ['target' => '.nav-item[href*="/users"]', 'title' => 'User Management', 'text' => 'Invite team members, assign roles (Editor or Reviewer), and configure the approval workflow.', 'position' => 'right'],
    ['target' => '.nav-item[href*="/reporting"]', 'title' => 'Reports', 'text' => 'Track performance, see failed posts, and analyze content distribution across platforms.', 'position' => 'right'],
    ['target' => null, 'title' => 'You\'re all set!', 'text' => 'You have full control over your social media engine. Start by generating some content!', 'position' => 'center'],
];

$editorSteps = [
    ['target' => '.nav-item[href*="/dashboard"]', 'title' => 'Dashboard', 'text' => 'Your home base. See post stats and recent activity.', 'position' => 'right'],
    ['target' => '.nav-item[href*="/generator"]', 'title' => 'Content Generator', 'text' => 'Create social media posts with AI. Choose how many, which days, and which themes.', 'position' => 'right'],
    ['target' => '.nav-item[href*="/posts"]', 'title' => 'Posts', 'text' => 'Edit your posts, tweak the copy, regenerate images, and schedule for publishing.', 'position' => 'right'],
    ['target' => '.nav-item[href*="/calendar"]', 'title' => 'Calendar', 'text' => 'See your content schedule at a glance. Click any day to view details.', 'position' => 'right'],
    ['target' => '.nav-item[href*="/reporting"]', 'title' => 'Reports', 'text' => 'Track which posts went out and spot any failures.', 'position' => 'right'],
    ['target' => null, 'title' => 'Ready to create!', 'text' => 'Head to the Generator to start making content. Happy posting!', 'position' => 'center'],
];

$reviewerSteps = [
    ['target' => '.nav-item[href*="/dashboard"]', 'title' => 'Dashboard', 'text' => 'Your overview. See what\'s happening across the content pipeline.', 'position' => 'right'],
    ['target' => '.nav-item[href*="/reviews"]', 'title' => 'Review Queue', 'text' => 'This is where you\'ll spend most of your time. Approve or request changes on posts before they go live.', 'position' => 'right'],
    ['target' => '.nav-item[href*="/calendar"]', 'title' => 'Calendar', 'text' => 'See what\'s scheduled and when posts are going out.', 'position' => 'right'],
    ['target' => null, 'title' => 'Ready to review!', 'text' => 'Check the Review Queue for any posts that need your approval.', 'position' => 'center'],
];

$tourSteps = match ($tourRole) {
    'admin' => $adminSteps,
    'editor' => $editorSteps,
    'reviewer' => $reviewerSteps,
    default => $editorSteps,
};

$brandingService = new BrandingService();
$tourBrand = $brandingService->get($GLOBALS['client_id']);
$tourPrimaryColor = $tourBrand['primary_color'] ?? '#6366f1';
$faviconUrl = BASE_URL . '/favicon-48.png';
?>

<style>
.tour-overlay {
    position: fixed;
    inset: 0;
    z-index: 99998;
    background: rgba(0,0,0,0.6);
    transition: opacity 0.3s ease;
    pointer-events: auto;
}
.tour-spotlight {
    position: fixed;
    z-index: 99999;
    box-shadow: 0 0 0 9999px rgba(0,0,0,0.6);
    border-radius: 12px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    pointer-events: none;
}
.tour-tooltip {
    position: fixed;
    z-index: 100000;
    background: var(--bg-card, #fff);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 16px;
    padding: 20px 20px 20px 72px;
    max-width: 340px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.2);
    animation: tourFadeIn 0.35s ease;
}
@keyframes tourFadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

.tour-avatar {
    position: absolute;
    left: 16px;
    top: 18px;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: <?= $tourPrimaryColor ?>;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.tour-avatar img {
    width: 26px;
    height: 26px;
    object-fit: contain;
    filter: brightness(0) invert(1);
}
.tour-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text, #1a1a2e);
    margin-bottom: 6px;
}
.tour-text {
    font-size: 13px;
    color: var(--text-secondary, #64748b);
    line-height: 1.6;
    margin-bottom: 16px;
}
.tour-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.tour-step-counter {
    font-size: 12px;
    color: var(--text-muted, #94a3b8);
    font-weight: 500;
}
.tour-btn-next {
    padding: 8px 20px;
    border: none;
    border-radius: 8px;
    background: <?= $tourPrimaryColor ?>;
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}
.tour-btn-next:hover { filter: brightness(1.1); transform: translateY(-1px); }
.tour-btn-skip {
    padding: 8px 12px;
    border: none;
    background: transparent;
    color: var(--text-muted, #94a3b8);
    font-size: 12px;
    cursor: pointer;
}
.tour-btn-skip:hover { color: var(--text, #1a1a2e); }

.tour-tooltip.center-mode {
    padding: 32px;
    text-align: center;
    max-width: 380px;
}
.tour-tooltip.center-mode .tour-avatar {
    position: static;
    margin: 0 auto 16px;
    width: 56px;
    height: 56px;
}
.tour-tooltip.center-mode .tour-avatar img { width: 34px; height: 34px; }
.tour-tooltip.center-mode .tour-title { font-size: 18px; }
</style>

<div id="tourOverlay" class="tour-overlay" style="display:none"></div>
<div id="tourSpotlight" class="tour-spotlight" style="display:none"></div>
<div id="tourTooltip" class="tour-tooltip" style="display:none">
    <div class="tour-avatar" id="tourAvatar">
        <img src="<?= $faviconUrl ?>" alt="Guide">
    </div>
    <div class="tour-title" id="tourTitle"></div>
    <div class="tour-text" id="tourText"></div>
    <div class="tour-footer">
        <span class="tour-step-counter" id="tourCounter"></span>
        <div>
            <button class="tour-btn-skip" id="tourSkipBtn" onclick="tourSkip()">Skip Tour</button>
            <button class="tour-btn-next" id="tourNextBtn" onclick="tourNext()">Next</button>
        </div>
    </div>
</div>

<script>
var TOUR_STEPS = <?= json_encode($tourSteps) ?>;
var tourCurrentStep = 0;
var tourActive = false;

function startTour() {
    if (!TOUR_STEPS.length) return;
    tourCurrentStep = 0;
    tourActive = true;
    document.getElementById('tourOverlay').style.display = '';
    showTourStep();
}

function showTourStep() {
    if (tourCurrentStep >= TOUR_STEPS.length) {
        endTour();
        return;
    }

    var step = TOUR_STEPS[tourCurrentStep];
    var tooltip = document.getElementById('tourTooltip');
    var spotlight = document.getElementById('tourSpotlight');
    var overlay = document.getElementById('tourOverlay');

    document.getElementById('tourTitle').textContent = step.title;
    document.getElementById('tourText').textContent = step.text;
    document.getElementById('tourCounter').textContent = (tourCurrentStep + 1) + ' of ' + TOUR_STEPS.length;

    var isLast = tourCurrentStep === TOUR_STEPS.length - 1;
    document.getElementById('tourNextBtn').textContent = isLast ? 'Get Started' : 'Next';
    document.getElementById('tourSkipBtn').style.display = isLast ? 'none' : '';

    if (!step.target || step.position === 'center') {
        // Center mode — no spotlight
        spotlight.style.display = 'none';
        overlay.style.display = '';
        tooltip.classList.add('center-mode');
        tooltip.style.display = '';
        tooltip.style.left = '50%';
        tooltip.style.top = '50%';
        tooltip.style.transform = 'translate(-50%, -50%)';
    } else {
        tooltip.classList.remove('center-mode');
        var target = document.querySelector(step.target);
        if (!target) {
            tourCurrentStep++;
            showTourStep();
            return;
        }

        var rect = target.getBoundingClientRect();
        var pad = 6;

        spotlight.style.display = '';
        spotlight.style.left = (rect.left - pad) + 'px';
        spotlight.style.top = (rect.top - pad) + 'px';
        spotlight.style.width = (rect.width + pad * 2) + 'px';
        spotlight.style.height = (rect.height + pad * 2) + 'px';
        overlay.style.display = 'none'; // spotlight box-shadow acts as overlay

        tooltip.style.display = '';
        tooltip.style.transform = '';

        // Position tooltip
        if (step.position === 'right') {
            tooltip.style.left = (rect.right + 16) + 'px';
            tooltip.style.top = Math.max(8, rect.top - 10) + 'px';
        } else if (step.position === 'bottom') {
            tooltip.style.left = rect.left + 'px';
            tooltip.style.top = (rect.bottom + 12) + 'px';
        } else {
            tooltip.style.left = rect.left + 'px';
            tooltip.style.top = (rect.top - 12) + 'px';
            tooltip.style.transform = 'translateY(-100%)';
        }
    }
}

function tourNext() {
    tourCurrentStep++;
    if (tourCurrentStep >= TOUR_STEPS.length) {
        endTour();
    } else {
        showTourStep();
    }
}

function tourSkip() {
    endTour();
}

function endTour() {
    tourActive = false;
    document.getElementById('tourOverlay').style.display = 'none';
    document.getElementById('tourSpotlight').style.display = 'none';
    document.getElementById('tourTooltip').style.display = 'none';

    // Mark tour complete on server
    fetch('<?= BASE_URL ?>/auth/complete-tour', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ csrf_token: '<?= $_SESSION['csrf_token'] ?? '' ?>' })
    });

    showToast('Tour complete! Enjoy the app.', 'success');
}
</script>
