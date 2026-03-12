<?php
session_start();
if (!isset($_SESSION['property_loggedin']) || $_SESSION['property_loggedin'] !== true) {
    header("location: login.php");
    exit;
}

include "connect.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

$datatable = "property_list";
$results_per_page = 20;
 
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$filtertext="";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_property'])) {
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

    $next_id_query = "SELECT MAX(idproperty_list) + 1 as next_id FROM property_list";
    $next_id_result = $conn->query($next_id_query);
    $next_id_row = $next_id_result->fetch_assoc();
    $next_id = $next_id_row['next_id'] ?? 1;

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
        echo "<script>setTimeout(function() { window.location.href = 'property_list.php?success=1'; }, 1500);</script>";
    } else {
        $error_message = "Error: " . mysqli_error($conn);
    }
}

if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success_message = "Property added successfully!";
}

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
    
    $sql = "INSERT INTO property_maintenance_costs (
        property_id, property_tag, cost_type, cost_description, cost_amount, 
        cost_date, performed_by, supplier_vendor, invoice_reference, remarks, created_by
    ) VALUES (
        $property_id, '$property_tag', '$cost_type', '$cost_description', '$cost_amount',
        '$cost_date', '$performed_by', '$supplier_vendor', '$invoice_reference', '$remarks', '$created_by'
    )";
    
    if (mysqli_query($conn, $sql)) {
        $update_sql = "UPDATE property_list SET 
            addition_cost = (SELECT COALESCE(SUM(cost_amount), 0) FROM property_maintenance_costs WHERE property_id = $property_id)
            WHERE idproperty_list = $property_id";
        mysqli_query($conn, $update_sql);
        $success_message = "Maintenance cost added successfully!";
        echo "<script>setTimeout(function() { window.location.href = 'property_list.php?maintenance_success=1'; }, 1500);</script>";
    } else {
        $error_message = "Error: " . mysqli_error($conn);
    }
}

if (isset($_GET['maintenance_success']) && $_GET['maintenance_success'] == '1') {
    $success_message = "Maintenance cost added successfully!";
}

$filtertext = isset($_GET['filtertext']) ? trim($_GET['filtertext']) : '';
if (isset($_GET["page"])) { $page = $_GET["page"]; } else { $page=1; };
$start_from = ($page-1) * $results_per_page;

$search_condition = "";
if (!empty($filtertext)) {
    $search_condition = " WHERE (property_no LIKE '%$filtertext%' OR property_tag LIKE '%$filtertext%' OR property_item LIKE '%$filtertext%' OR property_description LIKE '%$filtertext%')";
}

$sql = "SELECT * FROM ".$datatable.$search_condition." ORDER BY property_no DESC LIMIT $start_from, ".$results_per_page;
$rs_result = $conn->query($sql);

$count_sql = "SELECT COUNT(*) AS total FROM ".$datatable.$search_condition;
$result = $conn->query($count_sql);
$row = $result->fetch_assoc();
$total_pages = ceil($row["total"] / $results_per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property List - PSAU Property Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --green-950: #052e16;
            --green-900: #14532d;
            --green-800: #166534;
            --green-700: #15803d;
            --green-600: #16a34a;
            --green-500: #22c55e;
            --green-100: #dcfce7;
            --green-50:  #f0fdf4;
            --gold:      #c9a84c;
            --gold-light:#f5e4a8;
            --gray-900:  #111827;
            --gray-700:  #374151;
            --gray-500:  #6b7280;
            --gray-300:  #d1d5db;
            --gray-100:  #f3f4f6;
            --white:     #ffffff;
            --shadow-sm: 0 1px 3px rgba(0,0,0,.08);
            --shadow:    0 4px 16px rgba(5,46,22,.10);
            --shadow-lg: 0 12px 40px rgba(5,46,22,.18);
            --radius:    10px;
            --radius-lg: 16px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #eef5f0;
            background-image:
                radial-gradient(ellipse 80% 40% at 50% -10%, rgba(21,128,61,.13) 0%, transparent 70%);
            min-height: 100vh;
            color: var(--gray-900);
            padding: 28px 32px 48px;
        }

        /* ── Page header ───────────────────────── */
        .page-header {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 28px;
        }
        .page-header-icon {
            width: 52px; height: 52px;
            background: linear-gradient(135deg, var(--green-800), var(--green-600));
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 14px rgba(21,128,61,.35);
            flex-shrink: 0;
        }
        .page-title {
            font-family: 'DM Serif Display', serif;
            font-size: 1.85rem;
            color: var(--green-900);
            line-height: 1.1;
        }
        .page-subtitle {
            font-size: .875rem;
            color: var(--gray-500);
            margin-top: 3px;
            font-weight: 400;
        }

        /* ── Toolbar ───────────────────────────── */
        .toolbar {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .search-form {
            display: flex;
            flex: 1;
            min-width: 260px;
            background: var(--white);
            border: 1.5px solid var(--gray-300);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: border-color .2s, box-shadow .2s;
        }
        .search-form:focus-within {
            border-color: var(--green-600);
            box-shadow: 0 0 0 3px rgba(22,163,74,.15);
        }
        .search-input {
            flex: 1;
            border: none;
            outline: none;
            padding: 11px 16px;
            font-family: 'DM Sans', sans-serif;
            font-size: .9rem;
            color: var(--gray-900);
            background: transparent;
        }
        .search-input::placeholder { color: var(--gray-500); }
        .search-btn {
            padding: 11px 20px;
            background: var(--green-700);
            color: var(--white);
            border: none;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            font-size: .88rem;
            font-weight: 600;
            letter-spacing: .02em;
            transition: background .2s;
            white-space: nowrap;
        }
        .search-btn:hover { background: var(--green-800); }

        .btn-add {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 11px 22px;
            background: linear-gradient(135deg, var(--green-700), var(--green-600));
            color: var(--white);
            border: none;
            border-radius: var(--radius);
            font-family: 'DM Sans', sans-serif;
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 3px 12px rgba(21,128,61,.3);
            transition: transform .15s, box-shadow .15s, background .2s;
            white-space: nowrap;
        }
        .btn-add:hover {
            background: linear-gradient(135deg, var(--green-800), var(--green-700));
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(21,128,61,.35);
        }
        .btn-add:active { transform: translateY(0); }

        /* ── Alerts ────────────────────────────── */
        .alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 18px;
            border-radius: var(--radius);
            font-size: .9rem;
            font-weight: 500;
            margin-bottom: 18px;
            animation: slideIn .3s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .alert-success {
            background: var(--green-50);
            color: var(--green-800);
            border: 1px solid #bbf7d0;
        }
        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* ── Table card ────────────────────────── */
        .table-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            overflow: hidden;
            border: 1px solid rgba(21,128,61,.08);
        }

        .table-wrapper { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: .865rem;
        }

        thead {
            background: linear-gradient(90deg, var(--green-950), var(--green-800));
            position: sticky;
            top: 0;
            z-index: 2;
        }
        thead th {
            padding: 14px 14px;
            text-align: left;
            color: rgba(255,255,255,.92);
            font-family: 'DM Sans', sans-serif;
            font-weight: 600;
            font-size: .78rem;
            letter-spacing: .06em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        thead th:first-child { padding-left: 20px; border-radius: 0; }
        thead th:last-child  { text-align: center; }
        thead th:nth-child(2)  { min-width: 180px; width: 180px; }  /* Property Tag — wider */
        thead th:nth-child(9)  { min-width: 100px; width: 100px; }  /* Accountable Person — narrower */

        tbody tr {
            border-bottom: 1px solid #e9f5ee;
            transition: background .15s;
        }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--green-50); }

        tbody td {
            padding: 16px 14px;
            color: var(--gray-700);
            vertical-align: middle;
            font-size: .875rem;
            line-height: 1.45;
        }
        tbody td:first-child { padding-left: 20px; }

        .property-no {
            font-size: .875rem;
            color: var(--gray-900);
            font-weight: 500;
        }
        .property-tag {
            font-weight: 700;
            color: var(--gray-900);
            font-size: .875rem;
        }
        .value-cell {
            font-size: .875rem;
            white-space: nowrap;
        }
        .addition-cell {
            font-size: .875rem;
            white-space: nowrap;
        }

        /* Status — plain text like screenshot */
        .status-badge {
            font-size: .875rem;
            color: var(--gray-700);
        }

        /* Tooltip */
        .tooltip { position: relative; display: inline-block; cursor: help; }
        .tooltip .tooltiptext {
            visibility: hidden;
            width: 280px;
            background: var(--gray-900);
            color: #e5e7eb;
            text-align: left;
            border-radius: 8px;
            padding: 10px 14px;
            position: absolute;
            z-index: 1000;
            bottom: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity .2s;
            box-shadow: var(--shadow-lg);
            font-size: .82rem;
            line-height: 1.5;
            pointer-events: none;
        }
        .tooltip .tooltiptext::after {
            content: "";
            position: absolute;
            top: 100%; left: 50%; margin-left: -5px;
            border: 5px solid transparent;
            border-top-color: var(--gray-900);
        }
        .tooltip:hover .tooltiptext { visibility: visible; opacity: 1; }
        .truncate-text {
            color: var(--green-700);
            text-decoration: underline dotted;
            cursor: help;
        }

        /* Action buttons */
        .action-cell {
            display: flex;
            flex-direction: column;
            gap: 5px;
            align-items: center;
        }
        .btn-view, .btn-transfer, .btn-maintenance {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100px;
            padding: 6px 0;
            border-radius: 6px;
            font-family: 'DM Sans', sans-serif;
            font-size: .8rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all .15s;
            text-decoration: none;
            white-space: nowrap;
            letter-spacing: .03em;
        }
        .btn-view {
            background: var(--green-800);
            color: var(--white);
            box-shadow: 0 2px 6px rgba(21,128,61,.25);
        }
        .btn-view:hover {
            background: var(--green-900);
            box-shadow: 0 4px 12px rgba(21,128,61,.35);
            transform: translateY(-1px);
        }
        .btn-transfer {
            background: var(--gold);
            color: var(--green-950);
            box-shadow: 0 2px 6px rgba(201,168,76,.25);
        }
        .btn-transfer:hover {
            background: #b8941f;
            box-shadow: 0 4px 12px rgba(201,168,76,.35);
            transform: translateY(-1px);
        }
        .btn-maintenance {
            background: var(--green-500);
            color: var(--white);
            box-shadow: 0 2px 6px rgba(34,197,94,.25);
        }
        .btn-maintenance:hover {
            background: var(--green-600);
            box-shadow: 0 4px 12px rgba(34,197,94,.35);
            transform: translateY(-1px);
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 64px 32px;
            color: var(--gray-500);
        }
        .empty-state-icon { font-size: 3rem; margin-bottom: 14px; opacity: .5; }
        .empty-state p { font-size: 1rem; }

        /* ── Pagination ─────────────────────────── */
        .pagination-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 20px 0 8px;
        }
        .pagination-wrap a, .pagination-wrap .pg-dots {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 8px;
            border-radius: 8px;
            font-size: .875rem;
            font-weight: 500;
            color: var(--green-800);
            text-decoration: none;
            border: 1.5px solid transparent;
            transition: all .15s;
        }
        .pagination-wrap a:hover { background: var(--green-50); border-color: #bbf7d0; }
        .pagination-wrap a.curPage {
            background: var(--green-700);
            color: var(--white);
            border-color: var(--green-700);
            font-weight: 700;
        }
        .pagination-wrap .pg-nav {
            background: var(--white);
            border: 1.5px solid var(--gray-300);
            color: var(--gray-700);
        }
        .pagination-wrap .pg-nav:hover { border-color: var(--green-600); color: var(--green-700); }
        .pg-dots { color: var(--gray-400); cursor: default; font-size: 1.1rem; }

        /* ── Modal ──────────────────────────────── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 2000;
            background: rgba(5,46,22,.45);
            backdrop-filter: blur(3px);
            animation: fadeOverlay .2s ease;
        }
        @keyframes fadeOverlay { from { opacity: 0; } to { opacity: 1; } }

        .modal-box {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            width: 90%;
            max-width: 760px;
            max-height: 92vh;
            overflow-y: auto;
            margin: 4vh auto;
            animation: slideModal .25s cubic-bezier(.34,1.56,.64,1);
            border: 1px solid rgba(21,128,61,.10);
        }
        @keyframes slideModal {
            from { opacity: 0; transform: translateY(24px) scale(.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 22px 26px 18px;
            border-bottom: 1px solid #e8f5ed;
            position: sticky;
            top: 0;
            background: var(--white);
            z-index: 1;
        }
        .modal-title-wrap { display: flex; align-items: center; gap: 12px; }
        .modal-icon {
            width: 38px; height: 38px;
            background: var(--green-50);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            border: 1.5px solid #bbf7d0;
        }
        .modal-title {
            font-family: 'DM Serif Display', serif;
            font-size: 1.3rem;
            color: var(--green-900);
        }
        .modal-close {
            width: 32px; height: 32px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 8px;
            border: none;
            background: var(--gray-100);
            color: var(--gray-500);
            font-size: 18px;
            cursor: pointer;
            transition: background .15s, color .15s;
        }
        .modal-close:hover { background: #fee2e2; color: #991b1b; }

        .modal-body { padding: 24px 26px; }

        .form-section-label {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--green-700);
            margin: 20px 0 12px;
            padding-bottom: 6px;
            border-bottom: 1.5px solid var(--green-100);
        }
        .form-section-label:first-child { margin-top: 0; }

        .form-row { display: flex; gap: 14px; }
        .form-row .form-group { flex: 1; min-width: 0; }
        .form-group { margin-bottom: 14px; }

        label {
            display: block;
            margin-bottom: 5px;
            font-size: .82rem;
            font-weight: 600;
            color: var(--gray-700);
        }
        .req { color: #ef4444; margin-left: 2px; }

        input[type="text"],
        input[type="date"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid var(--gray-300);
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .875rem;
            color: var(--gray-900);
            background: var(--white);
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }
        input:focus, textarea:focus, select:focus {
            border-color: var(--green-600);
            box-shadow: 0 0 0 3px rgba(22,163,74,.13);
        }
        textarea { resize: vertical; min-height: 72px; }
        select { cursor: pointer; }

        .modal-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px 26px 22px;
            border-top: 1px solid #e8f5ed;
        }
        .btn-cancel {
            padding: 9px 20px;
            background: var(--white);
            color: var(--gray-700);
            border: 1.5px solid var(--gray-300);
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .9rem;
            font-weight: 500;
            cursor: pointer;
            transition: background .15s;
        }
        .btn-cancel:hover { background: var(--gray-100); }
        .btn-submit {
            padding: 9px 24px;
            background: linear-gradient(135deg, var(--green-700), var(--green-600));
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 3px 10px rgba(21,128,61,.28);
            transition: all .15s;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, var(--green-800), var(--green-700));
            box-shadow: 0 5px 16px rgba(21,128,61,.36);
            transform: translateY(-1px);
        }

        /* Scrollbar */
        .modal-box::-webkit-scrollbar { width: 6px; }
        .modal-box::-webkit-scrollbar-track { background: transparent; }
        .modal-box::-webkit-scrollbar-thumb { background: var(--gray-300); border-radius: 99px; }
    </style>
</head>
<body>

    <!-- Page header -->
    <div class="page-header">
        <div class="page-header-icon">🏛️</div>
        <div>
            <div class="page-title">Property Inventory</div>
            <div class="page-subtitle">PSAU Property Management System &mdash; Asset Registry</div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar">
        <form class="search-form" action="property_list.php" method="GET">
            <input 
                type="text" 
                name="filtertext" 
                class="search-input" 
                placeholder="Search by Property No, Tag, Item, or Description…" 
                value="<?php echo isset($_GET['filtertext']) ? htmlspecialchars($_GET['filtertext']) : ''; ?>"
            >
            <button type="submit" class="search-btn">🔍 Search</button>
        </form>
        <button onclick="openAddPropertyModal()" class="btn-add">
            ➕ Add Property
        </button>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success">✅ <?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <div class="alert alert-error">⚠️ <?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Pagination top -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination-wrap">
        <?php if ($page > 1): ?>
            <a href="property_list.php?filtertext=<?php echo urlencode($filtertext); ?>&page=<?php echo $page - 1; ?>" class="pg-nav">‹</a>
        <?php endif; ?>
        <?php
        $max_pages = 10;
        $start_page = max(1, $page - floor($max_pages / 2));
        $end_page = min($total_pages, $start_page + $max_pages - 1);
        if ($end_page - $start_page < $max_pages - 1) $start_page = max(1, $end_page - $max_pages + 1);
        if ($start_page > 1): ?>
            <a href="property_list.php?filtertext=<?php echo urlencode($filtertext); ?>&page=1" class="<?php if (1==$page) echo 'curPage'; ?>">1</a>
            <?php if ($start_page > 2): ?><span class="pg-dots">…</span><?php endif; ?>
        <?php endif; ?>
        <?php for ($i=$start_page; $i<=$end_page; $i++): ?>
            <a href="property_list.php?filtertext=<?php echo urlencode($filtertext); ?>&page=<?php echo $i; ?>" class="<?php if ($i==$page) echo 'curPage'; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        <?php if ($end_page < $total_pages): ?>
            <?php if ($end_page < $total_pages - 1): ?><span class="pg-dots">…</span><?php endif; ?>
            <a href="property_list.php?filtertext=<?php echo urlencode($filtertext); ?>&page=<?php echo $total_pages; ?>" class="<?php if ($total_pages==$page) echo 'curPage'; ?>"><?php echo $total_pages; ?></a>
        <?php endif; ?>
        <?php if ($page < $total_pages): ?>
            <a href="property_list.php?filtertext=<?php echo urlencode($filtertext); ?>&page=<?php echo $page + 1; ?>" class="pg-nav">›</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="table-card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Property #</th>
                        <th>Tag</th>
                        <th>Item</th>
                        <th>Description</th>
                        <th>Serial No.</th>
                        <th>Value</th>
                        <th>Add. Cost</th>
                        <th>Acquired</th>
                        <th>Accountable Person</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rs_result && $rs_result->num_rows > 0): ?>
                        <?php while($row = $rs_result->fetch_assoc()): ?>
                        <tr>
                            <td><span class="property-no"><?php echo htmlspecialchars($row["property_no"]); ?></span></td>
                            <td><span class="property-tag"><?php echo htmlspecialchars($row["property_tag"]); ?></span></td>
                            <td><?php echo htmlspecialchars($row["property_item"]); ?></td>
                            <td><?php 
                                $description = htmlspecialchars($row["property_description"] ?? '');
                                if (strlen($description) > 22) {
                                    echo '<div class="tooltip"><span class="truncate-text">' . substr($description, 0, 22) . '…</span><span class="tooltiptext">' . $description . '</span></div>';
                                } else { echo $description; }
                            ?></td>
                            <td style="font-size:.82rem;color:var(--gray-500);"><?php echo htmlspecialchars($row["property_serial_number"] ?? ''); ?></td>
                            <td class="value-cell">₱<?php 
                                $v = str_replace([',',' '], '', $row["property_value"] ?? '0');
                                echo is_numeric($v) ? number_format((float)$v, 2) : htmlspecialchars($row["property_value"]);
                            ?></td>
                            <td class="addition-cell">₱<?php 
                                $c = str_replace([',',' '], '', $row["addition_cost"] ?? '0');
                                echo is_numeric($c) ? number_format((float)$c, 2) : htmlspecialchars($row["addition_cost"]);
                            ?></td>
                            <td style="font-size:.84rem;"><?php echo htmlspecialchars($row["property_acquisition_date"] ?? ''); ?></td>
                            <td style="text-align:center;font-size:.84rem;word-break:break-word;max-width:100px;"><?php echo htmlspecialchars($row["property_accountable_person"] ?? ''); ?></td>
                            <td>
                                <?php echo htmlspecialchars($row["property_status"] ?? ''); ?>
                            </td>
                            <td><?php 
                                $remarks = htmlspecialchars($row["property_remarks"] ?? '');
                                if (strlen($remarks) > 22) {
                                    echo '<div class="tooltip"><span class="truncate-text">' . substr($remarks, 0, 22) . '…</span><span class="tooltiptext">' . $remarks . '</span></div>';
                                } else { echo $remarks; }
                            ?></td>
                            <td>
                                <div class="action-cell">
                                    <form action="propertydocument.php" method="GET" style="display:inline;">
                                        <button type="submit" name="filtertext" value="<?php echo $row['property_tag']; ?>" class="btn-view">View</button>
                                    </form>
                                    <a href="transfer_property.php?property_id=<?php echo urlencode($row['idproperty_list']); ?>" class="btn-transfer">Transfer</a>
                                    <a href="maintenance_costs.php?property_tag=<?php echo urlencode($row['property_tag']); ?>" class="btn-maintenance">Repair</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="12">
                                <div class="empty-state">
                                    <div class="empty-state-icon">🗂️</div>
                                    <p><?php if (!empty($filtertext)): ?>
                                        No properties found matching "<strong><?php echo htmlspecialchars($filtertext); ?></strong>"
                                    <?php else: ?>
                                        No properties found in the system.
                                    <?php endif; ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination bottom -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination-wrap">
        <?php if ($page > 1): ?>
            <a href="property_list.php?filtertext=<?php echo urlencode($filtertext); ?>&page=<?php echo $page - 1; ?>" class="pg-nav">‹</a>
        <?php endif; ?>
        <?php
        $start_page = max(1, $page - floor($max_pages / 2));
        $end_page = min($total_pages, $start_page + $max_pages - 1);
        if ($end_page - $start_page < $max_pages - 1) $start_page = max(1, $end_page - $max_pages + 1);
        if ($start_page > 1): ?>
            <a href="property_list.php?filtertext=<?php echo urlencode($filtertext); ?>&page=1" class="<?php if (1==$page) echo 'curPage'; ?>">1</a>
            <?php if ($start_page > 2): ?><span class="pg-dots">…</span><?php endif; ?>
        <?php endif; ?>
        <?php for ($i=$start_page; $i<=$end_page; $i++): ?>
            <a href="property_list.php?filtertext=<?php echo urlencode($filtertext); ?>&page=<?php echo $i; ?>" class="<?php if ($i==$page) echo 'curPage'; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        <?php if ($end_page < $total_pages): ?>
            <?php if ($end_page < $total_pages - 1): ?><span class="pg-dots">…</span><?php endif; ?>
            <a href="property_list.php?filtertext=<?php echo urlencode($filtertext); ?>&page=<?php echo $total_pages; ?>" class="<?php if ($total_pages==$page) echo 'curPage'; ?>"><?php echo $total_pages; ?></a>
        <?php endif; ?>
        <?php if ($page < $total_pages): ?>
            <a href="property_list.php?filtertext=<?php echo urlencode($filtertext); ?>&page=<?php echo $page + 1; ?>" class="pg-nav">›</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>


    <!-- ══ Add Property Modal ══════════════════════════════════════ -->
    <div id="addPropertyModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <div class="modal-icon">🏷️</div>
                    <span class="modal-title">Add New Property</span>
                </div>
                <button class="modal-close" onclick="closeAddPropertyModal()">✕</button>
            </div>
            <form method="POST" action="" id="addPropertyForm">
                <input type="hidden" name="add_property" value="1">
                <div class="modal-body">

                    <div class="form-section-label">Identification</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Property No <span class="req">*</span></label>
                            <input type="text" name="property_no" required>
                        </div>
                        <div class="form-group">
                            <label>Property Tag</label>
                            <input type="text" name="property_tag">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Property Item <span class="req">*</span></label>
                        <input type="text" name="property_item" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="property_description"></textarea>
                    </div>

                    <div class="form-section-label">Technical Details</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Model Number</label>
                            <input type="text" name="property_model_number">
                        </div>
                        <div class="form-group">
                            <label>Serial Number</label>
                            <input type="text" name="property_serial_number">
                        </div>
                    </div>

                    <div class="form-section-label">Acquisition</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Property Value</label>
                            <input type="text" name="property_value">
                        </div>
                        <div class="form-group">
                            <label>Acquisition Date</label>
                            <input type="date" name="property_acquisition_date">
                        </div>
                    </div>

                    <div class="form-section-label">Assignment</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Accountable Person</label>
                            <input type="text" name="property_accountable_person">
                        </div>
                        <div class="form-group">
                            <label>Actual Location</label>
                            <input type="text" name="property_actual_location">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea name="property_remarks" style="min-height:54px;"></textarea>
                    </div>

                    <div class="form-section-label">Classification</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Counted</label>
                            <select name="property_counted">
                                <option value="">Select…</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Condition</label>
                            <select name="property_condition">
                                <option value="">Select…</option>
                                <option value="Good">Good</option>
                                <option value="Fair">Fair</option>
                                <option value="Poor">Poor</option>
                                <option value="Damaged">Damaged</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Validated</label>
                            <select name="property_validated">
                                <option value="">Select…</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="property_status">
                                <option value="">Select…</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Disposed">Disposed</option>
                                <option value="Lost">Lost</option>
                            </select>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeAddPropertyModal()" class="btn-cancel">Cancel</button>
                    <button type="submit" class="btn-submit">➕ Add Property</button>
                </div>
            </form>
        </div>
    </div>


    <!-- ══ Maintenance Cost Modal ══════════════════════════════════ -->
    <div id="maintenanceCostModal" class="modal-overlay">
        <div class="modal-box" style="max-width:640px;">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <div class="modal-icon">🔧</div>
                    <span class="modal-title">Add Repair / Maintenance Cost</span>
                </div>
                <button class="modal-close" onclick="closeMaintenanceCostModal()">✕</button>
            </div>
            <form method="POST" action="" id="maintenanceCostForm">
                <input type="hidden" name="add_maintenance_cost" value="1">
                <input type="hidden" id="maintenance_property_id" name="property_id">
                <input type="hidden" id="maintenance_property_tag" name="property_tag">
                <div class="modal-body">

                    <div class="form-section-label">Cost Details</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Cost Type <span class="req">*</span></label>
                            <select id="maintenance_cost_type" name="cost_type" required>
                                <option value="">Select Type…</option>
                                <option value="repair">Repair</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="replace">Replace Parts</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Cost Amount (₱) <span class="req">*</span></label>
                            <input type="number" id="maintenance_cost_amount" name="cost_amount" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description <span class="req">*</span></label>
                        <textarea id="maintenance_cost_description" name="cost_description" required></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Cost Date <span class="req">*</span></label>
                            <input type="date" id="maintenance_cost_date" name="cost_date" required>
                        </div>
                        <div class="form-group">
                            <label>Performed By</label>
                            <input type="text" id="maintenance_performed_by" name="performed_by">
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeMaintenanceCostModal()" class="btn-cancel">Cancel</button>
                    <button type="submit" class="btn-submit">🔧 Add Cost</button>
                </div>
            </form>
        </div>
    </div>


    <script>
    function openAddPropertyModal() {
        document.getElementById('addPropertyModal').style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
    function closeAddPropertyModal() {
        document.getElementById('addPropertyModal').style.display = 'none';
        document.body.style.overflow = 'auto';
        document.getElementById('addPropertyForm').reset();
    }
    function openMaintenanceCostModal(propertyId, propertyTag) {
        document.getElementById('maintenance_property_id').value = propertyId;
        document.getElementById('maintenance_property_tag').value = propertyTag;
        document.getElementById('maintenanceCostModal').style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
    function closeMaintenanceCostModal() {
        document.getElementById('maintenanceCostModal').style.display = 'none';
        document.body.style.overflow = 'auto';
        document.getElementById('maintenanceCostForm').reset();
    }
    window.onclick = function(event) {
        if (event.target === document.getElementById('addPropertyModal')) closeAddPropertyModal();
        if (event.target === document.getElementById('maintenanceCostModal')) closeMaintenanceCostModal();
    };
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') { closeAddPropertyModal(); closeMaintenanceCostModal(); }
    });
    window.onload = function() {
        var p = new URLSearchParams(window.location.search);
        if (p.has('success') || p.has('maintenance_success')) {
            closeAddPropertyModal();
            closeMaintenanceCostModal();
            window.history.replaceState({}, '', window.location.pathname);
        }
    };
    </script>
</body>
</html>