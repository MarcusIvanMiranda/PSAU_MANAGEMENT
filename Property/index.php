<?php
session_start();
if (!isset($_SESSION['property_loggedin']) || $_SESSION['property_loggedin'] !== true) {
    header("location: login.php");
    exit;
}

include "connect.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get user data for sidebar
$user_initial = strtoupper(substr($_SESSION['property_full_name'], 0, 1));
$user_name = htmlspecialchars($_SESSION['property_full_name']);
$user_role = ucfirst($_SESSION['property_role']);
$user_office = htmlspecialchars($_SESSION['property_office'] ?? '');
$user_members = htmlspecialchars($_SESSION['property_members'] ?? '');
$is_admin = $_SESSION['property_role'] === 'admin';

// Handle form submission for adding new property
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_property'])) {
    // Sanitize and collect form data
    $property_no = mysqli_real_escape_string($conn, $_POST['property_no'] ?? '');
    $property_tag = mysqli_real_escape_string($conn, $_POST['property_tag'] ?? '');
    $property_item = mysqli_real_escape_string($conn, $_POST['property_item'] ?? '');
    $property_description = mysqli_real_escape_string($conn, $_POST['property_description'] ?? '');
    $property_model_number = mysqli_real_escape_string($conn, $_POST['property_model_number'] ?? '');
    $property_serial_number = mysqli_real_escape_string($conn, $_POST['property_serial_number'] ?? '');
    $property_value = mysqli_real_escape_string($conn, $_POST['property_value'] ?? '');
    $property_acquisition_date = mysqli_real_escape_string($conn, $_POST['property_acquisition_date'] ?? '');
    $property_accountable_person = mysqli_real_escape_string($conn, $_POST['property_accountable_person'] ?? '');
    $property_actual_location = mysqli_real_escape_string($conn, $_POST['property_actual_location'] ?? '');
    $property_remarks = mysqli_real_escape_string($conn, $_POST['property_remarks'] ?? '');
    $property_counted = mysqli_real_escape_string($conn, $_POST['property_counted'] ?? '');
    $property_condition = mysqli_real_escape_string($conn, $_POST['property_condition'] ?? '');
    $property_validated = mysqli_real_escape_string($conn, $_POST['property_validated'] ?? '');
    $property_status = mysqli_real_escape_string($conn, $_POST['property_status'] ?? '');
    $property_fund = mysqli_real_escape_string($conn, $_POST['property_fund'] ?? '');
    $property_year_purchased = mysqli_real_escape_string($conn, $_POST['property_year_purchased'] ?? '');
    $property_sm_group_account = mysqli_real_escape_string($conn, $_POST['property_sm_group_account'] ?? '');
    $property_gl_account = mysqli_real_escape_string($conn, $_POST['property_gl_account'] ?? '');
    $property_number = mysqli_real_escape_string($conn, $_POST['property_number'] ?? '');
    $property_loc = mysqli_real_escape_string($conn, $_POST['property_loc'] ?? '');

    // Get the next ID for idproperty_list
    $next_id_query = "SELECT MAX(idproperty_list) + 1 as next_id FROM property_list";
    $next_id_result = $conn->query($next_id_query);
    $next_id_row = $next_id_result->fetch_assoc();
    $next_id = $next_id_row['next_id'] ?? 1;

    // Insert query
    $sql = "INSERT INTO property_list (
        idproperty_list, property_no, property_tag, property_item, property_description, 
        property_model_number, property_serial_number, property_value, 
        property_acquisition_date, property_accountable_person, property_actual_location, 
        property_remarks, property_counted, property_condition, property_validated, 
        property_status, property_fund, property_year_purchased, property_sm_group_account, 
        property_gl_account, property_number, property_loc
    ) VALUES (
        $next_id, '$property_no', '$property_tag', '$property_item', '$property_description', 
        '$property_model_number', '$property_serial_number', '$property_value', 
        '$property_acquisition_date', '$property_accountable_person', '$property_actual_location', 
        '$property_remarks', '$property_counted', '$property_condition', '$property_validated', 
        '$property_status', '$property_fund', '$property_year_purchased', '$property_sm_group_account', 
        '$property_gl_account', '$property_number', '$property_loc'
    )";

    if (mysqli_query($conn, $sql)) {
        $success_message = "Property added successfully!";
        echo "<script>
            setTimeout(function() {
                window.location.href = 'index.php?success=1';
            }, 1500);
        </script>";
    } else {
        $error_message = "Error: " . mysqli_error($conn);
    }
}

// Handle success parameter from redirect
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success_message = "Property added successfully!";
}

// Handle maintenance cost submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_maintenance_cost'])) {
    $property_id = mysqli_real_escape_string($conn, $_POST['property_id'] ?? '');
    $property_tag = mysqli_real_escape_string($conn, $_POST['property_tag'] ?? '');
    $cost_type = mysqli_real_escape_string($conn, $_POST['cost_type'] ?? '');
    $cost_description = mysqli_real_escape_string($conn, $_POST['cost_description'] ?? '');
    $cost_amount = mysqli_real_escape_string($conn, $_POST['cost_amount'] ?? '0');
    $cost_date = mysqli_real_escape_string($conn, $_POST['cost_date'] ?? '');
    $performed_by = mysqli_real_escape_string($conn, $_POST['performed_by'] ?? '');
    $supplier_vendor = mysqli_real_escape_string($conn, $_POST['supplier_vendor'] ?? '');
    $invoice_reference = mysqli_real_escape_string($conn, $_POST['invoice_reference'] ?? '');
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks'] ?? '');
    $created_by = $_SESSION['property_full_name'] ?? '';
    
    // Insert maintenance cost record
    $sql = "INSERT INTO property_maintenance_costs (
        property_id, property_tag, cost_type, cost_description, cost_amount, 
        cost_date, performed_by, supplier_vendor, invoice_reference, remarks, created_by
    ) VALUES (
        $property_id, '$property_tag', '$cost_type', '$cost_description', '$cost_amount',
        '$cost_date', '$performed_by', '$supplier_vendor', '$invoice_reference', '$remarks', '$created_by'
    )";
    
    if (mysqli_query($conn, $sql)) {
        // Update addition_cost in property_list
        $update_sql = "UPDATE property_list SET 
            addition_cost = (SELECT COALESCE(SUM(cost_amount), 0) FROM property_maintenance_costs WHERE property_id = $property_id)
            WHERE idproperty_list = $property_id";
        
        mysqli_query($conn, $update_sql);
        
        $success_message = "Maintenance cost added successfully!";
        echo "<script>
            setTimeout(function() {
                window.location.href = 'index.php?maintenance_success=1';
            }, 1500);
        </script>";
    } else {
        $error_message = "Error: " . mysqli_error($conn);
    }
}

// Handle maintenance success parameter
if (isset($_GET['maintenance_success']) && $_GET['maintenance_success'] == '1') {
    $success_message = "Maintenance cost added successfully!";
}

$datatable = "property_list"; // MySQL table name
$results_per_page = 20; // number of results per page
 
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$filtertext="";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PSAU Property Management System</title>
    <link rel="icon" href="PSAU.ico">
    <link rel="stylesheet" href="style.css">
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

        <div class="sidebar-brand" onclick="document.querySelector('.nav-link').click()" style="cursor: pointer;">
            <div class="brand-logo-wrap">
                <div class="brand-logo"><img src="PSAU_10.jpg" alt="PSAU"></div>
                <div>
                    <div class="brand-text-title">PSAU Property</div>
                    <div class="brand-text-sub">Management System</div>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">

            <div class="nav-section-label">Main</div>

            <button class="nav-link active" onclick="loadPage('property_list.php', this)">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </span>
                Property List
            </button>

            
            <?php if ($is_admin): ?>
            <div class="nav-section-label">Admin</div>

            <button class="nav-link" onclick="loadPage('manage_accounts.php', this)">
                <span class="nav-icon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </span>
                Manage Accounts
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

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-avatar"><?= $user_initial ?></div>
                <div>
                    <div class="sidebar-user-name"><?= $user_name ?></div>
                    <div class="sidebar-user-role"><?= $user_role ?></div>
                </div>
            </div>
            <button class="btn-logout" onclick="window.location.href='logout.php'">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Sign Out
            </button>
        </div>
    </aside>

    <!-- ═══ MAIN ═══ -->
    <main class="main">

        <!-- Topbar -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="mobile-toggle" onclick="toggleSidebar()">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="page-title" id="pageTitle">Property List</h1>
            </div>
            <div class="topbar-right">
                <span class="clock" id="clock"></span>
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
        'property_list.php':      'Property List',
        'manage_accounts.php':     'Manage Accounts',
        'about.php':               'About',
    };

    function loadPage(url, el) {
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        el.classList.add('active');
        localStorage.setItem('activePage', url);

        const frame = document.getElementById('contentFrame');
        frame.style.opacity = '0';
        frame.src = url;
        frame.onload = () => frame.style.opacity = '1';

        document.getElementById('pageTitle').textContent = PAGE_TITLES[url] || 'Property List';

        if (window.innerWidth <= 768) closeSidebar();
    }

    function restorePage() {
        const saved = localStorage.getItem('activePage') || 'property_list.php';
        const link  = document.querySelector(`.nav-link[onclick*="${saved}"]`);
        if (link) {
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            document.getElementById('pageTitle').textContent = PAGE_TITLES[saved] || 'Property List';
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

