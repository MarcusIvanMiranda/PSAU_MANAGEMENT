<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - PSAU Document Tracking System</title>
    <link rel="icon" href="PSAU.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --green-950: #0d2b1e;
            --green-900: #1e5a3d;
            --green-800: #2d7a4f;
            --green-700: #3a9160;
            --green-600: #4aab72;
            --green-200: #a7d4b8;
            --green-100: #d4edd e;
            --green-50:  #eef8f2;
            --gold:      #c9a84c;
            --gold-light:#e8cc82;
            --white:     #ffffff;
            --gray-50:   #f8f9f8;
            --gray-100:  #f0f2f0;
            --gray-200:  #e2e5e2;
            --gray-400:  #9bab9e;
            --gray-600:  #586a5c;
            --gray-700:  #3d4f40;
            --gray-900:  #1a2a1c;
        }

        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--gray-50);
            min-height: 100vh;
            color: var(--gray-700);
            overflow-x: hidden;
        }

        /* ── Background ── */
        .bg-layer {
            position: fixed;
            inset: 0;
            z-index: 0;
            background:
                radial-gradient(ellipse 80% 60% at 10% 10%, rgba(30,90,61,0.09) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 90% 90%, rgba(74,171,114,0.07) 0%, transparent 55%),
                var(--gray-50);
        }

        .bg-grid {
            position: fixed;
            inset: 0;
            z-index: 0;
            background-image:
                linear-gradient(rgba(30,90,61,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(30,90,61,0.04) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        /* ── Layout ── */
        .page {
            position: relative;
            z-index: 1;
            max-width: 860px;
            margin: 0 auto;
            padding: 48px 24px 64px;
        }

        /* ── Back link ── */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8125rem;
            font-weight: 500;
            color: var(--gray-600);
            text-decoration: none;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            padding: 8px 14px 8px 10px;
            border-radius: 999px;
            border: 1px solid var(--gray-200);
            background: var(--white);
            margin-bottom: 36px;
            transition: all 0.25s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .back-link:hover {
            color: var(--green-900);
            border-color: var(--green-700);
            background: var(--green-50);
            transform: translateX(-3px);
        }
        .back-link svg { width: 14px; height: 14px; }

        /* ── Hero card ── */
        .hero {
            background: linear-gradient(145deg, var(--green-950) 0%, var(--green-900) 55%, var(--green-800) 100%);
            border-radius: 20px;
            padding: 52px 48px 48px;
            text-align: center;
            position: relative;
            overflow: hidden;
            margin-bottom: 28px;
            box-shadow: 0 24px 64px rgba(14,43,30,0.25), 0 4px 12px rgba(14,43,30,0.15);
        }

        /* Decorative ring */
        .hero::before {
            content: '';
            position: absolute;
            width: 460px; height: 460px;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.07);
            top: -180px; right: -120px;
        }
        .hero::after {
            content: '';
            position: absolute;
            width: 300px; height: 300px;
            border-radius: 50%;
            border: 1px solid rgba(201,168,76,0.15);
            bottom: -130px; left: -60px;
        }

        /* Noise overlay */
        .hero-noise {
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
            opacity: 0.4;
            pointer-events: none;
        }

        .hero-logo-wrap {
            position: relative;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 92px; height: 92px;
            background: var(--white);
            border-radius: 50%;
            padding: 6px;
            margin-bottom: 24px;
            box-shadow: 0 0 0 4px rgba(201,168,76,0.3), 0 12px 32px rgba(0,0,0,0.3);
        }
        .hero-logo-wrap img {
            width: 100%; height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .hero-eyebrow {
            position: relative;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.6875rem;
            font-weight: 600;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--gold-light);
            margin-bottom: 12px;
        }
        .hero-eyebrow span {
            display: block;
            width: 28px; height: 1px;
            background: var(--gold);
            opacity: 0.6;
        }

        .hero-title {
            position: relative;
            z-index: 2;
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.75rem, 4vw, 2.5rem);
            font-weight: 700;
            color: var(--white);
            line-height: 1.18;
            margin-bottom: 10px;
            letter-spacing: -0.01em;
        }

        .hero-sub {
            position: relative;
            z-index: 2;
            font-size: 0.9375rem;
            color: rgba(255,255,255,0.6);
            font-weight: 300;
            letter-spacing: 0.01em;
        }

        /* Gold divider */
        .gold-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0 auto 0;
            width: fit-content;
            position: relative;
            z-index: 2;
            margin-top: 28px;
        }
        .gold-divider::before,
        .gold-divider::after {
            content: '';
            display: block;
            width: 48px; height: 1px;
            background: linear-gradient(90deg, transparent, var(--gold));
        }
        .gold-divider::after {
            background: linear-gradient(90deg, var(--gold), transparent);
        }
        .gold-dot { width: 5px; height: 5px; border-radius: 50%; background: var(--gold); }

        /* ── Stats ribbon ── */
        .stats-ribbon {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2px;
            background: var(--gray-200);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 28px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .stat-cell {
            background: var(--white);
            padding: 22px 16px;
            text-align: center;
        }
        .stat-number {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--green-900);
            line-height: 1;
            margin-bottom: 4px;
        }
        .stat-label {
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--gray-400);
        }

        /* ── Section card ── */
        .card {
            background: var(--white);
            border-radius: 16px;
            border: 1px solid var(--gray-200);
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: box-shadow 0.25s ease;
        }
        .card:hover { box-shadow: 0 6px 24px rgba(0,0,0,0.09); }

        .card-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 22px 28px 18px;
            border-bottom: 1px solid var(--gray-100);
        }

        .card-icon {
            width: 38px; height: 38px;
            border-radius: 10px;
            background: var(--green-50);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .card-icon svg { width: 18px; height: 18px; color: var(--green-800); }

        .card-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.1875rem;
            font-weight: 600;
            color: var(--gray-900);
        }

        .card-body { padding: 24px 28px; }

        /* ── Overview text ── */
        .overview-text {
            font-size: 0.9375rem;
            line-height: 1.8;
            color: var(--gray-600);
            font-weight: 300;
        }
        .overview-text strong {
            color: var(--green-900);
            font-weight: 600;
        }

        /* ── Team grid ── */
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 14px;
        }
        .team-card {
            padding: 18px 16px;
            border-radius: 12px;
            border: 1px solid var(--gray-200);
            background: var(--gray-50);
            transition: all 0.22s ease;
            position: relative;
            overflow: hidden;
        }
        .team-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 3px;
            background: linear-gradient(90deg, var(--green-800), var(--green-600));
            opacity: 0;
            transition: opacity 0.22s ease;
        }
        .team-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(30,90,61,0.1); border-color: var(--green-200); }
        .team-card:hover::before { opacity: 1; }

        .team-role {
            font-size: 0.6875rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--green-700);
            margin-bottom: 6px;
        }
        .team-name {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--gray-900);
            line-height: 1.3;
        }
        .team-unit {
            font-size: 0.8125rem;
            color: var(--gray-400);
            margin-top: 4px;
            font-weight: 300;
        }

        /* ── Feature grid ── */
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
        }
        .feature-item {
            padding: 20px 18px;
            border-radius: 12px;
            border: 1px solid var(--gray-200);
            background: linear-gradient(145deg, var(--white) 0%, var(--green-50) 100%);
            transition: all 0.22s ease;
        }
        .feature-item:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(30,90,61,0.1); }

        .feature-icon {
            font-size: 1.5rem;
            margin-bottom: 10px;
            display: block;
        }
        .feature-name {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 6px;
            letter-spacing: 0.01em;
        }
        .feature-desc {
            font-size: 0.8125rem;
            color: var(--gray-600);
            line-height: 1.6;
            font-weight: 300;
        }

        /* ── Tags ── */
        .tag-list { display: flex; flex-wrap: wrap; gap: 8px; }
        .tag {
            padding: 5px 13px;
            border-radius: 999px;
            background: var(--green-50);
            border: 1px solid var(--green-200);
            color: var(--green-800);
            font-size: 0.8125rem;
            font-weight: 500;
            letter-spacing: 0.02em;
        }

        /* ── Footer ── */
        .footer {
            text-align: center;
            margin-top: 36px;
            padding: 0 16px;
        }
        .footer-inner {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 20px 32px;
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .footer-seal {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .footer-seal-line {
            width: 32px; height: 1px;
            background: var(--gray-200);
        }
        .footer-seal-text {
            font-size: 0.6875rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--gray-400);
            font-weight: 500;
        }
        .footer-copy {
            font-size: 0.8125rem;
            color: var(--gray-400);
            font-weight: 300;
        }
        .footer-copy strong { color: var(--green-900); font-weight: 600; }

        /* ── Animations ── */
        .reveal {
            opacity: 0;
            transform: translateY(18px);
            animation: revealUp 0.55s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }
        .reveal:nth-child(1)  { animation-delay: 0.05s; }
        .reveal:nth-child(2)  { animation-delay: 0.12s; }
        .reveal:nth-child(3)  { animation-delay: 0.19s; }
        .reveal:nth-child(4)  { animation-delay: 0.26s; }
        .reveal:nth-child(5)  { animation-delay: 0.33s; }
        .reveal:nth-child(6)  { animation-delay: 0.40s; }

        @keyframes revealUp {
            to { opacity: 1; transform: translateY(0); }
        }

        /* ── Responsive ── */
        @media (max-width: 640px) {
            .page { padding: 32px 16px 48px; }
            .hero { padding: 36px 24px 32px; }
            .card-header, .card-body { padding-left: 20px; padding-right: 20px; }
            .stats-ribbon { grid-template-columns: repeat(3, 1fr); }
            .stat-number { font-size: 1.375rem; }
        }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--green-600); border-radius: 99px; }
    </style>
</head>
<body>
<div class="bg-layer"></div>
<div class="bg-grid"></div>

<main class="page">



    <!-- Hero -->
    <div class="hero reveal">
        <div class="hero-noise"></div>

        <div class="hero-logo-wrap">
            <img src="PSAU_10.jpg" alt="PSAU Seal">
        </div>

        <div class="hero-eyebrow">
            <span></span> Pampanga State Agricultural University <span></span>
        </div>

        <h1 class="hero-title">Document Tracking<br>System</h1>
        <p class="hero-sub">Management Information System Unit</p>

        <div class="gold-divider">
            <div class="gold-dot"></div>
            <div class="gold-dot"></div>
            <div class="gold-dot"></div>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-ribbon reveal">
        <div class="stat-cell">
            <div class="stat-number">v1.0</div>
            <div class="stat-label">Version</div>
        </div>
        <div class="stat-cell">
            <div class="stat-number">2024</div>
            <div class="stat-label">Released</div>
        </div>
        <div class="stat-cell">
            <div class="stat-number">3</div>
            <div class="stat-label">Developers</div>
        </div>
    </div>

    <!-- Overview -->
    <div class="card reveal">
        <div class="card-header">
            <div class="card-icon">
                <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h2 class="card-title">System Overview</h2>
        </div>
        <div class="card-body">
            <p class="overview-text">
                The <strong>PSAU Document Tracking System</strong> is a comprehensive digital solution designed to streamline the management, tracking, and monitoring of official documents across all offices of the university. It ensures <strong>efficient document workflow</strong>, enhanced security, and improved accountability — fully aligned with PSAU's digital transformation initiatives and data management policies.
            </p>
        </div>
    </div>

    <!-- Team -->
    <div class="card reveal">
        <div class="card-header">
            <div class="card-icon">
                <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <h2 class="card-title">Development Team</h2>
        </div>
        <div class="card-body">
            <div class="team-grid">
                <div class="team-card">
                    <div class="team-role">Main Developer</div>
                    <div class="team-name">Arthur S. Agustin</div>
                </div>
                <div class="team-card">
                    <div class="team-role">Co-Developer</div>
                    <div class="team-name">Kenard Vincent N. Ducut</div>
                </div>
                <div class="team-card">
                    <div class="team-role">Co-Developer</div>
                    <div class="team-name">Marcus Ivan Miranda</div>
                </div>
                <div class="team-card">
                    <div class="team-role">Development Unit</div>
                    <div class="team-name">MIS Unit</div>
                    <div class="team-unit">Management Information System</div>
                </div>
                <div class="team-card">
                    <div class="team-role">Release Date</div>
                    <div class="team-name">June 10, 2024</div>
                </div>
                <div class="team-card">
                    <div class="team-role">System Version</div>
                    <div class="team-name">v1.0.0 — Stable</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features -->
    <div class="card reveal">
        <div class="card-header">
            <div class="card-icon">
                <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <h2 class="card-title">Key Features</h2>
        </div>
        <div class="card-body">
            <div class="feature-grid">
                <div class="feature-item">
                    <span class="feature-icon">📄</span>
                    <div class="feature-name">Document Management</div>
                    <div class="feature-desc">Registration, tracking, and archiving of all university documents</div>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">👥</span>
                    <div class="feature-name">User Management</div>
                    <div class="feature-desc">Role-based access control with secure authentication</div>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">📊</span>
                    <div class="feature-name">Real-time Monitoring</div>
                    <div class="feature-desc">Live tracking of document status and workflow progress</div>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">🔍</span>
                    <div class="feature-name">Advanced Search</div>
                    <div class="feature-desc">Smart filters for quick and efficient document retrieval</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tech -->
    <div class="card reveal">
        <div class="card-header">
            <div class="card-icon">
                <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <h2 class="card-title">System Standards</h2>
        </div>
        <div class="card-body">
            <p class="overview-text" style="margin-bottom: 18px;">
                Built with modern web technologies ensuring a reliable, secure, and maintainable platform for the university's document operations.
            </p>
            <div class="tag-list">
                <span class="tag">Security-First</span>
                <span class="tag">Scalable Architecture</span>
                <span class="tag">Responsive Design</span>
                <span class="tag">Role-Based Access</span>
                <span class="tag">Data Compliance</span>
                <span class="tag">PSAU Digital Roadmap</span>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer reveal">
        <div class="footer-inner">
            <div class="footer-seal">
                <div class="footer-seal-line"></div>
                <div class="footer-seal-text">Official System</div>
                <div class="footer-seal-line"></div>
            </div>
            <p class="footer-copy">
                &copy; <?php echo date('Y'); ?> <strong>Pampanga State Agricultural University</strong><br>
                Management Information System Unit &nbsp;·&nbsp; Document Tracking System
            </p>
        </div>
    </div>

</main>
</body>
</html>