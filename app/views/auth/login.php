<?php
$error = $error ?? '';
$logoUrl = $branding['logo_url'] ?? '';
$bgUrl = $branding['login_bg_url'] ?? '';
$primaryColor = $branding['primary_color'] ?? '#6366f1';
$secondaryColor = $branding['secondary_color'] ?? '#8b5cf6';
$companyName = $branding['company_name'] ?? 'SolidTech Social';
$faviconUrl = $branding['favicon_url'] ?? '';
$particlesEnabled = $branding['particles_enabled'] ?? 1;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?= htmlspecialchars($companyName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: <?= $bgUrl
                ? "url('" . htmlspecialchars($bgUrl) . "') center/cover no-repeat"
                : "linear-gradient(180deg, " . htmlspecialchars($primaryColor) . " 0%, #0a0a0a 60%, #000000 100%)" ?>;
            overflow: hidden;
        }

        #tsparticles {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        /* ── Boot loader (arc progress) ── */
        .boot-loader {
            position: fixed;
            inset: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            animation: bootFade 0.3s ease 0.75s forwards;
            opacity: 1;
        }
        .boot-loader svg { width: 64px; height: 64px; }
        .boot-arc {
            fill: none;
            stroke: <?= htmlspecialchars($primaryColor) ?>;
            stroke-width: 3;
            stroke-linecap: round;
            stroke-dasharray: 170;
            stroke-dashoffset: 170;
            animation: arcFill 0.7s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            filter: drop-shadow(0 0 6px <?= htmlspecialchars($primaryColor) ?>88);
        }
        @keyframes arcFill { to { stroke-dashoffset: 0; } }
        @keyframes bootFade { to { opacity: 0; } }

        /* ── Login wrapper ── */
        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            padding: 20px;
            transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
        }
        .login-wrapper.zooming {
            transform: scale(1.08);
            opacity: 0;
            filter: blur(8px);
        }

        .login-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 24px;
            padding: 48px 40px;
            box-shadow: 0 32px 64px rgba(0, 0, 0, 0.4);
            opacity: 0;
            transform: translateY(20px) scale(0.97);
            animation: cardIn 0.5s cubic-bezier(0.23, 1, 0.32, 1) 0.65s forwards;
        }
        @keyframes cardIn { to { opacity: 1; transform: translateY(0) scale(1); } }

        /* ── Logo ── */
        .login-logo {
            text-align: center;
            margin-bottom: 8px;
            opacity: 0;
            transform: scale(0.8);
            animation: logoIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) 0.95s forwards;
        }
        @keyframes logoIn { to { opacity: 1; transform: scale(1); } }

        .login-logo img {
            max-width: 200px;
            max-height: 60px;
            object-fit: contain;
            margin: 0 auto;
            filter: brightness(0) invert(1);
        }

        .login-logo .logo-placeholder {
            width: 64px; height: 64px; margin: 0 auto;
            background: <?= htmlspecialchars($primaryColor) ?>;
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 28px; font-weight: 700; color: #fff;
        }

        /* ── Heading ── */
        .login-heading {
            text-align: center; color: #fff; font-size: 24px; font-weight: 700;
            margin: 16px 0 4px; opacity: 0;
            animation: headingFlash 0.35s ease 1.1s forwards;
        }
        @keyframes headingFlash {
            0% { opacity: 0; filter: brightness(2); transform: scale(1.03); }
            60% { opacity: 1; filter: brightness(1.6); transform: scale(1.01); }
            100% { opacity: 1; filter: brightness(1); transform: scale(1); }
        }

        .login-subheading {
            text-align: center; color: rgba(255,255,255,0.55);
            font-size: 14px; margin-bottom: 32px; font-weight: 400;
            opacity: 0; animation: fadeIn 0.3s ease 1.2s forwards;
        }
        @keyframes fadeIn { to { opacity: 1; } }

        /* ── Form groups ── */
        .form-group {
            margin-bottom: 20px; opacity: 0; transform: translateY(10px);
        }
        .form-group:nth-of-type(1) { animation: inputReveal 0.35s ease 1.3s forwards; }
        .form-group:nth-of-type(2) { animation: inputReveal 0.35s ease 1.5s forwards; }
        @keyframes inputReveal { to { opacity: 1; transform: translateY(0); } }

        .form-group label {
            display: block; color: rgba(255,255,255,0.7);
            font-size: 13px; font-weight: 500; margin-bottom: 8px; letter-spacing: 0.02em;
        }

        .form-group input {
            width: 100%; padding: 14px 16px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px; color: #fff; font-size: 15px;
            font-family: inherit; outline: none; transition: all 0.3s ease;
        }

        .form-group:nth-of-type(1) input { animation: inputGlow 0.6s ease 1.35s both; }
        .form-group:nth-of-type(2) input { animation: inputGlow 0.6s ease 1.55s both; }
        @keyframes inputGlow {
            0% { box-shadow: 0 0 0 0 transparent; }
            40% { box-shadow: 0 0 20px <?= htmlspecialchars($primaryColor) ?>55, 0 0 0 3px <?= htmlspecialchars($primaryColor) ?>33; border-color: <?= htmlspecialchars($primaryColor) ?>; }
            100% { box-shadow: 0 0 0 0 transparent; border-color: rgba(255,255,255,0.1); }
        }

        .form-group input:focus {
            border-color: <?= htmlspecialchars($primaryColor) ?>;
            box-shadow: 0 0 0 3px <?= htmlspecialchars($primaryColor) ?>33;
            background: rgba(255,255,255,0.09);
        }
        .form-group input::placeholder { color: rgba(255,255,255,0.3); }

        /* ── Sign-in button ── */
        .login-btn {
            width: 100%; padding: 15px;
            background: <?= htmlspecialchars($primaryColor) ?>;
            color: #fff; border: none; border-radius: 6px;
            font-size: 15px; font-weight: 600; font-family: inherit;
            cursor: pointer; transition: all 0.2s ease; margin-top: 8px;
            opacity: 0; transform: translateY(8px);
            animation: btnReveal 0.4s ease 1.7s forwards;
            position: relative; overflow: hidden;
        }
        @keyframes btnReveal { to { opacity: 1; transform: translateY(0); } }

        .login-btn::before {
            content: ''; position: absolute; top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.35), transparent);
            animation: btnInitShine 0.5s ease 1.9s forwards, btnShineLoop 3s ease 2.4s infinite;
        }
        @keyframes btnInitShine { to { left: 100%; } }
        @keyframes btnShineLoop {
            0% { left: -100%; opacity: 1; }
            40% { left: 100%; opacity: 1; }
            40.01%, 100% { left: -100%; opacity: 0; }
        }

        .login-btn::after {
            content: ''; position: absolute; inset: -1px;
            border-radius: inherit; background: transparent;
            box-shadow: 0 0 16px <?= htmlspecialchars($primaryColor) ?>44;
            animation: btnPulseGlow 2s ease-in-out 2s infinite;
            pointer-events: none; opacity: 0;
        }
        @keyframes btnPulseGlow {
            0%, 100% { opacity: 0.4; box-shadow: 0 0 16px <?= htmlspecialchars($primaryColor) ?>44; }
            50% { opacity: 0.8; box-shadow: 0 0 28px <?= htmlspecialchars($primaryColor) ?>66, 0 4px 20px <?= htmlspecialchars($primaryColor) ?>33; }
        }

        .login-btn:hover { filter: brightness(1.1); transform: translateY(-1px); box-shadow: 0 8px 24px <?= htmlspecialchars($primaryColor) ?>44; }
        .login-btn:active { transform: translateY(0); }
        .login-btn:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }

        /* ── Error ── */
        .error-toast {
            background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3);
            color: #fca5a5; padding: 12px 16px; border-radius: 12px;
            font-size: 13px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;
            animation: shake 0.4s ease;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        .error-toast svg { flex-shrink: 0; width: 18px; height: 18px; }

        .footer-text {
            text-align: center; margin-top: 24px;
            color: rgba(255,255,255,0.3); font-size: 12px;
            opacity: 0; animation: fadeIn 0.4s ease 2s forwards;
        }
        form > input[type="hidden"] { opacity: 1; }

        /* ═══════════════════════════════════════════
           TRANSITION OVERLAY (shown after login)
           ═══════════════════════════════════════════ */
        .transition-overlay {
            position: fixed; inset: 0; z-index: 9999;
            display: flex; align-items: center; justify-content: center;
            flex-direction: column;
            background: linear-gradient(165deg, <?= htmlspecialchars($primaryColor) ?> 0%, color-mix(in srgb, <?= htmlspecialchars($primaryColor) ?> 40%, #0a0a0a) 60%, #0a0a0a 100%);
            opacity: 0; pointer-events: none;
            transition: opacity 0.4s ease;
            overflow: hidden;
        }
        .transition-overlay.active { opacity: 1; pointer-events: all; }

        /* Ambient mist */
        .tr-mist {
            position: absolute; width: 300px; height: 300px;
            border-radius: 50%; pointer-events: none;
            background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, transparent 70%);
            animation: trMist 4s ease-in-out infinite;
        }
        @keyframes trMist {
            0%,100% { opacity: 0.4; transform: scale(1); }
            50% { opacity: 0.65; transform: scale(1.15); }
        }

        /* Atom orbits */
        .tr-atom {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 260px; height: 260px; pointer-events: none;
        }
        .tr-orbit {
            position: absolute; inset: 0; border-radius: 50%;
        }
        .tr-orbit:nth-child(1) {
            width: 280px; height: 100px; top: calc(50% - 50px); left: calc(50% - 140px);
            border: 1.5px solid rgba(255,255,255,0.12);
            animation: trSpin 6s linear infinite;
        }
        .tr-orbit:nth-child(2) {
            width: 240px; height: 90px; top: calc(50% - 45px); left: calc(50% - 120px);
            border: 1.5px solid rgba(255,255,255,0.10);
            animation: trSpin 4.5s linear infinite reverse;
            transform: rotate(55deg);
        }
        .tr-orbit:nth-child(3) {
            width: 80px; height: 240px; top: calc(50% - 120px); left: calc(50% - 40px);
            border: 1.5px solid rgba(255,255,255,0.08);
            animation: trSpin 7s linear infinite;
            transform: rotate(25deg);
        }
        @keyframes trSpin { to { transform: rotate(360deg); } }

        .tr-electron {
            position: absolute; width: 6px; height: 6px;
            background: #fff; border-radius: 50%;
            top: -3px; left: calc(50% - 3px);
            box-shadow: 0 0 12px rgba(255,255,255,0.7);
        }

        /* Pulse rings */
        .tr-pulse {
            position: absolute; top: 50%; left: 50%;
            width: 120px; height: 120px;
            margin: -60px 0 0 -60px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.3);
            animation: trPulse 2.4s ease-out infinite;
            pointer-events: none;
        }
        .tr-pulse:nth-child(2) { animation-delay: 0.6s; }
        .tr-pulse:nth-child(3) { animation-delay: 1.2s; }
        @keyframes trPulse {
            0% { transform: scale(1); opacity: 0.5; }
            100% { transform: scale(2.8); opacity: 0; }
        }

        /* Floating particles */
        .tr-particles {
            position: absolute; inset: 0; overflow: hidden; pointer-events: none;
        }
        .tr-particles span {
            position: absolute; width: 3px; height: 3px;
            border-radius: 50%; background: rgba(255,255,255,0.5);
            opacity: 0; animation: trFloat 3.5s ease-in-out infinite;
        }
        @keyframes trFloat {
            0% { bottom: -8px; opacity: 0; transform: scale(0.5); }
            20% { opacity: 0.6; }
            80% { opacity: 0.2; }
            100% { bottom: 110%; opacity: 0; transform: scale(1.1); }
        }

        /* Center content */
        .tr-center {
            position: relative; z-index: 10;
            display: flex; flex-direction: column; align-items: center;
            text-align: center;
        }

        /* Lock icon */
        .tr-lock {
            width: 80px; height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(4px);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 20px;
            opacity: 0; transform: scale(0.5);
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .tr-lock.visible { opacity: 1; transform: scale(1); }
        .tr-lock svg {
            width: 32px; height: 32px; fill: #fff;
            transition: all 0.3s ease;
        }
        .tr-lock.unlocked svg {
            transform: scale(1.1);
            filter: drop-shadow(0 0 12px rgba(255,255,255,0.6));
        }

        /* Welcome text */
        .tr-welcome {
            font-size: 22px; font-weight: 700; color: #fff;
            margin-bottom: 6px;
            opacity: 0; transform: translateY(10px);
            transition: all 0.4s ease;
        }
        .tr-welcome.visible { opacity: 1; transform: translateY(0); }

        .tr-subtitle {
            font-size: 14px; color: rgba(255,255,255,0.6);
            margin-bottom: 28px;
            opacity: 0;
            transition: all 0.3s ease;
        }
        .tr-subtitle.visible { opacity: 1; }

        /* Progress bar */
        .tr-progress-track {
            width: 200px; height: 3px;
            background: rgba(255,255,255,0.12);
            border-radius: 100px; overflow: hidden;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .tr-progress-track.visible { opacity: 1; }
        .tr-progress-fill {
            width: 0; height: 100%;
            background: linear-gradient(90deg, rgba(255,255,255,0.8), #fff);
            border-radius: 100px;
            transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 0 8px rgba(255,255,255,0.4);
        }

        /* Fade-to-white for exit */
        .transition-overlay.exiting {
            animation: trExitWhite 0.4s ease forwards;
        }
        @keyframes trExitWhite {
            to { opacity: 0; filter: brightness(3) blur(4px); }
        }
    </style>
</head>
<body>

<?php if ($particlesEnabled): ?>
<div id="tsparticles"></div>
<?php endif; ?>

<!-- Boot arc loader -->
<div class="boot-loader" id="bootLoader">
    <svg viewBox="0 0 64 64">
        <circle class="boot-arc" cx="32" cy="32" r="27"/>
    </svg>
</div>

<div class="login-wrapper" id="loginWrapper">
    <div class="login-card">
        <div class="login-logo">
            <?php if ($logoUrl): ?>
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo">
            <?php else: ?>
                <div class="logo-placeholder"><?= strtoupper(substr($companyName, 0, 1)) ?></div>
            <?php endif; ?>
        </div>

        <?php if (!$logoUrl): ?>
            <h1 class="login-heading"><?= htmlspecialchars($companyName) ?></h1>
        <?php endif; ?>
        <p class="login-subheading">Sign in to your account</p>

        <div id="errorContainer">
        <?php if ($error): ?>
            <div class="error-toast">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/></svg>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        </div>

        <form id="loginForm" method="POST" action="<?= BASE_URL ?>/login">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="login-btn" id="loginBtn">Sign In</button>
        </form>

        <p class="footer-text">&copy; <?= date('Y') ?> <?= htmlspecialchars($companyName) ?>. All rights reserved.</p>
    </div>
</div>

<!-- ═══ Transition Overlay ═══ -->
<div class="transition-overlay" id="transOverlay">
    <div class="tr-mist"></div>
    <div class="tr-mist" style="top:60%;left:70%;width:200px;height:200px;animation-delay:-1.5s"></div>

    <div class="tr-atom">
        <div class="tr-orbit"><div class="tr-electron"></div></div>
        <div class="tr-orbit"><div class="tr-electron"></div></div>
        <div class="tr-orbit"><div class="tr-electron"></div></div>
    </div>

    <div class="tr-pulse"></div>
    <div class="tr-pulse"></div>
    <div class="tr-pulse"></div>

    <div class="tr-particles" id="trParticles"></div>

    <div class="tr-center">
        <div class="tr-lock" id="trLock">
            <svg id="trLockIcon" viewBox="0 0 448 512"><path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"/></svg>
        </div>
        <div class="tr-welcome" id="trWelcome">Welcome back</div>
        <div class="tr-subtitle" id="trSubtitle">Preparing your dashboard...</div>
        <div class="tr-progress-track" id="trProgressTrack">
            <div class="tr-progress-fill" id="trProgressFill"></div>
        </div>
    </div>
</div>

<?php if ($particlesEnabled): ?>
<script defer src="https://cdn.jsdelivr.net/npm/tsparticles-engine@2/tsparticles.engine.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/tsparticles-slim@2/tsparticles.slim.bundle.min.js"></script>
<script>
window.addEventListener('load', function() {
    if (typeof tsParticles === 'undefined') return;
    tsParticles.load("tsparticles", {
        fullScreen: false,
        background: { color: "transparent" },
        particles: {
            number: { value: 60, density: { enable: true, area: 900 } },
            color: { value: "<?= htmlspecialchars($primaryColor) ?>" },
            shape: { type: "circle" },
            opacity: { value: 0.4, random: true, animation: { enable: true, speed: 0.5, minimumValue: 0.15, sync: false } },
            size: { value: 3, random: true },
            links: { enable: true, distance: 150, color: "<?= htmlspecialchars($primaryColor) ?>", opacity: 0.2, width: 1 },
            move: { enable: true, speed: 1, direction: "none", outModes: "bounce" }
        },
        interactivity: {
            events: { onHover: { enable: true, mode: "grab" } },
            modes: { grab: { distance: 140, links: { opacity: 0.4 } } }
        }
    });
});
</script>
<?php endif; ?>

<script>
(function() {
    var BASE_URL = '<?= BASE_URL ?>';

    // Remove boot loader
    setTimeout(function() {
        var bl = document.getElementById('bootLoader');
        if (bl) bl.remove();
    }, 1100);

    // Generate floating particles for transition overlay
    var particleContainer = document.getElementById('trParticles');
    for (var i = 0; i < 16; i++) {
        var span = document.createElement('span');
        var size = 2 + Math.random() * 4;
        span.style.width = size + 'px';
        span.style.height = size + 'px';
        span.style.left = (3 + Math.random() * 94) + '%';
        span.style.animationDuration = (3 + Math.random() * 3) + 's';
        span.style.animationDelay = '-' + (Math.random() * 4) + 's';
        particleContainer.appendChild(span);
    }

    // AJAX form submission
    var form = document.getElementById('loginForm');
    var btn = document.getElementById('loginBtn');
    var isSubmitting = false;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (isSubmitting) return;
        isSubmitting = true;
        btn.disabled = true;
        btn.textContent = 'Signing in...';

        var formData = new FormData(form);

        fetch(BASE_URL + '/login-ajax', {
            method: 'POST',
            body: formData
        })
        .then(function(res) {
            return res.json().catch(function() {
                throw new Error('Server error. Please try again.');
            }).then(function(data) {
                return { ok: res.ok, data: data };
            });
        })
        .then(function(result) {
            if (!result.ok || !result.data.success) {
                throw new Error(result.data.error || 'Login failed');
            }
            playTransition(result.data.first_name || '');
        })
        .catch(function(err) {
            isSubmitting = false;
            btn.disabled = false;
            btn.textContent = 'Sign In';
            showError(err.message);
        });
    });

    function showError(msg) {
        var container = document.getElementById('errorContainer');
        var toast = document.createElement('div');
        toast.className = 'error-toast';
        toast.innerHTML = '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/></svg>';
        toast.appendChild(document.createTextNode(msg));
        container.innerHTML = '';
        container.appendChild(toast);
    }

    function playTransition(firstName) {
        var overlay = document.getElementById('transOverlay');
        var wrapper = document.getElementById('loginWrapper');
        var lock = document.getElementById('trLock');
        var lockIcon = document.getElementById('trLockIcon');
        var welcome = document.getElementById('trWelcome');
        var subtitle = document.getElementById('trSubtitle');
        var progressTrack = document.getElementById('trProgressTrack');
        var progressFill = document.getElementById('trProgressFill');

        // Set welcome name
        if (firstName) {
            welcome.textContent = 'Welcome back, ' + firstName;
        }

        // Phase 1: Zoom out login card, show gradient overlay
        wrapper.classList.add('zooming');

        setTimeout(function() {
            overlay.classList.add('active');
        }, 200);

        // Phase 2: Lock icon appears
        setTimeout(function() {
            lock.classList.add('visible');
        }, 500);

        // Phase 2b: Lock unlocks
        setTimeout(function() {
            lockIcon.innerHTML = '<path d="M144 144c0-44.2 35.8-80 80-80s80 35.8 80 80v48H80V256c0-35.3 28.7-64 64-64h16V144zM224 0C144.5 0 80 64.5 80 144v48H64c-35.3 0-64 28.7-64 64V448c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V256c0-35.3-28.7-64-64-64H336V144c0-17.7-3.2-34.6-9-50.2"/>';
            lock.classList.add('unlocked');
        }, 900);

        // Phase 3: Welcome text
        setTimeout(function() {
            welcome.classList.add('visible');
        }, 1000);

        // Phase 3b: Subtitle
        setTimeout(function() {
            subtitle.classList.add('visible');
        }, 1200);

        // Phase 3c: Progress bar
        setTimeout(function() {
            progressTrack.classList.add('visible');
            // Trigger fill in next frame
            requestAnimationFrame(function() {
                progressFill.style.width = '100%';
            });
        }, 1300);

        // Phase 4: Exit → navigate to dashboard
        setTimeout(function() {
            overlay.classList.add('exiting');
        }, 2600);

        setTimeout(function() {
            window.location.href = BASE_URL + '/dashboard';
        }, 2900);
    }
})();
</script>

</body>
</html>
