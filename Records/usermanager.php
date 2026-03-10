<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit();
}

require_once 'connect.php';

// Check if current user is admin
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$current_user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT role FROM users WHERE id = $current_user_id");
$current_user = $result->fetch_assoc();

if ($current_user['role'] !== 'admin') {
    header("location: index.php");
    exit();
}

// Handle user creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $department = $_POST['department'];
    $members = $_POST['members'];
    $role = $_POST['role'];
    
    $sql = "INSERT INTO users (username, password, full_name, email, department, members, role) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $username, $password, $full_name, $email, $department, $members, $role);
    
    if ($stmt->execute()) {
        $success_message = "User created successfully!";
    } else {
        if ($stmt->errno == 1062) {
            $error_message = "Error: Username already exists. Please choose a different username.";
        } else {
            $error_message = "Error creating user: " . $stmt->error;
        }
    }
}

// Handle user editing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_user'])) {
    $user_id = $_POST['edit_user_id'];
    $username = $_POST['edit_username'];
    $full_name = $_POST['edit_full_name'];
    $email = $_POST['edit_email'];
    $department = $_POST['edit_department'];
    $members = $_POST['edit_members'];
    $role = $_POST['edit_role'];
    $password = $_POST['edit_password'];
    
    // Build update query
    if (!empty($password)) {
        // Update with new password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET username = ?, full_name = ?, email = ?, department = ?, members = ?, role = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssi", $username, $full_name, $email, $department, $members, $role, $password_hash, $user_id);
    } else {
        // Update without changing password
        $sql = "UPDATE users SET username = ?, full_name = ?, email = ?, department = ?, members = ?, role = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $username, $full_name, $email, $department, $members, $role, $user_id);
    }
    
    if ($stmt->execute()) {
        $success_message = "User updated successfully!";
    } else {
        if ($stmt->errno == 1062) {
            $error_message = "Error: Username already exists. Please choose a different username.";
        } else {
            $error_message = "Error updating user: " . $stmt->error;
        }
    }
}

// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    // Don't allow deleting yourself
    if ($delete_id != $current_user_id) {
        $conn->query("DELETE FROM users WHERE id = $delete_id");
        $success_message = "User deleted successfully!";
    }
}


// ── Ensure sub_offices table exists ──
$conn->query("CREATE TABLE IF NOT EXISTS sub_offices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    office_id INT NOT NULL,
    sub_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (office_id) REFERENCES offices(id) ON DELETE CASCADE
)");

// ── Seed offices data if offices table is empty ──
$officeCount = $conn->query("SELECT COUNT(*) as cnt FROM offices")->fetch_assoc()['cnt'];
if ($officeCount == 0) {
    $seedData = [
        "Office of the President" => [
            "University President","University Secretary","Head, Legal Unit",
            "Director, Internal Audit Unit","Head, Security Unit",
            "Director, Office of Gender and Development",
            "Director, Office of Public Affairs and International Linkages",
            "Head, External Affairs Unit","Head, International Affairs Unit",
            "Head, Strategic Communication Unit","Head, Information Unit",
            "Director, Office of Institutional Quality Assurance",
            "Head, Institutional and Program Accreditation Unit",
            "Head, Government-Initiated Accreditation and International Assessment Unit",
            "Head, Monitoring and Quality Analytics Unit","Head, Quality Management System Unit"
        ],
        "Office of the Vice President for Academic Affairs" => [
            "Vice President for Academic Affairs",
            "Dean, College of Agriculture Systems and Technology",
            "Dean, College of Arts and Sciences",
            "Dean, College of Business, Economics and Entrepreneurship",
            "Dean, College of Education","Dean, College of Engineering and Computer Studies",
            "Dean, College of Forestry and Agroforestry","Dean, College of Veterinary Medicine",
            "Dean, Graduate School","Director, Office of Admissions and Registration",
            "Director, Office of Student Affairs and Services",
            "Director, Office of Sports Development","Director, Office of Cultural and Performing Arts",
            "Director, Office of Guidance and Testing",
            "Director, Office of Alumni Relations and Placement",
            "OIC Director, Office of Library Services and Museum",
            "Chief Coordinator, National Service Training Program Unit",
            "Chief Coordinator, Special Academic Programs and Services Unit",
            "University Registrar","Assistant Registrar"
        ],
        "Office of the Vice President for Research, Innovation, Extension and Training" => [
            "Vice President for Research, Innovation, Extension and Training",
            "Director, Office of Research and Development",
            "Assistant Director, Office of Research and Development",
            "Head, Alias RDE Center","Head, Bamboo and Rattan RDE Center","Head, Tamarind RDE Center",
            "Director, Office of Innovation",
            "Head, Intellectual Property and Technology Business Management Unit",
            "Head, Technology Business Incubation Unit",
            "Director, Office of Extension and Training",
            "Assistant Director, Office of Extension and Training",
            "Head, Technical Vocational Education and Training (TVET) Center",
            "Head, Community Radio Station","Head, Satellite Cattle Breeding Center"
        ],
        "Office of the Vice President for Administration and Finance" => [
            "Acting Vice President for Administration and Finance & Concurrent Board Secretary",
            "Director, Office of Administrative Services",
            "Supervising Administrative Officer, Administrative Services",
            "Head, Cash Unit","Head, Human Resource Management Unit",
            "Head, Records Unit","Head, Procurement Unit",
            "Head, Supply and Property Management Unit",
            "Director, Office of Financial Services",
            "Supervising Administrative Officer, Financial Services",
            "Head, Accounting Unit","Head, Budget Unit",
            "Director, Office of General and Auxiliary Services",
            "Head, Faculty, Staff and Student Housing Unit","Head, Health Unit",
            "Head, Motorpool and Agri-Machinery Unit",
            "Head, Ground and Physical Plant Improvement Unit",
            "Head, Project Monitoring Committee"
        ],
        "Office of the Vice President for Planning and Resource Generation" => [
            "Vice President for Planning and Resource Generation",
            "Director, Office of Planning, Project Development and Monitoring",
            "Head, Planning Unit","Head, Project Development, Management and Monitoring Unit",
            "Head, Land and Agroecological Resource Management Unit",
            "Head, Disaster Risk Reduction and Management Unit",
            "Head, Management Information System Unit",
            "Director, Office of Business Affairs","Assistant Director, Office of Business Affairs",
            "Head, Agribusiness Unit","Head, Non-Agribusiness Unit","University Economist"
        ],
        "Other Officials" => [
            "President, PSAU Faculty Union","President, PSAU Non-Academic Staff Association",
            "President, Supreme Student Council","President, PSAU Federation of Alumni Associations"
        ]
    ];
    foreach ($seedData as $officeName => $subs) {
        $stmt = $conn->prepare("INSERT INTO offices (office_name) VALUES (?)");
        $stmt->bind_param("s", $officeName);
        $stmt->execute();
        $officeId = $stmt->insert_id;
        $stmt->close();
        foreach ($subs as $sub) {
            $stmt2 = $conn->prepare("INSERT INTO sub_offices (office_id, sub_name) VALUES (?, ?)");
            $stmt2->bind_param("is", $officeId, $sub);
            $stmt2->execute();
            $stmt2->close();
        }
    }
}

// ── Handle Add Office ──
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_office'])) {
    $office_name = trim($_POST['office_name'] ?? '');
    $sub_names   = array_filter(array_map('trim', $_POST['sub_names'] ?? []));
    if (!empty($office_name)) {
        $stmt = $conn->prepare("INSERT INTO offices (office_name) VALUES (?)");
        $stmt->bind_param("s", $office_name);
        if ($stmt->execute()) {
            $new_office_id = $stmt->insert_id;
            foreach ($sub_names as $sub) {
                $s2 = $conn->prepare("INSERT INTO sub_offices (office_id, sub_name) VALUES (?, ?)");
                $s2->bind_param("is", $new_office_id, $sub);
                $s2->execute(); $s2->close();
            }
            $success_message = "Office added successfully!";
        } else { $error_message = "Error adding office: " . $stmt->error; }
        $stmt->close();
    }
}

// ── Handle Edit Office ──
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_office'])) {
    $edit_office_id   = (int)$_POST['edit_office_id'];
    $edit_office_name = trim($_POST['edit_office_name'] ?? '');
    $edit_sub_names   = array_filter(array_map('trim', $_POST['edit_sub_names'] ?? []));
    if (!empty($edit_office_name)) {
        $stmt = $conn->prepare("UPDATE offices SET office_name = ? WHERE id = ?");
        $stmt->bind_param("si", $edit_office_name, $edit_office_id);
        $stmt->execute(); $stmt->close();
        $conn->query("DELETE FROM sub_offices WHERE office_id = $edit_office_id");
        foreach ($edit_sub_names as $sub) {
            $s2 = $conn->prepare("INSERT INTO sub_offices (office_id, sub_name) VALUES (?, ?)");
            $s2->bind_param("is", $edit_office_id, $sub);
            $s2->execute(); $s2->close();
        }
        $success_message = "Office updated successfully!";
    }
}

// ── Handle Delete Office ──
if (isset($_GET['delete_office']) && is_numeric($_GET['delete_office'])) {
    $conn->query("DELETE FROM offices WHERE id = " . (int)$_GET['delete_office']);
    $success_message = "Office deleted successfully!";
}

// ── Fetch all offices with their sub-offices ──
$offices_result = $conn->query("SELECT o.id, o.office_name, o.created_at,
    GROUP_CONCAT(s.sub_name ORDER BY s.id SEPARATOR '||') AS sub_offices
    FROM offices o LEFT JOIN sub_offices s ON o.id = s.office_id
    GROUP BY o.id ORDER BY o.id ASC");

// Get all users
$users = $conn->query("SELECT id, username, full_name, email, department, members, role, created_at FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Manager - PSAU Records System</title>
    <link rel="icon" href="PSAU.ico">
    <link rel="stylesheet" href="assets/css/psau-style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: var(--psau-gray-50);
            font-family: var(--font-sans);
        }
        
        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--psau-primary) 0%, var(--psau-secondary) 100%);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            padding: var(--space-8);
            margin-bottom: var(--space-6);
            border: 1px solid var(--psau-gray-200);
            position: relative;
            overflow: hidden;
            color: var(--psau-white);
        }
        
        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.15"/><circle cx="20" cy="60" r="0.5" fill="white" opacity="0.15"/><circle cx="80" cy="40" r="0.5" fill="white" opacity="0.15"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: var(--space-4);
            position: relative;
            z-index: 1;
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--psau-white);
            margin: 0;
        }
        
        .page-subtitle {
            color: rgba(255, 255, 255, 0.9);
            margin: var(--space-1) 0 0 0;
            font-size: 1rem;
        }
        
        .header-actions {
            display: flex;
            gap: 0.75rem;
        }
        
        .main-content {
            background: var(--psau-white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            border: 1px solid var(--psau-gray-200);
            overflow: hidden;
        }
        
        .content-body {
            padding: 1.5rem;
        }
        
        /* Table Styles */
        .users-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }
        
        .users-table th,
        .users-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--psau-gray-200);
        }
        
        .users-table th {
            font-weight: 600;
            color: var(--psau-gray-700);
            background-color: var(--psau-gray-50);
            border-bottom: 2px solid var(--psau-gray-200);
        }
        
        .users-table tbody tr:hover {
            background-color: var(--psau-gray-50);
        }
        
        /* Role Badge */
        .role-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }
        
        .role-admin {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .role-user {
            background-color: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        
        /* User Avatar */
        .user-avatar {
            width: 32px;
            height: 32px;
            background: var(--psau-primary);
            color: var(--psau-white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            margin-right: 0.75rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-details {
            flex: 1;
        }
        
        .user-name {
            font-weight: 500;
            color: var(--psau-gray-900);
            margin-bottom: 0.125rem;
        }
        
        .user-email {
            font-size: 0.75rem;
            color: var(--psau-gray-500);
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-icon {
            padding: 0.5rem;
            border-radius: var(--radius);
            border: 1px solid var(--psau-gray-300);
            background: var(--psau-white);
            color: var(--psau-gray-600);
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.875rem;
        }
        
        .btn-icon:hover {
            background: var(--psau-gray-50);
            border-color: var(--psau-gray-400);
        }
        
        .btn-icon.delete:hover {
            background: #fef2f2;
            color: #991b1b;
            border-color: #fecaca;
        }
        
        .btn-icon.edit:hover {
            background: #f0f9ff;
            color: #1e40af;
            border-color: #bfdbfe;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.show {
            display: flex;
        }
        
        .modal-content {
            background: var(--psau-white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--psau-gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--psau-gray-900);
            margin: 0;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--psau-gray-400);
            padding: 0;
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius);
        }
        
        .modal-close:hover {
            color: var(--psau-gray-600);
            background: var(--psau-gray-100);
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--psau-gray-200);
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
        }
        
        /* Form Styles */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--psau-gray-700);
            font-size: 0.875rem;
        }
        
        .form-control {
            display: block;
            width: 100%;
            padding: 0.625rem 0.875rem;
            font-size: 0.875rem;
            line-height: 1.5;
            color: var(--psau-gray-900);
            background-color: var(--psau-white);
            border: 1px solid var(--psau-gray-300);
            border-radius: var(--radius);
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-control:focus {
            outline: 0;
            border-color: var(--psau-primary);
            box-shadow: 0 0 0 3px rgb(30 90 61 / 0.1);
        }
        
        /* Alert */
        .alert {
            padding: 0.75rem 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            border: 1px solid;
        }
        
        .alert-success {
            background-color: #f0fdf4;
            color: #166534;
            border-color: #bbf7d0;
        }
        
        .alert-error {
            background-color: #fef2f2;
            color: #991b1b;
            border-color: #fecaca;
        }
        
        /* Confirmation Dialog */
        .confirm-dialog {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        
        .confirm-dialog.show {
            display: flex;
        }
        
        .confirm-dialog-content {
            background: var(--psau-white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            max-width: 400px;
            width: 90%;
            text-align: center;
            padding: 2rem;
        }
        
        .confirm-dialog-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #dc2626;
        }
        
        .confirm-dialog-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--psau-gray-900);
            margin-bottom: 0.5rem;
        }
        
        .confirm-dialog-message {
            color: var(--psau-gray-600);
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }
        
        .confirm-dialog-buttons {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
        }
        
        .btn-confirm {
            background: #dc2626;
            color: white;
            border: none;
            padding: 0.625rem 1.5rem;
            border-radius: var(--radius);
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s ease;
        }
        
        .btn-confirm:hover {
            background: #b91c1c;
        }
        
        .btn-cancel {
            background: var(--psau-gray-200);
            color: var(--psau-gray-700);
            border: none;
            padding: 0.625rem 1.5rem;
            border-radius: var(--radius);
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s ease;
        }
        
        .btn-cancel:hover {
            background: var(--psau-gray-300);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .page-container {
                padding: 1rem;
            }
            
            .header-content {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .users-table {
                font-size: 0.75rem;
            }
            
            .users-table th,
            .users-table td {
                padding: 0.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--psau-gray-500);
        }
        
        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .empty-state-title {
            font-size: 1.125rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--psau-gray-700);
        }
        
        .empty-state-text {
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
        }
        
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
    </style>
</head>
<body>
    <div class="page-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div>
                    <h1 class="page-title">User Manager</h1>
                    <p class="page-subtitle">Manage system users, offices and their access levels</p>
                </div>
                <div class="header-actions" id="headerActions">
                    <button class="btn btn-primary" id="btnCreateUser" onclick="showCreateModal()">+ Create User</button>
                    <button class="btn btn-primary" id="btnCreateOffice" onclick="showOfficeModal()" style="display:none;background:#117a65;">+ Add Office</button>
                </div>
            </div>
        </div>

        <!-- Tab Nav -->
        <div style="display:flex;gap:4px;margin-bottom:0;background:#fff;border-radius:8px 8px 0 0;border:1px solid var(--psau-gray-200);border-bottom:none;overflow:hidden;">
            <button id="tab-users" onclick="switchTab('users')" style="padding:12px 28px;border:none;border-bottom:3px solid var(--psau-primary);background:#fff;font-weight:600;color:var(--psau-primary);cursor:pointer;font-size:0.9rem;">👥 Users</button>
            <button id="tab-offices" onclick="switchTab('offices')" style="padding:12px 28px;border:none;border-bottom:3px solid transparent;background:#fff;font-weight:500;color:var(--psau-gray-500);cursor:pointer;font-size:0.9rem;">🏢 Offices</button>
        </div>

        <!-- Main Content -->
        <div class="main-content" style="border-radius:0 8px 8px 8px;">
            <div class="content-body">
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <!-- ── USERS TAB ── -->
                <div id="section-users">
                <?php if ($users->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Username</th>
                                    <th>Office</th>
                                    <th>Members</th>
                                    <th>Role</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $users->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">
                                                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                                </div>
                                                <div class="user-details">
                                                    <div class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                                    <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['department'] ?? 'Not Assigned'); ?></td>
                                        <td><?php echo htmlspecialchars($user['members'] ?? 'Not Assigned'); ?></td>
                                        <td>
                                            <span class="role-badge role-<?php echo $user['role']; ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-icon edit" onclick="editUser(<?php echo $user['id']; ?>)" title="Edit User">
                                                    ✏️
                                                </button>
                                                <?php if ($user['id'] != $current_user_id): ?>
                                                    <button class="btn-icon delete" onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>')" title="Delete User">
                                                        🗑️
                                                    </button>
                                                <?php else: ?>
                                                    <span style="color: var(--psau-gray-400); font-size: 0.75rem;">Current User</span>
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
                        <div class="empty-state-icon">👥</div>
                        <div class="empty-state-title">No users found</div>
                        <div class="empty-state-text">Create your first user to get started.</div>
                        <button class="btn btn-primary" onclick="showCreateModal()">
                            + Create User
                        </button>
                    </div>
                <?php endif; ?>
                </div><!-- end section-users -->

                <!-- ── OFFICES TAB ── -->
                <div id="section-offices" style="display:none;">
                    <?php if ($offices_result && $offices_result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th style="width:30%">Main Office</th>
                                    <th>Sub-Offices / Units</th>
                                    <th style="width:130px;">Created</th>
                                    <th style="width:90px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php while ($off = $offices_result->fetch_assoc()):
                                $subs = $off['sub_offices'] ? explode('||', $off['sub_offices']) : [];
                            ?>
                                <tr>
                                    <td><strong style="color:var(--psau-primary);"><?= htmlspecialchars($off['office_name']) ?></strong></td>
                                    <td>
                                        <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                        <?php foreach ($subs as $sub): ?>
                                            <span style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;border-radius:9999px;padding:2px 10px;font-size:0.75rem;">
                                                <?= htmlspecialchars($sub) ?>
                                            </span>
                                        <?php endforeach; ?>
                                        <?php if (empty($subs)): ?><span style="color:#aaa;font-size:0.8rem;">None</span><?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($off['created_at'])) ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-icon edit" onclick="editOffice(<?= $off['id'] ?>, <?= htmlspecialchars(json_encode($off['office_name'])) ?>, <?= htmlspecialchars(json_encode($subs)) ?>)" title="Edit Office">✏️</button>
                                            <button class="btn-icon delete" onclick="confirmDeleteOffice(<?= $off['id'] ?>, '<?= htmlspecialchars($off['office_name']) ?>')" title="Delete Office">🗑️</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">🏢</div>
                            <div class="empty-state-title">No offices found</div>
                            <div class="empty-state-text">Add your first office to get started.</div>
                            <button class="btn btn-primary" onclick="showOfficeModal()">+ Add Office</button>
                        </div>
                    <?php endif; ?>
                </div><!-- end section-offices -->

            </div>
        </div>
    </div>

    <!-- ── Add Office Modal ── -->
    <div class="modal" id="officeModal">
        <div class="modal-content" style="max-width:600px;">
            <div class="modal-header">
                <h2 class="modal-title">Add Office</h2>
                <button class="modal-close" onclick="hideOfficeModal()">&times;</button>
            </div>
            <form method="post" autocomplete="off">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Main Office Name *</label>
                        <input type="text" name="office_name" class="form-control" required placeholder="e.g. Office of the President">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sub-Offices / Units</label>
                        <div id="subOfficeList" style="display:flex;flex-direction:column;gap:8px;">
                            <div class="sub-office-row" style="display:flex;gap:6px;align-items:center;">
                                <input type="text" name="sub_names[]" class="form-control" placeholder="e.g. University President">
                                <button type="button" onclick="removeSubRow(this)" style="background:none;border:1px solid #fecaca;color:#991b1b;border-radius:6px;padding:6px 10px;cursor:pointer;white-space:nowrap;">✕</button>
                            </div>
                            <div class="sub-office-row" style="display:flex;gap:6px;align-items:center;">
                                <input type="text" name="sub_names[]" class="form-control" placeholder="e.g. University Secretary">
                                <button type="button" onclick="removeSubRow(this)" style="background:none;border:1px solid #fecaca;color:#991b1b;border-radius:6px;padding:6px 10px;cursor:pointer;white-space:nowrap;">✕</button>
                            </div>
                            <div class="sub-office-row" style="display:flex;gap:6px;align-items:center;">
                                <input type="text" name="sub_names[]" class="form-control" placeholder="e.g. Head, Legal Unit">
                                <button type="button" onclick="removeSubRow(this)" style="background:none;border:1px solid #fecaca;color:#991b1b;border-radius:6px;padding:6px 10px;cursor:pointer;white-space:nowrap;">✕</button>
                            </div>
                        </div>
                        <button type="button" onclick="addSubOfficeRow('subOfficeList','sub_names[]')" style="margin-top:10px;background:#f0fdf4;border:1px dashed #86efac;color:#166534;border-radius:6px;padding:7px 16px;cursor:pointer;font-size:0.85rem;width:100%;">+ Add Sub-Office</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideOfficeModal()">Cancel</button>
                    <button type="submit" name="create_office" class="btn btn-primary" style="background:#117a65;">Save Office</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Edit Office Modal ── -->
    <div class="modal" id="editOfficeModal">
        <div class="modal-content" style="max-width:600px;">
            <div class="modal-header">
                <h2 class="modal-title">Edit Office</h2>
                <button class="modal-close" onclick="hideEditOfficeModal()">&times;</button>
            </div>
            <form method="post" autocomplete="off">
                <input type="hidden" id="edit_office_id" name="edit_office_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Main Office Name *</label>
                        <input type="text" id="edit_office_name" name="edit_office_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sub-Offices / Units</label>
                        <div id="editSubOfficeList" style="display:flex;flex-direction:column;gap:8px;"></div>
                        <button type="button" onclick="addSubOfficeRow('editSubOfficeList','edit_sub_names[]')" style="margin-top:10px;background:#f0fdf4;border:1px dashed #86efac;color:#166534;border-radius:6px;padding:7px 16px;cursor:pointer;font-size:0.85rem;width:100%;">+ Add Sub-Office</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideEditOfficeModal()">Cancel</button>
                    <button type="submit" name="edit_office" class="btn btn-primary" style="background:#117a65;">Update Office</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Create User Modal -->
    <div class="modal" id="createModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Create New User</h2>
                <button class="modal-close" onclick="hideCreateModal()">&times;</button>
            </div>
            <form method="post" autocomplete="off">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username" class="form-label">Username *</label>
                            <input type="text" id="username" name="username" class="form-control" required autocomplete="new-username" readonly onfocus="this.removeAttribute('readonly')">
                        </div>
                        <div class="form-group">
                            <label for="password" class="form-label">Password *</label>
                            <input type="password" id="password" name="password" class="form-control" required autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly')">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="full_name" class="form-label">Full Name *</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="department" class="form-label">Office *</label>
                            <select id="department" name="department" class="form-control" required>
                                <option value="">Select Office</option>
                                <option value="MIS UNIT">MIS UNIT</option>
                                <option value="RECORD UNIT">RECORD UNIT</option>
                                <option value="HR">HR</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="members" class="form-label">Members *</label>
                            <select id="members" name="members" class="form-control" required>
                                <option value="">Select Members</option>
                                <option value="Head">Head</option>
                                <option value="Member">Member</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="role" class="form-label">Role *</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideCreateModal()">Cancel</button>
                    <button type="submit" name="create_user" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit User Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Edit User</h2>
                <button class="modal-close" onclick="hideEditModal()">&times;</button>
            </div>
            <form method="post" autocomplete="off">
                <input type="hidden" id="edit_user_id" name="edit_user_id">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_username" class="form-label">Username *</label>
                            <input type="text" id="edit_username" name="edit_username" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_email" class="form-label">Email *</label>
                            <input type="email" id="edit_email" name="edit_email" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_full_name" class="form-label">Full Name *</label>
                            <input type="text" id="edit_full_name" name="edit_full_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_department" class="form-label">Office *</label>
                            <select id="edit_department" name="edit_department" class="form-control" required>
                                <option value="">Select Office</option>
                                <option value="MIS UNIT">MIS UNIT</option>
                                <option value="RECORD UNIT">RECORD UNIT</option>
                                <option value="HR">HR</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_members" class="form-label">Members *</label>
                            <select id="edit_members" name="edit_members" class="form-control" required>
                                <option value="">Select Members</option>
                                <option value="Head">Head</option>
                                <option value="Member">Member</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_role" class="form-label">Role *</label>
                            <select id="edit_role" name="edit_role" class="form-control" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_password" class="form-label">New Password (leave blank to keep current)</label>
                        <input type="password" id="edit_password" name="edit_password" class="form-control" placeholder="Enter new password or leave blank">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideEditModal()">Cancel</button>
                    <button type="submit" name="edit_user" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Dialog -->
    <div class="confirm-dialog" id="deleteDialog">
        <div class="confirm-dialog-content">
            <div class="confirm-dialog-icon">⚠️</div>
            <h3 class="confirm-dialog-title">Confirm Deletion</h3>
            <p class="confirm-dialog-message" id="deleteMessage">Are you sure you want to delete this user? This action cannot be undone.</p>
            <div class="confirm-dialog-buttons">
                <button class="btn-cancel" id="deleteCancelBtn" onclick="hideDeleteDialog()">Cancel</button>
                <button class="btn-confirm" id="deleteYesBtn">Yes, Delete</button>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Dialog for Offices -->
    <div class="confirm-dialog" id="deleteOfficeDialog">
        <div class="confirm-dialog-content">
            <div class="confirm-dialog-icon">⚠️</div>
            <h3 class="confirm-dialog-title">Confirm Deletion</h3>
            <p class="confirm-dialog-message" id="deleteOfficeMessage">Are you sure you want to delete this office? This action cannot be undone.</p>
            <div class="confirm-dialog-buttons">
                <button class="btn-cancel" id="deleteOfficeCancelBtn" onclick="hideDeleteOfficeDialog()">Cancel</button>
                <button class="btn-confirm" id="deleteOfficeYesBtn">Yes, Delete</button>
            </div>
        </div>
    </div>
    
    <script>
        // ── Tab switching ──
        function switchTab(tab) {
            const isUsers = tab === 'users';
            document.getElementById('section-users').style.display   = isUsers ? '' : 'none';
            document.getElementById('section-offices').style.display = isUsers ? 'none' : '';
            document.getElementById('tab-users').style.borderBottomColor   = isUsers ? 'var(--psau-primary)' : 'transparent';
            document.getElementById('tab-offices').style.borderBottomColor = isUsers ? 'transparent' : '#117a65';
            document.getElementById('tab-users').style.color   = isUsers ? 'var(--psau-primary)' : 'var(--psau-gray-500)';
            document.getElementById('tab-offices').style.color = isUsers ? 'var(--psau-gray-500)' : '#117a65';
            document.getElementById('tab-users').style.fontWeight   = isUsers ? '600' : '500';
            document.getElementById('tab-offices').style.fontWeight = isUsers ? '500' : '600';
            document.getElementById('btnCreateUser').style.display   = isUsers ? '' : 'none';
            document.getElementById('btnCreateOffice').style.display = isUsers ? 'none' : '';
        }

        // ── Office modal helpers ──
        function addSubOfficeRow(listId, inputName) {
            const list = document.getElementById(listId);
            const div = document.createElement('div');
            div.className = 'sub-office-row';
            div.style.cssText = 'display:flex;gap:6px;align-items:center;';
            div.innerHTML = `<input type="text" name="${inputName}" class="form-control" placeholder="Sub-office / unit name">
                <button type="button" onclick="removeSubRow(this)" style="background:none;border:1px solid #fecaca;color:#991b1b;border-radius:6px;padding:6px 10px;cursor:pointer;white-space:nowrap;">✕</button>`;
            list.appendChild(div);
        }

        function removeSubRow(btn) {
            const row = btn.closest('.sub-office-row');
            const list = row.parentElement;
            if (list.querySelectorAll('.sub-office-row').length > 1) {
                row.remove();
            } else {
                row.querySelector('input').value = '';
            }
        }

        function showOfficeModal() {
            document.getElementById('officeModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        function hideOfficeModal() {
            document.getElementById('officeModal').classList.remove('show');
            document.body.style.overflow = '';
        }
        function showEditOfficeModal() {
            document.getElementById('editOfficeModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        function hideEditOfficeModal() {
            document.getElementById('editOfficeModal').classList.remove('show');
            document.body.style.overflow = '';
        }
        function editOffice(id, name, subs) {
            document.getElementById('edit_office_id').value = id;
            document.getElementById('edit_office_name').value = name;
            const list = document.getElementById('editSubOfficeList');
            list.innerHTML = '';
            const subsArr = Array.isArray(subs) ? subs : [];
            const toShow = subsArr.length > 0 ? subsArr : ['','',''];
            toShow.forEach(sub => {
                const div = document.createElement('div');
                div.className = 'sub-office-row';
                div.style.cssText = 'display:flex;gap:6px;align-items:center;';
                div.innerHTML = `<input type="text" name="edit_sub_names[]" class="form-control" value="${sub.replace(/"/g,'&quot;')}" placeholder="Sub-office / unit name">
                    <button type="button" onclick="removeSubRow(this)" style="background:none;border:1px solid #fecaca;color:#991b1b;border-radius:6px;padding:6px 10px;cursor:pointer;white-space:nowrap;">✕</button>`;
                list.appendChild(div);
            });
            showEditOfficeModal();
        }
        function confirmDeleteOffice(id, name) {
            showDeleteOfficeDialog(id, name);
        }
        
        function showDeleteOfficeDialog(id, name) {
            const dialog = document.getElementById('deleteOfficeDialog');
            const messageElement = document.getElementById('deleteOfficeMessage');
            messageElement.textContent = `Are you sure you want to delete office "${name}" and all its sub-offices? This action cannot be undone.`;
            
            // Set up the Yes button to perform the deletion
            const yesBtn = document.getElementById('deleteOfficeYesBtn');
            yesBtn.onclick = function() {
                hideDeleteOfficeDialog();
                // Perform deletion via GET request
                window.location.href = `?delete_office=${id}`;
            };
            
            dialog.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        
        function hideDeleteOfficeDialog() {
            const dialog = document.getElementById('deleteOfficeDialog');
            dialog.classList.remove('show');
            document.body.style.overflow = '';
        }

        // Close office modals on outside click / Escape
        document.getElementById('officeModal').addEventListener('click', function(e) { if (e.target===this) hideOfficeModal(); });
        document.getElementById('editOfficeModal').addEventListener('click', function(e) { if (e.target===this) hideEditOfficeModal(); });

        // ── User Modal functions ──
        function showCreateModal() {
            document.getElementById('createModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        
        function hideCreateModal() {
            document.getElementById('createModal').classList.remove('show');
            document.body.style.overflow = '';
            // Reset form
            document.querySelector('#createModal form').reset();
        }
        
        // Delete confirmation
        function confirmDelete(userId, userName) {
            showDeleteDialog(userId, userName);
        }
        
        // Edit user function
        function editUser(userId) {
            // Fetch user data via AJAX to get department info
            fetch(`get_user_data.php?id=${userId}`)
                .then(response => response.json())
                .then(user => {
                    // Fill the edit form
                    document.getElementById('edit_user_id').value = user.id;
                    document.getElementById('edit_username').value = user.username;
                    document.getElementById('edit_email').value = user.email;
                    document.getElementById('edit_full_name').value = user.full_name;
                    document.getElementById('edit_department').value = user.department || '';
                    document.getElementById('edit_members').value = user.members || '';
                    document.getElementById('edit_role').value = user.role;
                    document.getElementById('edit_password').value = '';
                    
                    // Show the edit modal
                    showEditModal();
                })
                .catch(error => {
                    console.error('Error fetching user data:', error);
                    // Fallback to table data if AJAX fails
                    const row = document.querySelector(`tr:has(button[onclick="editUser(${userId})"])`);
                    const cells = row.getElementsByTagName('td');
                    
                    const userInfo = cells[0].querySelector('.user-name').textContent;
                    const userEmail = cells[0].querySelector('.user-email').textContent;
                    const username = cells[1].textContent;
                    const role = cells[2].querySelector('.role-badge').textContent.toLowerCase();
                    
                    document.getElementById('edit_user_id').value = userId;
                    document.getElementById('edit_username').value = username;
                    document.getElementById('edit_email').value = userEmail;
                    document.getElementById('edit_full_name').value = userInfo;
                    document.getElementById('edit_department').value = '';
                    document.getElementById('edit_role').value = role;
                    document.getElementById('edit_password').value = '';
                    
                    showEditModal();
                });
        }
        
        function showEditModal() {
            document.getElementById('editModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        
        function hideEditModal() {
            document.getElementById('editModal').classList.remove('show');
            document.body.style.overflow = '';
            document.querySelector('#editModal form').reset();
        }
        
        // Delete confirmation dialog
        function showDeleteDialog(userId, userName) {
            const dialog = document.getElementById('deleteDialog');
            const messageElement = document.getElementById('deleteMessage');
            messageElement.textContent = `Are you sure you want to delete user "${userName}"? This action cannot be undone.`;
            
            // Set up the Yes button to perform the deletion
            const yesBtn = document.getElementById('deleteYesBtn');
            yesBtn.onclick = function() {
                hideDeleteDialog();
                // Perform deletion via GET request
                window.location.href = `?delete=${userId}`;
            };
            
            dialog.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        
        function hideDeleteDialog() {
            const dialog = document.getElementById('deleteDialog');
            dialog.classList.remove('show');
            document.body.style.overflow = '';
        }
        
        // Close modal on outside click
        document.getElementById('createModal').addEventListener('click', function(event) {
            if (event.target === this) {
                hideCreateModal();
            }
        });
        
        document.getElementById('editModal').addEventListener('click', function(event) {
            if (event.target === this) {
                hideEditModal();
            }
        });
        
        document.getElementById('deleteDialog').addEventListener('click', function(event) {
            if (event.target === this) {
                hideDeleteDialog();
            }
        });
        
        document.getElementById('deleteOfficeDialog').addEventListener('click', function(event) {
            if (event.target === this) {
                hideDeleteOfficeDialog();
            }
        });
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                hideCreateModal();
                hideEditModal();
                hideOfficeModal();
                hideEditOfficeModal();
                hideDeleteDialog();
                hideDeleteOfficeDialog();
            }
        });

        // Form validation
        document.querySelector('#createModal form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const existingUsernames = <?php echo json_encode(array_column($users->fetch_all(MYSQLI_ASSOC), 'username')); ?>;
            
            if (existingUsernames.includes(username)) {
                e.preventDefault();
                alert('Username "' + username + '" already exists. Please choose a different username.');
                return false;
            }
        });

        document.querySelector('#editModal form').addEventListener('submit', function(e) {
            const username = document.getElementById('edit_username').value.trim();
            const currentUserId = document.getElementById('edit_user_id').value;
            const existingUsers = <?php echo json_encode($users->fetch_all(MYSQLI_ASSOC)); ?>;
            
            const conflictingUser = existingUsers.find(user => 
                user.username === username && user.id != currentUserId
            );
            
            if (conflictingUser) {
                e.preventDefault();
                alert('Username "' + username + '" already exists. Please choose a different username.');
                return false;
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>