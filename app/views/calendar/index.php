<style>
.cal-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; }
.cal-header h2 { font-size:22px; font-weight:700; color:var(--text); min-width:220px; text-align:center; }
.cal-nav { display:flex; gap:8px; }
.cal-grid { display:grid; grid-template-columns:repeat(7,1fr); border:1px solid var(--border); border-radius:var(--radius-lg); overflow:hidden; background:var(--bg-card); box-shadow:var(--shadow-sm); }
.cal-day-header { padding:12px 8px; font-size:12px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; text-align:center; background:var(--bg-input); border-bottom:1px solid var(--border); }
.cal-cell { min-height:110px; padding:8px; border-bottom:1px solid var(--border-light); border-right:1px solid var(--border-light); position:relative; transition:background var(--transition); }
.cal-cell:nth-child(7n) { border-right:none; }
.cal-cell:hover { background:var(--bg-input); }
.cal-cell.today { background:rgba(var(--primary-rgb),0.04); }
.cal-cell.today .cal-date { background:var(--primary); color:#fff; }
.cal-cell.other-month { opacity:0.35; }
.cal-date { display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:50%; font-size:13px; font-weight:600; color:var(--text); margin-bottom:4px; }
.cal-dots { display:flex; flex-direction:column; gap:3px; }
.cal-dot { display:flex; align-items:center; gap:5px; padding:3px 6px; border-radius:6px; cursor:pointer; transition:background var(--transition); font-size:11px; font-weight:500; overflow:hidden; white-space:nowrap; text-overflow:ellipsis; }
.cal-dot:hover { filter:brightness(1.15); }
.cal-dot.status-draft { background:rgba(148,163,184,0.18); color:var(--text-muted); }
.cal-dot.status-scheduled { background:rgba(59,130,246,0.15); color:var(--info); }
.cal-dot.status-published { background:rgba(34,197,94,0.15); color:var(--success); }
.cal-dot.status-failed { background:rgba(239,68,68,0.15); color:var(--danger); }
.cal-dot .dot-circle { width:6px; height:6px; border-radius:50%; flex-shrink:0; }
.status-draft .dot-circle { background:#94a3b8; }
.status-scheduled .dot-circle { background:var(--info); }
.status-published .dot-circle { background:var(--success); }
.status-failed .dot-circle { background:var(--danger); }
.cal-tooltip { position:absolute; z-index:200; background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius-md); box-shadow:var(--shadow-lg); padding:12px; width:240px; pointer-events:none; display:none; }
.cal-tooltip.visible { display:block; }
.cal-tooltip-img { width:100%; height:100px; object-fit:cover; border-radius:var(--radius-sm); margin-bottom:8px; background:var(--bg-input); }
.cal-tooltip-title { font-size:13px; font-weight:600; color:var(--text); margin-bottom:4px; }
.cal-tooltip-meta { display:flex; align-items:center; gap:6px; }
</style>

<div class="cal-header">
    <div class="cal-nav">
        <button class="btn btn-ghost btn-sm btn-icon" id="cal-prev" title="Previous month">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>
    <h2 id="cal-title"></h2>
    <div class="cal-nav">
        <button class="btn btn-ghost btn-sm" id="cal-today">Today</button>
        <button class="btn btn-ghost btn-sm btn-icon" id="cal-next" title="Next month">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>

<div id="cal-grid-wrapper"></div>

<!-- Floating tooltip -->
<div class="cal-tooltip" id="cal-tooltip">
    <img class="cal-tooltip-img" id="cal-tooltip-img" src="" alt="">
    <div class="cal-tooltip-title" id="cal-tooltip-title"></div>
    <div class="cal-tooltip-meta" id="cal-tooltip-meta"></div>
</div>

<script>
(function() {
    var BASE = '<?= BASE_URL ?>';
    var wrapper = document.getElementById('cal-grid-wrapper');
    var titleEl = document.getElementById('cal-title');
    var tooltip = document.getElementById('cal-tooltip');

    var now = new Date();
    var viewMonth = now.getMonth() + 1;
    var viewYear = now.getFullYear();
    var postsCache = [];

    var monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    var dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

    function renderTitle() {
        titleEl.textContent = monthNames[viewMonth - 1] + ' ' + viewYear;
    }

    function postsForDay(day) {
        var results = [];
        for (var i = 0; i < postsCache.length; i++) {
            var p = postsCache[i];
            if (!p.scheduled_at) continue;
            var d = new Date(p.scheduled_at.replace(' ', 'T'));
            if (d.getDate() === day && (d.getMonth() + 1) === viewMonth && d.getFullYear() === viewYear) {
                results.push(p);
            }
        }
        return results;
    }

    function buildCalendar(posts) {
        postsCache = posts || [];

        // Build fresh grid
        var html = '<div class="cal-grid">';

        // Day headers
        for (var h = 0; h < 7; h++) {
            html += '<div class="cal-day-header">' + dayNames[h] + '</div>';
        }

        var firstDay = new Date(viewYear, viewMonth - 1, 1).getDay();
        var daysInMonth = new Date(viewYear, viewMonth, 0).getDate();
        var prevMonthDays = new Date(viewYear, viewMonth - 1, 0).getDate();
        var todayDate = now.getDate();
        var todayMonth = now.getMonth() + 1;
        var todayYear = now.getFullYear();
        var totalCells = Math.ceil((firstDay + daysInMonth) / 7) * 7;

        for (var i = 0; i < totalCells; i++) {
            var dayNum, isOther = false;

            if (i < firstDay) {
                dayNum = prevMonthDays - firstDay + i + 1;
                isOther = true;
            } else if (i >= firstDay + daysInMonth) {
                dayNum = i - firstDay - daysInMonth + 1;
                isOther = true;
            } else {
                dayNum = i - firstDay + 1;
            }

            var classes = 'cal-cell';
            if (isOther) classes += ' other-month';
            if (!isOther && dayNum === todayDate && viewMonth === todayMonth && viewYear === todayYear) {
                classes += ' today';
            }

            html += '<div class="' + classes + '">';
            html += '<div class="cal-date">' + dayNum + '</div>';

            if (!isOther) {
                var dayPosts = postsForDay(dayNum);
                if (dayPosts.length > 0) {
                    html += '<div class="cal-dots">';
                    for (var j = 0; j < dayPosts.length; j++) {
                        var p = dayPosts[j];
                        var status = p.status || 'draft';
                        var title = p.title || 'Untitled';
                        if (title.length > 22) title = title.substring(0, 22) + '...';
                        html += '<div class="cal-dot status-' + escH(status) + '" data-post-id="' + p.id + '" data-post-idx="' + j + '" data-day="' + dayNum + '">';
                        html += '<span class="dot-circle"></span>' + escH(title);
                        html += '</div>';
                    }
                    html += '</div>';
                }
            }

            html += '</div>';
        }

        html += '</div>';
        wrapper.innerHTML = html;

        // Attach event listeners
        var dots = wrapper.querySelectorAll('.cal-dot');
        for (var d = 0; d < dots.length; d++) {
            dots[d].addEventListener('mouseenter', onDotHover);
            dots[d].addEventListener('mouseleave', onDotLeave);
            dots[d].addEventListener('click', onDotClick);
        }
    }

    function getPostFromDot(dotEl) {
        var day = parseInt(dotEl.getAttribute('data-day'));
        var idx = parseInt(dotEl.getAttribute('data-post-idx'));
        var dayPosts = postsForDay(day);
        return dayPosts[idx] || null;
    }

    function onDotHover(e) {
        var post = getPostFromDot(e.currentTarget);
        if (!post) return;

        var imgEl = document.getElementById('cal-tooltip-img');
        var titleEl2 = document.getElementById('cal-tooltip-title');
        var metaEl = document.getElementById('cal-tooltip-meta');

        if (post.image_url) {
            imgEl.src = post.image_url;
            imgEl.style.display = 'block';
        } else {
            imgEl.style.display = 'none';
        }
        titleEl2.textContent = post.title || 'Untitled';
        var platformBadges = buildPlatformBadges(post);
        metaEl.innerHTML = platformBadges
                         + ' <span class="badge badge-' + (post.status || 'draft') + '">' + ucfirst(post.status || 'draft') + '</span>';

        var rect = e.currentTarget.getBoundingClientRect();
        tooltip.style.top = (rect.bottom + window.scrollY + 6) + 'px';
        tooltip.style.left = (rect.left + window.scrollX) + 'px';
        tooltip.classList.add('visible');
    }

    function onDotLeave() {
        tooltip.classList.remove('visible');
    }

    function onDotClick(e) {
        var post = getPostFromDot(e.currentTarget);
        if (!post) return;
        tooltip.classList.remove('visible');

        var imgHtml = post.image_url
            ? '<img src="' + escH(post.image_url) + '" style="width:100%;max-height:200px;object-fit:cover;border-radius:var(--radius-sm);margin-bottom:16px">'
            : '';

        var body = imgHtml
            + '<div style="margin-bottom:12px">' + buildPlatformBadges(post) + ' '
            + '<span class="badge badge-' + (post.status||'draft') + '">' + ucfirst(post.status||'draft') + '</span></div>'
            + '<div style="font-size:13px;color:var(--text-secondary);line-height:1.6;margin-bottom:12px">' + escH(post.content || 'No content') + '</div>'
            + (post.scheduled_at ? '<div class="text-muted text-small"><i class="fas fa-clock"></i> ' + formatDate(post.scheduled_at) + '</div>' : '');

        var footer = '<a href="' + BASE + '/posts/edit/' + post.id + '" class="btn btn-primary btn-sm"><i class="fas fa-pen"></i> Edit Post</a>'
                    + '<button class="btn btn-ghost btn-sm" onclick="closeModal()">Close</button>';

        openModal(post.title || 'Untitled Post', body, footer);
    }

    function loadEvents() {
        renderTitle();
        fetch(BASE + '/calendar/events?month=' + viewMonth + '&year=' + viewYear)
            .then(function(res) { return res.json(); })
            .then(function(data) {
                buildCalendar(data.posts || []);
            })
            .catch(function(err) {
                console.error('Calendar load error:', err);
                buildCalendar([]);
            });
    }

    document.getElementById('cal-prev').addEventListener('click', function() {
        viewMonth--;
        if (viewMonth < 1) { viewMonth = 12; viewYear--; }
        loadEvents();
    });

    document.getElementById('cal-next').addEventListener('click', function() {
        viewMonth++;
        if (viewMonth > 12) { viewMonth = 1; viewYear++; }
        loadEvents();
    });

    document.getElementById('cal-today').addEventListener('click', function() {
        viewMonth = now.getMonth() + 1;
        viewYear = now.getFullYear();
        loadEvents();
    });

    function buildPlatformBadges(post) {
        var platforms = [];
        if (post.platforms) {
            try { platforms = JSON.parse(post.platforms); } catch(e) {}
        }
        if (!platforms.length && post.platform) {
            platforms = [post.platform];
        }
        var html = '';
        for (var i = 0; i < platforms.length; i++) {
            html += '<span class="badge badge-' + escH(platforms[i]) + '">' + ucfirst(platforms[i]) + '</span> ';
        }
        return html || '<span class="badge">No platform</span>';
    }

    function escH(str) {
        var d = document.createElement('div');
        d.textContent = str || '';
        return d.innerHTML;
    }
    function ucfirst(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }
    function formatDate(d) {
        var dt = new Date(d.replace(' ', 'T'));
        return dt.toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric', hour:'numeric', minute:'2-digit' });
    }

    // Load on page ready
    loadEvents();
})();
</script>
