<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f4f4f7;font-family:'Helvetica Neue',Arial,sans-serif">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f7;padding:40px 0">
<tr><td align="center">
<table width="520" cellpadding="0" cellspacing="0" style="max-width:520px;width:100%">

<!-- Header with gradient -->
<tr><td style="background:linear-gradient(135deg, <?= htmlspecialchars($primaryColor) ?> 0%, #0a0a0a 100%);border-radius:16px 16px 0 0;padding:32px 40px;text-align:center">
    <?php if (!empty($logoUrl)): ?>
    <img src="<?= htmlspecialchars($logoUrl) ?>" alt="<?= htmlspecialchars($companyName) ?>" style="max-width:180px;max-height:50px;margin-bottom:8px;filter:brightness(0) invert(1)">
    <?php else: ?>
    <div style="font-size:24px;font-weight:800;color:#fff;letter-spacing:-0.5px"><?= htmlspecialchars($companyName) ?></div>
    <?php endif; ?>
</td></tr>

<!-- Body -->
<tr><td style="background:#ffffff;padding:40px;border-radius:0 0 16px 16px">
    <h1 style="font-size:22px;font-weight:700;color:#1a1a2e;margin:0 0 8px">You've been invited!</h1>
    <p style="font-size:15px;color:#64748b;line-height:1.6;margin:0 0 24px">
        You have been invited to join <strong style="color:#1a1a2e"><?= htmlspecialchars($companyName) ?></strong>'s social media management platform. Use the credentials below to sign in.
    </p>

    <!-- Credentials box -->
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:24px">
    <tr><td style="padding:20px 24px">
        <div style="margin-bottom:12px">
            <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#94a3b8;margin-bottom:4px">Username</div>
            <div style="font-size:16px;font-weight:600;color:#1a1a2e"><?= htmlspecialchars($recipientName) ?></div>
        </div>
        <div>
            <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#94a3b8;margin-bottom:4px">Temporary Password</div>
            <div style="font-size:16px;font-weight:600;color:#1a1a2e;font-family:monospace;letter-spacing:1px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:8px 12px;display:inline-block"><?= htmlspecialchars($tempPassword) ?></div>
        </div>
    </td></tr>
    </table>

    <p style="font-size:13px;color:#94a3b8;margin:0 0 24px">You will be asked to change your password on first login.</p>

    <!-- CTA Button -->
    <table width="100%" cellpadding="0" cellspacing="0">
    <tr><td align="center">
        <a href="<?= htmlspecialchars($loginUrl) ?>" style="display:inline-block;padding:14px 40px;background:<?= htmlspecialchars($primaryColor) ?>;color:#ffffff;font-size:15px;font-weight:600;text-decoration:none;border-radius:8px">Sign In</a>
    </td></tr>
    </table>

    <p style="font-size:12px;color:#94a3b8;margin:24px 0 0;text-align:center">
        Or copy this link: <a href="<?= htmlspecialchars($loginUrl) ?>" style="color:<?= htmlspecialchars($primaryColor) ?>"><?= htmlspecialchars($loginUrl) ?></a>
    </p>
</td></tr>

<!-- Footer -->
<tr><td style="padding:24px 40px;text-align:center">
    <p style="font-size:12px;color:#94a3b8;margin:0">&copy; <?= date('Y') ?> <?= htmlspecialchars($companyName) ?>. All rights reserved.</p>
</td></tr>

</table>
</td></tr>
</table>
</body>
</html>
