<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="margin:0;padding:0;background:#0a0a0a;font-family:'Helvetica Neue',Arial,sans-serif;-webkit-font-smoothing:antialiased">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#0a0a0a;padding:40px 0">
<tr><td align="center">
<table width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%">

<!-- Header with branded gradient -->
<tr><td style="background:linear-gradient(165deg, <?= htmlspecialchars($primaryColor) ?> 0%, <?= htmlspecialchars($primaryColor) ?>99 40%, #1a1a2e 100%);border-radius:20px 20px 0 0;padding:40px 40px 36px;text-align:center">
    <?php if (!empty($logoUrl)): ?>
    <img src="<?= htmlspecialchars($logoUrl) ?>" alt="<?= htmlspecialchars($companyName) ?>" style="max-width:200px;max-height:56px;margin-bottom:12px">
    <?php else: ?>
    <div style="font-size:28px;font-weight:800;color:#fff;letter-spacing:-0.5px;margin-bottom:8px"><?= htmlspecialchars($companyName) ?></div>
    <?php endif; ?>
    <div style="font-size:13px;color:rgba(255,255,255,0.5);font-weight:500;letter-spacing:0.05em;text-transform:uppercase">Social Media Platform</div>
</td></tr>

<!-- Accent line -->
<tr><td style="height:3px;background:linear-gradient(90deg, <?= htmlspecialchars($primaryColor) ?>, <?= htmlspecialchars($primaryColor) ?>44, transparent)"></td></tr>

<!-- Body -->
<tr><td style="background:#ffffff;padding:44px 40px 36px">
    <!-- Welcome icon -->
    <table width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding-bottom:24px">
        <div style="width:56px;height:56px;border-radius:50%;background:<?= htmlspecialchars($primaryColor) ?>15;display:inline-flex;align-items:center;justify-content:center">
            <div style="font-size:28px">&#x1F389;</div>
        </div>
    </td></tr></table>

    <h1 style="font-size:24px;font-weight:700;color:#1a1a2e;margin:0 0 8px;text-align:center">You're Invited!</h1>
    <p style="font-size:15px;color:#64748b;line-height:1.7;margin:0 0 28px;text-align:center">
        You've been invited to join <strong style="color:#1a1a2e"><?= htmlspecialchars($companyName) ?></strong>'s social media management platform. Use the credentials below to sign in and get started.
    </p>

    <!-- Credentials card -->
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-left:4px solid <?= htmlspecialchars($primaryColor) ?>;border-radius:12px;margin-bottom:28px">
    <tr><td style="padding:24px 28px">
        <div style="margin-bottom:16px">
            <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;margin-bottom:6px">Username</div>
            <div style="font-size:18px;font-weight:700;color:#1a1a2e"><?= htmlspecialchars($recipientName) ?></div>
        </div>
        <div style="border-top:1px solid #e2e8f0;padding-top:16px">
            <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;margin-bottom:6px">Temporary Password</div>
            <div style="font-size:18px;font-weight:700;color:#1a1a2e;font-family:'Courier New',monospace;letter-spacing:2px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 16px;display:inline-block"><?= htmlspecialchars($tempPassword) ?></div>
        </div>
    </td></tr>
    </table>

    <p style="font-size:13px;color:#94a3b8;margin:0 0 28px;text-align:center">You'll be asked to set a new password on your first login.</p>

    <!-- CTA Button -->
    <table width="100%" cellpadding="0" cellspacing="0">
    <tr><td align="center">
        <a href="<?= htmlspecialchars($loginUrl) ?>" style="display:inline-block;padding:16px 48px;background:<?= htmlspecialchars($primaryColor) ?>;color:#ffffff;font-size:16px;font-weight:700;text-decoration:none;border-radius:8px;letter-spacing:0.02em">Sign In Now</a>
    </td></tr>
    </table>

    <p style="font-size:12px;color:#cbd5e1;margin:24px 0 0;text-align:center;line-height:1.6">
        Or copy this link:<br>
        <a href="<?= htmlspecialchars($loginUrl) ?>" style="color:<?= htmlspecialchars($primaryColor) ?>;word-break:break-all"><?= htmlspecialchars($loginUrl) ?></a>
    </p>
</td></tr>

<!-- Footer -->
<tr><td style="background:#0f172a;border-radius:0 0 20px 20px;padding:28px 40px;text-align:center">
    <p style="font-size:12px;color:#64748b;margin:0 0 4px;font-weight:500">&copy; <?= date('Y') ?> <?= htmlspecialchars($companyName) ?>. All rights reserved.</p>
    <p style="font-size:11px;color:#475569;margin:0">Powered by Solid Social Media Platform</p>
</td></tr>

</table>
</td></tr>
</table>
</body>
</html>
