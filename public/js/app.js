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
    const btn = document.getElementById('themeToggleBtn');
    if (icon) {
        icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        icon.style.color = '#fff';
    }
    if (btn) {
        // Light mode: solid brand color background
        // Dark mode: subtle glass background with brand color border
        if (theme === 'dark') {
            btn.style.background = 'rgba(255,255,255,0.08)';
            btn.style.boxShadow = '0 0 0 1px var(--primary), 0 2px 8px rgba(0,0,0,0.2)';
        } else {
            btn.style.background = 'var(--primary)';
            btn.style.boxShadow = '0 2px 8px rgba(0,0,0,0.15)';
        }
    }
}

// ---- Nav Group Accordion (only one open at a time) ----
function toggleNavGroup(btn) {
    var group = btn.closest('.nav-group');
    if (!group) return;

    var isOpen = group.classList.contains('open');

    // Close all groups first
    document.querySelectorAll('.nav-group.open').forEach(function(g) {
        if (g !== group) g.classList.remove('open');
    });

    // Toggle the clicked one
    if (isOpen) {
        group.classList.remove('open');
    } else {
        group.classList.add('open');
    }
}

// ---- Sidebar Toggle ----
function toggleSidebar() {
    var sidebar = document.getElementById('sidebar');
    var wrapper = document.querySelector('.main-wrapper');
    var isCollapsing = !sidebar.classList.contains('collapsed');

    sidebar.classList.toggle('collapsed');
    wrapper.classList.toggle('sidebar-collapsed');

    // Shine effect on the collapsed initial circle when collapsing
    if (isCollapsing) {
        setTimeout(function() {
            var initial = sidebar.querySelector('.sidebar-collapsed-initial');
            if (initial) {
                initial.style.boxShadow = '0 0 0 2px rgba(255,255,255,0.35), 0 0 20px rgba(255,255,255,0.15)';
                setTimeout(function() {
                    initial.style.transition = 'box-shadow 0.8s ease, opacity 0.3s ease 0.1s, transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) 0.1s';
                    initial.style.boxShadow = 'none';
                    setTimeout(function() { initial.style.transition = ''; }, 800);
                }, 500);
            }
        }, 250);
    }
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
    initConstellation();
});

/* Constellation / geometry network behind sidebar logo */
function initConstellation() {
    var canvas = document.getElementById('brand-constellation');
    if (!canvas) return;
    var ctx = canvas.getContext('2d');
    var w, h, nodes = [], linkDist = 60, nodeCount = 14, animId;

    function resize() {
        var rect = canvas.parentElement.getBoundingClientRect();
        w = canvas.width = rect.width;
        h = canvas.height = rect.height;
        // Clamp existing nodes into new bounds
        for (var i = 0; i < nodes.length; i++) {
            nodes[i].x = Math.min(nodes[i].x, w);
            nodes[i].y = Math.min(nodes[i].y, h);
        }
    }
    resize();

    for (var i = 0; i < nodeCount; i++) {
        nodes.push({
            x: Math.random() * w,
            y: Math.random() * h,
            vx: (Math.random() - 0.5) * 0.3,
            vy: (Math.random() - 0.5) * 0.3,
            r: Math.random() * 1.5 + 0.5
        });
    }

    function draw() {
        ctx.clearRect(0, 0, w, h);

        // Move nodes
        for (var i = 0; i < nodes.length; i++) {
            var n = nodes[i];
            n.x += n.vx;
            n.y += n.vy;
            if (n.x < 0 || n.x > w) n.vx *= -1;
            if (n.y < 0 || n.y > h) n.vy *= -1;
            n.x = Math.max(0, Math.min(w, n.x));
            n.y = Math.max(0, Math.min(h, n.y));
        }

        // Draw connections
        for (var i = 0; i < nodes.length; i++) {
            for (var j = i + 1; j < nodes.length; j++) {
                var dx = nodes[i].x - nodes[j].x;
                var dy = nodes[i].y - nodes[j].y;
                var dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < linkDist) {
                    var alpha = (1 - dist / linkDist) * 0.25;
                    ctx.beginPath();
                    ctx.moveTo(nodes[i].x, nodes[i].y);
                    ctx.lineTo(nodes[j].x, nodes[j].y);
                    ctx.strokeStyle = 'rgba(255,255,255,' + alpha + ')';
                    ctx.lineWidth = 0.5;
                    ctx.stroke();
                }
            }
        }

        // Draw nodes
        for (var i = 0; i < nodes.length; i++) {
            var n = nodes[i];
            ctx.beginPath();
            ctx.arc(n.x, n.y, n.r, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(255,255,255,0.35)';
            ctx.fill();
        }

        animId = requestAnimationFrame(draw);
    }

    draw();

    // Pause when sidebar is collapsed, resize after transition ends
    var observer = new MutationObserver(function() {
        var sidebar = document.getElementById('sidebar');
        if (sidebar && sidebar.classList.contains('collapsed')) {
            cancelAnimationFrame(animId);
        } else {
            // Wait for CSS transition to finish before resizing
            setTimeout(function() { resize(); }, 320);
            draw();
        }
    });
    var sidebar = document.getElementById('sidebar');
    if (sidebar) observer.observe(sidebar, { attributes: true, attributeFilter: ['class'] });

    window.addEventListener('resize', resize);

    // Pause animation when tab is hidden to save CPU
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            cancelAnimationFrame(animId);
        } else {
            var sidebar = document.getElementById('sidebar');
            if (!sidebar || !sidebar.classList.contains('collapsed')) {
                draw();
            }
        }
    });
}
