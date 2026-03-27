<?php
$error = $error ?? '';
$logoUrl = $branding['logo_url'] ?? '';
$bgUrl = $branding['login_bg_url'] ?? '';
$primaryColor = $branding['primary_color'] ?? '#6366f1';
$companyName = $branding['company_name'] ?? 'SolidTech Social';
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 24px;
            padding: 48px 40px;
            box-shadow: 0 32px 64px rgba(0, 0, 0, 0.4);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-logo {
            text-align: center;
            margin-bottom: 8px;
        }

        .login-logo img {
            max-width: 200px;
            max-height: 60px;
            object-fit: contain;
            margin: 0 auto;
            filter: brightness(0) invert(1);
        }

        .login-logo .logo-placeholder {
            width: 64px;
            height: 64px;
            margin: 0 auto;
            background: <?= htmlspecialchars($primaryColor) ?>;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 700;
            color: #fff;
        }

        .login-heading {
            text-align: center;
            color: #fff;
            font-size: 24px;
            font-weight: 700;
            margin: 16px 0 4px;
        }

        .login-subheading {
            text-align: center;
            color: rgba(255, 255, 255, 0.55);
            font-size: 14px;
            margin-bottom: 32px;
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: rgba(255, 255, 255, 0.7);
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 8px;
            letter-spacing: 0.02em;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 15px;
            font-family: inherit;
            outline: none;
            transition: all 0.2s ease;
        }

        .form-group input:focus {
            border-color: <?= htmlspecialchars($primaryColor) ?>;
            box-shadow: 0 0 0 3px <?= htmlspecialchars($primaryColor) ?>33;
            background: rgba(255, 255, 255, 0.09);
        }

        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: <?= htmlspecialchars($primaryColor) ?>;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 8px;
        }

        .login-btn:hover {
            filter: brightness(1.1);
            transform: translateY(-1px);
            box-shadow: 0 8px 24px <?= htmlspecialchars($primaryColor) ?>44;
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .error-toast {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            animation: shake 0.4s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .error-toast svg {
            flex-shrink: 0;
            width: 18px;
            height: 18px;
        }

        .footer-text {
            text-align: center;
            margin-top: 24px;
            color: rgba(255, 255, 255, 0.3);
            font-size: 12px;
        }
    </style>
</head>
<body>

<?php if ($particlesEnabled): ?>
<div id="tsparticles"></div>
<?php endif; ?>

<div class="login-wrapper">
    <div class="login-card">
        <div class="login-logo">
            <?php if ($logoUrl): ?>
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo">
            <?php else: ?>
                <div class="logo-placeholder">S</div>
            <?php endif; ?>
        </div>

        <?php if (!$logoUrl): ?>
            <h1 class="login-heading"><?= htmlspecialchars($companyName) ?></h1>
        <?php endif; ?>
        <p class="login-subheading">Sign in to your account</p>

        <?php if ($error): ?>
            <div class="error-toast">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/></svg>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/login">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="login-btn">Sign In</button>
        </form>

        <p class="footer-text">&copy; <?= date('Y') ?> <?= htmlspecialchars($companyName) ?>. All rights reserved.</p>
    </div>
</div>

<?php if ($particlesEnabled): ?>
<script src="https://cdn.jsdelivr.net/npm/tsparticles-engine@2/tsparticles.engine.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tsparticles-slim@2/tsparticles.slim.bundle.min.js"></script>
<script>
tsParticles.load("tsparticles", {
    fullScreen: false,
    background: { color: "transparent" },
    particles: {
        number: { value: 60, density: { enable: true, area: 900 } },
        color: { value: "<?= htmlspecialchars($primaryColor) ?>" },
        shape: { type: "circle" },
        opacity: { value: 0.3, random: true, animation: { enable: true, speed: 0.5, minimumValue: 0.1, sync: false } },
        size: { value: 3, random: true },
        links: { enable: true, distance: 150, color: "<?= htmlspecialchars($primaryColor) ?>", opacity: 0.15, width: 1 },
        move: { enable: true, speed: 1, direction: "none", outModes: "bounce" }
    },
    interactivity: {
        events: { onHover: { enable: true, mode: "grab" } },
        modes: { grab: { distance: 140, links: { opacity: 0.3 } } }
    }
});
</script>
<?php endif; ?>

</body>
</html>
