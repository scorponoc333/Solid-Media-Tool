<?php
$firstName = $_SESSION['first_name'] ?? '';
$brand = (new BrandingService())->get($GLOBALS['client_id']);
$companyName = htmlspecialchars($brand['company_name'] ?? 'Your Company');
$nameGreet = $firstName ? htmlspecialchars($firstName) : 'there';
?>

<style>
/* ---- Documentation Layout ---- */
.docs-layout {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 32px;
    align-items: start;
}
@media (max-width: 1024px) {
    .docs-layout { grid-template-columns: 1fr; }
    .docs-toc-card { display: none; }
}

/* ---- Table of Contents ---- */
.docs-toc-card {
    position: sticky;
    top: 96px;
}
.docs-toc { list-style: none; padding: 0; margin: 0; }
.docs-toc li { margin-bottom: 2px; }
.docs-toc a {
    display: block;
    padding: 7px 14px;
    font-size: 13px;
    font-weight: 500;
    color: var(--text-secondary);
    border-radius: 6px;
    border-left: 2px solid transparent;
    transition: all 0.15s ease;
    text-decoration: none;
}
.docs-toc a:hover {
    color: var(--primary);
    background: rgba(var(--primary-rgb), 0.05);
}
.docs-toc a.active {
    color: var(--primary);
    background: rgba(var(--primary-rgb), 0.08);
    border-left-color: var(--primary);
    font-weight: 600;
}
.docs-toc .toc-section-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-muted);
    padding: 16px 14px 6px;
    display: block;
}
.docs-toc .toc-section-label:first-child { padding-top: 0; }

/* ---- Content ---- */
.docs-content section {
    scroll-margin-top: 96px;
}
.docs-section {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 32px;
    box-shadow: var(--shadow-sm);
    margin-bottom: 24px;
}
.docs-section h2 {
    font-size: 20px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.docs-section h2 .doc-icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: rgba(var(--primary-rgb), 0.1);
    color: var(--primary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
}
.docs-section .section-lead {
    font-size: 14px;
    color: var(--text-muted);
    margin-bottom: 20px;
    line-height: 1.5;
}
.docs-section h3 {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    margin: 20px 0 8px;
}
.docs-section p, .docs-section li {
    font-size: 14px;
    color: var(--text-secondary);
    line-height: 1.7;
}
.docs-section ul, .docs-section ol {
    padding-left: 20px;
    margin-bottom: 14px;
}
.docs-section li { margin-bottom: 6px; }
.docs-section strong { color: var(--text); }

/* Field reference table */
.field-table {
    width: 100%;
    border-collapse: collapse;
    margin: 12px 0 20px;
    font-size: 13px;
}
.field-table th {
    text-align: left;
    padding: 10px 14px;
    background: var(--bg-input);
    color: var(--text);
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    border-bottom: 1px solid var(--border);
}
.field-table td {
    padding: 10px 14px;
    border-bottom: 1px solid var(--border-light);
    color: var(--text-secondary);
    vertical-align: top;
}
.field-table tr:last-child td { border-bottom: none; }
.field-table code {
    background: var(--bg-input);
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 12px;
    font-family: 'SF Mono', 'Fira Code', monospace;
    color: var(--primary);
}

/* Tip callout */
.doc-tip {
    background: rgba(var(--primary-rgb), 0.05);
    border-left: 3px solid var(--primary);
    border-radius: 0 8px 8px 0;
    padding: 14px 18px;
    margin: 16px 0;
    font-size: 13px;
    color: var(--text-secondary);
    line-height: 1.6;
}
.doc-tip strong { color: var(--primary); }

/* Workflow diagram */
.workflow-diagram {
    width: 100%;
    overflow-x: auto;
    padding: 20px 0;
}
.workflow-diagram svg {
    display: block;
    margin: 0 auto;
    max-width: 100%;
    height: auto;
}

/* Hero banner */
.docs-hero {
    background: var(--sidebar-gradient, linear-gradient(180deg, var(--primary) 0%, #0a0a0a 60%, #000 100%));
    border-radius: var(--radius-lg);
    padding: 40px 36px;
    margin-bottom: 28px;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.docs-hero h1 {
    font-size: 26px;
    font-weight: 800;
    margin-bottom: 6px;
}
.docs-hero .hero-sub {
    font-size: 15px;
    color: rgba(255,255,255,0.6);
    line-height: 1.6;
    max-width: 600px;
}
.docs-hero .hero-version {
    position: absolute;
    top: 20px;
    right: 24px;
    background: rgba(255,255,255,0.12);
    padding: 4px 12px;
    border-radius: 100px;
    font-size: 12px;
    font-weight: 600;
    color: rgba(255,255,255,0.7);
}

/* Keyboard shortcut badge */
kbd {
    background: var(--bg-input);
    border: 1px solid var(--border);
    border-radius: 4px;
    padding: 2px 6px;
    font-size: 12px;
    font-family: inherit;
    font-weight: 600;
    color: var(--text);
}
</style>

<!-- Hero -->
<div class="docs-hero">
    <span class="hero-version">v1.0</span>
    <h1><?= $companyName ?> Social — Documentation</h1>
    <p class="hero-sub">Welcome, <?= $nameGreet ?>. Everything you need to know about creating, scheduling, and managing social media content for <?= $companyName ?>.</p>
</div>

<div class="docs-layout">
    <!-- Table of Contents -->
    <div class="docs-toc-card card" style="padding:20px">
        <ul class="docs-toc" id="docsToc">
            <li><span class="toc-section-label">Getting Started</span></li>
            <li><a href="#introduction">Introduction</a></li>
            <li><a href="#workflow">How It Works</a></li>

            <li><span class="toc-section-label">Pages</span></li>
            <li><a href="#dashboard">Dashboard</a></li>
            <li><a href="#generator">Content Generator</a></li>
            <li><a href="#posts">Posts Manager</a></li>
            <li><a href="#editor">Post Editor</a></li>
            <li><a href="#calendar">Calendar</a></li>
            <li><a href="#reporting">Reports</a></li>

            <li><span class="toc-section-label">Settings</span></li>
            <li><a href="#branding">Branding</a></li>
            <li><a href="#memory">Content Memory</a></li>

            <li><span class="toc-section-label">Reference</span></li>
            <li><a href="#statuses">Post Statuses</a></li>
            <li><a href="#platforms">Platforms</a></li>
            <li><a href="#dark-mode">Dark Mode</a></li>
        </ul>
    </div>

    <!-- Documentation Content -->
    <div class="docs-content">

        <!-- Introduction -->
        <section id="introduction" class="docs-section">
            <h2><span class="doc-icon"><i class="fas fa-rocket"></i></span> Introduction</h2>
            <p class="section-lead">A quick overview of what this platform does and who it's built for.</p>
            <p><?= $companyName ?> Social is an AI-powered content management system designed to streamline the way <?= $companyName ?> creates, schedules, and publishes social media content. Instead of spending hours drafting posts, you can generate an entire week of on-brand content in seconds, review and edit each post, then schedule them to go live across your platforms.</p>
            <h3>Key Capabilities</h3>
            <ul>
                <li><strong>AI Content Generation</strong> — Create full weeks or individual posts with a single click. The AI crafts varied, on-brand copy for <?= $companyName ?>.</li>
                <li><strong>Post Editor</strong> — Review, edit, and fine-tune every post before it goes out. Adjust captions, swap images, and set scheduling times.</li>
                <li><strong>Calendar View</strong> — See your entire content schedule laid out on a monthly calendar with color-coded status indicators.</li>
                <li><strong>Content Memory</strong> — The system tracks every topic, keyword, and angle used so your content stays fresh and never repeats.</li>
                <li><strong>Branding Control</strong> — Customize colors, logo, and personalization so the platform feels like it belongs to <?= $companyName ?>.</li>
                <li><strong>Scheduling &amp; Publishing</strong> — Connect to Zernio to schedule posts for automatic publishing at the perfect time.</li>
            </ul>
        </section>

        <!-- Workflow Diagram -->
        <section id="workflow" class="docs-section">
            <h2><span class="doc-icon"><i class="fas fa-project-diagram"></i></span> How It Works</h2>
            <p class="section-lead">The high-level content workflow from idea to published post.</p>

            <div class="workflow-diagram">
                <svg viewBox="0 0 900 280" xmlns="http://www.w3.org/2000/svg" fill="none">
                    <!-- Step boxes -->
                    <!-- 1. Generate -->
                    <rect x="10" y="80" width="140" height="120" rx="14" fill="rgba(var(--primary-rgb),0.08)" stroke="var(--primary)" stroke-width="1.5"/>
                    <text x="80" y="125" text-anchor="middle" font-family="Inter,sans-serif" font-size="13" font-weight="700" fill="var(--text)">Generate</text>
                    <text x="80" y="145" text-anchor="middle" font-family="Inter,sans-serif" font-size="11" fill="var(--text-muted)">AI creates posts</text>
                    <text x="80" y="160" text-anchor="middle" font-family="Inter,sans-serif" font-size="11" fill="var(--text-muted)">for <?= $companyName ?></text>
                    <circle cx="80" cy="60" r="18" fill="var(--primary)"/>
                    <text x="80" y="65" text-anchor="middle" font-family="Inter,sans-serif" font-size="12" font-weight="700" fill="#fff">1</text>

                    <!-- Arrow 1-2 -->
                    <line x1="155" y1="140" x2="195" y2="140" stroke="var(--primary)" stroke-width="2" stroke-dasharray="6,3"/>
                    <polygon points="195,135 205,140 195,145" fill="var(--primary)"/>

                    <!-- 2. Review -->
                    <rect x="210" y="80" width="140" height="120" rx="14" fill="rgba(var(--primary-rgb),0.08)" stroke="var(--primary)" stroke-width="1.5"/>
                    <text x="280" y="125" text-anchor="middle" font-family="Inter,sans-serif" font-size="13" font-weight="700" fill="var(--text)">Review &amp; Edit</text>
                    <text x="280" y="145" text-anchor="middle" font-family="Inter,sans-serif" font-size="11" fill="var(--text-muted)">Refine copy, swap</text>
                    <text x="280" y="160" text-anchor="middle" font-family="Inter,sans-serif" font-size="11" fill="var(--text-muted)">images, set type</text>
                    <circle cx="280" cy="60" r="18" fill="var(--primary)"/>
                    <text x="280" y="65" text-anchor="middle" font-family="Inter,sans-serif" font-size="12" font-weight="700" fill="#fff">2</text>

                    <!-- Arrow 2-3 -->
                    <line x1="355" y1="140" x2="395" y2="140" stroke="var(--primary)" stroke-width="2" stroke-dasharray="6,3"/>
                    <polygon points="395,135 405,140 395,145" fill="var(--primary)"/>

                    <!-- 3. Schedule -->
                    <rect x="410" y="80" width="140" height="120" rx="14" fill="rgba(var(--primary-rgb),0.08)" stroke="var(--primary)" stroke-width="1.5"/>
                    <text x="480" y="125" text-anchor="middle" font-family="Inter,sans-serif" font-size="13" font-weight="700" fill="var(--text)">Schedule</text>
                    <text x="480" y="145" text-anchor="middle" font-family="Inter,sans-serif" font-size="11" fill="var(--text-muted)">Set date, time,</text>
                    <text x="480" y="160" text-anchor="middle" font-family="Inter,sans-serif" font-size="11" fill="var(--text-muted)">and platform</text>
                    <circle cx="480" cy="60" r="18" fill="var(--primary)"/>
                    <text x="480" y="65" text-anchor="middle" font-family="Inter,sans-serif" font-size="12" font-weight="700" fill="#fff">3</text>

                    <!-- Arrow 3-4 -->
                    <line x1="555" y1="140" x2="595" y2="140" stroke="var(--primary)" stroke-width="2" stroke-dasharray="6,3"/>
                    <polygon points="595,135 605,140 595,145" fill="var(--primary)"/>

                    <!-- 4. Publish -->
                    <rect x="610" y="80" width="140" height="120" rx="14" fill="rgba(var(--primary-rgb),0.08)" stroke="var(--primary)" stroke-width="1.5"/>
                    <text x="680" y="125" text-anchor="middle" font-family="Inter,sans-serif" font-size="13" font-weight="700" fill="var(--text)">Publish</text>
                    <text x="680" y="145" text-anchor="middle" font-family="Inter,sans-serif" font-size="11" fill="var(--text-muted)">Goes live via</text>
                    <text x="680" y="160" text-anchor="middle" font-family="Inter,sans-serif" font-size="11" fill="var(--text-muted)">Zernio integration</text>
                    <circle cx="680" cy="60" r="18" fill="var(--primary)"/>
                    <text x="680" y="65" text-anchor="middle" font-family="Inter,sans-serif" font-size="12" font-weight="700" fill="#fff">4</text>

                    <!-- Arrow 4-5 -->
                    <line x1="755" y1="140" x2="795" y2="140" stroke="var(--primary)" stroke-width="2" stroke-dasharray="6,3"/>
                    <polygon points="795,135 805,140 795,145" fill="var(--primary)"/>

                    <!-- 5. Track -->
                    <rect x="810" y="80" width="80" height="120" rx="14" fill="rgba(var(--primary-rgb),0.08)" stroke="var(--primary)" stroke-width="1.5"/>
                    <text x="850" y="130" text-anchor="middle" font-family="Inter,sans-serif" font-size="13" font-weight="700" fill="var(--text)">Track</text>
                    <text x="850" y="150" text-anchor="middle" font-family="Inter,sans-serif" font-size="11" fill="var(--text-muted)">Reports</text>
                    <circle cx="850" cy="60" r="18" fill="var(--primary)"/>
                    <text x="850" y="65" text-anchor="middle" font-family="Inter,sans-serif" font-size="12" font-weight="700" fill="#fff">5</text>

                    <!-- Memory feedback loop -->
                    <path d="M 680 205 L 680 250 Q 680 260 670 260 L 90 260 Q 80 260 80 250 L 80 205" stroke="var(--text-muted)" stroke-width="1.5" stroke-dasharray="5,4" fill="none"/>
                    <polygon points="75,208 80,198 85,208" fill="var(--text-muted)"/>
                    <text x="380" y="255" text-anchor="middle" font-family="Inter,sans-serif" font-size="11" font-weight="600" fill="var(--text-muted)">Content Memory feeds back to keep content fresh</text>
                </svg>
            </div>

            <p>Each time content is generated, the <strong>Content Memory</strong> engine logs the topic, keywords, and angle used. This feedback loop ensures that future posts cover new ground and avoid repetition.</p>
        </section>

        <!-- Dashboard -->
        <section id="dashboard" class="docs-section">
            <h2><span class="doc-icon"><i class="fas fa-th-large"></i></span> Dashboard</h2>
            <p class="section-lead">Your home base — a snapshot of <?= $companyName ?>'s content at a glance.</p>
            <p>The Dashboard is the first screen you see after logging in. It provides a real-time summary of your content pipeline and quick links to the most common tasks.</p>

            <h3>Stat Cards</h3>
            <p>Four cards at the top display your key metrics:</p>
            <table class="field-table">
                <thead><tr><th>Card</th><th>What It Shows</th></tr></thead>
                <tbody>
                    <tr><td><strong>Total Posts</strong></td><td>The total number of posts created for <?= $companyName ?>, regardless of status.</td></tr>
                    <tr><td><strong>Scheduled</strong></td><td>Posts that have a date and time set and are waiting to be published.</td></tr>
                    <tr><td><strong>Published</strong></td><td>Posts that have been successfully published to a social platform.</td></tr>
                    <tr><td><strong>Drafts</strong></td><td>Posts saved as drafts that still need review or scheduling.</td></tr>
                </tbody>
            </table>

            <h3>Quick Actions</h3>
            <p>Three shortcut cards let you jump directly to the most-used features: the Content Generator, the Calendar, and Reports.</p>

            <h3>Recent Posts</h3>
            <p>A table of the most recently created posts, showing title, platform, type, status, and scheduled date. Click the edit icon on any row to open that post in the editor.</p>

            <div class="doc-tip"><strong>Tip:</strong> If you've just started and have no posts yet, the Dashboard will show a prompt to generate your first post — click it to go straight to the Generator.</div>
        </section>

        <!-- Content Generator -->
        <section id="generator" class="docs-section">
            <h2><span class="doc-icon"><i class="fas fa-magic"></i></span> Content Generator</h2>
            <p class="section-lead">Create on-brand content for <?= $companyName ?> using AI — one post or a full week at a time.</p>

            <h3>Generate Full Week</h3>
            <p>Click <strong>Generate Full Week</strong> to create seven days of varied social media content in one action. The AI considers <?= $companyName ?>'s brand voice and consults the Content Memory to ensure no topic is repeated.</p>
            <ul>
                <li>Posts are generated as drafts — nothing is published automatically.</li>
                <li>Each post gets a unique topic, angle, and post type (educational, promotional, engagement, etc.).</li>
                <li>Results appear as editable cards below the controls.</li>
            </ul>

            <h3>Generate Single Post</h3>
            <p>For more control, use the single-post form to specify exactly what you want:</p>
            <table class="field-table">
                <thead><tr><th>Field</th><th>Description</th></tr></thead>
                <tbody>
                    <tr><td><strong>Topic</strong></td><td>The subject of the post (e.g., "Benefits of cloud computing"). Be specific for better results.</td></tr>
                    <tr><td><strong>Post Type</strong></td><td>The tone and format: <code>Educational</code>, <code>Promotional</code>, <code>Engagement</code>, <code>Storytelling</code>, or <code>Behind the Scenes</code>.</td></tr>
                </tbody>
            </table>

            <h3>Working with Results</h3>
            <p>Each generated post appears as an editable card with these actions:</p>
            <ul>
                <li><strong>Edit title &amp; content</strong> — Click directly on the title or text area to make changes inline.</li>
                <li><strong>Regenerate Text</strong> — Ask the AI to rewrite the copy with a fresh angle while keeping the same topic.</li>
                <li><strong>Regenerate Image</strong> — Generate a new image based on the post content.</li>
                <li><strong>Save as Draft</strong> — Saves the post to your library as a draft, ready for scheduling.</li>
            </ul>
            <div class="doc-tip"><strong>Tip:</strong> You can edit the title and content before saving. Tweak the AI's output to match <?= $companyName ?>'s exact voice, then save.</div>
        </section>

        <!-- Posts Manager -->
        <section id="posts" class="docs-section">
            <h2><span class="doc-icon"><i class="fas fa-edit"></i></span> Posts Manager</h2>
            <p class="section-lead">Browse, filter, and manage all of <?= $companyName ?>'s posts in one table.</p>

            <p>The Posts page shows every post in your library, with columns for title, platform, type, status, and scheduled date.</p>

            <h3>Filtering</h3>
            <p>Use the filter bar at the top to narrow results:</p>
            <table class="field-table">
                <thead><tr><th>Filter</th><th>What It Does</th></tr></thead>
                <tbody>
                    <tr><td><strong>Status</strong></td><td>Show only posts in a specific state (Draft, Scheduled, Published, or Failed).</td></tr>
                    <tr><td><strong>Platform</strong></td><td>Filter by social network (Instagram, Facebook, LinkedIn, Twitter).</td></tr>
                    <tr><td><strong>Search</strong></td><td>Type to search by post title in real time.</td></tr>
                </tbody>
            </table>

            <h3>Actions</h3>
            <ul>
                <li><strong>New Post</strong> — Takes you to the Generator to create a new post.</li>
                <li><strong>Edit</strong> (pencil icon) — Opens the Post Editor for that post.</li>
                <li><strong>Delete</strong> (trash icon) — Removes the post after confirmation.</li>
            </ul>
        </section>

        <!-- Post Editor -->
        <section id="editor" class="docs-section">
            <h2><span class="doc-icon"><i class="fas fa-pen-fancy"></i></span> Post Editor</h2>
            <p class="section-lead">Fine-tune every detail of a post before it goes live.</p>

            <p>The editor opens with a two-column layout: the content preview on the left, and settings on the right.</p>

            <h3>Content Panel (Left)</h3>
            <table class="field-table">
                <thead><tr><th>Field</th><th>Description</th></tr></thead>
                <tbody>
                    <tr><td><strong>Image Preview</strong></td><td>Shows the current post image. If no image exists, a placeholder is displayed.</td></tr>
                    <tr><td><strong>Title</strong></td><td>The headline or hook for the post. Editable directly.</td></tr>
                    <tr><td><strong>Content</strong></td><td>The full post body/caption. Supports multi-line text editing.</td></tr>
                </tbody>
            </table>

            <h3>Settings Panel (Right)</h3>
            <table class="field-table">
                <thead><tr><th>Field</th><th>Description</th></tr></thead>
                <tbody>
                    <tr><td><strong>Platform</strong></td><td>Which social network this post is for (Instagram, Facebook, LinkedIn, Twitter).</td></tr>
                    <tr><td><strong>Post Type</strong></td><td>The content category (Educational, Promotional, Engagement, etc.).</td></tr>
                    <tr><td><strong>Status</strong></td><td>Current state of the post: Draft, Scheduled, Published, or Failed.</td></tr>
                    <tr><td><strong>Schedule Date/Time</strong></td><td>When the post should go live. Uses a date-time picker.</td></tr>
                    <tr><td><strong>Topic</strong></td><td>The subject matter — recorded by the Content Memory engine.</td></tr>
                    <tr><td><strong>Keywords</strong></td><td>Comma-separated keywords relevant to the post. Used for memory tracking.</td></tr>
                    <tr><td><strong>Angle</strong></td><td>The specific perspective or hook used (e.g., "customer success story").</td></tr>
                </tbody>
            </table>

            <h3>Editor Actions</h3>
            <ul>
                <li><strong>Save</strong> — Saves all changes without scheduling.</li>
                <li><strong>Schedule</strong> — Saves changes and submits the post for scheduled publishing via Zernio.</li>
                <li><strong>Delete</strong> — Permanently removes the post after confirmation.</li>
            </ul>
        </section>

        <!-- Calendar -->
        <section id="calendar" class="docs-section">
            <h2><span class="doc-icon"><i class="fas fa-calendar-alt"></i></span> Calendar</h2>
            <p class="section-lead">A visual overview of <?= $companyName ?>'s content schedule.</p>

            <p>The Calendar displays a month-at-a-glance view of all posts, plotted on their scheduled dates.</p>

            <h3>How to Use</h3>
            <ul>
                <li><strong>Navigate months</strong> — Use the left/right arrows or the "Today" button in the header.</li>
                <li><strong>Color-coded dots</strong> — Each post appears as a small dot on its scheduled day. Colors indicate status:
                    <ul>
                        <li><span style="color:var(--text-muted)">Grey</span> = Draft</li>
                        <li><span style="color:var(--info)">Blue</span> = Scheduled</li>
                        <li><span style="color:var(--success)">Green</span> = Published</li>
                        <li><span style="color:var(--danger)">Red</span> = Failed</li>
                    </ul>
                </li>
                <li><strong>Hover</strong> — Hover over a dot to see a tooltip with the post's thumbnail, title, and platform.</li>
                <li><strong>Click</strong> — Click a dot to open a modal with full post details and a link to the editor.</li>
            </ul>
            <div class="doc-tip"><strong>Tip:</strong> The calendar is a great way to spot gaps in your schedule. If you see empty days, head to the Generator to fill them in.</div>
        </section>

        <!-- Reporting -->
        <section id="reporting" class="docs-section">
            <h2><span class="doc-icon"><i class="fas fa-chart-bar"></i></span> Reports</h2>
            <p class="section-lead">Track content performance and patterns for <?= $companyName ?>.</p>

            <p>The Reports page gives you an analytical view of your content library.</p>

            <h3>Summary Cards</h3>
            <p>The same four stat cards as the Dashboard (Total, Scheduled, Published, Drafts) are displayed at the top for context.</p>

            <h3>Filters</h3>
            <table class="field-table">
                <thead><tr><th>Filter</th><th>Description</th></tr></thead>
                <tbody>
                    <tr><td><strong>Date Range</strong></td><td>Filter posts by their scheduled date (start and end).</td></tr>
                    <tr><td><strong>Platform</strong></td><td>Narrow to a specific social network.</td></tr>
                    <tr><td><strong>Status</strong></td><td>Show only posts in a particular state.</td></tr>
                    <tr><td><strong>Post Type</strong></td><td>Filter by content category.</td></tr>
                </tbody>
            </table>

            <h3>Topic Distribution</h3>
            <p>A visual breakdown of which topics have been covered and how many times, shown as pill badges with counts. Useful for ensuring <?= $companyName ?>'s content covers a healthy variety of subjects.</p>

            <h3>Platform Breakdown</h3>
            <p>Horizontal bar chart showing how many posts are planned or published per platform, helping you balance your social presence.</p>
        </section>

        <!-- Branding -->
        <section id="branding" class="docs-section">
            <h2><span class="doc-icon"><i class="fas fa-palette"></i></span> Branding</h2>
            <p class="section-lead">Customize the platform to look and feel like it belongs to <?= $companyName ?>.</p>

            <p>Everything set on this page applies globally across the app — the sidebar gradient, button colors, stat cards, login screen, and more.</p>

            <h3>Brand Identity</h3>
            <table class="field-table">
                <thead><tr><th>Setting</th><th>What It Controls</th></tr></thead>
                <tbody>
                    <tr><td><strong>Company Name</strong></td><td>Displayed in the browser tab, login screen (when no logo), and personalized copy throughout the app.</td></tr>
                    <tr><td><strong>Tagline</strong></td><td>A short slogan. Currently stored for future use in exports and templates.</td></tr>
                    <tr><td><strong>Primary Color</strong></td><td>The main brand color. Drives the sidebar gradient, stat cards, buttons, icon backgrounds, and all accent elements.</td></tr>
                    <tr><td><strong>Secondary Color</strong></td><td>A complementary color. Used for secondary accents and future theming options.</td></tr>
                    <tr><td><strong>Particles Effect</strong></td><td>Toggle the animated particle constellation on the login screen on or off.</td></tr>
                </tbody>
            </table>

            <h3>Logo &amp; Background</h3>
            <ul>
                <li><strong>Logo</strong> — Upload your company logo. When set, it replaces the company name text in the sidebar and login screen. Accepted formats: JPG, PNG, GIF, WebP, SVG (max 2 MB).</li>
                <li><strong>Login Background</strong> — Upload a background image for the login page. When set, it replaces the primary-color gradient. Recommended: 1920x1080 or larger.</li>
            </ul>

            <h3>Personalization</h3>
            <p>Enter your first name and it will appear in greetings and messages throughout the app (e.g., "Good morning, <?= $nameGreet ?>").</p>

            <h3>Live Preview</h3>
            <p>The preview card at the bottom of the page shows exactly how your login screen will look with the current settings. Changes to color and company name update in real time.</p>
        </section>

        <!-- Content Memory -->
        <section id="memory" class="docs-section">
            <h2><span class="doc-icon"><i class="fas fa-brain"></i></span> Content Memory</h2>
            <p class="section-lead">The engine that keeps <?= $companyName ?>'s content fresh and varied.</p>

            <p>Every time content is generated or saved, the Content Memory logs three pieces of data:</p>
            <table class="field-table">
                <thead><tr><th>Data Point</th><th>Purpose</th></tr></thead>
                <tbody>
                    <tr><td><strong>Topic</strong></td><td>The subject matter (e.g., "cloud security"). Prevents the same topic from being overused.</td></tr>
                    <tr><td><strong>Keywords</strong></td><td>Specific terms and phrases used. Helps diversify the vocabulary across posts.</td></tr>
                    <tr><td><strong>Angle</strong></td><td>The creative approach (e.g., "myth-busting", "customer testimonial"). Ensures varied perspectives.</td></tr>
                </tbody>
            </table>

            <h3>Memory Dashboard</h3>
            <ul>
                <li><strong>Stat Cards</strong> — Total memories, unique topics, and total angles used.</li>
                <li><strong>Topic Pills</strong> — Visual list of every topic with usage counts.</li>
                <li><strong>Recent Angles</strong> — The latest creative approaches used.</li>
                <li><strong>Memory Log</strong> — A table showing every memory entry with its linked post.</li>
            </ul>

            <div class="doc-tip"><strong>How it works:</strong> When the AI generates new content, it checks the memory log first. If a topic or angle has been used recently, the AI avoids it and chooses a fresh direction. This happens automatically — no action needed on your part.</div>
        </section>

        <!-- Post Statuses -->
        <section id="statuses" class="docs-section">
            <h2><span class="doc-icon"><i class="fas fa-tags"></i></span> Post Statuses</h2>
            <p class="section-lead">Understanding what each status means.</p>
            <table class="field-table">
                <thead><tr><th>Status</th><th>Color</th><th>Meaning</th></tr></thead>
                <tbody>
                    <tr><td><strong>Draft</strong></td><td><span style="color:var(--text-muted)">Grey</span></td><td>Saved but not yet scheduled. Still being worked on.</td></tr>
                    <tr><td><strong>Scheduled</strong></td><td><span style="color:var(--info)">Blue</span></td><td>Has a scheduled date and time. Waiting to be published via Zernio.</td></tr>
                    <tr><td><strong>Published</strong></td><td><span style="color:var(--success)">Green</span></td><td>Successfully posted to the target social platform.</td></tr>
                    <tr><td><strong>Failed</strong></td><td><span style="color:var(--danger)">Red</span></td><td>The publishing attempt failed. Check Zernio integration settings and retry.</td></tr>
                </tbody>
            </table>
        </section>

        <!-- Platforms -->
        <section id="platforms" class="docs-section">
            <h2><span class="doc-icon"><i class="fas fa-share-alt"></i></span> Platforms</h2>
            <p class="section-lead">Supported social networks.</p>
            <table class="field-table">
                <thead><tr><th>Platform</th><th>Notes</th></tr></thead>
                <tbody>
                    <tr><td><strong>Instagram</strong></td><td>Image-focused posts. Best with square or portrait images.</td></tr>
                    <tr><td><strong>Facebook</strong></td><td>Supports longer captions and link previews.</td></tr>
                    <tr><td><strong>LinkedIn</strong></td><td>Professional tone. Works well with educational and thought-leadership content.</td></tr>
                    <tr><td><strong>Twitter</strong></td><td>Keep it concise. Character limits apply.</td></tr>
                </tbody>
            </table>
        </section>

        <!-- Dark Mode -->
        <section id="dark-mode" class="docs-section">
            <h2><span class="doc-icon"><i class="fas fa-moon"></i></span> Dark Mode</h2>
            <p class="section-lead">Easy on the eyes, any time of day.</p>
            <p>Click the moon/sun icon in the top-right corner of the navigation bar to toggle between light and dark mode. Your preference is saved automatically and persists across sessions — even after logging out and back in.</p>
            <p>The sidebar and stat cards maintain <?= $companyName ?>'s brand colors in both modes.</p>
        </section>

    </div>
</div>

<!-- TOC Active Tracking -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tocLinks = document.querySelectorAll('.docs-toc a');
    const sections = [];

    tocLinks.forEach(link => {
        const id = link.getAttribute('href').replace('#', '');
        const section = document.getElementById(id);
        if (section) sections.push({ id, el: section, link });
    });

    function updateActive() {
        let current = sections[0];
        const offset = 120;
        for (const s of sections) {
            if (s.el.getBoundingClientRect().top <= offset) {
                current = s;
            }
        }
        tocLinks.forEach(l => l.classList.remove('active'));
        if (current) current.link.classList.add('active');
    }

    // Smooth scroll
    tocLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('href').replace('#', '');
            const target = document.getElementById(id);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                history.replaceState(null, '', '#' + id);
            }
        });
    });

    window.addEventListener('scroll', updateActive, { passive: true });
    updateActive();
});
</script>
