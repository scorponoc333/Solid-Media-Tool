<?php
$tourRole = $_SESSION['role'] ?? 'admin';
$brandingService = new BrandingService();
$tourBrand = $brandingService->get($GLOBALS['client_id']);
$tourPrimaryColor = $tourBrand['primary_color'] ?? '#6366f1';
$tourCompany = htmlspecialchars($tourBrand['company_name'] ?? 'SolidTech');
$faviconUrl = BASE_URL . '/favicon-48.png';

// Steps: target (CSS selector), title, text, url (page to navigate to), group (accordion 1/2/3)
$adminSteps = [
    ['target' => '.nav-item[href*="/dashboard"]', 'title' => 'Dashboard', 'text' => 'Your command center. View real-time stats, weekly trends, quick actions, and recent posts at a glance.', 'url' => '/dashboard', 'group' => 1, 'position' => 'right'],
    ['target' => '.nav-item[href*="/generator"]', 'title' => 'Content Generator', 'text' => 'Plan and generate a full week of AI-powered content or create single posts. The AI uses your themes, brand voice, and content memory.', 'url' => '/generator', 'group' => 1, 'position' => 'right'],
    ['target' => '.nav-item[href*="/posts"]', 'title' => 'Posts Manager', 'text' => 'Manage all your posts in table or kanban view. Filter by platform, status, or search. Edit, schedule, publish, or delete.', 'url' => '/posts', 'group' => 1, 'position' => 'right'],
    ['target' => '.nav-item[href*="/calendar"]', 'title' => 'Content Calendar', 'text' => 'Visual monthly calendar showing all scheduled posts. Color-coded by status. Hover for previews, click for details.', 'url' => '/calendar', 'group' => 1, 'position' => 'right'],
    ['target' => '.nav-item[href*="/reporting"]', 'title' => 'Reports & Analytics', 'text' => 'Track performance with interactive stat cards, failed post management, topic distribution, and platform breakdown. Export data as CSV.', 'url' => '/reporting', 'group' => 1, 'position' => 'right'],
    ['target' => '.nav-item[href*="/content-strategy"]', 'title' => 'Content Strategy', 'text' => 'Define content themes with copy instructions and sample posts. Assign themes to days of the week. Run AI critique on your copy.', 'url' => '/content-strategy', 'group' => 2, 'position' => 'right'],
    ['target' => '.nav-item[href*="/art-direction"]', 'title' => 'Art Direction', 'text' => 'Control AI image generation: style, realism, color temperature, mood, watermarks, and quick presets. See a live prompt preview.', 'url' => '/art-direction', 'group' => 2, 'position' => 'right'],
    ['target' => '.nav-item[href*="/branding"]', 'title' => 'Branding', 'text' => 'Set your company logo, colors, favicon, login background, and API keys. Everything here applies across the entire platform.', 'url' => '/branding', 'group' => 2, 'position' => 'right'],
    ['target' => '.nav-item[href*="/users"]', 'title' => 'User Management', 'text' => 'Invite team members as Editors or Reviewers. Configure the approval workflow and set minimum approvals needed.', 'url' => '/users', 'group' => 2, 'position' => 'right'],
    ['target' => null, 'title' => 'You\'re all set!', 'text' => 'You have full control over ' . $tourCompany . '\'s social media engine. Head to the Generator to create your first batch of content!', 'url' => null, 'group' => 0, 'position' => 'center'],
];

$editorSteps = [
    ['target' => '.nav-item[href*="/dashboard"]', 'title' => 'Dashboard', 'text' => 'Your home base. See post stats, weekly trends, and jump to common tasks.', 'url' => '/dashboard', 'group' => 1, 'position' => 'right'],
    ['target' => '.nav-item[href*="/generator"]', 'title' => 'Content Generator', 'text' => 'Create a week of AI content or single posts. The AI uses your brand\'s themes and voice automatically.', 'url' => '/generator', 'group' => 1, 'position' => 'right'],
    ['target' => '.nav-item[href*="/posts"]', 'title' => 'Posts Manager', 'text' => 'Browse and edit all posts. Use table or kanban view. Schedule posts or submit for review.', 'url' => '/posts', 'group' => 1, 'position' => 'right'],
    ['target' => '.nav-item[href*="/calendar"]', 'title' => 'Content Calendar', 'text' => 'See your content schedule laid out visually. Spot gaps and plan ahead.', 'url' => '/calendar', 'group' => 1, 'position' => 'right'],
    ['target' => '.nav-item[href*="/reporting"]', 'title' => 'Reports', 'text' => 'Track which posts went out, spot failures, and analyze content patterns.', 'url' => '/reporting', 'group' => 1, 'position' => 'right'],
    ['target' => null, 'title' => 'Ready to create!', 'text' => 'Head to the Generator to start making content for ' . $tourCompany . '. Happy posting!', 'url' => null, 'group' => 0, 'position' => 'center'],
];

$reviewerSteps = [
    ['target' => '.nav-item[href*="/dashboard"]', 'title' => 'Dashboard', 'text' => 'Your overview of the content pipeline. See stats and recent activity.', 'url' => '/dashboard', 'group' => 1, 'position' => 'right'],
    ['target' => '.nav-item[href*="/reviews"]', 'title' => 'Review Queue', 'text' => 'This is your main workspace. Approve posts or request changes before they go live. Track approval progress.', 'url' => '/reviews', 'group' => 1, 'position' => 'right'],
    ['target' => '.nav-item[href*="/calendar"]', 'title' => 'Content Calendar', 'text' => 'See what\'s scheduled and when posts are going out across all platforms.', 'url' => '/calendar', 'group' => 1, 'position' => 'right'],
    ['target' => null, 'title' => 'Ready to review!', 'text' => 'Check the Review Queue for posts awaiting your approval. The team is counting on you!', 'url' => null, 'group' => 0, 'position' => 'center'],
];

$tourSteps = match ($tourRole) {
    'admin' => $adminSteps,
    'editor' => $editorSteps,
    'reviewer' => $reviewerSteps,
    default => $editorSteps,
};
?>

<style>
/* ═══ Tour Overlay & Spotlight ═══ */
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
    box-shadow: 0 0 0 9999px rgba(0,0,0,0.55);
    border-radius: 12px;
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    pointer-events: none;
}

/* ═══ Page Transition Overlay ═══ */
.tour-transition {
    position: fixed;
    inset: 0;
    z-index: 100001;
    background: linear-gradient(165deg, <?= htmlspecialchars($tourPrimaryColor) ?> 0%, color-mix(in srgb, <?= htmlspecialchars($tourPrimaryColor) ?> 35%, #0a0a0a) 55%, #0a0a0a 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.35s ease;
}
.tour-transition.active {
    opacity: 1;
    pointer-events: all;
    pointer-events: all;
}

/* Transition inner elements */
.tour-tr-ring {
    width: 52px; height: 52px;
    border: 2.5px solid rgba(255,255,255,0.15);
    border-top-color: rgba(255,255,255,0.7);
    border-radius: 50%;
    animation: tourTrSpin 0.8s linear infinite;
    margin-bottom: 16px;
}
@keyframes tourTrSpin { to { transform: rotate(360deg); } }

.tour-tr-text {
    font-size: 14px;
    font-weight: 600;
    color: rgba(255,255,255,0.6);
    letter-spacing: 0.03em;
}

/* Ambient particles in transition */
.tour-tr-particles {
    position: absolute;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
}
.tour-tr-particles span {
    position: absolute;
    width: 3px; height: 3px;
    border-radius: 50%;
    background: rgba(255,255,255,0.4);
    opacity: 0;
    animation: tourTrFloat 2.5s ease-in-out infinite;
}
@keyframes tourTrFloat {
    0% { bottom: -5px; opacity: 0; }
    20% { opacity: 0.5; }
    80% { opacity: 0.15; }
    100% { bottom: 105%; opacity: 0; }
}

/* ═══ Tooltip ═══ */
.tour-tooltip {
    position: fixed;
    z-index: 100000;
    background: var(--bg-card, #fff);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 18px;
    padding: 24px 24px 20px 80px;
    max-width: 380px;
    box-shadow: 0 16px 48px rgba(0,0,0,0.25), 0 0 0 1px rgba(255,255,255,0.05);
    opacity: 0;
    transform: translateY(10px) scale(0.97);
    transition: opacity 0.4s ease, transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.tour-tooltip.visible {
    opacity: 1;
    transform: translateY(0) scale(1);
}

.tour-avatar {
    position: absolute;
    left: 18px;
    top: 22px;
    width: 46px;
    height: 46px;
    border-radius: 50%;
    background: <?= htmlspecialchars($tourPrimaryColor) ?>;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
}
.tour-avatar img {
    width: 28px; height: 28px;
    object-fit: contain;
    filter: brightness(0) invert(1);
}
.tour-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text, #1a1a2e);
    margin-bottom: 6px;
}
.tour-text {
    font-size: 13px;
    color: var(--text-secondary, #64748b);
    line-height: 1.65;
    margin-bottom: 18px;
}

/* Progress dots */
.tour-progress {
    display: flex;
    gap: 5px;
    margin-bottom: 14px;
}
.tour-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--border, #e2e8f0);
    transition: all 0.3s ease;
}
.tour-dot.active {
    background: <?= htmlspecialchars($tourPrimaryColor) ?>;
    transform: scale(1.25);
    box-shadow: 0 0 6px <?= htmlspecialchars($tourPrimaryColor) ?>66;
}
.tour-dot.done {
    background: <?= htmlspecialchars($tourPrimaryColor) ?>;
    opacity: 0.5;
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
    padding: 9px 22px;
    border: none;
    border-radius: 8px;
    background: <?= htmlspecialchars($tourPrimaryColor) ?>;
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}
.tour-btn-next:hover { filter: brightness(1.1); transform: translateY(-1px); }
.tour-btn-next::before {
    content: '';
    position: absolute;
    top: 0; left: -100%;
    width: 100%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.25), transparent);
    animation: tourBtnShine 3s ease infinite;
}
@keyframes tourBtnShine {
    0% { left: -100%; }
    30% { left: 100%; }
    100% { left: 100%; }
}
.tour-btn-skip {
    padding: 8px 12px;
    border: none;
    background: transparent;
    color: var(--text-muted, #94a3b8);
    font-size: 12px;
    font-family: inherit;
    cursor: pointer;
    transition: color 0.2s ease;
}
.tour-btn-skip:hover { color: var(--text, #1a1a2e); }

/* Center mode (finale) */
.tour-tooltip.center-mode {
    padding: 36px;
    text-align: center;
    max-width: 420px;
}
.tour-tooltip.center-mode .tour-avatar {
    position: static;
    margin: 0 auto 18px;
    width: 60px; height: 60px;
}
.tour-tooltip.center-mode .tour-avatar img { width: 36px; height: 36px; }
.tour-tooltip.center-mode .tour-title { font-size: 20px; }
.tour-tooltip.center-mode .tour-progress { justify-content: center; }
.tour-tooltip.center-mode .tour-footer { justify-content: center; }
.tour-tooltip.center-mode .tour-step-counter { display: none; }

/* Welcome intro card */
.tour-welcome {
    position: fixed;
    inset: 0;
    z-index: 100002;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(165deg, <?= htmlspecialchars($tourPrimaryColor) ?> 0%, color-mix(in srgb, <?= htmlspecialchars($tourPrimaryColor) ?> 35%, #0a0a0a) 55%, #0a0a0a 100%);
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.4s ease;
}
.tour-welcome.active { opacity: 1; pointer-events: all; }
.tour-welcome-card {
    text-align: center;
    color: #fff;
    max-width: 400px;
    padding: 40px;
    opacity: 0;
    transform: scale(0.9) translateY(20px);
    transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.tour-welcome.active .tour-welcome-card {
    opacity: 1;
    transform: scale(1) translateY(0);
}
.tour-welcome-icon {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: rgba(255,255,255,0.12);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}
.tour-welcome-icon img {
    width: 44px; height: 44px;
    object-fit: contain;
    filter: brightness(0) invert(1);
}
.tour-welcome h2 {
    font-size: 24px;
    font-weight: 800;
    margin-bottom: 8px;
}
.tour-welcome p {
    font-size: 15px;
    color: rgba(255,255,255,0.6);
    line-height: 1.6;
    margin-bottom: 28px;
}
.tour-welcome-btn {
    padding: 12px 32px;
    border: none;
    border-radius: 8px;
    background: #fff;
    color: <?= htmlspecialchars($tourPrimaryColor) ?>;
    font-size: 15px;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}
.tour-welcome-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.3); }
.tour-skip-welcome {
    display: block;
    margin-top: 16px;
    background: none;
    border: none;
    color: rgba(255,255,255,0.4);
    font-size: 13px;
    font-family: inherit;
    cursor: pointer;
}
.tour-skip-welcome:hover { color: rgba(255,255,255,0.7); }
</style>

<!-- Welcome Intro -->
<div class="tour-welcome" id="tourWelcome">
    <div class="tour-welcome-card">
        <div class="tour-welcome-icon">
            <img src="<?= $faviconUrl ?>" alt="">
        </div>
        <h2>Welcome to <?= $tourCompany ?> Social</h2>
        <p>Let's take a quick tour of the platform. We'll walk you through the key features so you can hit the ground running.</p>
        <button class="tour-welcome-btn" onclick="beginTour()">Start Tour</button>
        <button class="tour-skip-welcome" onclick="skipTourEntirely()">Skip, I'll explore on my own</button>
    </div>
</div>

<!-- Overlay, Spotlight, Tooltip -->
<div id="tourOverlay" class="tour-overlay" style="display:none"></div>
<div id="tourSpotlight" class="tour-spotlight" style="display:none"></div>
<div id="tourTooltip" class="tour-tooltip" style="display:none">
    <div class="tour-avatar" id="tourAvatar">
        <img src="<?= $faviconUrl ?>" alt="">
    </div>
    <div class="tour-title" id="tourTitle"></div>
    <div class="tour-text" id="tourText"></div>
    <div class="tour-progress" id="tourProgress"></div>
    <div class="tour-footer">
        <span class="tour-step-counter" id="tourCounter"></span>
        <div>
            <button class="tour-btn-skip" id="tourSkipBtn" onclick="tourSkip()">Skip Tour</button>
            <button class="tour-btn-next" id="tourNextBtn" onclick="tourNext()">Next</button>
        </div>
    </div>
</div>

<!-- Page Transition Overlay -->
<div class="tour-transition" id="tourTransition">
    <div class="tour-tr-particles" id="tourTrParticles"></div>
    <div class="tour-tr-ring"></div>
    <div class="tour-tr-text" id="tourTrText">Loading...</div>
</div>

<script>
(function() {
    var BASE = '<?= BASE_URL ?>';
    var STEPS = <?= json_encode(array_values($tourSteps)) ?>;
    var currentStep = 0;
    var isManualRestart = false;

    // Generate particles for transition
    var pCont = document.getElementById('tourTrParticles');
    for (var i = 0; i < 12; i++) {
        var sp = document.createElement('span');
        sp.style.left = (5 + Math.random() * 90) + '%';
        sp.style.animationDuration = (2 + Math.random() * 2) + 's';
        sp.style.animationDelay = '-' + (Math.random() * 3) + 's';
        pCont.appendChild(sp);
    }

    // Check if resuming mid-tour
    var savedStep = localStorage.getItem('tourStep');
    var tourActive = localStorage.getItem('tourActive');

    window.startTour = function() {
        // Show welcome screen
        var welcome = document.getElementById('tourWelcome');
        if (welcome) {
            welcome.classList.add('active');
        }
    };

    window.startTourDirect = function() {
        // Start without welcome (for restart button)
        isManualRestart = true;
        localStorage.setItem('tourActive', '1');
        localStorage.setItem('tourStep', '0');
        currentStep = 0;
        navigateToStep(0);
    };

    window.beginTour = function() {
        // Dismiss welcome, start tour
        var welcome = document.getElementById('tourWelcome');
        if (welcome) {
            welcome.style.transition = 'opacity 0.4s ease';
            welcome.style.opacity = '0';
            setTimeout(function() { welcome.remove(); }, 400);
        }
        localStorage.setItem('tourActive', '1');
        localStorage.setItem('tourStep', '0');
        currentStep = 0;
        // Small delay then show first step
        setTimeout(function() { showStep(0); }, 500);
    };

    window.skipTourEntirely = function() {
        var welcome = document.getElementById('tourWelcome');
        if (welcome) {
            welcome.style.opacity = '0';
            setTimeout(function() { welcome.remove(); }, 400);
        }
        endTour();
    };

    window.tourNext = function() {
        var nextIdx = currentStep + 1;
        if (nextIdx >= STEPS.length) {
            endTour();
            return;
        }
        navigateToStep(nextIdx);
    };

    window.tourSkip = function() {
        endTour();
    };

    function navigateToStep(idx) {
        var step = STEPS[idx];
        currentStep = idx;
        localStorage.setItem('tourStep', idx.toString());

        // Check if we need to change pages
        var currentPath = window.location.pathname;
        var needsNav = step.url && !currentPath.endsWith(step.url) && !currentPath.endsWith(step.url + '/');

        if (needsNav) {
            // Show transition overlay, then navigate
            showTransition(step.title);
            setTimeout(function() {
                window.location.href = BASE + step.url;
            }, 800);
        } else {
            // Same page — just show the step
            showStep(idx);
        }
    }

    function showTransition(title) {
        var tr = document.getElementById('tourTransition');
        document.getElementById('tourTrText').textContent = 'Loading ' + title + '...';
        tr.classList.add('active');

        // Hide current tooltip
        hideTooltip();
    }

    function hideTransition() {
        var tr = document.getElementById('tourTransition');
        tr.style.transition = 'opacity 0.4s ease';
        tr.classList.remove('active');
    }

    function showStep(idx) {
        var step = STEPS[idx];
        var tooltip = document.getElementById('tourTooltip');
        var spotlight = document.getElementById('tourSpotlight');
        var overlay = document.getElementById('tourOverlay');

        // Open the correct accordion group
        if (step.group > 0) {
            openAccordionGroup(step.group);
        }

        // Small delay for accordion animation
        setTimeout(function() {
            // Update tooltip content
            document.getElementById('tourTitle').textContent = step.title;
            document.getElementById('tourText').textContent = step.text;
            document.getElementById('tourCounter').textContent = 'Step ' + (idx + 1) + ' of ' + STEPS.length;

            var isLast = idx === STEPS.length - 1;
            document.getElementById('tourNextBtn').textContent = isLast ? 'Get Started' : 'Next';
            document.getElementById('tourSkipBtn').style.display = isLast ? 'none' : '';

            // Build progress dots
            var progHtml = '';
            for (var i = 0; i < STEPS.length; i++) {
                var cls = 'tour-dot';
                if (i === idx) cls += ' active';
                else if (i < idx) cls += ' done';
                progHtml += '<div class="' + cls + '"></div>';
            }
            document.getElementById('tourProgress').innerHTML = progHtml;

            if (!step.target || step.position === 'center') {
                // Center mode (finale)
                spotlight.style.display = 'none';
                overlay.style.display = '';
                tooltip.className = 'tour-tooltip center-mode';
                tooltip.style.display = '';
                tooltip.style.left = '50%';
                tooltip.style.top = '50%';
                tooltip.style.transform = 'translate(-50%, -50%)';
                requestAnimationFrame(function() { tooltip.classList.add('visible'); });
            } else {
                tooltip.className = 'tour-tooltip';
                var target = document.querySelector(step.target);
                if (!target) {
                    // Skip missing elements
                    currentStep++;
                    localStorage.setItem('tourStep', currentStep.toString());
                    showStep(currentStep);
                    return;
                }

                // Scroll target into view if needed
                target.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

                setTimeout(function() {
                    var rect = target.getBoundingClientRect();
                    var pad = 6;

                    spotlight.style.display = '';
                    spotlight.style.left = (rect.left - pad) + 'px';
                    spotlight.style.top = (rect.top - pad) + 'px';
                    spotlight.style.width = (rect.width + pad * 2) + 'px';
                    spotlight.style.height = (rect.height + pad * 2) + 'px';
                    overlay.style.display = 'none';

                    tooltip.style.display = '';
                    tooltip.style.transform = '';

                    // Position tooltip to the right of the sidebar item
                    var tooltipWidth = 380;
                    var leftPos = rect.right + 18;

                    // Fallback if tooltip would go off-screen
                    if (leftPos + tooltipWidth > window.innerWidth - 20) {
                        leftPos = window.innerWidth - tooltipWidth - 20;
                    }

                    tooltip.style.left = leftPos + 'px';
                    tooltip.style.top = Math.max(12, rect.top - 10) + 'px';

                    requestAnimationFrame(function() { tooltip.classList.add('visible'); });
                }, 150);
            }
        }, step.group > 0 ? 300 : 100);
    }

    function hideTooltip() {
        var tooltip = document.getElementById('tourTooltip');
        var spotlight = document.getElementById('tourSpotlight');
        var overlay = document.getElementById('tourOverlay');
        tooltip.classList.remove('visible');
        tooltip.style.display = 'none';
        spotlight.style.display = 'none';
        overlay.style.display = 'none';
    }

    function openAccordionGroup(groupNum) {
        var groups = document.querySelectorAll('.nav-group');
        groups.forEach(function(g, i) {
            if (i + 1 === groupNum) {
                g.classList.add('open');
            } else {
                g.classList.remove('open');
            }
        });
    }

    function endTour() {
        hideTooltip();
        localStorage.removeItem('tourStep');
        localStorage.removeItem('tourActive');

        // Mark complete on server (only if not a manual restart replay)
        if (!isManualRestart) {
            fetch(BASE + '/auth/complete-tour', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ csrf_token: '<?= $_SESSION['csrf_token'] ?? '' ?>' })
            });
        }

        if (typeof showToast === 'function') {
            showToast('Tour complete! Enjoy the app.', 'success');
        }
    }

    // Resume tour if returning from a page navigation
    if (tourActive === '1' && savedStep !== null) {
        currentStep = parseInt(savedStep, 10);
        // Fade out transition overlay if it was showing
        setTimeout(function() {
            hideTransition();
            setTimeout(function() { showStep(currentStep); }, 450);
        }, 300);
    }
})();
</script>
