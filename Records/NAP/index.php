<?php
require_once '../connect.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

$success_message = '';
$error_message = '';

$current_user_full_name = '';
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_result = $conn->query("SELECT full_name FROM users WHERE id = $user_id");
    if ($user_result) {
        $user_data = $user_result->fetch_assoc();
        $current_user_full_name = $user_data['full_name'] ?? '';
    }
}

$current_date = date('Y-m-d');

$offices_result = $conn->query("SELECT id, office_name FROM offices ORDER BY office_name");
$offices = [];
if ($offices_result) while ($row = $offices_result->fetch_assoc()) $offices[] = $row;

$sub_offices_result = $conn->query("SELECT office_id, sub_name FROM sub_offices ORDER BY office_id, sub_name");
$sub_offices = [];
if ($sub_offices_result) while ($row = $sub_offices_result->fetch_assoc()) $sub_offices[$row['office_id']][] = $row['sub_name'];

// ── SAVE HEADER ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_header'])) {
    $name_of_office      = trim($_POST['name_of_office'] ?? '');
    $department_division = trim($_POST['department_division'] ?? '');
    $section_unit        = trim($_POST['section_unit'] ?? '');
    $telephone_no        = trim($_POST['telephone_no'] ?? '');
    $email_address       = trim($_POST['email_address'] ?? '');
    $address             = trim($_POST['address'] ?? '');
    $person_incharge     = trim($_POST['person_incharge'] ?? '');
    $date_prepared       = trim($_POST['date_prepared'] ?? '');

    if ($name_of_office && $department_division && $section_unit && $address && $person_incharge && $date_prepared) {
        $stmt = $conn->prepare("INSERT INTO nap_headers (name_of_office, department_division, section_unit, telephone_no, email_address, address, person_incharge, date_prepared) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssssss", $name_of_office, $department_division, $section_unit, $telephone_no, $email_address, $address, $person_incharge, $date_prepared);
        if ($stmt->execute()) {
            $_SESSION['active_header_id'] = $stmt->insert_id;
            $success_message = "Header saved! Now add record rows below.";
        } else {
            $error_message = "Error saving header: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Please fill all required header fields.";
    }
}

if (isset($_GET['select_header'])) {
    $_SESSION['active_header_id'] = (int)$_GET['select_header'];
    header("Location: index.php");
    exit;
}

// ── ADD RECORD ROW ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_record'])) {
    $header_id = (int)($_POST['header_id'] ?? 0);
    if (!$header_id) {
        $error_message = "No header selected.";
    } else {
        $records_series_title    = trim($_POST['records_series_title'] ?? '');
        $records_description     = trim($_POST['records_description'] ?? '');
        $period_covered_from     = trim($_POST['period_covered_from'] ?? '');
        $volume                  = trim($_POST['volume'] ?? '');
        $records_medium          = trim($_POST['records_medium'] ?? '');
        $restrictions            = trim($_POST['restrictions'] ?? '');
        $location_of_records     = trim($_POST['location_of_records'] ?? '');
        $request_frequency       = trim($_POST['request_frequency'] ?? '');
        $duplication_value       = trim($_POST['duplication_value'] ?? '');
        $time_value              = trim($_POST['time_value'] ?? '');
        $utility_value           = trim($_POST['utility_value'] ?? '');
        $retention_period_active = trim($_POST['retention_period_active'] ?? '');
        $retention_period_storage= trim($_POST['retention_period_storage'] ?? '');
        $retention_period_total  = trim($_POST['retention_period_total'] ?? '');
        $disposition_provision   = trim($_POST['disposition_provision'] ?? '');

        if ($records_series_title && $period_covered_from && $volume && $records_medium && $retention_period_active && $disposition_provision) {
            $stmt = $conn->prepare("INSERT INTO nap_records (header_id, records_series_title, records_description, period_covered_from, volume, records_medium, restrictions, location_of_records, request_frequency, duplication_value, time_value, utility_value, retention_period_active, retention_period_storage, retention_period_total, disposition_provision) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("isssssssssssssss",
                $header_id, $records_series_title, $records_description, $period_covered_from,
                $volume, $records_medium, $restrictions, $location_of_records, $request_frequency,
                $duplication_value, $time_value, $utility_value,
                $retention_period_active, $retention_period_storage, $retention_period_total, $disposition_provision
            );
            if ($stmt->execute()) {
                $success_message = "Record row added successfully!";
            } else {
                $error_message = "Error adding record: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "Please fill all required fields (marked with *).";
        }
    }
}

if (isset($_GET['delete_record'])) {
    $del_id = (int)$_GET['delete_record'];
    $conn->query("DELETE FROM nap_records WHERE id = $del_id");
    header("Location: index.php");
    exit;
}

if (isset($_GET['delete_header'])) {
    $del_id = (int)$_GET['delete_header'];
    $conn->query("DELETE FROM nap_records WHERE header_id = $del_id");
    $conn->query("DELETE FROM nap_headers WHERE id = $del_id");
    if (isset($_SESSION['active_header_id']) && $_SESSION['active_header_id'] == $del_id) unset($_SESSION['active_header_id']);
    header("Location: index.php");
    exit;
}

$active_header_id = $_SESSION['active_header_id'] ?? 0;
$active_header = null;
if ($active_header_id) {
    $r = $conn->query("SELECT * FROM nap_headers WHERE id = $active_header_id");
    if ($r) $active_header = $r->fetch_assoc();
}

$active_records = [];
if ($active_header_id) {
    $r = $conn->query("SELECT * FROM nap_records WHERE header_id = $active_header_id ORDER BY created_at ASC");
    if ($r) while ($row = $r->fetch_assoc()) $active_records[] = $row;
}

$headers_result = $conn->query("SELECT h.*, COUNT(r.id) as record_count FROM nap_headers h LEFT JOIN nap_records r ON r.header_id = h.id GROUP BY h.id ORDER BY h.created_at DESC");
$all_headers = [];
if ($headers_result) while ($row = $headers_result->fetch_assoc()) $all_headers[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>NAP Records – Entry System</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --green-dark:   #1a5c38;
    --green-main:   #1e7a47;
    --green-mid:    #2d9e5f;
    --green-light:  #e8f5ee;
    --green-border: #b7dfc8;
    --green-hover:  #f0faf4;
    --white:        #ffffff;
    --gray-50:      #f8fafb;
    --gray-100:     #f1f4f6;
    --gray-200:     #e2e8ed;
    --gray-300:     #c8d3db;
    --gray-400:     #9eadb8;
    --gray-500:     #6b7f8c;
    --gray-700:     #374a55;
    --gray-900:     #1a2830;
    --red:          #dc2626;
    --shadow-sm:    0 1px 3px rgba(0,0,0,0.07);
    --shadow-md:    0 4px 12px rgba(0,0,0,0.10);
    --radius:       8px;
    --radius-lg:    12px;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Inter', sans-serif; background: var(--gray-100); color: var(--gray-900); min-height: 100vh; font-size: 14px; }

/* ── TOPNAV ── */
.topnav {
    background: var(--green-dark);
    padding: 0 24px;
    display: flex; align-items: center; gap: 10px;
    height: 56px; position: sticky; top: 0; z-index: 200;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}
.topnav .logo { font-weight: 700; font-size: 15px; color: #fff; display: flex; align-items: center; gap: 8px; margin-right: auto; }
.topnav .logo small { font-weight: 400; font-size: 11px; color: rgba(255,255,255,0.6); }
.nav-btn {
    padding: 7px 15px; font-size: 12px; font-weight: 500; font-family: inherit;
    background: rgba(255,255,255,0.13); color: #fff;
    border: 1px solid rgba(255,255,255,0.22); border-radius: 6px;
    text-decoration: none; cursor: pointer; transition: background 0.15s;
}
.nav-btn:hover { background: rgba(255,255,255,0.22); }
.nav-btn.green { background: var(--green-mid); border-color: var(--green-mid); }
.nav-btn.green:hover { background: #27ae60; }

/* ── LAYOUT ── */
.layout { display: grid; grid-template-columns: 272px 1fr; min-height: calc(100vh - 56px); }

/* ── SIDEBAR ── */
.sidebar {
    background: var(--white); border-right: 1px solid var(--gray-200);
    display: flex; flex-direction: column;
    position: sticky; top: 56px; height: calc(100vh - 56px); overflow: hidden;
}
.sb-top { padding: 14px 14px 12px; border-bottom: 1px solid var(--gray-200); background: var(--gray-50); }
.sb-top h4 { font-size: 10px; font-weight: 700; color: var(--gray-500); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
.btn-new {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    width: 100%; padding: 9px; background: var(--green-main); color: #fff;
    border: none; border-radius: var(--radius); font-size: 13px; font-weight: 600;
    font-family: inherit; cursor: pointer; transition: background 0.15s;
}
.btn-new:hover { background: var(--green-dark); }
.sb-list { overflow-y: auto; flex: 1; padding: 10px; }
.sb-empty { padding: 28px 12px; text-align: center; color: var(--gray-400); font-size: 12px; line-height: 1.6; }

.hcard {
    padding: 10px 12px; background: var(--white);
    border: 1.5px solid var(--gray-200); border-radius: var(--radius);
    cursor: pointer; transition: all 0.15s; margin-bottom: 7px;
}
.hcard:hover { border-color: var(--green-mid); background: var(--green-hover); }
.hcard.active { border-color: var(--green-main); background: var(--green-light); }
.hc-name { font-size: 11px; font-weight: 700; color: var(--green-dark); margin-bottom: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.hc-dept { font-size: 11px; color: var(--gray-700); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.hc-unit { font-size: 10px; color: var(--gray-400); margin-top: 1px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.hc-foot { display: flex; align-items: center; gap: 5px; margin-top: 8px; }
.badge { font-size: 10px; padding: 2px 7px; border-radius: 20px; font-weight: 600; }
.bg-green { background: var(--green-light); color: var(--green-dark); border: 1px solid var(--green-border); }
.bg-gray  { background: var(--gray-100);   color: var(--gray-500);  border: 1px solid var(--gray-200); }
.hc-acts { display: flex; gap: 4px; margin-left: auto; }
.hc-acts a { font-size: 10px; padding: 2px 7px; border-radius: 4px; text-decoration: none; font-weight: 500; transition: all 0.12s; }
.act-view { background: var(--green-light); color: var(--green-dark); border: 1px solid var(--green-border); }
.act-view:hover { background: var(--green-main); color: #fff; border-color: var(--green-main); }
.act-del  { background: #fef2f2; color: var(--red); border: 1px solid #fecaca; }
.act-del:hover  { background: var(--red); color: #fff; border-color: var(--red); }

/* ── MAIN ── */
.main { padding: 22px; }

/* ── ALERTS ── */
.alert { padding: 11px 16px; border-radius: var(--radius); font-size: 13px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
.alert-ok  { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
.alert-err { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

/* ── ACTIVE BANNER ── */
.active-banner {
    background: var(--green-light); border: 1.5px solid var(--green-border);
    border-radius: var(--radius-lg); padding: 14px 18px; margin-bottom: 18px;
}
.ab-label { font-size: 10px; font-weight: 700; color: var(--green-dark); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 10px; display: flex; align-items: center; gap: 6px; }
.ab-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px 18px; }
.ab-item .lbl { font-size: 10px; color: var(--green-mid); font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 2px; }
.ab-item .val { font-size: 13px; color: var(--green-dark); font-weight: 600; }

/* ── CARD ── */
.card { background: var(--white); border: 1px solid var(--gray-200); border-radius: var(--radius-lg); margin-bottom: 18px; box-shadow: var(--shadow-sm); overflow: hidden; }
.card-hd { padding: 13px 18px; background: var(--gray-50); border-bottom: 1px solid var(--gray-200); display: flex; align-items: center; gap: 10px; }
.card-hd h3 { font-size: 14px; font-weight: 600; }
.card-hd .hd-sub { font-size: 12px; color: var(--gray-400); margin-left: auto; }
.card-bd { padding: 20px; }

.step-dot {
    width: 26px; height: 26px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; flex-shrink: 0;
    background: var(--green-main); color: #fff;
}
.step-dot.done { background: var(--green-mid); }

/* ── FORM ── */
.fsec-label {
    font-size: 10px; font-weight: 700; color: var(--green-dark);
    text-transform: uppercase; letter-spacing: 0.9px;
    padding-bottom: 8px; border-bottom: 2px solid var(--green-light);
    margin-bottom: 14px;
}
.fg { display: flex; flex-direction: column; gap: 5px; }
.fg label { font-size: 11px; font-weight: 600; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.3px; }
.fg label .req { color: var(--red); }
.fg input, .fg select {
    padding: 8px 11px; background: var(--white); border: 1.5px solid var(--gray-200);
    border-radius: 6px; color: var(--gray-900); font-size: 13px; font-family: inherit;
    transition: border-color 0.15s, box-shadow 0.15s; width: 100%;
}
.fg input:focus, .fg select:focus { outline: none; border-color: var(--green-main); box-shadow: 0 0 0 3px rgba(30,122,71,0.09); }
.fg input[readonly] { background: var(--gray-50); color: var(--gray-400); cursor: default; }

.grid4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
.grid3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
.grid6 { display: grid; grid-template-columns: repeat(6, 1fr); gap: 14px; }
.s2 { grid-column: span 2; }
.s3 { grid-column: span 3; }
.s4 { grid-column: span 4; }
.sf { grid-column: 1 / -1; }

.divider { height: 1px; background: var(--gray-200); margin: 16px 0; }

/* ── BUTTONS ── */
.btn-primary {
    padding: 9px 20px; background: var(--green-main); color: #fff; border: none;
    border-radius: 7px; font-size: 13px; font-weight: 600; font-family: inherit;
    cursor: pointer; transition: all 0.15s; display: inline-flex; align-items: center; gap: 6px;
}
.btn-primary:hover { background: var(--green-dark); box-shadow: 0 4px 12px rgba(30,122,71,0.25); }
.btn-add {
    padding: 9px 20px; background: var(--green-mid); color: #fff; border: none;
    border-radius: 7px; font-size: 13px; font-weight: 600; font-family: inherit;
    cursor: pointer; transition: all 0.15s; display: inline-flex; align-items: center; gap: 6px;
}
.btn-add:hover { background: var(--green-main); box-shadow: 0 4px 12px rgba(30,122,71,0.25); }
.btn-ghost {
    padding: 8px 16px; background: #fff; color: var(--green-dark);
    border: 1.5px solid var(--green-border); border-radius: 7px;
    font-size: 12px; font-weight: 600; font-family: inherit; cursor: pointer;
    text-decoration: none; transition: all 0.15s; display: inline-flex; align-items: center; gap: 6px;
}
.btn-ghost:hover { background: var(--green-light); }

/* ── TABLE ── */
.tbl-scroll { overflow-x: auto; }
.rtbl { width: 100%; border-collapse: collapse; font-size: 12px; }
.rtbl thead tr { background: var(--green-dark); }
.rtbl th {
    padding: 9px 10px; text-align: left; font-size: 10px; font-weight: 600;
    color: rgba(255,255,255,0.88); letter-spacing: 0.3px; white-space: nowrap;
    border-right: 1px solid rgba(255,255,255,0.08);
}
.rtbl th:last-child { border-right: none; }
.rtbl td {
    padding: 9px 10px; border-bottom: 1px solid var(--gray-100);
    vertical-align: middle; color: var(--gray-700);
    border-right: 1px solid var(--gray-100);
}
.rtbl td:last-child { border-right: none; }
.rtbl tbody tr:hover td { background: var(--green-hover); }
.rnum { color: var(--gray-400); font-size: 11px; text-align: center; }
.rt-title { font-weight: 600; font-size: 12px; color: var(--gray-900); }
.rt-desc  { font-size: 10px; color: var(--gray-400); margin-top: 2px; }
.ret-val  { font-family: monospace; font-size: 11px; color: var(--gray-500); }
.tp { font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 4px; }
.tp-t { background: #fef3c7; color: #92400e; }
.tp-p { background: #dbeafe; color: #1e40af; }
.btn-rm {
    padding: 3px 9px; background: #fef2f2; color: var(--red);
    border: 1px solid #fecaca; border-radius: 4px; font-size: 11px;
    font-weight: 500; text-decoration: none; transition: all 0.12s; white-space: nowrap;
}
.btn-rm:hover { background: var(--red); color: #fff; border-color: var(--red); }

/* ── NO HEADER ── */
.no-hdr {
    background: #fffbeb; border: 2px dashed #fcd34d;
    border-radius: var(--radius-lg); padding: 48px; text-align: center;
}
.no-hdr .icon { font-size: 44px; margin-bottom: 12px; }
.no-hdr h4 { font-size: 17px; font-weight: 700; color: #92400e; margin-bottom: 6px; }
.no-hdr p  { font-size: 13px; color: #b45309; line-height: 1.6; }

/* ── EMPTY STATE ── */
.empty { padding: 44px; text-align: center; }
.empty .icon { font-size: 38px; margin-bottom: 10px; }
.empty p { font-size: 13px; color: var(--gray-400); }

/* ── MODAL ── */
.modal-wrap {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.4); z-index: 300;
    align-items: center; justify-content: center;
}
.modal-wrap.open { display: flex; }
.modal {
    background: var(--white); border-radius: var(--radius-lg);
    width: 680px; max-width: 96vw; max-height: 92vh; overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    animation: up 0.2s ease;
}
@keyframes up { from { transform: translateY(18px); opacity: 0; } to { transform: none; opacity: 1; } }
.modal-top {
    padding: 16px 20px; background: var(--green-dark); color: #fff;
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    display: flex; align-items: center; justify-content: space-between;
}
.modal-top h3 { font-size: 15px; font-weight: 700; }
.modal-x { background: none; border: none; color: #fff; font-size: 22px; cursor: pointer; line-height: 1; }
.modal-bd { padding: 22px; }
.modal-ft { padding: 14px 22px; border-top: 1px solid var(--gray-200); display: flex; gap: 10px; }
</style>
</head>
<body>

<!-- TOPNAV -->
<div class="topnav">
    <div class="logo">📋 NAP Records <small>Inventory &amp; Appraisal System</small></div>
    <?php if ($active_header): ?>
        <a href="records_table.php?header_id=<?= $active_header_id ?>" class="nav-btn green" target="_blank">🖨 Print Active Group</a>
    <?php endif; ?>
    <a href="records_table.php" class="nav-btn" target="_blank">📄 View All</a>
</div>

<div class="layout">

    <!-- ══ SIDEBAR ══ -->
    <div class="sidebar">
        <div class="sb-top">
            <h4>Header Groups</h4>
            <button class="btn-new" onclick="document.getElementById('mNew').classList.add('open')">+ New Header</button>
        </div>
        <div class="sb-list">
            <?php if (empty($all_headers)): ?>
                <div class="sb-empty">No headers yet.<br>Click "New Header" to create one.</div>
            <?php endif; ?>
            <?php foreach ($all_headers as $h): ?>
            <div class="hcard <?= $h['id'] == $active_header_id ? 'active' : '' ?>"
                 onclick="location='index.php?select_header=<?= $h['id'] ?>'">
                <div class="hc-name"><?= htmlspecialchars($h['name_of_office']) ?></div>
                <div class="hc-dept"><?= htmlspecialchars($h['department_division']) ?></div>
                <div class="hc-unit"><?= htmlspecialchars($h['section_unit']) ?></div>
                <div class="hc-foot">
                    <span class="badge bg-green"><?= $h['record_count'] ?> row<?= $h['record_count'] != 1 ? 's' : '' ?></span>
                    <span class="badge bg-gray"><?= $h['date_prepared'] ?></span>
                    <div class="hc-acts" onclick="event.stopPropagation()">
                        <a href="records_table.php?header_id=<?= $h['id'] ?>" class="act-view" target="_blank">View</a>
                        <a href="?delete_header=<?= $h['id'] ?>" class="act-del"
                           onclick="return confirm('Delete header and ALL its records?')">Del</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ══ MAIN ══ -->
    <div class="main">

        <?php if ($success_message): ?>
            <div class="alert alert-ok">✓ <?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-err">✕ <?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <?php if ($active_header): ?>

        <!-- Active Header Banner -->
        <div class="active-banner">
            <div class="ab-label"><span style="color:var(--green-main)">✓</span> Active Header — records below belong to this group</div>
            <div class="ab-grid">
                <div class="ab-item"><div class="lbl">1. Name of Office</div><div class="val"><?= htmlspecialchars($active_header['name_of_office']) ?></div></div>
                <div class="ab-item"><div class="lbl">2. Dept / Division</div><div class="val"><?= htmlspecialchars($active_header['department_division']) ?></div></div>
                <div class="ab-item"><div class="lbl">3. Section / Unit</div><div class="val"><?= htmlspecialchars($active_header['section_unit']) ?></div></div>
                <div class="ab-item"><div class="lbl">4. Telephone</div><div class="val"><?= htmlspecialchars($active_header['telephone_no'] ?: '—') ?></div></div>
                <div class="ab-item"><div class="lbl">5. Email</div><div class="val"><?= htmlspecialchars($active_header['email_address'] ?: '—') ?></div></div>
                <div class="ab-item"><div class="lbl">6. Address</div><div class="val"><?= htmlspecialchars($active_header['address']) ?></div></div>
                <div class="ab-item"><div class="lbl">7. Person-in-Charge</div><div class="val"><?= htmlspecialchars($active_header['person_incharge']) ?></div></div>
                <div class="ab-item"><div class="lbl">8. Date Prepared</div><div class="val"><?= htmlspecialchars($active_header['date_prepared']) ?></div></div>
            </div>
        </div>

        <!-- Add Record Form -->
        <div class="card">
            <div class="card-hd">
                <div class="step-dot done">2</div>
                <h3>Add Record Row <span style="font-weight:400; color:var(--gray-400); font-size:13px;">(Fields 9–20)</span></h3>
                <span class="hd-sub"><?= count($active_records) ?> row<?= count($active_records) != 1 ? 's' : '' ?> so far</span>
            </div>
            <div class="card-bd">
                <form method="POST">
                    <input type="hidden" name="header_id" value="<?= $active_header_id ?>">

                    <div class="fsec-label">Records Series Information</div>
                    <div class="grid4">
                        <div class="fg s2"><label>9a. Records Series Title <span class="req">*</span></label>
                            <input type="text" name="records_series_title" placeholder="e.g. Personnel Files, Budget Reports…" required></div>
                        <div class="fg s2"><label>9b. Description</label>
                            <input type="text" name="records_description" placeholder="Brief description of the record series"></div>
                        <div class="fg"><label>10. Period Covered <span class="req">*</span></label>
                            <input type="text" name="period_covered_from" placeholder="e.g. Sep-24" required></div>
                        <div class="fg"><label>11. Volume <span class="req">*</span></label>
                            <input type="text" name="volume" placeholder="e.g. 3 boxes" required></div>
                        <div class="fg"><label>12. Records Medium <span class="req">*</span></label>
                            <select name="records_medium" required>
                                <option value="">— Select —</option>
                                <option>Paper</option><option>Micro Film</option><option>Electronic</option>
                                <option>CD/DVD</option><option>Maps</option><option>Drawings</option>
                                <option>Computer Print Out</option>
                            </select></div>
                        <div class="fg"><label>13. Restrictions</label>
                            <select name="restrictions">
                                <option value="">— None —</option>
                                <option>Top Secret</option><option>Secret</option><option>Confidential</option>
                                <option>Restricted</option><option>Open Access</option>
                            </select></div>
                    </div>

                    <div class="divider"></div>
                    <div class="fsec-label">Usage &amp; Value</div>
                    <div class="grid4">
                        <div class="fg"><label>14. Location of Records</label>
                            <input type="text" name="location_of_records" id="rec_loc" placeholder="Cabinet / Room…"></div>
                        <div class="fg"><label>15. Frequency of Use</label>
                            <select name="request_frequency">
                                <option value="">— Select —</option>
                                <option>Daily</option><option>Weekly</option><option>Monthly</option>
                                <option>Quarterly</option><option>Semi-Annually</option><option>Annually</option><option>ANA</option>
                            </select></div>
                        <div class="fg"><label>16. Duplication</label>
                            <select name="duplication_value">
                                <option value="N/A" selected>N/A</option>
                                <?php foreach ($offices as $o): ?>
                                    <option value="<?= htmlspecialchars($o['office_name']) ?>"><?= htmlspecialchars($o['office_name']) ?></option>
                                <?php endforeach; ?>
                            </select></div>
                        <div class="fg"><label>17. Time Value</label>
                            <select name="time_value" id="rec_tv" onchange="handleTV(this)">
                                <option value="">— Select —</option>
                                <option value="T">T – Temporary</option>
                                <option value="P">P – Permanent</option>
                            </select></div>
                        <div class="fg s2"><label>18. Utility Value</label>
                            <select name="utility_value">
                                <option value="">— Select —</option>
                                <option value="Adm">Adm – Administrative</option>
                                <option value="F">F – Fiscal</option>
                                <option value="L">L – Legal</option>
                                <option value="Arc">Arc – Archival</option>
                            </select></div>
                    </div>

                    <div class="divider"></div>
                    <div class="fsec-label">Retention Period &amp; Disposition</div>
                    <div class="grid4">
                        <div class="fg"><label>19a. Active (yrs) <span class="req">*</span></label>
                            <input type="number" name="retention_period_active" id="rec_a" placeholder="0" onchange="calcT()" required></div>
                        <div class="fg"><label>19b. Storage (yrs)</label>
                            <input type="number" name="retention_period_storage" id="rec_s" placeholder="0" onchange="calcT()"></div>
                        <div class="fg"><label>19c. Total (auto)</label>
                            <input type="number" name="retention_period_total" id="rec_t" readonly placeholder="—"></div>
                        <div class="fg"><label>20. Disposition Provision <span class="req">*</span></label>
                            <input type="text" name="disposition_provision" id="rec_disp" placeholder="e.g. Transfer to NAP / Destroy" required></div>
                    </div>

                    <div style="margin-top:20px; display:flex; gap:10px; align-items:center;">
                        <button type="submit" name="add_record" class="btn-add">+ Add Record Row</button>
                        <span style="font-size:12px; color:var(--gray-400);">Fields marked <span style="color:var(--red)">*</span> are required.</span>
                    </div>
                </form>
            </div>
        </div>

        <!-- Records Table -->
        <div class="card">
            <div class="card-hd">
                <div class="step-dot done">3</div>
                <h3>Record Rows Under This Header</h3>
                <a href="records_table.php?header_id=<?= $active_header_id ?>" target="_blank" class="btn-ghost" style="margin-left:auto;">🖨 Print / View Form</a>
            </div>
            <?php if (empty($active_records)): ?>
                <div class="empty"><div class="icon">📂</div><p>No rows yet. Fill in the form above and click "Add Record Row".</p></div>
            <?php else: ?>
            <div class="tbl-scroll">
            <table class="rtbl">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>9. Title &amp; Description</th>
                        <th>10. Period</th>
                        <th>11. Volume</th>
                        <th>12. Medium</th>
                        <th>13. Restrictions</th>
                        <th>14. Location</th>
                        <th>15. Freq</th>
                        <th>16. Dup</th>
                        <th>17. T/P</th>
                        <th>18. Utility</th>
                        <th>19. Ret A/S/T</th>
                        <th>20. Disposition</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($active_records as $i => $rec): ?>
                    <tr>
                        <td class="rnum"><?= $i+1 ?></td>
                        <td>
                            <div class="rt-title"><?= htmlspecialchars($rec['records_series_title']) ?></div>
                            <?php if ($rec['records_description']): ?><div class="rt-desc"><?= htmlspecialchars($rec['records_description']) ?></div><?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($rec['period_covered_from']) ?></td>
                        <td><?= htmlspecialchars($rec['volume']) ?></td>
                        <td><?= htmlspecialchars($rec['records_medium']) ?></td>
                        <td><?= htmlspecialchars($rec['restrictions']) ?></td>
                        <td><?= htmlspecialchars($rec['location_of_records']) ?></td>
                        <td><?= htmlspecialchars($rec['request_frequency']) ?></td>
                        <td><?= htmlspecialchars($rec['duplication_value']) ?></td>
                        <td><?php if ($rec['time_value']): ?><span class="tp <?= $rec['time_value']==='P'?'tp-p':'tp-t' ?>"><?= $rec['time_value'] ?></span><?php endif; ?></td>
                        <td><?= htmlspecialchars($rec['utility_value']) ?></td>
                        <td class="ret-val"><?= htmlspecialchars($rec['retention_period_active']) ?> / <?= htmlspecialchars($rec['retention_period_storage']) ?> / <?= htmlspecialchars($rec['retention_period_total']) ?></td>
                        <td><?= htmlspecialchars($rec['disposition_provision']) ?></td>
                        <td><a href="?delete_record=<?= $rec['id'] ?>" class="btn-rm" onclick="return confirm('Remove this record row?')">✕ Remove</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>

        <?php else: ?>
        <div class="no-hdr">
            <div class="icon">📋</div>
            <h4>No Header Selected</h4>
            <p>Click <strong>"+ New Header"</strong> in the sidebar to create a header group,<br>
               or click an existing header to select it and start adding records.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ══ MODAL: NEW HEADER ══ -->
<div class="modal-wrap" id="mNew" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal">
        <div class="modal-top">
            <h3>Step 1 — Create New Header (Fields 1–8)</h3>
            <button class="modal-x" onclick="document.getElementById('mNew').classList.remove('open')">×</button>
        </div>
        <div class="modal-bd">
            <p style="font-size:13px; color:var(--gray-500); margin-bottom:18px; line-height:1.6;">
                Enter the office information once. Multiple record rows can be added under this header.
            </p>
            <form method="POST">
                <div class="fsec-label">Office Information</div>
                <div class="grid4" style="margin-bottom:14px;">
                    <div class="fg sf"><label>1. Name of Office <span class="req">*</span></label>
                        <select name="name_of_office" required>
                            <option value="Pampanga State Agricultural University" selected>Pampanga State Agricultural University</option>
                        </select></div>
                    <div class="fg s2"><label>2. Department / Division <span class="req">*</span></label>
                        <select name="department_division" id="mdept" required onchange="syncSec(this,'msec')">
                            <option value="">— Select Department —</option>
                            <?php foreach ($offices as $o): ?>
                                <option value="<?= htmlspecialchars($o['office_name']) ?>" data-oid="<?= $o['id'] ?>">
                                    <?= htmlspecialchars($o['office_name']) ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="fg s2"><label>3. Section / Unit <span class="req">*</span></label>
                        <select name="section_unit" id="msec" required>
                            <option value="">— Select Department First —</option>
                        </select></div>
                    <div class="fg s2"><label>4. Telephone No.</label>
                        <input type="text" name="telephone_no" placeholder="045-XXX-XXXX"></div>
                    <div class="fg s2"><label>5. Email Address</label>
                        <input type="text" name="email_address" placeholder="office@psau.edu.ph"></div>
                    <div class="fg sf"><label>6. Address <span class="req">*</span></label>
                        <select name="address" required>
                            <option value="Magalang Pampanga" selected>Magalang Pampanga</option>
                        </select></div>
                    <div class="fg s2"><label>7. Person-in-Charge <span class="req">*</span></label>
                        <input type="text" name="person_incharge" value="<?= htmlspecialchars($current_user_full_name) ?>" readonly></div>
                    <div class="fg s2"><label>8. Date Prepared <span class="req">*</span></label>
                        <input type="text" name="date_prepared" value="<?= $current_date ?>" readonly></div>
                </div>
                <div class="modal-ft" style="padding:0; margin-top:6px; border:none; display:flex; gap:10px;">
                    <button type="submit" name="save_header" class="btn-primary">💾 Save Header</button>
                    <button type="button" class="btn-ghost" onclick="document.getElementById('mNew').classList.remove('open')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const subData = <?= json_encode($sub_offices) ?>;

function syncSec(dept, targetId) {
    const sec = document.getElementById(targetId);
    const oid = dept.options[dept.selectedIndex]?.getAttribute('data-oid');
    sec.innerHTML = '<option value="">— Select Section —</option>';
    if (oid && subData[oid]) {
        subData[oid].forEach(s => {
            const o = document.createElement('option');
            o.value = s; o.textContent = s; sec.appendChild(o);
        });
    }
}

function calcT() {
    const a = parseFloat(document.getElementById('rec_a')?.value) || 0;
    const s = parseFloat(document.getElementById('rec_s')?.value) || 0;
    const t = document.getElementById('rec_t');
    if (t) t.value = (a + s) > 0 ? (a + s) : '';
}

function handleTV(sel) {
    const disp = document.getElementById('rec_disp');
    const stor = document.getElementById('rec_s');
    const tot  = document.getElementById('rec_t');
    if (sel.value === 'P') {
        if (disp) disp.value = 'Permanent';
        if (stor) { stor.value = ''; stor.readOnly = true; stor.placeholder = '—'; }
        if (tot)  { tot.value = ''; tot.placeholder = '—'; }
    } else {
        if (disp && disp.value === 'Permanent') disp.value = '';
        if (stor) { stor.readOnly = false; stor.placeholder = '0'; }
        if (tot)  { tot.placeholder = '—'; }
    }
}

<?php if ($error_message && isset($_POST['save_header'])): ?>
document.getElementById('mNew').classList.add('open');
<?php endif; ?>
</script>
</body>
</html>