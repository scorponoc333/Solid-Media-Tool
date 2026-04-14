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
$themes = $themes ?? [];
$schedule = $schedule ?? [];
$missingBranding = $brandingService->isProfileComplete($GLOBALS['client_id']);
?>

<input type="hidden" id="csrf-token" value="<?= htmlspecialchars($csrfToken) ?>">
<script>var MISSING_BRANDING = <?= json_encode($missingBranding) ?>;</script>

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

<!-- Plan Lightbox (multi-step before generation) -->
<style>
/* Plan lightbox entrance */
.plan-modal { transform: scale(0.85) translateY(30px); opacity: 0; transition: transform 0.7s cubic-bezier(0.34,1.56,0.64,1), opacity 0.4s ease; }
.plan-modal.visible { transform: scale(1) translateY(0); opacity: 1; }

/* Step transitions */
.plan-step { display:none; }
.plan-step.active { display:block; animation: stepAssemble 0.6s cubic-bezier(0.23,1,0.32,1) both; }
.plan-step.exiting { display:block; animation: stepDissolve 0.35s ease forwards; pointer-events:none; }
@keyframes stepAssemble {
    0% { opacity:0; transform:perspective(800px) rotateY(-4deg) translateX(40px) scale(0.95); filter:blur(6px); }
    60% { filter:blur(0); }
    100% { opacity:1; transform:perspective(800px) rotateY(0) translateX(0) scale(1); filter:blur(0); }
}
@keyframes stepDissolve {
    0% { opacity:1; transform:perspective(800px) rotateY(0) translateX(0) scale(1); filter:blur(0); }
    100% { opacity:0; transform:perspective(800px) rotateY(4deg) translateX(-40px) scale(0.95); filter:blur(6px); }
}
@keyframes stepAssembleReverse {
    0% { opacity:0; transform:perspective(800px) rotateY(4deg) translateX(-40px) scale(0.95); filter:blur(6px); }
    60% { filter:blur(0); }
    100% { opacity:1; transform:perspective(800px) rotateY(0) translateX(0) scale(1); filter:blur(0); }
}
.plan-step.active-reverse { display:block; animation: stepAssembleReverse 0.6s cubic-bezier(0.23,1,0.32,1) both; }
.plan-step.exiting-forward { display:block; animation: stepDissolve 0.35s ease forwards; pointer-events:none; }

/* Elements within steps cascade in */
.plan-step.active .plan-el, .plan-step.active-reverse .plan-el { animation: planElIn 0.4s cubic-bezier(0.23,1,0.32,1) both; }
.plan-el:nth-child(1) { animation-delay: 0.15s !important; }
.plan-el:nth-child(2) { animation-delay: 0.25s !important; }
.plan-el:nth-child(3) { animation-delay: 0.35s !important; }
.plan-el:nth-child(4) { animation-delay: 0.45s !important; }
@keyframes planElIn { from { opacity:0; transform:translateY(12px); filter:blur(3px); } to { opacity:1; transform:translateY(0); filter:blur(0); } }

/* Step indicator dots */
.plan-dots { display:flex; gap:8px; }
.plan-dot { width:8px; height:8px; border-radius:50%; background:var(--border); transition:all 0.4s cubic-bezier(0.34,1.56,0.64,1); }
.plan-dot.active { background:var(--primary); transform:scale(1.4); box-shadow:0 0 8px rgba(var(--primary-rgb),0.4); }
.plan-dot.done { background:var(--primary); opacity:0.4; }
</style>

<div id="plan-lightbox" class="ai-lightbox">
    <div class="plan-modal" id="planModal" style="background:var(--bg-card);border-radius:24px;max-width:520px;width:92%;max-height:85vh;overflow-y:auto;box-shadow:var(--shadow-xl);padding:28px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <div>
                <h3 style="font-size:18px;font-weight:700;color:var(--text);margin-bottom:4px">Plan Your Content</h3>
                <div class="plan-dots">
                    <div class="plan-dot active" id="planDot1"></div>
                    <div class="plan-dot" id="planDot2"></div>
                    <div class="plan-dot" id="planDot3"></div>
                </div>
            </div>
            <button onclick="closePlanLightbox()" style="width:32px;height:32px;border-radius:8px;border:none;background:var(--bg-input);color:var(--text-muted);cursor:pointer;transition:all 0.2s"><i class="fas fa-times"></i></button>
        </div>

        <!-- Step 1: How many -->
        <div id="planStep1" class="plan-step active">
            <div class="plan-el" style="font-size:14px;color:var(--text-secondary);margin-bottom:16px">How many posts do you want to generate?</div>
            <div class="plan-el" style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
                <button type="button" class="btn btn-ghost btn-sm" onclick="adjustCount(-1)"><i class="fas fa-minus"></i></button>
                <input type="number" id="planCount" min="1" max="7" value="3" style="width:60px;text-align:center;font-size:20px;font-weight:700;border:1px solid var(--border);border-radius:var(--radius-sm);background:var(--bg-input);color:var(--text);padding:8px">
                <button type="button" class="btn btn-ghost btn-sm" onclick="adjustCount(1)"><i class="fas fa-plus"></i></button>
            </div>
            <div class="plan-el" style="display:flex;justify-content:flex-end">
                <button class="btn btn-primary btn-shine" onclick="planNext(1)">Next <i class="fas fa-arrow-right" style="margin-left:4px"></i></button>
            </div>
        </div>

        <!-- Step 2: Which days -->
        <div id="planStep2" class="plan-step">
            <div class="plan-el" style="font-size:14px;color:var(--text-secondary);margin-bottom:16px">Select <span id="planDayCountLabel">3</span> days to publish:</div>
            <div class="plan-el" id="planDaysGrid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:20px">
                <?php
                $dayAbbr = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
                for ($d = 1; $d <= 6; $d++): ?>
                <label style="display:flex;align-items:center;gap:6px;padding:10px;border:1px solid var(--border);border-radius:var(--radius-sm);cursor:pointer;font-size:13px;font-weight:600;color:var(--text);transition:all 0.2s">
                    <input type="checkbox" class="plan-day-cb" value="<?= $d ?>" data-day="<?= $dayAbbr[$d] ?>day" style="accent-color:var(--primary)"> <?= $dayAbbr[$d] ?>
                </label>
                <?php endfor; ?>
                <label style="display:flex;align-items:center;gap:6px;padding:10px;border:1px solid var(--border);border-radius:var(--radius-sm);cursor:pointer;font-size:13px;font-weight:600;color:var(--text);transition:all 0.2s">
                    <input type="checkbox" class="plan-day-cb" value="0" data-day="Sunday" style="accent-color:var(--primary)"> Sun
                </label>
            </div>
            <div class="plan-el" style="display:flex;justify-content:space-between">
                <button class="btn btn-ghost" onclick="planBack(2)"><i class="fas fa-arrow-left" style="margin-right:4px"></i> Back</button>
                <button class="btn btn-primary btn-shine" onclick="planNext(2)">Next <i class="fas fa-arrow-right" style="margin-left:4px"></i></button>
            </div>
        </div>

        <!-- Step 3: Which themes -->
        <div id="planStep3" class="plan-step">
            <div class="plan-el" style="font-size:14px;color:var(--text-secondary);margin-bottom:16px">Assign a theme to each day:</div>
            <div class="plan-el" id="planThemeAssign" style="margin-bottom:20px"></div>
            <div class="plan-el" style="display:flex;justify-content:space-between">
                <button class="btn btn-ghost" onclick="planBack(3)"><i class="fas fa-arrow-left" style="margin-right:4px"></i> Back</button>
                <button class="btn btn-primary btn-shine" onclick="startGeneration()" id="planGenerateBtn"><i class="fas fa-bolt" style="margin-right:4px"></i> Generate</button>
            </div>
        </div>
    </div>
</div>

<script>
var GEN_THEMES = <?= json_encode($themes) ?>;
var GEN_SCHEDULE = <?= json_encode($schedule) ?>;
var DAY_NAMES = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

function checkBrandingComplete() {
    if (MISSING_BRANDING && MISSING_BRANDING.length > 0) {
        var list = MISSING_BRANDING.join(', ');
        alertModal(
            'Complete Your Profile First',
            'Before generating posts, please set up the following in your branding settings: <strong>' + list + '</strong>.<br><br>This ensures every post includes your company contact information.<br><br><a href="' + BASE + '/branding" class="btn btn-primary btn-sm" style="margin-top:8px"><i class="fas fa-palette" style="margin-right:4px"></i> Go to Branding</a>',
            'warning'
        );
        return false;
    }
    return true;
}

var planCurrentStep = 1;

function openPlanLightbox() {
    if (!checkBrandingComplete()) return;

    planCurrentStep = 1;

    // Reset all steps
    document.querySelectorAll('.plan-step').forEach(function(s) {
        s.className = 'plan-step';
    });
    document.getElementById('planStep1').className = 'plan-step active';
    updatePlanDots(1);

    // Pre-check scheduled days
    var scheduledDays = Object.keys(GEN_SCHEDULE).map(Number);
    document.querySelectorAll('.plan-day-cb').forEach(function(cb) {
        cb.checked = scheduledDays.indexOf(parseInt(cb.value)) !== -1;
    });

    document.getElementById('plan-lightbox').classList.add('visible');
    document.body.style.overflow = 'hidden';

    // Animate modal in
    setTimeout(function() {
        document.getElementById('planModal').classList.add('visible');
    }, 50);
}

function closePlanLightbox() {
    var modal = document.getElementById('planModal');
    modal.classList.remove('visible');
    setTimeout(function() {
        document.getElementById('plan-lightbox').classList.remove('visible');
        document.body.style.overflow = '';
    }, 400);
}

function transitionPlanStep(fromStep, toStep, direction) {
    var fromEl = document.getElementById('planStep' + fromStep);
    var toEl = document.getElementById('planStep' + toStep);
    var isForward = direction === 'forward';

    // Dissolve out current step
    fromEl.className = 'plan-step ' + (isForward ? 'exiting-forward' : 'exiting');

    setTimeout(function() {
        fromEl.className = 'plan-step';
        // Assemble in next step
        toEl.className = 'plan-step ' + (isForward ? 'active' : 'active-reverse');
        planCurrentStep = toStep;
        updatePlanDots(toStep);
    }, 350);
}

function updatePlanDots(step) {
    for (var i = 1; i <= 3; i++) {
        var dot = document.getElementById('planDot' + i);
        dot.className = 'plan-dot' + (i === step ? ' active' : (i < step ? ' done' : ''));
    }
}

function adjustCount(delta) {
    var el = document.getElementById('planCount');
    var val = parseInt(el.value) + delta;
    if (val < 1) val = 1;
    if (val > 7) val = 7;
    el.value = val;
}

function planNext(step) {
    if (step === 1) {
        var count = parseInt(document.getElementById('planCount').value);
        document.getElementById('planDayCountLabel').textContent = count;
        transitionPlanStep(1, 2, 'forward');
    } else if (step === 2) {
        var checked = document.querySelectorAll('.plan-day-cb:checked');
        var count = parseInt(document.getElementById('planCount').value);
        if (checked.length !== count) {
            showToast('Select exactly ' + count + ' day(s)', 'warning');
            return;
        }
        buildThemeAssignment();
        transitionPlanStep(2, 3, 'forward');
    }
}

function planBack(step) {
    if (step === 2) {
        transitionPlanStep(2, 1, 'back');
    } else if (step === 3) {
        transitionPlanStep(3, 2, 'back');
    }
}

function buildThemeAssignment() {
    var container = document.getElementById('planThemeAssign');
    container.innerHTML = '';
    var checked = document.querySelectorAll('.plan-day-cb:checked');
    checked.forEach(function(cb) {
        var dayNum = parseInt(cb.value);
        var dayName = DAY_NAMES[dayNum];
        var schedTheme = GEN_SCHEDULE[dayNum] ? GEN_SCHEDULE[dayNum].theme_id : '';

        var row = document.createElement('div');
        row.style.cssText = 'display:flex;align-items:center;gap:12px;margin-bottom:10px';
        row.innerHTML = '<span style="min-width:80px;font-size:13px;font-weight:600;color:var(--text)">' + dayName + '</span>'
            + '<select class="form-input plan-theme-select" data-day="' + dayNum + '" style="flex:1;padding:8px 12px;font-size:13px">'
            + '<option value="">General (no theme)</option>'
            + GEN_THEMES.map(function(t) {
                return '<option value="' + t.id + '"' + (t.id == schedTheme ? ' selected' : '') + '>' + escHtml(t.name) + '</option>';
              }).join('')
            + '</select>';
        container.appendChild(row);
    });
}

function startGeneration() {
    var days = [];
    var themeIds = [];
    document.querySelectorAll('.plan-theme-select').forEach(function(sel) {
        days.push(DAY_NAMES[parseInt(sel.dataset.day)]);
        themeIds.push(sel.value || null);
    });

    closePlanLightbox();
    generateWeekWithPlan(days, themeIds);
}

async function generateWeekWithPlan(days, themeIds) {
    var btn = document.getElementById('btn-generate-week');
    setLoading(btn, true, 'Generating...');
    showAILightbox(
        'AI is generating your content',
        'Crafting ' + days.length + ' posts for <?= addslashes($companyName) ?>'
    );

    try {
        var res = await fetch(BASE + '/generator/week', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                csrf_token: csrfToken(),
                days: days,
                theme_ids: themeIds
            })
        });
        var data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Generation failed');

        hideAILightbox();
        setTimeout(function() {
            renderResults(data.posts || []);
            showToast('Content generated!', 'success');
        }, 800);
    } catch (err) {
        hideAILightbox();
        showToast(err.message, 'error');
    } finally {
        setLoading(btn, false);
    }
}
</script>

<style>
@keyframes wizSlideUp { from { transform: translateY(40px) scale(0.95); opacity: 0; } to { transform: translateY(0) scale(1); opacity: 1; } }
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
        color-mix(in srgb, <?= htmlspecialchars($primaryColor) ?> 50%, #0a0a0a) 100%
    );
    box-shadow:
        0 0 0 1px rgba(0,0,0,0.1),
        0 24px 80px rgba(0,0,0,0.3),
        0 0 120px -20px <?= htmlspecialchars($primaryColor) ?>88;
    max-width: 440px;
    width: 90%;
    overflow: hidden;
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
    <!-- Plan & Generate Week -->
    <div class="card" style="display:flex;flex-direction:column">
        <div class="card-header">
            <div>
                <h3 class="card-title"><i class="fas fa-calendar-week" style="color:var(--primary);margin-right:8px"></i>Plan & Generate</h3>
                <p class="card-subtitle">Choose days, themes, and generate</p>
            </div>
        </div>
        <p style="color:var(--text-secondary);font-size:14px;line-height:1.7;margin-bottom:20px">
            <?= $nameGreet ?> can plan and generate posts with themes from your content strategy. Pick how many, which days, and which themes — AI does the rest.
        </p>
        <div style="margin-top:auto">
            <button class="btn btn-primary w-full btn-cta-glow" id="btn-generate-week" onclick="openPlanLightbox()">
                <i class="fas fa-magic"></i> Plan & Generate
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
                <button type="submit" class="btn btn-primary w-full btn-cta-glow" id="btn-generate-single">
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

// Legacy generateWeek now opens the plan lightbox
function generateWeek() {
    openPlanLightbox();
}

async function generateSingle(e) {
    e.preventDefault();
    if (!checkBrandingComplete()) return;
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
                    <textarea class="editable-content" id="content-${uid}" rows="8" style="white-space:pre-wrap;line-height:1.7">${escHtml(post.content || '')}</textarea>
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
                        <i class="fas fa-image"></i> Generate Image
                    </button>
                    <button class="btn btn-primary btn-sm" style="margin-left:auto" onclick="saveDraft('${uid}', '${escAttr(post.post_type || 'educational')}', '${escAttr(post.topic || '')}', '${escAttr(post.keywords || '')}', '${escAttr(post.angle || '')}')">
                        <i class="fas fa-save"></i> Save Draft
                    </button>
                </div>
            </div>`;

        card.dataset.imageUrl = post.image_url || '';
        card.dataset.imagePrompt = post.image_prompt || '';
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

    var gPrimary = '<?= $primaryColor ?>';
    var overlayId = 'imgOverlay_' + uid;
    imgWrap.innerHTML += '<div class="img-loading-overlay" id="' + overlayId + '" style="background:linear-gradient(165deg,'+gPrimary+' 0%,#0a0a0a 70%,#000 100%);flex-direction:column;gap:10px">'
        // Misty ambient glow
        + '<div style="position:absolute;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,0.08),transparent 65%);animation:atomMist 4s ease-in-out infinite"></div>'
        // Atom orbit 1 — wide ellipse
        + '<div style="position:absolute;width:280px;height:120px;border:1px solid rgba(255,255,255,0.08);border-radius:50%;animation:aiSpin 6s linear infinite;transform-origin:center">'
        + '<div style="position:absolute;width:5px;height:5px;background:#fff;border-radius:50%;top:-2px;left:calc(50% - 2px);box-shadow:0 0 10px rgba(255,255,255,0.6);opacity:0.75"></div></div>'
        // Atom orbit 2 — tilted
        + '<div style="position:absolute;width:260px;height:110px;border:1px solid rgba(255,255,255,0.06);border-radius:50%;animation:aiSpin 5s linear infinite reverse;transform:rotate(60deg);transform-origin:center">'
        + '<div style="position:absolute;width:4px;height:4px;background:rgba(255,255,255,0.9);border-radius:50%;bottom:-2px;left:calc(50% - 2px);box-shadow:0 0 8px rgba(255,255,255,0.5);opacity:0.65"></div></div>'
        // Atom orbit 3 — steep
        + '<div style="position:absolute;width:100px;height:240px;border:1px solid rgba(255,255,255,0.05);border-radius:50%;animation:aiSpin 7s linear infinite;transform:rotate(30deg);transform-origin:center">'
        + '<div style="position:absolute;width:4px;height:4px;background:rgba(255,255,255,0.85);border-radius:50%;top:-2px;left:calc(50% - 2px);box-shadow:0 0 8px rgba(255,255,255,0.4);opacity:0.55"></div></div>'
        // Core rings
        + '<div style="width:50px;height:50px;position:relative;display:flex;align-items:center;justify-content:center;z-index:2">'
        + '<div style="position:absolute;inset:0;border:1px solid rgba(255,255,255,0.08);border-radius:50%;animation:aiSpin 8s linear infinite"></div>'
        + '<div style="position:absolute;inset:6px;border:2px solid rgba(255,255,255,0.06);border-top-color:rgba(255,255,255,0.5);border-radius:50%;animation:aiSpin 2.5s linear infinite;box-shadow:0 0 12px rgba(255,255,255,0.06)"></div>'
        + '<div style="width:5px;height:5px;border-radius:50%;background:#fff;box-shadow:0 0 8px rgba(255,255,255,0.6)"></div>'
        + '</div>'
        // Badge + text
        + '<div style="padding:4px 12px;border-radius:12px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.1);z-index:2">'
        + '<span style="font-size:9px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(255,255,255,0.6)">AI Image Generation</span>'
        + '</div>'
        + '<div style="font-size:11px;color:rgba(255,255,255,0.6);margin-top:4px;text-align:center;transition:opacity 0.2s ease;z-index:2" id="imgStatus_'+uid+'">Connecting to AI server...</div>'
        + '<div style="font-size:10px;color:rgba(255,255,255,0.3);margin-top:2px;z-index:2">This may take 2\u20133 minutes</div>'
        + '<style>'
        + '@keyframes aiSpin{to{transform:rotate(360deg)}}'
        + '@keyframes atomMist{0%,100%{opacity:0.4;transform:scale(1)}50%{opacity:0.7;transform:scale(1.15)}}'
        + '</style>'
        + '</div>';

    // Rotating status messages
    var imgMsgs = ['Connecting to AI server...','Queuing image task...','AI is composing the scene...','Rendering visual elements...','Applying lighting & contrast...','Building composition...','Processing art direction...','Refining image details...','Enhancing resolution...','Applying brand watermark...','Almost ready...'];
    var imgMsgIdx = 0;
    var imgMsgInterval = setInterval(function() {
        imgMsgIdx = (imgMsgIdx + 1) % imgMsgs.length;
        var el = document.getElementById('imgStatus_'+uid);
        if (el) { el.style.opacity='0'; setTimeout(function(){ el.textContent=imgMsgs[imgMsgIdx]; el.style.opacity='1'; },200); }
    }, 4000);

    try {
        const prompt = card.dataset.imagePrompt || contentEl.value;

        const formData = new FormData();
        formData.append('csrf_token', csrfToken());
        formData.append('prompt', prompt);

        const res = await fetch(BASE + '/generator/regenerate-image', {
            method: 'POST',
            body: formData
        });

        clearInterval(imgMsgInterval);

        const text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            throw new Error('The image generation request timed out.');
        }
        if (!res.ok) throw new Error(data.error || 'Image generation failed');

        if (data.image_url) {
            if (data.image_url.includes('placehold.co') || data.image_url.includes('Image+')) {
                throw new Error('The AI could not generate this image. The request may have been too complex or the service is busy.');
            }
            imgWrap.innerHTML = `<img src="${escHtml(data.image_url)}" alt="Post image">`;
            card.dataset.imageUrl = data.image_url;
            showToast('Image generated!', 'success');
        } else {
            throw new Error('No image was returned from the AI service.');
        }
    } catch (err) {
        clearInterval(imgMsgInterval);
        // Show error lightbox instead of just a toast
        var overlay = imgWrap.querySelector('.img-loading-overlay');
        if (overlay) overlay.remove();
        showImageErrorModal(err.message, function() { regenerateImage(uid); });
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

        // Show lock animation on the card
        showSaveLockAnimation(card);

        btn.innerHTML = '<i class="fas fa-check"></i> Saved';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-primary');
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-save"></i> Save Draft';
            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
        }, 2500);
    } catch (err) {
        showToast(err.message, 'error');
    } finally {
        setLoading(btn, false);
    }
}

function showSaveLockAnimation(card) {
    var primary = '<?= $primaryColor ?>';

    // Create overlay for the image area
    var imgWrap = card.querySelector('.post-card-image-wrap');
    var bodyArea = card.querySelector('.post-card-body');

    if (imgWrap) {
        var imgOverlay = document.createElement('div');
        imgOverlay.className = 'save-lock-overlay';
        imgOverlay.style.background = 'linear-gradient(165deg,' + primary + 'dd 0%,rgba(10,10,10,0.85) 100%)';
        imgOverlay.innerHTML = '<div class="save-lock-icon"><i class="fas fa-lock"></i></div>'
            + '<div class="save-lock-text">Secured</div>'
            + '<div style="position:absolute;inset:0;overflow:hidden;pointer-events:none">'
            + Array.from({length:6}, function(_,i) {
                return '<div style="position:absolute;left:'+(10+i*16)+'%;width:3px;height:3px;border-radius:50%;background:rgba(255,255,255,0.5);animation:ptFloat '+(2.5+i%2)+'s ease-in-out infinite;animation-delay:-'+(i*0.3).toFixed(1)+'s;opacity:0"></div>';
              }).join('')
            + '</div>';
        imgWrap.style.position = 'relative';
        imgWrap.appendChild(imgOverlay);
    }

    // Create overlay for the body area (staggered 150ms later)
    if (bodyArea) {
        setTimeout(function() {
            var bodyOverlay = document.createElement('div');
            bodyOverlay.className = 'save-lock-overlay';
            bodyOverlay.style.background = 'linear-gradient(165deg,' + primary + 'cc 0%,rgba(10,10,10,0.8) 100%)';
            bodyOverlay.innerHTML = '<div class="save-lock-icon" style="font-size:24px"><i class="fas fa-check-circle"></i></div>'
                + '<div class="save-lock-text">Draft Saved</div>';
            bodyArea.style.position = 'relative';
            bodyArea.appendChild(bodyOverlay);
        }, 150);
    }

    // Remove after 1.5s
    setTimeout(function() {
        if (imgWrap) {
            var o = imgWrap.querySelector('.save-lock-overlay');
            if (o) { o.classList.add('exiting'); setTimeout(function(){ o.remove(); }, 400); }
        }
        if (bodyArea) {
            setTimeout(function() {
                var o = bodyArea.querySelector('.save-lock-overlay');
                if (o) { o.classList.add('exiting'); setTimeout(function(){ o.remove(); }, 400); }
            }, 100);
        }
    }, 1500);
}

function escHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function escAttr(str) {
    return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/'/g,'&#39;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function showImageErrorModal(errorMsg, retryCallback) {
    var overlay = document.createElement('div');
    overlay.style.cssText = 'position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,0.6);backdrop-filter:blur(6px);display:flex;align-items:center;justify-content:center;animation:fadeIn 0.3s ease';
    overlay.innerHTML = '<div style="background:var(--bg-card);border-radius:20px;max-width:440px;width:90%;padding:32px;box-shadow:0 24px 80px rgba(0,0,0,0.3);animation:slideUp 0.4s cubic-bezier(0.34,1.56,0.64,1)">'
        + '<div style="text-align:center;margin-bottom:20px">'
        + '<div style="width:52px;height:52px;border-radius:50%;background:rgba(239,68,68,0.1);display:flex;align-items:center;justify-content:center;margin:0 auto 14px"><i class="fas fa-exclamation-triangle" style="color:var(--danger);font-size:22px"></i></div>'
        + '<h3 style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:6px">Image Generation Failed</h3>'
        + '<p style="font-size:13px;color:var(--text-secondary);line-height:1.6">' + escHtml(errorMsg) + '</p>'
        + '</div>'
        + '<div style="background:var(--bg-input);border-radius:var(--radius-sm);padding:14px;margin-bottom:20px">'
        + '<div style="font-size:12px;font-weight:600;color:var(--text);margin-bottom:6px">What you can do:</div>'
        + '<div style="font-size:12px;color:var(--text-secondary);line-height:1.6">'
        + '1. Click <strong>Try Again</strong> to regenerate the image<br>'
        + '2. The AI service may be busy — wait a moment and retry<br>'
        + '3. If the issue persists, try simplifying the post content'
        + '</div></div>'
        + '<div style="display:flex;gap:10px;justify-content:flex-end">'
        + '<button class="btn btn-ghost" id="imgErrDismiss">Dismiss</button>'
        + '<button class="btn btn-primary" id="imgErrRetry"><i class="fas fa-redo" style="margin-right:4px"></i> Try Again</button>'
        + '</div></div>';

    document.body.appendChild(overlay);

    overlay.querySelector('#imgErrDismiss').onclick = function() { overlay.remove(); };
    overlay.querySelector('#imgErrRetry').onclick = function() {
        overlay.remove();
        if (retryCallback) retryCallback();
    };
}
</script>
<style>
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
@keyframes slideUp{from{transform:translateY(30px) scale(0.95);opacity:0}to{transform:translateY(0) scale(1);opacity:1}}

/* Enticing CTA buttons — periodic glow pulse + shimmer sweep */
.btn-cta-glow {
    position: relative;
    overflow: hidden;
    animation: ctaBreathing 3s ease-in-out infinite;
}
.btn-cta-glow::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -75%;
    width: 50%;
    height: 200%;
    background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,0.25) 45%, rgba(255,255,255,0.08) 50%, transparent 55%);
    animation: ctaSweep 4s ease-in-out infinite;
    pointer-events: none;
}
.btn-cta-glow::after {
    content: '';
    position: absolute;
    inset: -2px;
    border-radius: inherit;
    background: linear-gradient(135deg, rgba(255,255,255,0.15), transparent, rgba(255,255,255,0.1));
    opacity: 0;
    animation: ctaEdgeGlow 3s ease-in-out infinite;
    pointer-events: none;
    z-index: -1;
}
@keyframes ctaBreathing {
    0%,100% { box-shadow: 0 4px 16px rgba(var(--primary-rgb), 0.25); }
    50% { box-shadow: 0 6px 28px rgba(var(--primary-rgb), 0.45), 0 0 12px rgba(var(--primary-rgb), 0.2); }
}
@keyframes ctaSweep {
    0%,70% { left: -75%; opacity: 0; }
    75% { opacity: 1; }
    100% { left: 125%; opacity: 0; }
}
@keyframes ctaEdgeGlow {
    0%,100% { opacity: 0; }
    50% { opacity: 0.6; }
}
.btn-cta-glow:hover {
    animation: none;
    box-shadow: 0 6px 28px rgba(var(--primary-rgb), 0.5), 0 0 16px rgba(var(--primary-rgb), 0.3);
    transform: translateY(-2px);
}

/* Save Lock Overlay for generator cards */
.save-lock-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 20;
    border-radius: inherit;
    overflow: hidden;
    animation: saveLockIn 0.4s cubic-bezier(0.34,1.56,0.64,1) both;
}
@keyframes saveLockIn {
    0% { opacity:0; transform:scale(0.8); }
    100% { opacity:1; transform:scale(1); }
}
.save-lock-overlay.exiting {
    animation: saveLockOut 0.4s ease forwards;
}
@keyframes saveLockOut {
    0% { opacity:1; transform:scale(1); }
    100% { opacity:0; transform:scale(1.05); }
}
.save-lock-icon {
    font-size: 32px;
    color: #fff;
    animation: lockBounce 0.5s cubic-bezier(0.34,1.56,0.64,1) both;
    animation-delay: 0.15s;
}
@keyframes lockBounce {
    0% { opacity:0; transform:scale(0) rotate(-20deg); }
    100% { opacity:1; transform:scale(1) rotate(0deg); }
}
.save-lock-text {
    font-size: 14px;
    font-weight: 700;
    color: #fff;
    margin-top: 10px;
    animation: lockTextIn 0.4s ease both;
    animation-delay: 0.3s;
}
@keyframes lockTextIn {
    from { opacity:0; transform:translateY(8px); }
    to { opacity:1; transform:translateY(0); }
}
</style>
