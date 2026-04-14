<?php
$s = $settings ?? [];
$provider     = htmlspecialchars($s['provider'] ?? 'smtp');
$smtpHost     = htmlspecialchars($s['smtp_host'] ?? '');
$smtpPort     = htmlspecialchars($s['smtp_port'] ?? '587');
$smtpUser     = htmlspecialchars($s['smtp_user'] ?? '');
$smtpPass     = htmlspecialchars($s['smtp_pass'] ?? '');
$smtpEncrypt  = htmlspecialchars($s['smtp_encryption'] ?? 'tls');
$fromName     = htmlspecialchars($s['from_name'] ?? '');
$fromEmail    = htmlspecialchars($s['from_email'] ?? '');
$sgKey        = htmlspecialchars($s['sendgrid_api_key'] ?? '');
$mgKey        = htmlspecialchars($s['mailgun_api_key'] ?? '');
$mgDomain     = htmlspecialchars($s['mailgun_domain'] ?? '');
$isConfigured = !empty($s['is_configured']);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
?>

<style>
/* Status Banner */
.smtp-banner {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    border-radius: var(--radius-md);
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 24px;
}
.smtp-banner-ok {
    background: rgba(34,197,94,0.1);
    border: 1px solid rgba(34,197,94,0.25);
    color: var(--success);
}
.smtp-banner-warn {
    background: rgba(var(--primary-rgb), 0.1);
    border: 1px solid rgba(var(--primary-rgb), 0.3);
    color: var(--primary);
}
.smtp-banner i {
    font-size: 18px;
    flex-shrink: 0;
}

/* Provider Tabs */
.provider-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 0;
}
.provider-tab {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: var(--radius-sm);
    border: 2px solid var(--border);
    background: var(--bg-input);
    color: var(--text-secondary);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition);
}
.provider-tab:hover {
    border-color: var(--text-muted);
    color: var(--text);
}
.provider-tab.active {
    border-color: var(--primary);
    background: rgba(var(--primary-rgb), 0.08);
    color: var(--primary);
}
.provider-tab i {
    font-size: 15px;
}

/* Panels */
.provider-panel {
    display: none;
    padding-top: 24px;
}
.provider-panel.active {
    display: block;
}

/* Form grid for two-column rows */
.smtp-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0 24px;
}
@media (max-width: 640px) {
    .smtp-form-grid { grid-template-columns: 1fr; }
    .provider-tabs { flex-direction: column; }
}

/* Password wrapper */
.password-wrapper {
    display: flex;
    gap: 8px;
}
.password-wrapper .form-input {
    flex: 1;
}
.password-wrapper .btn {
    padding: 8px 12px;
    white-space: nowrap;
    flex-shrink: 0;
}

/* Actions row */
.smtp-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    border-top: 1px solid var(--border);
    margin-top: 24px;
    padding-top: 20px;
}
.smtp-actions .spacer {
    flex: 1;
}
#testResult {
    font-size: 13px;
    font-weight: 500;
    transition: opacity var(--transition);
}

/* External link */
.smtp-external-link {
    font-size: 12px;
    color: var(--text-muted);
    margin-bottom: 12px;
}
.smtp-external-link a {
    color: var(--primary);
    text-decoration: underline;
}
.smtp-external-link i {
    margin-right: 4px;
}
</style>

<!-- Status Banner -->
<?php if ($isConfigured): ?>
<div class="smtp-banner smtp-banner-ok">
    <i class="fas fa-check-circle"></i>
    <span>Email is configured and ready</span>
</div>
<?php else: ?>
<div class="smtp-banner smtp-banner-warn">
    <i class="fas fa-exclamation-triangle"></i>
    <span>Email is not configured. Set up a provider below.</span>
</div>
<?php endif; ?>

<!-- Provider Selection -->
<div class="card" style="margin-bottom:28px">
    <div class="card-header">
        <div>
            <div class="card-title"><i class="fas fa-envelope" style="margin-right:8px;color:var(--primary)"></i>Email Provider</div>
            <div class="card-subtitle">Choose how outgoing emails are sent from the application</div>
        </div>
    </div>

    <div style="padding:0 24px 24px">
        <!-- Tabs -->
        <div class="provider-tabs">
            <button type="button" class="provider-tab <?= $provider === 'smtp' ? 'active' : '' ?>" onclick="switchProvider('smtp')" id="tab-smtp">
                <i class="fas fa-server"></i> SMTP
            </button>
            <button type="button" class="provider-tab <?= $provider === 'sendgrid' ? 'active' : '' ?>" onclick="switchProvider('sendgrid')" id="tab-sendgrid">
                <i class="fas fa-paper-plane"></i> SendGrid
            </button>
            <button type="button" class="provider-tab <?= $provider === 'mailgun' ? 'active' : '' ?>" onclick="switchProvider('mailgun')" id="tab-mailgun">
                <i class="fas fa-mail-bulk"></i> Mailgun
            </button>
        </div>

        <!-- SMTP Panel -->
        <div class="provider-panel <?= $provider === 'smtp' ? 'active' : '' ?>" id="panel-smtp">
            <div class="smtp-form-grid">
                <div class="form-group">
                    <label class="form-label" for="smtp_host">SMTP Host</label>
                    <input type="text" id="smtp_host" class="form-input" value="<?= $smtpHost ?>" placeholder="smtp.gmail.com">
                </div>
                <div class="form-group">
                    <label class="form-label" for="smtp_port">Port</label>
                    <input type="number" id="smtp_port" class="form-input" value="<?= $smtpPort ?>" placeholder="587">
                </div>
            </div>

            <div class="smtp-form-grid">
                <div class="form-group">
                    <label class="form-label" for="smtp_user">Username</label>
                    <input type="text" id="smtp_user" class="form-input" value="<?= $smtpUser ?>" placeholder="user@gmail.com">
                </div>
                <div class="form-group">
                    <label class="form-label" for="smtp_pass">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="smtp_pass" class="form-input" value="<?= $smtpPass ?>" placeholder="App password or SMTP password">
                        <button type="button" class="btn btn-ghost" onclick="togglePasswordVisibility('smtp_pass', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-group" style="max-width:calc(50% - 12px)">
                <label class="form-label" for="smtp_encryption">Encryption</label>
                <select id="smtp_encryption" class="form-input">
                    <option value="tls" <?= $smtpEncrypt === 'tls' ? 'selected' : '' ?>>TLS</option>
                    <option value="ssl" <?= $smtpEncrypt === 'ssl' ? 'selected' : '' ?>>SSL</option>
                    <option value="none" <?= $smtpEncrypt === 'none' ? 'selected' : '' ?>>None</option>
                </select>
            </div>

            <div class="smtp-form-grid">
                <div class="form-group">
                    <label class="form-label" for="smtp_from_name">From Name</label>
                    <input type="text" id="smtp_from_name" class="form-input" value="<?= $fromName ?>" placeholder="My Company">
                </div>
                <div class="form-group">
                    <label class="form-label" for="smtp_from_email">From Email</label>
                    <input type="email" id="smtp_from_email" class="form-input" value="<?= $fromEmail ?>" placeholder="noreply@mycompany.com">
                </div>
            </div>
        </div>

        <!-- SendGrid Panel -->
        <div class="provider-panel <?= $provider === 'sendgrid' ? 'active' : '' ?>" id="panel-sendgrid">
            <div class="form-group">
                <label class="form-label" for="sg_api_key">API Key</label>
                <div class="password-wrapper">
                    <input type="password" id="sg_api_key" class="form-input" value="<?= $sgKey ?>" placeholder="SG.xxxxxxxxxxxxxxxxxxxx">
                    <button type="button" class="btn btn-ghost" onclick="togglePasswordVisibility('sg_api_key', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="smtp-external-link">
                <i class="fas fa-external-link-alt"></i>
                Get your key at <a href="https://app.sendgrid.com/settings/api_keys" target="_blank">sendgrid.com</a>
            </div>

            <div class="smtp-form-grid">
                <div class="form-group">
                    <label class="form-label" for="sg_from_name">From Name</label>
                    <input type="text" id="sg_from_name" class="form-input" value="<?= $fromName ?>" placeholder="My Company">
                </div>
                <div class="form-group">
                    <label class="form-label" for="sg_from_email">From Email</label>
                    <input type="email" id="sg_from_email" class="form-input" value="<?= $fromEmail ?>" placeholder="noreply@mycompany.com">
                </div>
            </div>
        </div>

        <!-- Mailgun Panel -->
        <div class="provider-panel <?= $provider === 'mailgun' ? 'active' : '' ?>" id="panel-mailgun">
            <div class="form-group">
                <label class="form-label" for="mg_api_key">API Key</label>
                <div class="password-wrapper">
                    <input type="password" id="mg_api_key" class="form-input" value="<?= $mgKey ?>" placeholder="key-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                    <button type="button" class="btn btn-ghost" onclick="togglePasswordVisibility('mg_api_key', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="mg_domain">Domain</label>
                <input type="text" id="mg_domain" class="form-input" value="<?= $mgDomain ?>" placeholder="mg.yourdomain.com">
            </div>

            <div class="smtp-form-grid">
                <div class="form-group">
                    <label class="form-label" for="mg_from_name">From Name</label>
                    <input type="text" id="mg_from_name" class="form-input" value="<?= $fromName ?>" placeholder="My Company">
                </div>
                <div class="form-group">
                    <label class="form-label" for="mg_from_email">From Email</label>
                    <input type="email" id="mg_from_email" class="form-input" value="<?= $fromEmail ?>" placeholder="noreply@yourdomain.com">
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="smtp-actions">
            <button type="button" class="btn btn-ghost" onclick="testSmtp()" id="testBtn">
                <i class="fas fa-plug"></i> Test Connection
            </button>
            <span id="testResult"></span>
            <div class="spacer"></div>
            <button type="button" class="btn btn-primary" onclick="saveSmtp()" id="saveBtn">
                <i class="fas fa-save"></i> Save Settings
            </button>
        </div>
    </div>
</div>

<script>
var activeProvider = '<?= $provider ?>';

function switchProvider(name) {
    activeProvider = name;

    // Update tabs
    document.querySelectorAll('.provider-tab').forEach(function(tab) {
        tab.classList.remove('active');
    });
    document.getElementById('tab-' + name).classList.add('active');

    // Update panels
    document.querySelectorAll('.provider-panel').forEach(function(panel) {
        panel.classList.remove('active');
    });
    document.getElementById('panel-' + name).classList.add('active');

    // Clear test result when switching
    document.getElementById('testResult').innerHTML = '';
}

function gatherData() {
    var data = {
        provider: activeProvider,
        csrf_token: '<?= $csrfToken ?>'
    };

    if (activeProvider === 'smtp') {
        data.smtp_host       = document.getElementById('smtp_host').value;
        data.smtp_port       = document.getElementById('smtp_port').value;
        data.smtp_user       = document.getElementById('smtp_user').value;
        data.smtp_pass       = document.getElementById('smtp_pass').value;
        data.smtp_encryption = document.getElementById('smtp_encryption').value;
        data.from_name       = document.getElementById('smtp_from_name').value;
        data.from_email      = document.getElementById('smtp_from_email').value;
    } else if (activeProvider === 'sendgrid') {
        data.sendgrid_api_key = document.getElementById('sg_api_key').value;
        data.from_name        = document.getElementById('sg_from_name').value;
        data.from_email       = document.getElementById('sg_from_email').value;
    } else if (activeProvider === 'mailgun') {
        data.mailgun_api_key = document.getElementById('mg_api_key').value;
        data.mailgun_domain  = document.getElementById('mg_domain').value;
        data.from_name       = document.getElementById('mg_from_name').value;
        data.from_email      = document.getElementById('mg_from_email').value;
    }

    return data;
}

function saveSmtp() {
    var btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    fetch('<?= BASE_URL ?>/smtp/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(gatherData())
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Save Settings';
        if (result.success) {
            showToast('Email settings saved successfully', 'success');
        } else {
            showToast(result.error || 'Failed to save settings', 'error');
        }
    })
    .catch(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Save Settings';
        showToast('Network error — could not save settings', 'error');
    });
}

function testSmtp() {
    var btn = document.getElementById('testBtn');
    var result = document.getElementById('testResult');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
    result.innerHTML = '';

    fetch('<?= BASE_URL ?>/smtp/test', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(gatherData())
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plug"></i> Test Connection';
        if (res.success || res.ok) {
            result.innerHTML = '<span style="color:var(--success)"><i class="fas fa-check-circle"></i> Connection successful</span>';
            showToast('Email test passed', 'success');
        } else {
            result.innerHTML = '<span style="color:var(--danger)"><i class="fas fa-times-circle"></i> ' + (res.error || 'Connection failed') + '</span>';
            showToast(res.error || 'Email test failed', 'error');
        }
    })
    .catch(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plug"></i> Test Connection';
        result.innerHTML = '<span style="color:var(--danger)"><i class="fas fa-times-circle"></i> Network error</span>';
        showToast('Network error — could not test connection', 'error');
    });
}

function togglePasswordVisibility(inputId, btn) {
    var input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
    } else {
        input.type = 'password';
        btn.innerHTML = '<i class="fas fa-eye"></i>';
    }
}
</script>
