<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

require_once 'connect.php';
$conn = new mysqli($servername, $username, $password, $dbname);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PSAU Records System</title>
    <link rel="icon" href="PSAU.ico">
    <link rel="stylesheet" href="assets/css/psau-style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, var(--psau-gray-50) 0%, var(--psau-lighter) 100%);
            font-family: var(--font-sans);
            min-height: 100vh;
        }
        
        .app-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 280px;
            background: var(--psau-white);
            border-right: 1px solid var(--psau-gray-200);
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 100;
        }
        
        .sidebar-header {
            padding: var(--space-6);
            border-bottom: 1px solid var(--psau-gray-200);
            background: linear-gradient(135deg, var(--psau-primary) 0%, var(--psau-secondary) 100%);
            color: var(--psau-white);
            position: relative;
            overflow: hidden;
        }
        
        .sidebar-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.15"/><circle cx="20" cy="60" r="0.5" fill="white" opacity="0.15"/><circle cx="80" cy="40" r="0.5" fill="white" opacity="0.15"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
        }
        
        .sidebar-logo {
            display: flex;
            align-items: center;
            margin-bottom: var(--space-2);
            position: relative;
            z-index: 1;
        }
        
        .sidebar-logo img {
            width: 48px;
            height: 48px;
            background: var(--psau-white);
            border-radius: 50%;
            padding: var(--space-1);
            margin-right: var(--space-3);
            box-shadow: var(--shadow);
        }
        
        .sidebar-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: var(--space-1);
        }
        
        .sidebar-subtitle {
            font-size: 0.75rem;
            opacity: 0.9;
            margin: 0;
            font-weight: 400;
        }
        
        .sidebar-nav {
            flex: 1;
            padding: var(--space-4) 0;
            overflow-y: auto;
        }
        
        .nav-item {
            margin-bottom: var(--space-1);
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: var(--space-3) var(--space-6);
            color: var(--psau-gray-600);
            text-decoration: none;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
            transition: all var(--transition);
            font-size: 0.875rem;
            font-weight: 500;
            border-left: 3px solid transparent;
            position: relative;
        }
        
        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 0;
            background: linear-gradient(90deg, var(--psau-primary), var(--psau-secondary));
            transition: width var(--transition);
        }
        
        .nav-link:hover {
            background-color: var(--psau-light);
            color: var(--psau-primary);
            padding-left: var(--space-8);
        }
        
        .nav-link:hover::before {
            width: 3px;
        }
        
        .nav-link.active {
            background: linear-gradient(90deg, var(--psau-light) 0%, rgba(74, 157, 106, 0.1) 100%);
            color: var(--psau-primary);
            border-left-color: var(--psau-accent);
            font-weight: 600;
        }
        
        .nav-link.active::before {
            width: 3px;
        }
        
        .nav-icon {
            margin-right: var(--space-3);
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .top-header {
            background: var(--psau-white);
            border-bottom: 1px solid var(--psau-gray-200);
            padding: var(--space-4) var(--space-6);
            box-shadow: var(--shadow-sm);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            position: relative;
            z-index: 100;
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--space-4);
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: var(--space-4);
        }
        
        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--psau-gray-900);
            margin: 0;
            background: linear-gradient(135deg, var(--psau-primary) 0%, var(--psau-secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: var(--space-4);
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-2) var(--space-3);
            background: linear-gradient(135deg, var(--psau-gray-50) 0%, var(--psau-gray-100) 100%);
            border-radius: var(--radius-lg);
            border: 1px solid var(--psau-gray-200);
            cursor: pointer;
            position: relative;
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--psau-primary) 0%, var(--psau-secondary) 100%);
            color: var(--psau-white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.875rem;
            box-shadow: var(--shadow);
        }
        
        .user-details {
            display: flex;
            flex-direction: column;
        }
        
        .user-name {
            font-weight: 600;
            color: var(--psau-gray-900);
            font-size: 0.875rem;
            line-height: 1;
        }
        
        .user-role {
            font-size: 0.75rem;
            color: var(--psau-gray-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            line-height: 1;
        }
        
        .logout-btn {
            padding: var(--space-2) var(--space-3);
            background: linear-gradient(135deg, var(--psau-error) 0%, #b91c1c 100%);
            color: var(--psau-white);
            border: none;
            border-radius: var(--radius);
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition);
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }
        
        .logout-btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        .profile-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: var(--psau-white);
            border: 1px solid var(--psau-gray-200);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            min-width: 150px;
            z-index: 9999;
            display: none;
            overflow: hidden;
        }
        
        .profile-dropdown.show {
            display: block;
        }
        
        .profile-dropdown-item {
            padding: var(--space-2) var(--space-3);
            display: flex;
            align-items: center;
            gap: var(--space-2);
            color: var(--psau-gray-700);
            text-decoration: none;
            font-size: 0.875rem;
            transition: all var(--transition);
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
        }
        
        .profile-dropdown-item:hover {
            background: var(--psau-gray-50);
            color: var(--psau-error);
        }
        
        .content-area {
            flex: 1;
            padding: var(--space-6);
            overflow: auto;
        }
        
        .iframe-container {
            background: var(--psau-white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--psau-gray-200);
            overflow: hidden;
            height: calc(100vh - 140px);
            position: relative;
            z-index: 1;
        }
        
        .iframe-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--psau-primary) 0%, var(--psau-secondary) 50%, var(--psau-accent) 100%);
            z-index: 1;
        }
        
        .content-frame {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        /* Mobile Responsive */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--psau-gray-600);
            padding: var(--space-2);
            border-radius: var(--radius);
            transition: all var(--transition);
            order: -1;
            min-height: 44px;
            min-width: 44px;
            touch-action: manipulation;
        }
        
        .mobile-menu-toggle:hover {
            background: var(--psau-gray-100);
            color: var(--psau-primary);
        }
        
        .mobile-menu-toggle:active {
            transform: scale(0.95);
        }
        
        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity var(--transition-slow);
            touch-action: manipulation;
        }
        
        .mobile-overlay.show {
            display: block;
            opacity: 1;
        }
        
        @media (max-width: 768px) {
            .app-container {
                flex-direction: column;
            }
            
            .sidebar {
                position: fixed;
                top: 0;
                left: -280px;
                height: 100vh;
                z-index: 1000;
                transition: left var(--transition-slow);
                box-shadow: var(--shadow-xl);
            }
            
            .sidebar.open {
                left: 0;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
            
            .content-area {
                padding: var(--space-4);
            }
            
            .iframe-container {
                height: calc(100vh - 120px);
            }
            
            .page-title {
                font-size: 1.25rem;
            }
            
            .header-content {
                flex-wrap: wrap;
            }
            
            .header-left {
                flex: 1;
                min-width: 0;
            }
            
            .header-actions {
                gap: var(--space-2);
            }
            
            .user-details {
                display: none;
            }
            
            .user-avatar {
                width: 32px;
                height: 32px;
                font-size: 0.75rem;
                min-height: 32px;
                min-width: 32px;
            }
            
            .user-profile {
                min-height: 44px;
                min-width: 44px;
                touch-action: manipulation;
            }
            
            .nav-link {
                min-height: 44px;
                touch-action: manipulation;
            }
            
            .btn {
                min-height: 44px;
                min-width: 44px;
                touch-action: manipulation;
            }
            
            #currentTime {
                display: none;
            }
        }
        
        @media (max-width: 480px) {
            .page-title {
                font-size: 1.125rem;
            }
            
            .content-area {
                padding: var(--space-3);
            }
            
            .iframe-container {
                height: calc(100vh - 100px);
            }
            
            .top-header {
                padding: var(--space-3) var(--space-4);
            }
            
            .mobile-menu-toggle {
                font-size: 1.25rem;
            }
        }
        
        /* Icons */
        .icon-dashboard::before { content: "🏠"; }
        .icon-plus::before { content: "📝"; }
        .icon-globe::before { content: "🌍"; }
        .icon-clipboard::before { content: "📋"; }
        .icon-building::before { content: "🏢"; }
        .icon-outbox::before { content: "⬆️"; }
        .icon-check-circle::before { content: "✅"; }
        .icon-users::before { content: "👥"; }
        .icon-info::before { content: "ℹ️"; }
        .icon-logout::before { content: "🚪"; }
        
        /* Scrollbar Styles */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--psau-accent);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #3d7d54;
        }

        /* Loading animation */
        .loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid var(--psau-gray-300);
            border-radius: 50%;
            border-top-color: var(--psau-primary);
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Smooth transitions */
        * {
            transition: color var(--transition), background-color var(--transition), border-color var(--transition);
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobileOverlay" onclick="toggleSidebar()"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <img src="PSAU_10.jpg" alt="PSAU Logo">
                    <div>
                        <div class="sidebar-title">PSAU Records</div>
                        <div class="sidebar-subtitle">Document Tracking System</div>
                    </div>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <button class="nav-link active" onclick="loadPage('logbook.php', this)">
                        <span class="nav-icon icon-dashboard"></span>
                        Home
                    </button>
                </div>
                <div class="nav-item">
                    <button class="nav-link" onclick="loadPage('adddocument.php', this)">
                        <span class="nav-icon icon-plus"></span>
                        Register Document
                    </button>
                </div>
                <?php 
                // Get current user's members role to show/hide Other Offices link
                $user_result = $conn->query("SELECT members FROM users WHERE id = " . $_SESSION['user_id']);
                $current_user_data = $user_result->fetch_assoc();
                
                if ($current_user_data['members'] === 'Head'): 
                ?>
                <div class="nav-item">
                    <button class="nav-link" onclick="loadPage('documents.php', this)">
                        <span class="nav-icon icon-globe"></span>
                        Other Offices
                    </button>
                </div>
                <div class="nav-item">
                    <button class="nav-link" onclick="loadPage('manage_requests.php', this)">
                        <span class="nav-icon icon-clipboard"></span>
                        Manage Requests
                    </button>
                </div>
                <?php endif; ?>
                
                <?php 
                // Hide My Office link from admin accounts
                if ($_SESSION['role'] !== 'admin'): 
                ?>
                <div class="nav-item">
                    <button class="nav-link" onclick="loadPage('department_documents.php', this)">
                        <span class="nav-icon icon-building"></span>
                        My Office
                    </button>
                </div>
                <?php endif; ?>
                <div class="nav-item">
                    <button class="nav-link" onclick="loadPage('viewgrid.php', this)">
                        <span class="nav-icon icon-outbox"></span>
                        For Releasing
                    </button>
                </div>
                <div class="nav-item">
                    <button class="nav-link" onclick="loadPage('viewdelivered.php', this)">
                        <span class="nav-icon icon-check-circle"></span>
                        Released
                    </button>
                </div>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <div class="nav-item">
                    <button class="nav-link" onclick="loadPage('usermanager.php', this)">
                        <span class="nav-icon icon-users"></span>
                        User Manager
                    </button>
                </div>
                <?php endif; ?>
                <div class="nav-item">
                </div>
                <div class="nav-item">
                    <button class="nav-link" onclick="loadPage('about.php', this)">
                        <span class="nav-icon icon-info"></span>
                        About
                    </button>
                </div>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <header class="top-header">
                <div class="header-content">
                    <div class="header-left">
                        <button class="mobile-menu-toggle" onclick="toggleSidebar()">
                            ☰
                        </button>
                        <h1 class="page-title" id="pageTitle">Dashboard</h1>
                    </div>
                    <div class="header-actions">
                        <span id="currentTime" style="color: var(--psau-gray-500); font-size: 0.875rem; font-weight: 500;"></span>
                        
                        <div class="user-profile" onclick="toggleProfileDropdown(event)">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                            </div>
                            <div class="user-details">
                                <div class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                                <div class="user-role"><?php echo ucfirst($_SESSION['role']); ?></div>
                            </div>
                            <div class="profile-dropdown" id="profileDropdown">
                                <button class="profile-dropdown-item" onclick="window.location.href='logout.php'">
                                    <span class="icon-logout"></span>
                                    Logout
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <div class="content-area">
                <div class="iframe-container">
                    <iframe src="" class="content-frame" id="contentFrame" style="opacity: 0;"></iframe>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Profile dropdown toggle
        function toggleProfileDropdown(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('show');
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('profileDropdown');
            const profile = document.querySelector('.user-profile');
            
            if (!profile.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });
        
        // Page loading functionality
        function loadPage(url, element) {
            // Update active state
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            element.classList.add('active');
            
            // Save current page to localStorage
            localStorage.setItem('activePage', url);
            
            // Show loading state
            const iframe = document.getElementById('contentFrame');
            iframe.style.opacity = '0.5';
            
            // Load page
            iframe.src = url;
            
            // Update page title
            const titles = {
                'logbook.php': 'Dashboard',
                'viewgrid.php': 'For Releasing',
                'adddocument.php': 'Register Document',
                'viewdelivered.php': 'Released Documents',
                'usermanager.php': 'User Manager',
                'about.php': 'About',
            };
            
            document.getElementById('pageTitle').textContent = titles[url] || 'Dashboard';
            
            // Restore iframe opacity after load
            iframe.onload = function() {
                iframe.style.opacity = '1';
            };
            
            // Close mobile sidebar
            if (window.innerWidth <= 768) {
                document.getElementById('sidebar').classList.remove('open');
            }
        }
        
        // Restore saved page on load
        function restoreSavedPage() {
            const savedPage = localStorage.getItem('activePage');
            const defaultPage = 'logbook.php';
            const targetPage = savedPage || defaultPage;
            
            // Find the corresponding navigation link
            const navLink = document.querySelector(`.nav-link[onclick*="${targetPage}"]`);
            if (navLink) {
                // Set active state immediately
                document.querySelectorAll('.nav-link').forEach(link => {
                    link.classList.remove('active');
                });
                navLink.classList.add('active');
                
                // Update page title
                const titles = {
                    'logbook.php': 'Dashboard',
                    'viewgrid.php': 'For Releasing',
                    'adddocument.php': 'Register Document',
                    'documents.php': 'Other Offices',
                    'department_documents.php': 'My Office',
                    'manage_requests.php': 'Manage Requests',
                    'viewdelivered.php': 'Released Documents',
                    'usermanager.php': 'User Manager',
                    'about.php': 'About',
                };
                
                document.getElementById('pageTitle').textContent = titles[targetPage] || 'Dashboard';
                
                // Load the page directly without animation
                const iframe = document.getElementById('contentFrame');
                iframe.src = targetPage;
                iframe.onload = function() {
                    iframe.style.opacity = '1';
                };
            }
        }
        
        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');
            
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
        }
        
        // Clock functionality
        function updateTime() {
            const now = new Date();
            const options = { 
                weekday: 'short', 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric', 
                hour: '2-digit', 
                minute: '2-digit' 
            };
            document.getElementById('currentTime').textContent = now.toLocaleDateString('en-US', options);
        }
        
        // Initialize
        updateTime();
        setInterval(updateTime, 60000);
        
        // Restore saved page after page load
        restoreSavedPage();
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.mobile-menu-toggle');
            const overlay = document.getElementById('mobileOverlay');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !toggle.contains(event.target) && 
                sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
                overlay.classList.remove('show');
            }
        });
        
        // Handle iframe resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');
            
            if (window.innerWidth > 768) {
                sidebar.classList.remove('open');
                overlay.classList.remove('show');
            }
        });
    </script>
</body>
</html>
