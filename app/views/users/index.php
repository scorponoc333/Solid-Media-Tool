<?php
$csrfToken = $_SESSION['csrf_token'] ?? '';
$currentRole = $_SESSION['role'] ?? 'editor';
$users = $users ?? [];
$approvalSettings = $approvalSettings ?? ['approval_required' => 0, 'min_approvals' => 1];
$smtpConfigured = $smtpConfigured ?? false;
$reviewerCount = 0;
foreach ($users as $u) {
    if (($u['role'] ?? '') === 'reviewer' && ($u['is_active'] ?? 0)) $reviewerCount++;
}
?>

<style>
.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 700;
    color: #fff;
    flex-shrink: 0;
    text-transform: uppercase;
}
.user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}
.user-cell-name {
    font-weight: 600;
    font-size: 14px;
    color: var(--text);
}
.user-cell-email {
    font-size: 12px;
    color: var(--text-muted);
}

.badge-admin { background: rgba(var(--primary-rgb), 0.12); color: var(--primary); font-weight: 600; }
.badge-editor { background: rgba(var(--primary-rgb), 0.08); color: var(--text-secondary); font-weight: 600; }
.badge-reviewer { background: rgba(var(--primary-rgb), 0.06); color: var(--text-muted); font-weight: 600; }
.badge-active { background: rgba(34,197,94,0.12); color: var(--success); }
.badge-inactive { background: rgba(148,163,184,0.15); color: var(--text-muted); }

.smtp-banner {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    background: rgba(var(--primary-rgb), 0.08);
    border: 1px solid rgba(var(--primary-rgb), 0.25);
    border-radius: var(--radius-md);
    margin-bottom: 24px;
    font-size: 13px;
    color: var(--primary);
    font-weight: 500;
}
.smtp-banner i { font-size: 16px; flex-shrink: 0; color: var(--primary); }
.smtp-banner a { color: var(--primary); text-decoration: underline; font-weight: 700; }

.approval-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}
.approval-row .form-label { margin-bottom: 0; }

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 26px;
}
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-slider {
    position: absolute;
    inset: 0;
    background: var(--bg-input);
    border: 1px solid var(--border);
    border-radius: 26px;
    cursor: pointer;
    transition: all var(--transition);
}
.toggle-slider::before {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    left: 3px;
    top: 2px;
    background: var(--text-muted);
    border-radius: 50%;
    transition: all var(--transition);
}
.toggle-switch input:checked + .toggle-slider {
    background: var(--primary);
    border-color: var(--primary);
}
.toggle-switch input:checked + .toggle-slider::before {
    transform: translateX(22px);
    background: #fff;
}

.min-approvals-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid var(--border);
}
.min-approvals-row input[type="number"] {
    width: 70px;
    text-align: center;
}
.reviewer-count-note {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 12px;
}

/* Modal (same pattern as theme-modal-overlay) */
.theme-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.25s ease;
}
.theme-modal-overlay.visible {
    opacity: 1;
    visibility: visible;
}
.theme-modal {
    background: var(--bg-card);
    border-radius: var(--radius-xl);
    max-width: 520px;
    width: 92%;
    max-height: 85vh;
    overflow-y: auto;
    box-shadow: var(--shadow-xl);
    transform: translateY(20px) scale(0.97);
    transition: transform 0.25s ease;
    padding: 28px;
}
.theme-modal-overlay.visible .theme-modal {
    transform: translateY(0) scale(1);
}
.theme-modal-title {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.theme-modal-close {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: none;
    background: var(--bg-input);
    color: var(--text-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
.theme-modal-close:hover { background: var(--danger); color: #fff; }

.actions-cell {
    display: flex;
    gap: 4px;
    align-items: center;
}

.temp-password-box {
    background: var(--bg-input);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 14px 16px;
    margin-top: 16px;
    text-align: center;
}
.temp-password-box .label {
    font-size: 12px;
    color: var(--text-muted);
    margin-bottom: 6px;
    font-weight: 500;
}
.temp-password-box .password {
    font-family: 'Courier New', monospace;
    font-size: 18px;
    font-weight: 700;
    color: var(--text);
    letter-spacing: 1px;
    user-select: all;
}
</style>

<?php if (!$smtpConfigured): ?>
<div class="smtp-banner">
    <i class="fas fa-exclamation-triangle"></i>
    <span>Email not configured. Invitation emails won't be sent until you set up SMTP. <a href="<?= BASE_URL ?>/smtp">Configure SMTP</a></span>
</div>
<?php endif; ?>

<?php
$brandService = new BrandingService();
$brandData = $brandService->get($GLOBALS['client_id']);
$uPrimary = $brandData['primary_color'] ?? '#6366f1';
?>
<!-- Team Members Card -->
<div class="card" style="padding:0;overflow:hidden;margin-bottom:28px">
    <div style="background:linear-gradient(165deg, <?= htmlspecialchars($uPrimary) ?> 0%, color-mix(in srgb, <?= htmlspecialchars($uPrimary) ?> 45%, #0a0a0a) 100%);padding:24px 28px;display:flex;align-items:center;justify-content:space-between">
        <div>
            <div style="font-size:17px;font-weight:700;color:#fff">Team Members</div>
            <div style="font-size:13px;color:rgba(255,255,255,0.55);margin-top:2px"><?= count($users) ?> user<?= count($users) !== 1 ? 's' : '' ?> on your team</div>
        </div>
        <button class="btn btn-sm" onclick="openInviteModal()" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2);color:#fff;backdrop-filter:blur(4px)">
            <i class="fas fa-plus" style="margin-right:4px"></i> Invite User
        </button>
    </div>

    <?php if (empty($users)): ?>
        <div class="empty-state" style="padding:48px 24px">
            <i class="fas fa-users" style="font-size:32px;color:var(--text-muted);margin-bottom:12px"></i>
            <p style="color:var(--text-secondary);margin-bottom:16px">No team members yet. Invite your first user to get started.</p>
            <button class="btn btn-primary" onclick="openInviteModal()">
                <i class="fas fa-plus" style="margin-right:4px"></i> Invite User
            </button>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user):
                        $uid = (int)$user['id'];
                        $uname = htmlspecialchars($user['username'] ?? '');
                        $fname = htmlspecialchars($user['first_name'] ?? '');
                        $uemail = htmlspecialchars($user['email'] ?? '');
                        $urole = htmlspecialchars($user['role'] ?? 'editor');
                        $uactive = (int)($user['is_active'] ?? 1);
                        $initials = '';
                        if ($fname) {
                            $initials = strtoupper(substr($fname, 0, 1));
                        } elseif ($uname) {
                            $initials = strtoupper(substr($uname, 0, 1));
                        } else {
                            $initials = '?';
                        }
                        // Role-based avatar: admin gets brand color, others get neutral
                        $avatarColor = match ($urole) {
                            'admin' => $uPrimary,
                            'editor' => '#475569',
                            'reviewer' => '#64748b',
                            default => '#94a3b8',
                        };

                        // Relative date for last login
                        $lastLogin = $user['last_login_at'] ?? null;
                        if ($lastLogin) {
                            $diff = time() - strtotime($lastLogin);
                            if ($diff < 60) $lastLoginStr = 'Just now';
                            elseif ($diff < 3600) $lastLoginStr = floor($diff / 60) . 'm ago';
                            elseif ($diff < 86400) $lastLoginStr = floor($diff / 3600) . 'h ago';
                            elseif ($diff < 604800) $lastLoginStr = floor($diff / 86400) . 'd ago';
                            else $lastLoginStr = date('M j, Y', strtotime($lastLogin));
                        } else {
                            $lastLoginStr = 'Never';
                        }
                    ?>
                    <tr data-user-id="<?= $uid ?>"
                        data-first-name="<?= $fname ?>"
                        data-email="<?= $uemail ?>"
                        data-role="<?= $urole ?>"
                        data-active="<?= $uactive ?>">
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar" style="background:<?= $avatarColor ?>"><?= $initials ?></div>
                                <div>
                                    <div class="user-cell-name"><?= $fname ?: $uname ?></div>
                                    <div class="user-cell-email"><?= $uemail ?></div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge badge-<?= $urole ?>"><?= ucfirst($urole) ?></span></td>
                        <td><span class="badge badge-<?= $uactive ? 'active' : 'inactive' ?>"><?= $uactive ? 'Active' : 'Inactive' ?></span></td>
                        <td><span class="text-small text-muted"><?= $lastLoginStr ?></span></td>
                        <td>
                            <div class="actions-cell">
                                <button class="btn btn-ghost btn-sm btn-icon" title="Edit" onclick="openEditModal(<?= $uid ?>)">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <?php if ($uid !== (int)($_SESSION['user_id'] ?? 0)): ?>
                                <button class="btn btn-ghost btn-sm btn-icon" title="Reset Password (Email)" onclick="resendInvite(<?= $uid ?>, '<?= addslashes($fname ?: $uname) ?>')">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                                <button class="btn btn-ghost btn-sm btn-icon" title="<?= $uactive ? 'Deactivate' : 'Activate' ?>" onclick="deactivateUser(<?= $uid ?>, '<?= addslashes($fname ?: $uname) ?>', <?= $uactive ?>)">
                                    <i class="fas fa-<?= $uactive ? 'ban' : 'check-circle' ?>" style="<?= $uactive ? 'color:var(--danger)' : 'color:var(--success)' ?>"></i>
                                </button>
                                <button class="btn btn-ghost btn-sm btn-icon" title="Delete User" onclick="deleteUser(<?= $uid ?>, '<?= addslashes($fname ?: $uname) ?>')">
                                    <i class="fas fa-trash-alt" style="color:var(--danger)"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Deleted Users Bucket -->
<?php $deletedUsers = $deletedUsers ?? []; ?>
<?php if (!empty($deletedUsers)): ?>
<div class="card" style="margin-bottom:28px;opacity:0.8">
    <div class="card-header">
        <div>
            <div class="card-title" style="color:var(--text-muted)"><i class="fas fa-trash-alt" style="margin-right:8px;opacity:0.5"></i>Deleted Users</div>
            <div class="card-subtitle"><?= count($deletedUsers) ?> deleted user<?= count($deletedUsers) !== 1 ? 's' : '' ?></div>
        </div>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr><th>User</th><th>Role</th><th>Deleted</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($deletedUsers as $du):
                    $duid = (int)$du['id'];
                    $dname = htmlspecialchars($du['first_name'] ?: $du['username']);
                    $demail = htmlspecialchars($du['email']);
                ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:36px;height:36px;border-radius:50%;background:var(--bg-input);color:var(--text-muted);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px"><?= strtoupper(substr($dname, 0, 1)) ?></div>
                            <div>
                                <div style="font-weight:600;color:var(--text-muted)"><?= $dname ?></div>
                                <div style="font-size:12px;color:var(--text-muted)"><?= $demail ?></div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge" style="opacity:0.5"><?= ucfirst(htmlspecialchars($du['role'])) ?></span></td>
                    <td><span class="text-small text-muted">—</span></td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button class="btn btn-ghost btn-sm" onclick="restoreUser(<?= $duid ?>, '<?= addslashes($dname) ?>')" style="color:var(--success)">
                                <i class="fas fa-undo" style="margin-right:4px"></i> Restore
                            </button>
                            <button class="btn btn-ghost btn-sm" onclick="permanentDeleteUser(<?= $duid ?>, '<?= addslashes($dname) ?>')" style="color:var(--danger)">
                                <i class="fas fa-skull-crossbones" style="margin-right:4px"></i> Permanently Delete
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Approval Settings Card -->
<div class="card" style="margin-bottom:28px">
    <div class="card-header">
        <div>
            <div class="card-title">Post Approval Workflow</div>
            <div class="card-subtitle">Require posts to be reviewed before publishing</div>
        </div>
    </div>
    <div style="max-width:480px">
        <div class="approval-row">
            <label class="toggle-switch">
                <input type="checkbox" id="approval-toggle" <?= $approvalSettings['approval_required'] ? 'checked' : '' ?> onchange="toggleApprovalOptions()">
                <span class="toggle-slider"></span>
            </label>
            <span class="form-label" style="margin-bottom:0;cursor:pointer" onclick="document.getElementById('approval-toggle').click()">Require approval before publishing</span>
        </div>
        <div id="approval-options" style="<?= $approvalSettings['approval_required'] ? '' : 'display:none' ?>">
            <div class="min-approvals-row">
                <label class="form-label" for="min-approvals" style="margin-bottom:0;white-space:nowrap">Minimum approvals required</label>
                <input type="number" id="min-approvals" class="form-input" min="1" max="5" value="<?= (int)$approvalSettings['min_approvals'] ?>">
            </div>
            <div class="reviewer-count-note">
                <i class="fas fa-info-circle" style="margin-right:4px"></i>
                You currently have <strong><?= $reviewerCount ?></strong> active reviewer<?= $reviewerCount !== 1 ? 's' : '' ?> on your team.
            </div>
        </div>
        <div style="margin-top:20px;display:flex;justify-content:flex-end">
            <button class="btn btn-primary btn-sm" onclick="saveApprovalSettings()" id="saveApprovalBtn">
                <i class="fas fa-save" style="margin-right:4px"></i> Save Settings
            </button>
        </div>
    </div>
</div>

<!-- Invite User Modal -->
<div class="theme-modal-overlay" id="inviteModal">
    <div class="theme-modal">
        <div class="theme-modal-title">
            <span>Invite New User</span>
            <button class="theme-modal-close" onclick="closeInviteModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="form-group">
            <label class="form-label" for="invite-email">Email <span style="color:var(--danger)">*</span></label>
            <input type="email" id="invite-email" class="form-input" placeholder="user@example.com" required>
        </div>
        <div class="form-group">
            <label class="form-label" for="invite-first-name">First Name</label>
            <input type="text" id="invite-first-name" class="form-input" placeholder="Jane">
        </div>
        <div class="form-group">
            <label class="form-label" for="invite-role">Role</label>
            <select id="invite-role" class="form-select">
                <option value="editor">Editor</option>
                <option value="reviewer">Reviewer</option>
                <?php if ($currentRole === 'admin'): ?>
                <option value="admin">Admin</option>
                <?php endif; ?>
            </select>
        </div>
        <div id="invite-result" style="display:none"></div>
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:8px">
            <button class="btn btn-ghost" onclick="closeInviteModal()">Cancel</button>
            <button class="btn btn-primary" onclick="inviteUser()" id="inviteBtn">
                <i class="fas fa-paper-plane" style="margin-right:4px"></i> Send Invite
            </button>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="theme-modal-overlay" id="editModal">
    <div class="theme-modal">
        <div class="theme-modal-title">
            <span>Edit User</span>
            <button class="theme-modal-close" onclick="closeEditModal()"><i class="fas fa-times"></i></button>
        </div>
        <input type="hidden" id="edit-user-id">
        <div class="form-group">
            <label class="form-label" for="edit-first-name">First Name</label>
            <input type="text" id="edit-first-name" class="form-input" placeholder="Jane">
        </div>
        <div class="form-group">
            <label class="form-label" for="edit-email">Email</label>
            <input type="email" id="edit-email" class="form-input" placeholder="user@example.com">
        </div>
        <div class="form-group">
            <label class="form-label" for="edit-role">Role</label>
            <select id="edit-role" class="form-select">
                <option value="editor">Editor</option>
                <option value="reviewer">Reviewer</option>
                <?php if ($currentRole === 'admin'): ?>
                <option value="admin">Admin</option>
                <?php endif; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="edit-password">New Password <span style="font-weight:400;color:var(--text-muted)">(leave blank to keep current)</span></label>
            <input type="password" id="edit-password" class="form-input" placeholder="Min 8 characters (optional)">
        </div>
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:8px">
            <button class="btn btn-ghost" onclick="closeEditModal()">Cancel</button>
            <button class="btn btn-primary" onclick="updateUser()" id="updateBtn">
                <i class="fas fa-save" style="margin-right:4px"></i> Save Changes
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    var BASE = '<?= BASE_URL ?>';
    var CSRF = '<?= $csrfToken ?>';

    // ---- Invite Modal ----
    window.openInviteModal = function() {
        document.getElementById('invite-email').value = '';
        document.getElementById('invite-first-name').value = '';
        document.getElementById('invite-role').value = 'editor';
        document.getElementById('invite-result').style.display = 'none';
        document.getElementById('invite-result').innerHTML = '';
        document.getElementById('inviteModal').classList.add('visible');
    };

    window.closeInviteModal = function() {
        document.getElementById('inviteModal').classList.remove('visible');
    };

    window.inviteUser = function() {
        var email = document.getElementById('invite-email').value.trim();
        var firstName = document.getElementById('invite-first-name').value.trim();
        var role = document.getElementById('invite-role').value;
        var resultBox = document.getElementById('invite-result');

        if (!email) {
            showToast('Please enter an email address.', 'error');
            return;
        }

        var btn = document.getElementById('inviteBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

        fetch(BASE + '/users/create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                email: email,
                first_name: firstName,
                role: role,
                csrf_token: CSRF
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane" style="margin-right:4px"></i> Send Invite';

            if (data.success) {
                if (data.email_sent) {
                    showToast('Invitation sent to ' + email + '!', 'success');
                    closeInviteModal();
                    setTimeout(function() { location.reload(); }, 600);
                } else {
                    // SMTP not configured — show temp password
                    resultBox.style.display = 'block';
                    resultBox.innerHTML =
                        '<div class="temp-password-box">' +
                        '<div class="label">Email could not be sent. Share this temporary password manually:</div>' +
                        '<div class="password">' + escHtml(data.temp_password || '—') + '</div>' +
                        '</div>';
                    showToast('User created, but email could not be sent.', 'warning');
                    // Reload list after brief delay so admin can copy the password
                }
            } else {
                showToast(data.error || 'Failed to create user.', 'error');
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane" style="margin-right:4px"></i> Send Invite';
            showToast('Network error. Please try again.', 'error');
        });
    };

    // ---- Edit Modal ----
    window.openEditModal = function(userId) {
        var row = document.querySelector('tr[data-user-id="' + userId + '"]');
        if (!row) return;

        document.getElementById('edit-user-id').value = userId;
        document.getElementById('edit-first-name').value = row.getAttribute('data-first-name') || '';
        document.getElementById('edit-email').value = row.getAttribute('data-email') || '';
        document.getElementById('edit-role').value = row.getAttribute('data-role') || 'editor';
        document.getElementById('edit-password').value = '';
        document.getElementById('editModal').classList.add('visible');
    };

    window.closeEditModal = function() {
        document.getElementById('editModal').classList.remove('visible');
    };

    window.updateUser = function() {
        var userId = document.getElementById('edit-user-id').value;
        var firstName = document.getElementById('edit-first-name').value.trim();
        var email = document.getElementById('edit-email').value.trim();
        var role = document.getElementById('edit-role').value;
        var newPassword = document.getElementById('edit-password').value;

        if (newPassword && newPassword.length < 8) {
            showToast('Password must be at least 8 characters', 'warning');
            return;
        }

        var btn = document.getElementById('updateBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        var payload = {
            first_name: firstName,
            email: email,
            role: role,
            csrf_token: CSRF
        };
        if (newPassword) payload.new_password = newPassword;

        fetch(BASE + '/users/update/' + userId, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save" style="margin-right:4px"></i> Save Changes';

            if (data.success) {
                showToast('User updated successfully.', 'success');
                closeEditModal();
                setTimeout(function() { location.reload(); }, 600);
            } else {
                showToast(data.error || 'Failed to update user.', 'error');
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save" style="margin-right:4px"></i> Save Changes';
            showToast('Network error. Please try again.', 'error');
        });
    };

    // ---- Deactivate / Activate User ----
    window.deactivateUser = function(id, name, isActive) {
        var action = isActive ? 'deactivate' : 'activate';
        var title = isActive ? 'Deactivate User' : 'Activate User';
        var message = isActive
            ? 'Are you sure you want to deactivate <strong>' + escHtml(name) + '</strong>? They will no longer be able to log in.'
            : 'Are you sure you want to reactivate <strong>' + escHtml(name) + '</strong>?';

        confirmModal(title, message, function() {
            fetch(BASE + '/users/' + action + '/' + id, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ csrf_token: CSRF })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    showToast('User ' + action + 'd successfully.', 'success');
                    setTimeout(function() { location.reload(); }, 600);
                } else {
                    showToast(data.error || 'Failed to ' + action + ' user.', 'error');
                }
            })
            .catch(function() {
                showToast('Network error. Please try again.', 'error');
            });
        });
    };

    // ---- Resend Invite ----
    window.resendInvite = function(id, name) {
        confirmModal('Resend Invitation', 'Resend the invitation email to <strong>' + escHtml(name) + '</strong>?', function() {
            fetch(BASE + '/users/resend-invite/' + id, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ csrf_token: CSRF })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    if (data.email_sent) {
                        showToast('Invitation resent to ' + escHtml(name) + '.', 'success');
                    } else {
                        showToast('SMTP not configured. Temp password: ' + (data.temp_password || '—'), 'warning');
                    }
                } else {
                    showToast(data.error || 'Failed to resend invitation.', 'error');
                }
            })
            .catch(function() {
                showToast('Network error. Please try again.', 'error');
            });
        });
    };

    // ---- Delete User ----
    window.deleteUser = function(id, name) {
        confirmModal('Delete User', 'Are you sure you want to delete <strong>' + escHtml(name) + '</strong>? They will be moved to the deleted users list and can be restored later.', function() {
            fetch(BASE + '/users/delete/' + id, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ csrf_token: CSRF })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    showToast(escHtml(name) + ' has been deleted.', 'success');
                    setTimeout(function() { location.reload(); }, 600);
                } else {
                    showToast(data.error || 'Failed to delete user.', 'error');
                }
            })
            .catch(function() {
                showToast('Network error. Please try again.', 'error');
            });
        });
    };

    // ---- Restore User ----
    window.restoreUser = function(id, name) {
        confirmModal('Restore User', 'Restore <strong>' + escHtml(name) + '</strong> to active users?', function() {
            fetch(BASE + '/users/restore/' + id, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ csrf_token: CSRF })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    showToast(escHtml(name) + ' has been restored.', 'success');
                    setTimeout(function() { location.reload(); }, 600);
                } else {
                    showToast(data.error || 'Failed to restore user.', 'error');
                }
            })
            .catch(function() {
                showToast('Network error. Please try again.', 'error');
            });
        });
    };

    // ---- Permanently Delete User ----
    window.permanentDeleteUser = function(id, name) {
        alertModal(
            'Permanently Delete User',
            '<div style="text-align:center">'
            + '<i class="fas fa-exclamation-triangle" style="font-size:36px;color:var(--danger);margin-bottom:12px"></i>'
            + '<p style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:8px">This action is irreversible.</p>'
            + '<p>You are about to permanently delete <strong>' + escHtml(name) + '</strong> and all associated data from the system. This cannot be undone — the account, activity history, and all records will be erased forever.</p>'
            + '<div style="margin-top:16px;display:flex;gap:8px;justify-content:center">'
            + '<button class="btn btn-ghost" onclick="closeModal()">Cancel</button>'
            + '<button class="btn" style="background:var(--danger);color:#fff" onclick="closeModal();executePermanentDelete(' + id + ',\'' + escHtml(name).replace(/'/g, "\\'") + '\')"><i class="fas fa-skull-crossbones" style="margin-right:4px"></i> Delete Forever</button>'
            + '</div></div>',
            'error'
        );
    };

    window.executePermanentDelete = function(id, name) {
        fetch(BASE + '/users/permanent-delete/' + id, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ csrf_token: CSRF })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                showToast(name + ' has been permanently deleted.', 'success');
                setTimeout(function() { location.reload(); }, 600);
            } else {
                showToast(data.error || 'Failed to delete user.', 'error');
            }
        })
        .catch(function() {
            showToast('Network error. Please try again.', 'error');
        });
    };

    // ---- Approval Settings ----
    window.toggleApprovalOptions = function() {
        var checked = document.getElementById('approval-toggle').checked;
        document.getElementById('approval-options').style.display = checked ? '' : 'none';
    };

    window.saveApprovalSettings = function() {
        var approvalRequired = document.getElementById('approval-toggle').checked ? 1 : 0;
        var minApprovals = parseInt(document.getElementById('min-approvals').value) || 1;

        var btn = document.getElementById('saveApprovalBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        fetch(BASE + '/users/save-approval-settings', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                approval_required: approvalRequired,
                min_approvals: minApprovals,
                csrf_token: CSRF
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save" style="margin-right:4px"></i> Save Settings';

            if (data.success) {
                showToast('Approval settings saved.', 'success');
            } else {
                showToast(data.error || 'Failed to save settings.', 'error');
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save" style="margin-right:4px"></i> Save Settings';
            showToast('Network error. Please try again.', 'error');
        });
    };

    // ---- Helpers ----
    function escHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    // Close modals on overlay click
    document.querySelectorAll('.theme-modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                overlay.classList.remove('visible');
            }
        });
    });

    // Close modals on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.theme-modal-overlay.visible').forEach(function(overlay) {
                overlay.classList.remove('visible');
            });
        }
    });
})();
</script>
