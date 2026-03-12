<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit();
}

require_once 'connect.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$current_user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT role FROM users WHERE id = $current_user_id");
$current_user = $result->fetch_assoc();
if ($current_user['role'] !== 'admin') { header("location: index.php"); exit(); }

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = $_POST['full_name']; $email = $_POST['email'];
    $department = $_POST['department']; $members = $_POST['members']; $role = $_POST['role'];
    $sql = "INSERT INTO users (username, password, full_name, email, department, members, role) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql); $stmt->bind_param("sssssss", $username, $password, $full_name, $email, $department, $members, $role);
    if ($stmt->execute()) { $success_message = "User created successfully!"; }
    else { $error_message = $stmt->errno == 1062 ? "Error: Username already exists." : "Error creating user: " . $stmt->error; }
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_user'])) {
    $user_id = $_POST['edit_user_id']; $username = $_POST['edit_username']; $full_name = $_POST['edit_full_name'];
    $email = $_POST['edit_email']; $department = $_POST['edit_department']; $members = $_POST['edit_members'];
    $role = $_POST['edit_role']; $password = $_POST['edit_password'];
    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET username=?,full_name=?,email=?,department=?,members=?,role=?,password=? WHERE id=?";
        $stmt = $conn->prepare($sql); $stmt->bind_param("sssssssi",$username,$full_name,$email,$department,$members,$role,$password_hash,$user_id);
    } else {
        $sql = "UPDATE users SET username=?,full_name=?,email=?,department=?,members=?,role=? WHERE id=?";
        $stmt = $conn->prepare($sql); $stmt->bind_param("ssssssi",$username,$full_name,$email,$department,$members,$role,$user_id);
    }
    if ($stmt->execute()) { $success_message = "User updated successfully!"; }
    else { $error_message = $stmt->errno == 1062 ? "Error: Username already exists." : "Error updating user: " . $stmt->error; }
}
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    if ($delete_id != $current_user_id) { $conn->query("DELETE FROM users WHERE id=$delete_id"); $success_message = "User deleted successfully!"; }
}
$conn->query("CREATE TABLE IF NOT EXISTS sub_offices (id INT AUTO_INCREMENT PRIMARY KEY, office_id INT NOT NULL, sub_name VARCHAR(255) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (office_id) REFERENCES offices(id) ON DELETE CASCADE)");
$officeCount = $conn->query("SELECT COUNT(*) as cnt FROM offices")->fetch_assoc()['cnt'];
if ($officeCount == 0) {
    $seedData = ["Office of the President"=>["University President","University Secretary","Head, Legal Unit"],"Office of the Vice President for Academic Affairs"=>["Vice President for Academic Affairs","Dean, College of Agriculture Systems and Technology"]];
    foreach ($seedData as $officeName => $subs) {
        $stmt = $conn->prepare("INSERT INTO offices (office_name) VALUES (?)"); $stmt->bind_param("s",$officeName); $stmt->execute(); $officeId=$stmt->insert_id; $stmt->close();
        foreach ($subs as $sub) { $stmt2=$conn->prepare("INSERT INTO sub_offices (office_id,sub_name) VALUES (?,?)"); $stmt2->bind_param("is",$officeId,$sub); $stmt2->execute(); $stmt2->close(); }
    }
}
if ($_SERVER["REQUEST_METHOD"]=="POST"&&isset($_POST['create_office'])) {
    $office_name=trim($_POST['office_name']??''); $sub_names=array_filter(array_map('trim',$_POST['sub_names']??[]));
    if (!empty($office_name)) {
        $stmt=$conn->prepare("INSERT INTO offices (office_name) VALUES (?)"); $stmt->bind_param("s",$office_name);
        if ($stmt->execute()) { $new_office_id=$stmt->insert_id; foreach ($sub_names as $sub) { $s2=$conn->prepare("INSERT INTO sub_offices (office_id,sub_name) VALUES (?,?)"); $s2->bind_param("is",$new_office_id,$sub); $s2->execute(); $s2->close(); } $success_message="Office added successfully!"; }
        else { $error_message="Error adding office: ".$stmt->error; } $stmt->close();
    }
}
if ($_SERVER["REQUEST_METHOD"]=="POST"&&isset($_POST['edit_office'])) {
    $edit_office_id=(int)$_POST['edit_office_id']; $edit_office_name=trim($_POST['edit_office_name']??''); $edit_sub_names=array_filter(array_map('trim',$_POST['edit_sub_names']??[]));
    if (!empty($edit_office_name)) {
        $stmt=$conn->prepare("UPDATE offices SET office_name=? WHERE id=?"); $stmt->bind_param("si",$edit_office_name,$edit_office_id); $stmt->execute(); $stmt->close();
        $conn->query("DELETE FROM sub_offices WHERE office_id=$edit_office_id");
        foreach ($edit_sub_names as $sub) { $s2=$conn->prepare("INSERT INTO sub_offices (office_id,sub_name) VALUES (?,?)"); $s2->bind_param("is",$edit_office_id,$sub); $s2->execute(); $s2->close(); }
        $success_message="Office updated successfully!";
    }
}
if (isset($_GET['delete_office'])&&is_numeric($_GET['delete_office'])) { $conn->query("DELETE FROM offices WHERE id=".(int)$_GET['delete_office']); $success_message="Office deleted successfully!"; }
$offices_result=$conn->query("SELECT o.id,o.office_name,o.created_at,GROUP_CONCAT(s.sub_name ORDER BY s.id SEPARATOR '||') AS sub_offices FROM offices o LEFT JOIN sub_offices s ON o.id=s.office_id GROUP BY o.id ORDER BY o.id ASC");
$users=$conn->query("SELECT id,username,full_name,email,department,members,role,created_at FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Manager - PSAU Records System</title>
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
            --red-50:    #fef2f2;
            --red-600:   #dc2626;
            --red-700:   #b91c1c;
            --red-200:   #fecaca;
        }
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--gray-50);
            min-height: 100vh;
            color: var(--gray-700);
        }
        .bg-layer {
            position: fixed; inset: 0; z-index: 0;
            background: radial-gradient(ellipse 70% 50% at 5% 0%, rgba(30,90,61,0.08) 0%, transparent 55%),
                        radial-gradient(ellipse 50% 40% at 95% 100%, rgba(74,171,114,0.06) 0%, transparent 50%), var(--gray-50);
        }
        .bg-grid {
            position: fixed; inset: 0; z-index: 0;
            background-image: linear-gradient(rgba(30,90,61,0.035) 1px, transparent 1px), linear-gradient(90deg, rgba(30,90,61,0.035) 1px, transparent 1px);
            background-size: 48px 48px;
        }
        .page { position: relative; z-index: 1; max-width: 1240px; margin: 0 auto; padding: 32px 24px 56px; }

        /* ── Header ── */
        .page-header {
            background: linear-gradient(145deg, var(--green-950) 0%, var(--green-900) 55%, var(--green-800) 100%);
            border-radius: 18px;
            padding: 32px 40px;
            margin-bottom: 24px;
            position: relative; overflow: hidden;
            box-shadow: 0 20px 56px rgba(14,43,30,0.22), 0 4px 12px rgba(14,43,30,0.14);
        }
        .page-header::before {
            content: ''; position: absolute;
            width: 380px; height: 380px; border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.06);
            top: -160px; right: -80px; pointer-events: none;
        }
        .page-header-noise {
            position: absolute; inset: 0; pointer-events: none;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
            opacity: 0.35;
        }
        .header-inner { position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
        .header-eyebrow { font-size: 0.6875rem; font-weight: 600; letter-spacing: 0.16em; text-transform: uppercase; color: rgba(201,168,76,0.85); margin-bottom: 6px; }
        .header-title { font-family: 'Playfair Display', serif; font-size: clamp(1.5rem,3vw,2.125rem); font-weight: 700; color: #fff; line-height: 1.15; }
        .header-sub { font-size: 0.875rem; color: rgba(255,255,255,0.55); font-weight: 300; margin-top: 4px; }
        .header-actions { display: flex; gap: 10px; flex-wrap: wrap; }

        /* ── Buttons ── */
        .btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 9px 20px; border-radius: 8px; border: none;
            font-family: 'DM Sans', sans-serif; font-size: 0.875rem; font-weight: 600;
            cursor: pointer; transition: all 0.2s ease; white-space: nowrap;
        }
        .btn-primary { background: #fff; color: var(--green-900); box-shadow: 0 1px 3px rgba(0,0,0,0.12); }
        .btn-primary:hover { background: var(--green-50); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .btn-teal { background: rgba(255,255,255,0.12); color: #fff; border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(4px); }
        .btn-teal:hover { background: rgba(255,255,255,0.2); transform: translateY(-1px); }
        .btn-secondary { background: var(--gray-100); color: var(--gray-700); border: 1px solid var(--gray-200); }
        .btn-secondary:hover { background: var(--gray-200); }
        .btn-danger { background: var(--red-600); color: #fff; }
        .btn-danger:hover { background: var(--red-700); }
        .btn-form-primary { background: var(--green-900); color: #fff; }
        .btn-form-primary:hover { background: var(--green-800); transform: translateY(-1px); }
        .btn-sm { padding: 6px 14px; font-size: 0.8125rem; }

        /* ── Tab Bar ── */
        .tab-bar {
            display: flex; gap: 2px;
            background: #fff; border-radius: 12px 12px 0 0;
            border: 1px solid var(--gray-200); border-bottom: none;
            overflow: hidden;
        }
        .tab-btn {
            padding: 13px 28px; border: none; background: transparent;
            font-family: 'DM Sans', sans-serif; font-size: 0.875rem; font-weight: 500;
            color: var(--gray-500); cursor: pointer; border-bottom: 3px solid transparent;
            transition: all 0.2s ease; display: flex; align-items: center; gap: 8px;
        }
        .tab-btn.active { color: var(--green-900); font-weight: 600; border-bottom-color: var(--green-800); background: var(--green-50); }
        .tab-btn:hover:not(.active) { color: var(--gray-700); background: var(--gray-50); }

        /* ── Main Card ── */
        .main-card {
            background: #fff; border-radius: 0 12px 12px 12px;
            border: 1px solid var(--gray-200); overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        }
        .card-body { padding: 28px; }

        /* ── Alert ── */
        .alert {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 16px; border-radius: 10px; border: 1px solid;
            margin-bottom: 24px; font-size: 0.875rem; font-weight: 500;
        }
        .alert-success { background: var(--green-50); color: #166534; border-color: var(--green-200); }
        .alert-error { background: var(--red-50); color: #991b1b; border-color: var(--red-200); }

        /* ── Table ── */
        .data-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        .data-table thead th {
            padding: 11px 16px; text-align: left;
            font-size: 0.7rem; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase;
            color: var(--gray-500); background: var(--gray-50);
            border-bottom: 1px solid var(--gray-200);
        }
        .data-table tbody td { padding: 14px 16px; border-bottom: 1px solid var(--gray-100); vertical-align: middle; }
        .data-table tbody tr:last-child td { border-bottom: none; }
        .data-table tbody tr:hover td { background: var(--green-50); }
        .data-table tbody tr { transition: background 0.15s ease; }

        /* ── Avatar ── */
        .avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, var(--green-800), var(--green-600));
            color: #fff; font-weight: 700; font-size: 0.875rem;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; box-shadow: 0 2px 6px rgba(30,90,61,0.3);
        }
        .user-cell { display: flex; align-items: center; gap: 12px; }
        .user-name { font-weight: 600; color: var(--gray-900); font-size: 0.9rem; }
        .user-email { font-size: 0.75rem; color: var(--gray-400); margin-top: 1px; }

        /* ── Badges ── */
        .badge {
            display: inline-flex; align-items: center;
            padding: 3px 11px; border-radius: 999px;
            font-size: 0.7rem; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase;
        }
        .badge-admin { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-user  { background: var(--green-50); color: #166534; border: 1px solid var(--green-200); }
        .badge-sub   { background: var(--green-50); color: #166534; border: 1px solid var(--green-200); font-size: 0.7rem; padding: 2px 9px; border-radius: 999px; }

        /* ── Action Buttons ── */
        .action-wrap { display: flex; gap: 6px; }
        .icon-btn {
            width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--gray-200);
            background: #fff; color: var(--gray-500); cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.875rem; transition: all 0.18s ease;
        }
        .icon-btn:hover { transform: translateY(-1px); box-shadow: 0 3px 8px rgba(0,0,0,0.1); }
        .icon-btn.edit:hover  { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
        .icon-btn.del:hover   { background: var(--red-50); color: var(--red-600); border-color: var(--red-200); }
        .current-user-tag { font-size: 0.7rem; color: var(--gray-400); font-style: italic; }

        /* ── Sub-office chips ── */
        .chips { display: flex; flex-wrap: wrap; gap: 4px; }

        /* ── Empty State ── */
        .empty-state { text-align: center; padding: 64px 24px; }
        .empty-icon { font-size: 3rem; opacity: 0.35; margin-bottom: 12px; }
        .empty-title { font-size: 1.0625rem; font-weight: 600; color: var(--gray-700); margin-bottom: 6px; }
        .empty-text  { font-size: 0.875rem; color: var(--gray-400); margin-bottom: 20px; }

        /* ── Modal ── */
        .modal {
            display: none; position: fixed; inset: 0;
            background: rgba(15,25,18,0.55); backdrop-filter: blur(3px);
            z-index: 1000; align-items: center; justify-content: center; padding: 20px;
        }
        .modal.show { display: flex; }
        .modal-box {
            background: #fff; border-radius: 16px;
            box-shadow: 0 32px 80px rgba(0,0,0,0.25);
            width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto;
            animation: modalIn 0.22s cubic-bezier(0.22,1,0.36,1);
        }
        .modal-box.wide { max-width: 620px; }
        @keyframes modalIn { from { opacity: 0; transform: scale(0.95) translateY(10px); } to { opacity:1; transform: scale(1) translateY(0); } }
        .modal-head {
            padding: 22px 24px 18px; border-bottom: 1px solid var(--gray-100);
            display: flex; align-items: center; justify-content: space-between;
        }
        .modal-title { font-family: 'Playfair Display', serif; font-size: 1.1875rem; font-weight: 700; color: var(--gray-900); }
        .modal-close {
            width: 30px; height: 30px; border-radius: 8px; border: 1px solid var(--gray-200);
            background: none; cursor: pointer; color: var(--gray-400); font-size: 1.125rem;
            display: flex; align-items: center; justify-content: center; transition: all 0.15s;
        }
        .modal-close:hover { background: var(--gray-100); color: var(--gray-700); }
        .modal-body { padding: 24px; }
        .modal-foot { padding: 16px 24px; border-top: 1px solid var(--gray-100); display: flex; gap: 10px; justify-content: flex-end; }

        /* ── Form ── */
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .form-group { margin-bottom: 16px; }
        .form-group:last-child { margin-bottom: 0; }
        .form-label { display: block; margin-bottom: 6px; font-size: 0.8125rem; font-weight: 600; color: var(--gray-700); letter-spacing: 0.01em; }
        .form-control {
            display: block; width: 100%; padding: 9px 13px;
            font-family: 'DM Sans', sans-serif; font-size: 0.875rem;
            color: var(--gray-900); background: #fff;
            border: 1px solid var(--gray-200); border-radius: 8px;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .form-control:focus { outline: none; border-color: var(--green-700); box-shadow: 0 0 0 3px rgba(30,90,61,0.1); }
        .form-hint { font-size: 0.75rem; color: var(--gray-400); margin-top: 4px; }

        /* ── Confirm dialog ── */
        .confirm-modal { display: none; position: fixed; inset: 0; background: rgba(15,25,18,0.6); backdrop-filter: blur(4px); z-index: 2000; align-items: center; justify-content: center; }
        .confirm-modal.show { display: flex; }
        .confirm-box { background: #fff; border-radius: 16px; max-width: 380px; width: 90%; padding: 36px 28px 28px; text-align: center; box-shadow: 0 32px 80px rgba(0,0,0,0.25); animation: modalIn 0.22s cubic-bezier(0.22,1,0.36,1); }
        .confirm-icon { font-size: 2.5rem; margin-bottom: 14px; }
        .confirm-title { font-family: 'Playfair Display', serif; font-size: 1.125rem; font-weight: 700; color: var(--gray-900); margin-bottom: 8px; }
        .confirm-msg { font-size: 0.875rem; color: var(--gray-500); line-height: 1.6; margin-bottom: 24px; }
        .confirm-btns { display: flex; gap: 10px; justify-content: center; }

        /* Sub-office adder */
        .sub-adder { display: flex; gap: 7px; align-items: center; }
        .sub-adder .form-control { flex: 1; }
        .sub-rm { background: none; border: 1px solid var(--red-200); color: var(--red-600); border-radius: 7px; padding: 7px 11px; cursor: pointer; font-size: 0.875rem; flex-shrink: 0; transition: all 0.15s; }
        .sub-rm:hover { background: var(--red-50); }
        .sub-add-btn { width: 100%; margin-top: 10px; padding: 8px; background: var(--green-50); border: 1px dashed var(--green-200); color: #166534; border-radius: 8px; cursor: pointer; font-size: 0.8125rem; font-family: 'DM Sans', sans-serif; transition: all 0.15s; }
        .sub-add-btn:hover { background: var(--green-100); border-color: var(--green-700); }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--green-600); border-radius: 99px; }

        /* ── Responsive ── */
        @media (max-width: 640px) {
            .page { padding: 20px 14px 40px; }
            .page-header { padding: 24px 20px; }
            .card-body { padding: 16px; }
            .form-row { grid-template-columns: 1fr; }
            .data-table { font-size: 0.8rem; }
            .data-table thead th, .data-table tbody td { padding: 10px 10px; }
        }
    </style>
</head>
<body>
<div class="bg-layer"></div>
<div class="bg-grid"></div>

<div class="page">

    <!-- Header -->
    <div class="page-header">
        <div class="page-header-noise"></div>
        <div class="header-inner">
            <div>
                <div class="header-eyebrow">Administration Panel</div>
                <h1 class="header-title">User Manager</h1>
                <p class="header-sub">Manage system users, offices, and access levels</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-primary" id="btnCreateUser" onclick="showCreateModal()">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Create User
                </button>
                <button class="btn btn-teal" id="btnCreateOffice" onclick="showOfficeModal()" style="display:none;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add Office
                </button>
            </div>
        </div>
    </div>

    <!-- Tab Bar -->
    <div class="tab-bar">
        <button id="tab-users" class="tab-btn active" onclick="switchTab('users')">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Users
        </button>
        <button id="tab-offices" class="tab-btn" onclick="switchTab('offices')">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            Offices
        </button>
    </div>

    <!-- Main Card -->
    <div class="main-card">
        <div class="card-body">

            <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <?= htmlspecialchars($success_message) ?>
            </div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
                <?= htmlspecialchars($error_message) ?>
            </div>
            <?php endif; ?>

            <!-- USERS TAB -->
            <div id="section-users">
                <?php if ($users->num_rows > 0): ?>
                <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Username</th>
                                <th>Office</th>
                                <th>Member</th>
                                <th>Role</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="avatar"><?= strtoupper(substr($user['full_name'],0,1)) ?></div>
                                        <div>
                                            <div class="user-name"><?= htmlspecialchars($user['full_name']) ?></div>
                                            <div class="user-email"><?= htmlspecialchars($user['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="color:var(--gray-600);font-weight:500;"><?= htmlspecialchars($user['username']) ?></td>
                                <td style="color:var(--gray-600);font-size:0.8125rem;"><?= htmlspecialchars($user['department'] ?? '—') ?></td>
                                <td style="color:var(--gray-600);font-size:0.8125rem;"><?= htmlspecialchars($user['members'] ?? '—') ?></td>
                                <td><span class="badge badge-<?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span></td>
                                <td style="color:var(--gray-400);font-size:0.8125rem;"><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <div class="action-wrap">
                                        <button class="icon-btn edit" onclick="editUser(<?= $user['id'] ?>)" title="Edit">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <?php if ($user['id'] != $current_user_id): ?>
                                        <button class="icon-btn del" onclick="confirmDelete(<?= $user['id'] ?>, '<?= htmlspecialchars($user['full_name']) ?>')" title="Delete">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                        <?php else: ?>
                                        <span class="current-user-tag">You</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">👥</div>
                    <div class="empty-title">No users found</div>
                    <div class="empty-text">Create your first user to get started.</div>
                    <button class="btn btn-form-primary" onclick="showCreateModal()">+ Create User</button>
                </div>
                <?php endif; ?>
            </div>

            <!-- OFFICES TAB -->
            <div id="section-offices" style="display:none;">
                <?php if ($offices_result && $offices_result->num_rows > 0): ?>
                <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width:28%">Main Office</th>
                                <th>Sub-Offices / Units</th>
                                <th style="width:120px;">Created</th>
                                <th style="width:80px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($off = $offices_result->fetch_assoc()):
                            $subs = $off['sub_offices'] ? explode('||', $off['sub_offices']) : [];
                        ?>
                            <tr>
                                <td><span style="font-weight:600;color:var(--green-900);"><?= htmlspecialchars($off['office_name']) ?></span></td>
                                <td>
                                    <div class="chips">
                                    <?php foreach ($subs as $sub): ?>
                                        <span class="badge-sub"><?= htmlspecialchars($sub) ?></span>
                                    <?php endforeach; ?>
                                    <?php if (empty($subs)): ?><span style="color:var(--gray-300);font-size:0.8rem;">None</span><?php endif; ?>
                                    </div>
                                </td>
                                <td style="color:var(--gray-400);font-size:0.8125rem;"><?= date('M d, Y', strtotime($off['created_at'])) ?></td>
                                <td>
                                    <div class="action-wrap">
                                        <button class="icon-btn edit" onclick="editOffice(<?= $off['id'] ?>,<?= htmlspecialchars(json_encode($off['office_name'])) ?>,<?= htmlspecialchars(json_encode($subs)) ?>)" title="Edit">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button class="icon-btn del" onclick="confirmDeleteOffice(<?= $off['id'] ?>, '<?= htmlspecialchars($off['office_name']) ?>')" title="Delete">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">🏢</div>
                    <div class="empty-title">No offices found</div>
                    <div class="empty-text">Add your first office to get started.</div>
                    <button class="btn btn-form-primary" onclick="showOfficeModal()">+ Add Office</button>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<!-- ══════════════════ MODALS ══════════════════ -->

<!-- Create User -->
<div class="modal" id="createModal">
    <div class="modal-box">
        <div class="modal-head">
            <h2 class="modal-title">Create New User</h2>
            <button class="modal-close" onclick="hideModal('createModal')">&times;</button>
        </div>
        <form method="post" autocomplete="off">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Username *</label><input type="text" name="username" class="form-control" required readonly onfocus="this.removeAttribute('readonly')" placeholder="e.g. jdoe"></div>
                    <div class="form-group"><label class="form-label">Password *</label><input type="password" name="password" class="form-control" required readonly onfocus="this.removeAttribute('readonly')" placeholder="••••••••"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Full Name *</label><input type="text" name="full_name" class="form-control" required placeholder="e.g. Juan Dela Cruz"></div>
                    <div class="form-group"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required placeholder="email@psau.edu.ph"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Office *</label>
                        <select name="department" class="form-control" required>
                            <option value="">Select Office</option>
                            <option value="MIS UNIT">MIS UNIT</option>
                            <option value="RECORD UNIT">RECORD UNIT</option>
                            <option value="HR">HR</option>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Member Type *</label>
                        <select name="members" class="form-control" required>
                            <option value="">Select Type</option>
                            <option value="Head">Head</option>
                            <option value="Member">Member</option>
                        </select>
                    </div>
                </div>
                <div class="form-group"><label class="form-label">Role *</label>
                    <select name="role" class="form-control" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-secondary btn-sm" onclick="hideModal('createModal')">Cancel</button>
                <button type="submit" name="create_user" class="btn btn-form-primary btn-sm">Create User</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User -->
<div class="modal" id="editModal">
    <div class="modal-box">
        <div class="modal-head">
            <h2 class="modal-title">Edit User</h2>
            <button class="modal-close" onclick="hideModal('editModal')">&times;</button>
        </div>
        <form method="post" autocomplete="off">
            <input type="hidden" id="edit_user_id" name="edit_user_id">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Username *</label><input type="text" id="edit_username" name="edit_username" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Email *</label><input type="email" id="edit_email" name="edit_email" class="form-control" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Full Name *</label><input type="text" id="edit_full_name" name="edit_full_name" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Office *</label>
                        <select id="edit_department" name="edit_department" class="form-control" required>
                            <option value="">Select Office</option>
                            <option value="MIS UNIT">MIS UNIT</option>
                            <option value="RECORD UNIT">RECORD UNIT</option>
                            <option value="HR">HR</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Member Type *</label>
                        <select id="edit_members" name="edit_members" class="form-control" required>
                            <option value="">Select Type</option>
                            <option value="Head">Head</option>
                            <option value="Member">Member</option>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Role *</label>
                        <select id="edit_role" name="edit_role" class="form-control" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" id="edit_password" name="edit_password" class="form-control" placeholder="Leave blank to keep current password">
                    <div class="form-hint">Only fill this in if you want to change the password.</div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-secondary btn-sm" onclick="hideModal('editModal')">Cancel</button>
                <button type="submit" name="edit_user" class="btn btn-form-primary btn-sm">Update User</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Office -->
<div class="modal" id="officeModal">
    <div class="modal-box wide">
        <div class="modal-head">
            <h2 class="modal-title">Add Office</h2>
            <button class="modal-close" onclick="hideModal('officeModal')">&times;</button>
        </div>
        <form method="post" autocomplete="off">
            <div class="modal-body">
                <div class="form-group"><label class="form-label">Main Office Name *</label><input type="text" name="office_name" class="form-control" required placeholder="e.g. Office of the President"></div>
                <div class="form-group">
                    <label class="form-label">Sub-Offices / Units</label>
                    <div id="subOfficeList" style="display:flex;flex-direction:column;gap:8px;">
                        <?php for($i=0;$i<3;$i++): ?>
                        <div class="sub-adder">
                            <input type="text" name="sub_names[]" class="form-control" placeholder="Sub-office or unit name">
                            <button type="button" class="sub-rm" onclick="removeSubRow(this)">✕</button>
                        </div>
                        <?php endfor; ?>
                    </div>
                    <button type="button" class="sub-add-btn" onclick="addSubRow('subOfficeList','sub_names[]')">+ Add Sub-Office</button>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-secondary btn-sm" onclick="hideModal('officeModal')">Cancel</button>
                <button type="submit" name="create_office" class="btn btn-form-primary btn-sm">Save Office</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Office -->
<div class="modal" id="editOfficeModal">
    <div class="modal-box wide">
        <div class="modal-head">
            <h2 class="modal-title">Edit Office</h2>
            <button class="modal-close" onclick="hideModal('editOfficeModal')">&times;</button>
        </div>
        <form method="post" autocomplete="off">
            <input type="hidden" id="edit_office_id" name="edit_office_id">
            <div class="modal-body">
                <div class="form-group"><label class="form-label">Main Office Name *</label><input type="text" id="edit_office_name" name="edit_office_name" class="form-control" required></div>
                <div class="form-group">
                    <label class="form-label">Sub-Offices / Units</label>
                    <div id="editSubOfficeList" style="display:flex;flex-direction:column;gap:8px;"></div>
                    <button type="button" class="sub-add-btn" onclick="addSubRow('editSubOfficeList','edit_sub_names[]')">+ Add Sub-Office</button>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-secondary btn-sm" onclick="hideModal('editOfficeModal')">Cancel</button>
                <button type="submit" name="edit_office" class="btn btn-form-primary btn-sm">Update Office</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirm Delete User -->
<div class="confirm-modal" id="deleteDialog">
    <div class="confirm-box">
        <div class="confirm-icon">⚠️</div>
        <div class="confirm-title">Delete User?</div>
        <p class="confirm-msg" id="deleteMessage"></p>
        <div class="confirm-btns">
            <button class="btn btn-secondary btn-sm" onclick="hideModal('deleteDialog')">Cancel</button>
            <button class="btn btn-danger btn-sm" id="deleteYesBtn">Delete</button>
        </div>
    </div>
</div>

<!-- Confirm Delete Office -->
<div class="confirm-modal" id="deleteOfficeDialog">
    <div class="confirm-box">
        <div class="confirm-icon">⚠️</div>
        <div class="confirm-title">Delete Office?</div>
        <p class="confirm-msg" id="deleteOfficeMessage"></p>
        <div class="confirm-btns">
            <button class="btn btn-secondary btn-sm" onclick="hideModal('deleteOfficeDialog')">Cancel</button>
            <button class="btn btn-danger btn-sm" id="deleteOfficeYesBtn">Delete</button>
        </div>
    </div>
</div>

<script>
// ── Generic Modal Helpers ──
function showModal(id) { document.getElementById(id).classList.add('show'); document.body.style.overflow='hidden'; }
function hideModal(id) { document.getElementById(id).classList.remove('show'); document.body.style.overflow=''; }
document.querySelectorAll('.modal,.confirm-modal').forEach(m => { m.addEventListener('click', e => { if(e.target===m) hideModal(m.id); }); });
document.addEventListener('keydown', e => { if(e.key==='Escape') document.querySelectorAll('.modal.show,.confirm-modal.show').forEach(m=>hideModal(m.id)); });

// ── Tab ──
function switchTab(tab) {
    const isUsers = tab==='users';
    document.getElementById('section-users').style.display = isUsers?'':'none';
    document.getElementById('section-offices').style.display = isUsers?'none':'';
    ['users','offices'].forEach(t => {
        const btn = document.getElementById('tab-'+t);
        const active = (t===tab);
        btn.classList.toggle('active', active);
    });
    document.getElementById('btnCreateUser').style.display = isUsers?'':'none';
    document.getElementById('btnCreateOffice').style.display = isUsers?'none':'';
}

// ── Create/Edit User ──
function showCreateModal() { showModal('createModal'); }
function editUser(userId) {
    fetch(`get_user_data.php?id=${userId}`).then(r=>r.json()).then(u => {
        document.getElementById('edit_user_id').value = u.id;
        document.getElementById('edit_username').value = u.username;
        document.getElementById('edit_email').value = u.email;
        document.getElementById('edit_full_name').value = u.full_name;
        document.getElementById('edit_department').value = u.department||'';
        document.getElementById('edit_members').value = u.members||'';
        document.getElementById('edit_role').value = u.role;
        document.getElementById('edit_password').value = '';
        showModal('editModal');
    }).catch(() => showModal('editModal'));
}

// ── Delete User ──
function confirmDelete(id, name) {
    document.getElementById('deleteMessage').textContent = `Are you sure you want to delete "${name}"? This action cannot be undone.`;
    document.getElementById('deleteYesBtn').onclick = () => { hideModal('deleteDialog'); window.location.href=`?delete=${id}`; };
    showModal('deleteDialog');
}

// ── Office ──
function showOfficeModal() { showModal('officeModal'); }
function editOffice(id, name, subs) {
    document.getElementById('edit_office_id').value = id;
    document.getElementById('edit_office_name').value = name;
    const list = document.getElementById('editSubOfficeList');
    list.innerHTML = '';
    const arr = Array.isArray(subs)&&subs.length?subs:['','',''];
    arr.forEach(sub => addSubRowWithValue('editSubOfficeList','edit_sub_names[]',sub));
    showModal('editOfficeModal');
}
function confirmDeleteOffice(id, name) {
    document.getElementById('deleteOfficeMessage').textContent = `Delete office "${name}" and all its sub-offices? This action cannot be undone.`;
    document.getElementById('deleteOfficeYesBtn').onclick = () => { hideModal('deleteOfficeDialog'); window.location.href=`?delete_office=${id}`; };
    showModal('deleteOfficeDialog');
}

// ── Sub-office rows ──
function addSubRow(listId, inputName) { addSubRowWithValue(listId, inputName, ''); }
function addSubRowWithValue(listId, inputName, value) {
    const list = document.getElementById(listId);
    const div = document.createElement('div');
    div.className = 'sub-adder';
    div.innerHTML = `<input type="text" name="${inputName}" class="form-control" value="${value.replace(/"/g,'&quot;')}" placeholder="Sub-office or unit name"><button type="button" class="sub-rm" onclick="removeSubRow(this)">✕</button>`;
    list.appendChild(div);
}
function removeSubRow(btn) {
    const row = btn.closest('.sub-adder');
    const list = row.parentElement;
    if (list.querySelectorAll('.sub-adder').length > 1) row.remove();
    else row.querySelector('input').value = '';
}
</script>
</body>
</html>
<?php $conn->close(); ?>