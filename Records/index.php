<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

require_once 'connect.php';
$conn = new mysqli($servername, $username, $password, $dbname);

$user_result       = $conn->query("SELECT members FROM users WHERE id = " . $_SESSION['user_id']);
$current_user_data = $user_result->fetch_assoc();
$is_head           = $current_user_data['members'] === 'Head';
$is_admin          = $_SESSION['role'] === 'admin';
$user_initial      = strtoupper(substr($_SESSION['full_name'], 0, 1));
$user_name         = htmlspecialchars($_SESSION['full_name']);
$user_role         = ucfirst($_SESSION['role']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PSAU Records System</title>
    <link rel="icon" href="PSAU.ico">
    <link rel="stylesheet" href="assets/css/psau-style.css">
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
            --green-100: #d4edde;
            --green-50:  #eef8f2;
            --gold:      #c9a84c;
            --gold-light:#e8cc82;
            --white:     #ffffff;
            --gray-50:   #f8f9f8;
            --gray-100:  #f0f2f0;
            --gray-200:  #e2e5e2;
            --gray-300:  #cdd1cd;
            --gray-400:  #9bab9e;
            --gray-500:  #6d7d70;
            --gray-600:  #586a5c;
            --gray-700:  #3d4f40;
            --gray-900:  #1a2a1c;
        }

        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--gray-50);
            min-height: 100vh;
            overflow: hidden;
            color: var(--gray-700);
        }

        /* ══════════════════ LAYOUT ══════════════════ */
        .shell { display: flex; height: 100vh; }

        /* ══════════════════ SIDEBAR ══════════════════ */
        .sidebar {
            width: 256px;
            flex-shrink: 0;
            background: var(--green-950);
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 200;
            transition: transform 0.3s cubic-bezier(0.22,1,0.36,1);
            box-shadow: 4px 0 24px rgba(0,0,0,0.18);
        }

        /* Sidebar subtle grid */
        .sidebar::before {
            content: '';
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
            background-size: 32px 32px;
            pointer-events: none;
        }

        /* Sidebar brand */
        .sidebar-brand {
            padding: 24px 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            position: relative; z-index: 1;
        }
        .brand-logo-wrap {
            display: flex; align-items: center; gap: 12px; margin-bottom: 0;
        }
        .brand-logo {
            width: 44px; height: 44px; border-radius: 50%;
            background: var(--white); padding: 3px;
            box-shadow: 0 0 0 2px rgba(201,168,76,0.4), 0 4px 12px rgba(0,0,0,0.3);
            flex-shrink: 0;
        }
        .brand-logo img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
        .brand-text-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.0625rem; font-weight: 700;
            color: var(--white); line-height: 1.2;
        }
        .brand-text-sub {
            font-size: 0.6875rem; font-weight: 400;
            color: rgba(255,255,255,0.7); letter-spacing: 0.04em;
            margin-top: 2px;
        }

        /* Nav sections */
        .sidebar-nav {
            flex: 1; overflow-y: auto; padding: 16px 0 12px;
            position: relative; z-index: 1;
        }
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 99px; }

        .nav-section-label {
            padding: 10px 20px 6px;
            font-size: 0.625rem; font-weight: 700;
            letter-spacing: 0.14em; text-transform: uppercase;
            color: rgba(255,255,255,0.5);
        }

        .nav-link {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 20px;
            color: rgba(255,255,255,0.85);
            text-decoration: none; background: none; border: none;
            width: 100%; text-align: left; cursor: pointer;
            font-family: 'DM Sans', sans-serif; font-size: 0.875rem; font-weight: 500;
            border-left: 2px solid transparent;
            transition: all 0.18s ease;
            position: relative;
        }
        .nav-link:hover {
            color: rgba(255,255,255,1);
            background: rgba(255,255,255,0.08);
            border-left-color: rgba(255,255,255,0.3);
        }
        .nav-link.active {
            color: var(--white);
            background: linear-gradient(90deg, rgba(74,171,114,0.2) 0%, rgba(74,171,114,0.05) 100%);
            border-left-color: var(--green-600);
            font-weight: 600;
        }
        .nav-link.active .nav-icon { color: var(--green-600); }

        .nav-icon {
            width: 18px; height: 18px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            color: rgba(255,255,255,0.7);
            transition: color 0.18s;
        }
        .nav-icon svg { width: 16px; height: 16px; }
        .nav-link:hover .nav-icon { color: rgba(255,255,255,0.95); }

        /* Sidebar footer */
        .sidebar-footer {
            padding: 14px 20px;
            border-top: 1px solid rgba(255,255,255,0.07);
            position: relative; z-index: 1;
        }
        .sidebar-user {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 10px;
            background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.08);
            margin-bottom: 10px;
        }
        .sidebar-avatar {
            width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
            background: linear-gradient(135deg, var(--green-700), var(--green-600));
            color: #fff; font-weight: 700; font-size: 0.8125rem;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 6px rgba(30,90,61,0.4);
        }
        .sidebar-user-name { font-size: 0.8125rem; font-weight: 600; color: rgba(255,255,255,0.95); line-height: 1.2; }
        .sidebar-user-role { font-size: 0.6875rem; color: rgba(255,255,255,0.6); text-transform: uppercase; letter-spacing: 0.06em; }
        .btn-logout {
            display: flex; align-items: center; gap: 8px; width: 100%;
            padding: 9px 14px; border-radius: 9px; border: 1px solid rgba(239,68,68,0.25);
            background: rgba(239,68,68,0.08); color: rgba(239,68,68,0.7);
            font-family: 'DM Sans', sans-serif; font-size: 0.8125rem; font-weight: 600;
            cursor: pointer; transition: all 0.18s ease;
        }
        .btn-logout:hover { background: rgba(239,68,68,0.15); color: #f87171; border-color: rgba(239,68,68,0.4); }
        .btn-logout svg { width: 14px; height: 14px; }

        /* ══════════════════ MAIN ══════════════════ */
        .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }

        /* Top bar */
        .topbar {
            height: 58px; flex-shrink: 0;
            background: rgba(255,255,255,0.96); backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--gray-200);
            padding: 0 28px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 1px 6px rgba(0,0,0,0.05);
            position: relative; z-index: 100;
        }
        .topbar-left { display: flex; align-items: center; gap: 14px; }
        .mobile-toggle {
            display: none; background: none; border: 1px solid var(--gray-200);
            border-radius: 8px; padding: 7px; cursor: pointer; color: var(--gray-500);
            transition: all 0.15s;
        }
        .mobile-toggle:hover { background: var(--gray-100); color: var(--gray-700); }
        .mobile-toggle svg { width: 18px; height: 18px; display: block; }

        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem; font-weight: 700;
            color: var(--green-900);
        }

        .topbar-right { display: flex; align-items: center; gap: 16px; }
        .clock {
            font-size: 0.8125rem; font-weight: 500; color: var(--gray-400);
            letter-spacing: 0.01em;
        }
        .topbar-divider { width: 1px; height: 24px; background: var(--gray-200); }

        /* Topbar user chip */
        .user-chip {
            display: flex; align-items: center; gap: 10px;
            padding: 6px 12px 6px 6px; border-radius: 999px;
            border: 1px solid var(--gray-200); background: var(--white);
            cursor: pointer; transition: all 0.18s ease;
            position: relative;
        }
        .user-chip:hover { border-color: var(--green-200); box-shadow: 0 2px 8px rgba(30,90,61,0.08); }
        .chip-avatar {
            width: 28px; height: 28px; border-radius: 50%;
            background: linear-gradient(135deg, var(--green-800), var(--green-600));
            color: #fff; font-weight: 700; font-size: 0.75rem;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 5px rgba(30,90,61,0.3);
        }
        .chip-name { font-size: 0.8125rem; font-weight: 600; color: var(--gray-900); }
        .chip-role { font-size: 0.6875rem; color: var(--gray-400); }
        .chip-caret { color: var(--gray-400); margin-left: 2px; }
        .chip-caret svg { width: 14px; height: 14px; }

        /* Dropdown */
        .user-dropdown {
            display: none; position: absolute;
            top: calc(100% + 8px); right: 0;
            min-width: 160px;
            background: var(--white); border: 1px solid var(--gray-200);
            border-radius: 11px; box-shadow: 0 12px 32px rgba(0,0,0,0.12);
            overflow: hidden; z-index: 9999;
        }
        .user-dropdown.show { display: block; }
        .dropdown-item {
            display: flex; align-items: center; gap: 9px;
            padding: 10px 14px; font-size: 0.875rem; font-weight: 500;
            color: var(--gray-700); cursor: pointer; border: none;
            background: none; width: 100%; text-align: left;
            transition: background 0.15s, color 0.15s;
        }
        .dropdown-item:hover { background: #fef2f2; color: #dc2626; }
        .dropdown-item svg { width: 14px; height: 14px; }

        /* Content area */
        .content-area { flex: 1; padding: 20px 24px; overflow: hidden; display: flex; flex-direction: column; }

        .iframe-wrap {
            flex: 1; border-radius: 14px; overflow: hidden;
            border: 1px solid var(--gray-200);
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            position: relative;
        }
        .iframe-wrap::before {
            content: ''; position: absolute;
            top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, var(--green-900), var(--green-700), var(--green-600));
            z-index: 2;
        }
        .content-frame { width: 100%; height: 100%; border: none; display: block; transition: opacity 0.2s ease; }

        /* Mobile overlay */
        .mob-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(10,20,14,0.6); backdrop-filter: blur(2px);
            z-index: 150;
        }
        .mob-overlay.show { display: block; }

        /* ══════════════════ RESPONSIVE ══════════════════ */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed; top: 0; left: 0; height: 100vh;
                transform: translateX(-100%);
            }
            .sidebar.open { transform: translateX(0); }
            .mobile-toggle { display: flex; }
            .content-area { padding: 14px; }
            .clock { display: none; }
            .topbar-divider { display: none; }
            .chip-name, .chip-role { display: none; }
            .chip-caret { display: none; }
            .user-chip { padding: 4px; }
        }

        /* ══════════════════ Scrollbar ══════════════════ */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--green-600); border-radius: 99px; }
    </style>
</head>
<body>

<div class="mob-overlay" id="mobOverlay" onclick="closeSidebar()"></div>

<div class="shell">

    <!-- ═══ SIDEBAR ═══ -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar-brand">
            <div class="brand-logo-wrap">
                <div class="brand-logo"><img src="PSAU_10.jpg" alt="PSAU"></div>
                <div>
                    <div class="brand-text-title">PSAU Records</div>
                    <div class="brand-text-sub">Document Tracking System</div>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">

            <div class="nav-section-label">Main</div>

            <button class="nav-link active" onclick="loadPage('logbook.php', this)">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </span>
                Home
            </button>

            <button class="nav-link" onclick="loadPage('adddocument.php', this)">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                </span>
                Register Document
            </button>

            <?php if ($is_head): ?>
            <div class="nav-section-label">Management</div>

            <button class="nav-link" onclick="loadPage('documents.php', this)">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M2 12h20M12 2a15.3 15.3 0 010 20M12 2a15.3 15.3 0 000 20"/></svg>
                </span>
                Other Offices
            </button>

            <button class="nav-link" onclick="loadPage('manage_requests.php', this)">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </span>
                Manage Requests
            </button>
            <?php endif; ?>

            <?php if (!$is_admin): ?>
            <button class="nav-link" onclick="loadPage('department_documents.php', this)">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </span>
                My Office
            </button>
            <?php endif; ?>

            <div class="nav-section-label">Documents</div>

            <button class="nav-link" onclick="loadPage('viewgrid.php', this)">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                </span>
                For Releasing
            </button>

            <button class="nav-link" onclick="loadPage('viewdelivered.php', this)">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                Released
            </button>

            <button class="nav-link" onclick="loadPage('NAP/index.php', this)">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                </span>
                NAP Records
            </button>

            <?php if ($is_admin): ?>
            <div class="nav-section-label">Admin</div>

            <button class="nav-link" onclick="loadPage('usermanager.php', this)">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </span>
                User Manager
            </button>
            <?php endif; ?>

            <div class="nav-section-label">System</div>

            <button class="nav-link" onclick="loadPage('about.php', this)">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                About
            </button>

        </nav>

      
    </aside>

    <!-- ═══ MAIN ═══ -->
    <main class="main">

        <!-- Topbar -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="mobile-toggle" onclick="toggleSidebar()">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="page-title" id="pageTitle">Dashboard</h1>
            </div>
            <div class="topbar-right">
                <span class="clock" id="clock"></span>
                <div class="topbar-divider"></div>
                <div class="user-chip" id="userChip" onclick="toggleDropdown(event)">
                    <div class="chip-avatar"><?= $user_initial ?></div>
                    <div>
                        <div class="chip-name"><?= $user_name ?></div>
                        <div class="chip-role"><?= $user_role ?></div>
                    </div>
                    <div class="chip-caret">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="user-dropdown" id="userDropdown">
                        <button class="dropdown-item" onclick="window.location.href='logout.php'">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Sign Out
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="content-area">
            <div class="iframe-wrap">
                <iframe src="" class="content-frame" id="contentFrame" style="opacity:0;"></iframe>
            </div>
        </div>

    </main>
</div>

<script>
    const PAGE_TITLES = {
        'logbook.php':              'Dashboard',
        'adddocument.php':          'Register Document',
        'documents.php':            'Other Offices',
        'manage_requests.php':      'Manage Requests',
        'department_documents.php': 'My Office',
        'viewgrid.php':             'For Releasing',
        'viewdelivered.php':        'Released Documents',
        'NAP/index.php':            'NAP Records',
        'usermanager.php':          'User Manager',
        'about.php':                'About',
    };

    function loadPage(url, el) {
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        el.classList.add('active');
        localStorage.setItem('activePage', url);

        const frame = document.getElementById('contentFrame');
        frame.style.opacity = '0';
        frame.src = url;
        frame.onload = () => frame.style.opacity = '1';

        document.getElementById('pageTitle').textContent = PAGE_TITLES[url] || 'Dashboard';

        if (window.innerWidth <= 768) closeSidebar();
    }

    function restorePage() {
        const saved = localStorage.getItem('activePage') || 'logbook.php';
        const link  = document.querySelector(`.nav-link[onclick*="${saved}"]`);
        if (link) {
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            document.getElementById('pageTitle').textContent = PAGE_TITLES[saved] || 'Dashboard';
        }
        const frame = document.getElementById('contentFrame');
        frame.src = saved;
        frame.onload = () => frame.style.opacity = '1';
    }

    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('mobOverlay').classList.toggle('show');
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('mobOverlay').classList.remove('show');
    }

    function toggleDropdown(e) {
        e.stopPropagation();
        document.getElementById('userDropdown').classList.toggle('show');
    }
    document.addEventListener('click', () => {
        document.getElementById('userDropdown').classList.remove('show');
    });

    function updateClock() {
        const now = new Date();
        document.getElementById('clock').textContent = now.toLocaleDateString('en-US', {
            weekday: 'short', month: 'short', day: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    }
    updateClock();
    setInterval(updateClock, 60000);

    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) closeSidebar();
    });

    restorePage();
</script>
</body>
</html>