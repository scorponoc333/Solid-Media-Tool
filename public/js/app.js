/* ============================================
   SolidTech Social — Core JavaScript
   ============================================ */

// ---- Modal System ----
function openModal(title, bodyHtml, footerHtml = '') {
    const modal = document.getElementById('app-modal');
    document.getElementById('modal-title').textContent = title;
    document.getElementById('modal-body').innerHTML = bodyHtml;
    document.getElementById('modal-footer').innerHTML = footerHtml;
    modal.style.display = 'flex';
    requestAnimationFrame(() => modal.classList.add('modal-visible'));
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal = document.getElementById('app-modal');
    modal.classList.remove('modal-visible');
    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }, 250);
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
});

// ---- Confirm Modal (replaces browser confirm) ----
function confirmModal(title, message, onConfirm) {
    const body = `<p style="color:var(--text-secondary);line-height:1.6">${message}</p>`;
    const footer = `
        <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
        <button class="btn btn-danger" id="confirm-action-btn">Confirm</button>
    `;
    openModal(title, body, footer);
    document.getElementById('confirm-action-btn').onclick = () => {
        closeModal();
        if (onConfirm) onConfirm();
    };
}

// ---- Alert Modal (replaces browser alert) ----
function alertModal(title, message, type = 'info') {
    const iconMap = {
        success: '<i class="fas fa-check-circle" style="color:#22c55e;font-size:32px"></i>',
        error: '<i class="fas fa-times-circle" style="color:#ef4444;font-size:32px"></i>',
        warning: '<i class="fas fa-exclamation-triangle" style="color:#f59e0b;font-size:32px"></i>',
        info: '<i class="fas fa-info-circle" style="color:var(--primary);font-size:32px"></i>',
    };
    const body = `
        <div style="text-align:center;padding:8px 0">
            ${iconMap[type] || iconMap.info}
            <p style="margin-top:16px;color:var(--text-secondary);line-height:1.6">${message}</p>
        </div>
    `;
    const footer = `<button class="btn btn-primary" onclick="closeModal()">OK</button>`;
    openModal(title, body, footer);
}

// ---- Toast Notifications ----
function showToast(message, type = 'info', duration = 4000) {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;

    const icons = {
        success: 'fa-check-circle',
        error: 'fa-times-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle',
    };

    toast.innerHTML = `
        <i class="fas ${icons[type] || icons.info}"></i>
        <span>${message}</span>
        <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
    `;

    container.appendChild(toast);
    requestAnimationFrame(() => toast.classList.add('toast-visible'));

    setTimeout(() => {
        toast.classList.remove('toast-visible');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// ---- Theme Toggle ----
function toggleTheme() {
    const html = document.documentElement;
    const current = html.getAttribute('data-theme');
    const next = current === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    document.cookie = `darkMode=${next === 'dark'};path=/;max-age=${365 * 86400}`;
    updateThemeIcon(next);
}

function updateThemeIcon(theme) {
    const icon = document.getElementById('theme-icon');
    if (icon) {
        icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
}

// ---- Sidebar Toggle ----
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('collapsed');
    document.querySelector('.main-wrapper').classList.toggle('sidebar-collapsed');
}

// ---- AJAX Helper ----
async function apiRequest(url, options = {}) {
    const defaults = {
        headers: { 'Content-Type': 'application/json' },
    };
    const config = { ...defaults, ...options };

    try {
        const response = await fetch(url, config);
        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.error || 'Request failed');
        }
        return data;
    } catch (err) {
        showToast(err.message, 'error');
        throw err;
    }
}

// ---- Form Data Helper ----
function formToJson(form) {
    const data = {};
    new FormData(form).forEach((val, key) => { data[key] = val; });
    return data;
}

// ---- Init ----
document.addEventListener('DOMContentLoaded', () => {
    const theme = document.documentElement.getAttribute('data-theme') || 'light';
    updateThemeIcon(theme);
});
