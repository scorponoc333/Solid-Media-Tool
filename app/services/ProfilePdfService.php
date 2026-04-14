<?php
/**
 * Generates Jason Hogan's profile as a nicely formatted HTML file
 * that can be attached to emails as an .html file or printed to PDF.
 */
class ProfilePdfService
{
    public static function generateHtml(): string
    {
        $blue = '#1a3a6b';
        $lightBlue = '#e8f0fe';

        return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jason Hogan — AI Full-Stack Developer</title>
<style>
    @import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap");
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: "Inter", Arial, sans-serif; background: #f8fafc; color: #1a1a2e; line-height: 1.6; }
    .cover { background: linear-gradient(165deg, ' . $blue . ' 0%, #0a0a1a 100%); color: #fff; padding: 80px 60px; text-align: center; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; page-break-after: always; }
    .cover h1 { font-size: 48px; font-weight: 900; margin-bottom: 8px; letter-spacing: -1px; }
    .cover .subtitle { font-size: 16px; color: rgba(255,255,255,0.5); margin-bottom: 40px; }
    .cover .tagline { font-size: 20px; color: rgba(255,255,255,0.7); max-width: 500px; line-height: 1.6; margin-bottom: 40px; }
    .cover .contact-row { display: flex; gap: 24px; flex-wrap: wrap; justify-content: center; }
    .cover .contact-item { font-size: 14px; color: rgba(255,255,255,0.5); }
    .cover .contact-item a { color: rgba(255,255,255,0.8); text-decoration: none; }
    .cover .divider { width: 60px; height: 3px; background: rgba(255,255,255,0.2); margin: 30px auto; border-radius: 2px; }
    .cover .year { font-size: 12px; color: rgba(255,255,255,0.25); margin-top: 20px; font-family: monospace; }

    .page { max-width: 700px; margin: 0 auto; padding: 50px 40px; }
    h2 { font-size: 22px; font-weight: 800; color: ' . $blue . '; margin: 32px 0 16px; padding-bottom: 8px; border-bottom: 2px solid ' . $lightBlue . '; }
    h3 { font-size: 16px; font-weight: 700; color: #1a1a2e; margin: 20px 0 8px; }
    p { font-size: 14px; color: #475569; margin-bottom: 14px; }
    .lead { font-size: 16px; color: #334155; line-height: 1.8; }

    .skills-grid { display: flex; flex-wrap: wrap; gap: 8px; margin: 16px 0 24px; }
    .skill { padding: 6px 14px; background: ' . $lightBlue . '; border: 1px solid #c8d8f0; border-radius: 6px; font-size: 12px; font-weight: 600; color: ' . $blue . '; }

    .achievement { display: flex; gap: 12px; margin-bottom: 14px; align-items: flex-start; }
    .achievement .icon { width: 28px; height: 28px; border-radius: 6px; background: ' . $lightBlue . '; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 14px; }
    .achievement .text { font-size: 13px; color: #475569; }
    .achievement .text strong { color: #1a1a2e; }

    .section-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .quote { background: ' . $lightBlue . '; border-left: 4px solid ' . $blue . '; padding: 20px 24px; border-radius: 0 10px 10px 0; margin: 20px 0; font-style: italic; color: #334155; }

    .footer { text-align: center; padding: 40px; color: #94a3b8; font-size: 12px; border-top: 1px solid #e2e8f0; margin-top: 40px; }
    .footer a { color: ' . $blue . '; text-decoration: none; }

    @media print {
        .cover { min-height: auto; padding: 60px 40px; }
        .page { padding: 30px 20px; }
    }
</style>
</head>
<body>

<!-- COVER PAGE -->
<div class="cover">
    <div class="subtitle">// DEVELOPER PROFILE //</div>
    <h1>Jason Hogan</h1>
    <div class="subtitle" style="margin-bottom:16px">AI Full-Stack Developer &bull; Automation Architect &bull; Innovation Expert</div>
    <div class="divider"></div>
    <div class="tagline">Building intelligent systems that automate business workflows, create media-rich experiences, and scale with AI-powered multi-agent orchestration.</div>
    <div class="contact-row">
        <div class="contact-item"><a href="mailto:me@jasonhogan.ca">me@jasonhogan.ca</a></div>
        <div class="contact-item"><a href="tel:+15879837066">587-983-7066</a></div>
        <div class="contact-item"><a href="https://jasonhogan.ca">jasonhogan.ca</a></div>
        <div class="contact-item"><a href="https://linkedin.com/in/jasonhogan333">LinkedIn</a></div>
    </div>
    <div class="year">' . date('Y') . ' // Edmonton, Alberta, Canada</div>
</div>

<!-- PROFILE CONTENT -->
<div class="page">

    <h2>About Jason</h2>
    <p class="lead">Jason Hogan is a rare breed of developer — a true full-stack engineer who builds everything from AI agent architectures to polished, media-rich user interfaces. Based in Edmonton, Alberta, Jason works at the intersection of artificial intelligence, web development, and multimedia production to create systems that don\'t just function — they impress.</p>
    <p>With expertise spanning code, design, video, and AI, Jason is the definition of a jack of all trades who has mastered them all. He works alongside a team of AI agents that he custom-built himself, orchestrating them to deliver enterprise-grade solutions at unprecedented speed.</p>

    <div class="quote">"I don\'t just write code — I architect intelligent systems that think, adapt, and scale. Every pixel, every API call, every animation is intentional."</div>

    <h2>Core Capabilities</h2>
    <div class="skills-grid">
        <span class="skill">Full-Stack Web Development</span>
        <span class="skill">AI Agent Development</span>
        <span class="skill">AI Automation Systems</span>
        <span class="skill">Multi-Agent Orchestration</span>
        <span class="skill">UI/UX Design</span>
        <span class="skill">Innovation & R&D</span>
        <span class="skill">Video Production</span>
        <span class="skill">AI Systems Architecture</span>
        <span class="skill">PHP / MySQL / JavaScript</span>
        <span class="skill">OpenAI / Claude / LLM APIs</span>
        <span class="skill">Multimedia Production</span>
        <span class="skill">Brand Systems</span>
    </div>

    <h2>What Jason Builds</h2>
    <div class="section-card">
        <h3>AI-Powered Business Automation</h3>
        <p>Complete systems that automate content creation, social media management, customer communications, and internal workflows. These aren\'t simple scripts — they\'re intelligent platforms with branded interfaces, role-based access, approval workflows, and real-time analytics.</p>
    </div>
    <div class="section-card">
        <h3>Multi-Agent AI Systems</h3>
        <p>Custom-built teams of AI agents that work together — generating content, creating images, analyzing data, and managing publishing schedules. Each agent has a specific role, and Jason orchestrates them into cohesive, production-ready systems.</p>
    </div>
    <div class="section-card">
        <h3>Media-Rich Web Applications</h3>
        <p>Enterprise-grade web applications with cinematic UI/UX — smooth animations, branded transitions, responsive design, and attention to detail that makes software feel premium. Built from scratch, no templates, no shortcuts.</p>
    </div>

    <h2>Notable Achievements</h2>
    <div class="achievement"><div class="icon">📚</div><div class="text"><strong>Amazon #3 Bestseller:</strong> <em>From Likes 2 Loyalty</em> — ranked in Office Automation category</div></div>
    <div class="achievement"><div class="icon">🏆</div><div class="text"><strong>3x IABC Award Winner</strong> — Award of Excellence (2015) + two Awards of Merit for digital communications and visual design</div></div>
    <div class="achievement"><div class="icon">🤖</div><div class="text"><strong>AI Agent Builders:</strong> Created an automated AI news blog with an AI avatar host — fully autonomous content pipeline</div></div>
    <div class="achievement"><div class="icon">🎓</div><div class="text"><strong>Google AI Essentials Certified</strong> (December 2025)</div></div>
    <div class="achievement"><div class="icon">👥</div><div class="text"><strong>11,000+ LinkedIn Followers</strong> — recognized voice in AI marketing and automation</div></div>
    <div class="achievement"><div class="icon">🎬</div><div class="text"><strong>Multimedia Expert:</strong> Final Cut Pro, After Effects, Photoshop, 3D modeling, audio mastering</div></div>

    <h2>Background</h2>
    <p><strong>Solid Technology Solutions Inc.</strong> — Edmonton, Alberta, Canada</p>
    <p>Jason runs Solid Technology Solutions, where he builds AI-powered tools for businesses. His approach combines deep technical skill with creative vision — every project gets the full treatment: intelligent backend, polished frontend, and cinematic user experience.</p>
    <p><strong>Education:</strong> Northern Alberta Institute of Technology (NAIT), 2003-2004</p>
    <p><strong>Languages:</strong> English (native), German (elementary), French (limited)</p>

    <h2>Get In Touch</h2>
    <div class="section-card" style="text-align:center">
        <p style="font-size:16px;color:#1a1a2e;margin-bottom:16px"><strong>Ready to build something incredible?</strong></p>
        <p>
            <a href="mailto:me@jasonhogan.ca" style="display:inline-block;padding:12px 28px;background:' . $blue . ';color:#fff;border-radius:8px;text-decoration:none;font-weight:600;margin:4px">Email Jason</a>
            <a href="tel:+15879837066" style="display:inline-block;padding:12px 28px;background:#f1f5f9;color:' . $blue . ';border-radius:8px;text-decoration:none;font-weight:600;margin:4px;border:1px solid #e2e8f0">Call: 587-983-7066</a>
        </p>
    </div>

    <div class="footer">
        <p>&copy; ' . date('Y') . ' Jason Hogan &bull; <a href="https://jasonhogan.ca">jasonhogan.ca</a> &bull; <a href="https://linkedin.com/in/jasonhogan333">LinkedIn</a></p>
        <p style="margin-top:8px;font-family:monospace;color:#cbd5e1">// Built different. //</p>
    </div>
</div>

</body>
</html>';
    }

    /**
     * Save HTML profile to a file and return the path.
     */
    public static function generateFile(): string
    {
        $html = self::generateHtml();
        $dir = UPLOAD_DIR . 'profile/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $path = $dir . 'Jason_Hogan_Profile.html';
        file_put_contents($path, $html);
        return $path;
    }
}
