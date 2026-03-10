<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header("location: login.php");
    exit;
}

require_once 'connect.php';

// Check if current user is admin
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$current_user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT role FROM property_users WHERE id = $current_user_id");
$current_user = $result->fetch_assoc();

if ($current_user['role'] !== 'admin') {
    header("location: index.php");
    exit();
}

// Handle office creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_office'])) {
    $office_name = trim($_POST['office_name']);
    
    if (!empty($office_name)) {
        $sql = "INSERT INTO offices (office_name) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $office_name);
        
        if ($stmt->execute()) {
            $office_success = "Office added successfully!";
        } else {
            if ($stmt->errno == 1062) {
                $office_error = "Office already exists.";
            } else {
                $office_error = "Error adding office: " . $stmt->error;
            }
        }
    } else {
        $office_error = "Office name is required.";
    }
}

// Handle office deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_office'])) {
    $office_id = $_POST['delete_office_id'];
    
    $sql = "DELETE FROM offices WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $office_id);
    
    if ($stmt->execute()) {
        $office_success = "Office deleted successfully!";
    } else {
        $office_error = "Error deleting office: " . $stmt->error;
    }
}

// Create offices table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS offices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    office_name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Insert default offices if table is empty
$result = $conn->query("SELECT COUNT(*) as count FROM offices");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $default_offices = [
        'PROPERTY MANAGEMENT OFFICE',
        'MIS UNIT',
        'RECORD UNIT',
        'HR'
    ];
    $stmt = $conn->prepare("INSERT INTO offices (office_name) VALUES (?)");
    foreach ($default_offices as $office) {
        $stmt->bind_param("s", $office);
        $stmt->execute();
    }
}

// Fetch all offices for dropdowns
$offices_result = $conn->query("SELECT id, office_name FROM offices ORDER BY office_name ASC");
$offices = [];
while ($office = $offices_result->fetch_assoc()) {
    $offices[] = $office;
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $office = $_POST['office'];
    $members = $_POST['members'];
    $role = $_POST['role'];
    
    $sql = "INSERT INTO property_users (username, password, full_name, email, office, members, role) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $username, $password, $full_name, $email, $office, $members, $role);
    
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
    $office = $_POST['edit_office'];
    $members = $_POST['edit_members'];
    $role = $_POST['edit_role'];
    
    $sql = "UPDATE property_users SET username = ?, full_name = ?, email = ?, office = ?, members = ?, role = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $username, $full_name, $email, $office, $members, $role, $user_id);
    
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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
    $user_id = $_POST['delete_user_id'];
    
    // Prevent admin from deleting themselves
    if ($user_id != $current_user_id) {
        $sql = "DELETE FROM property_users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $success_message = "User deleted successfully!";
        } else {
            $error_message = "Error deleting user: " . $stmt->error;
        }
    } else {
        $error_message = "You cannot delete your own account.";
    }
}

// Fetch all users
$users = $conn->query("SELECT id, username, full_name, email, office, members, role, created_at FROM property_users ORDER BY created_at DESC");
$offices_result = $conn->query("SELECT id, office_name FROM offices ORDER BY office_name ASC");
$offices = [];
while ($office = $offices_result->fetch_assoc()) {
    $offices[] = $office;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Manager - PSAU Property Management</title>
    <link rel="icon" href="PSAU.ico">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Page Container */
        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            background: var(--gray-50);
            min-height: 100vh;
        }

        /* Page Header */
        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            border: 1px solid var(--gray-200);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0 0 0.5rem 0;
        }

        .page-subtitle {
            color: var(--gray-600);
            margin: 0;
            font-size: 1rem;
        }

        /* Main Content */
        .main-content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }

        .content-body {
            padding: 2rem;
        }

        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
        }

        .users-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .users-table th {
            background: var(--gray-50);
            color: var(--gray-700);
            font-weight: 600;
            text-align: left;
            padding: 1rem;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            background-color: var(--gray-50);
            border-bottom: 2px solid var(--gray-200);
        }

        .users-table tbody tr:hover {
            background-color: var(--gray-50);
        }

        .users-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--gray-200);
            vertical-align: middle;
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
            background: var(--primary-color);
            color: white;
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
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.125rem;
        }

        .user-email {
            font-size: 0.875rem;
            color: var(--gray-500);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .btn-icon.edit {
            background: #dbeafe;
            color: #1e40af;
        }

        .btn-icon.edit:hover {
            background: #bfdbfe;
        }

        .btn-icon.delete {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn-icon.delete:hover {
            background: #fecaca;
        }

        /* Buttons */
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--gray-200);
            color: var(--gray-700);
        }

        .btn-secondary:hover {
            background: var(--gray-300);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray-400);
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
        }

        .modal-close:hover {
            background: var(--gray-100);
            color: var(--gray-600);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            padding: 1.5rem;
            border-top: 1px solid var(--gray-200);
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        /* Form Styles */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--gray-700);
            font-size: 0.875rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            font-size: 0.875rem;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(30, 90, 61, 0.1);
        }

        /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .empty-state-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .empty-state-text {
            color: var(--gray-500);
            margin-bottom: 2rem;
        }

        /* Back Link */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 2rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: background-color 0.2s;
        }

        .back-link:hover {
            background: var(--gray-100);
        }

        /* Office Management Styles */
        .office-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid var(--gray-200);
        }

        .office-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .office-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 0.75rem;
        }

        .office-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            min-height: 60px;
            box-sizing: border-box;
        }

        .office-name {
            font-weight: 500;
            color: var(--gray-700);
            word-break: break-word;
            line-height: 1.3;
            flex: 1;
            padding-right: 0.5rem;
        }

        .office-actions {
            display: flex;
            gap: 0.25rem;
        }

        .btn-icon-sm {
            width: 24px;
            height: 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            transition: all 0.2s;
        }

        .btn-icon-sm.delete {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn-icon-sm.delete:hover {
            background: #fecaca;
        }

        .empty-offices {
            text-align: center;
            padding: 2rem;
            color: var(--gray-500);
        }
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
                font-size: 0.875rem;
            }

            .users-table th,
            .users-table td {
                padding: 0.75rem 0.5rem;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <a href="index.php" class="back-link">← Back to Dashboard</a>
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div>
                    <h1 class="page-title">User Manager</h1>
                    <p class="page-subtitle">Manage system users and their access levels</p>
                </div>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="showCreateModal()">
                        + Create User
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="content-body">
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        ✅ <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error">
                        ⚠️ <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
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
                                        <td><?php echo htmlspecialchars($user['office'] ?? 'Not Assigned'); ?></td>
                                        <td><?php echo htmlspecialchars($user['members'] ?? 'Not Assigned'); ?></td>
                                        <td>
                                            <span class="role-badge role-<?php echo $user['role']; ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-icon edit" onclick="editUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>', '<?php echo htmlspecialchars($user['full_name']); ?>', '<?php echo htmlspecialchars($user['email']); ?>', '<?php echo htmlspecialchars($user['office'] ?? ''); ?>', '<?php echo htmlspecialchars($user['members'] ?? ''); ?>', '<?php echo $user['role']; ?>')" title="Edit User">
                                                    ✏️
                                                </button>
                                                <?php if ($user['id'] != $current_user_id): ?>
                                                    <button class="btn-icon delete" onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>')" title="Delete User">
                                                        🗑️
                                                    </button>
                                                <?php else: ?>
                                                    <span style="color: var(--gray-400); font-size: 0.75rem;">Current User</span>
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
                
                <!-- Office Management Section -->
                <div class="office-section">
                    <div class="office-header">
                        <h3 style="margin: 0; color: var(--gray-700);">Manage Offices</h3>
                        <button type="button" class="btn btn-primary" onclick="showAddOfficeModal()">
                            + Add Office
                        </button>
                    </div>
                    
                    <?php if (isset($office_success)): ?>
                        <div class="alert alert-success">
                            ✅ <?php echo htmlspecialchars($office_success); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($office_error)): ?>
                        <div class="alert alert-error">
                            ⚠️ <?php echo htmlspecialchars($office_error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (count($offices) > 0): ?>
                        <div class="office-list">
                            <?php foreach ($offices as $office): ?>
                                <div class="office-item">
                                    <span class="office-name"><?php echo htmlspecialchars($office['office_name']); ?></span>
                                    <div class="office-actions">
                                        <button type="button" class="btn-icon-sm delete" onclick="confirmDeleteOffice(<?php echo $office['id']; ?>, '<?php echo htmlspecialchars($office['office_name']); ?>')" title="Delete Office">
                                            🗑️
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-offices">
                            No offices added yet. Click "Add Office" to create one.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
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
                            <label for="office" class="form-label">Office *</label>
                            <select id="office" name="office" class="form-control" required>
                                <option value="">Select Office</option>
                                <?php foreach ($offices as $office): ?>
                                    <option value="<?php echo htmlspecialchars($office['office_name']); ?>"><?php echo htmlspecialchars($office['office_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="members" class="form-label">Members *</label>
                            <select id="members" name="members" class="form-control" required>
                                <option value="">Select Members</option>
                                <option value="Head">Head</option>
                                <option value="Staff">Staff</option>
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
                            <label for="edit_office" class="form-label">Office *</label>
                            <select id="edit_office" name="edit_office" class="form-control" required>
                                <option value="">Select Office</option>
                                <?php foreach ($offices as $office): ?>
                                    <option value="<?php echo htmlspecialchars($office['office_name']); ?>"><?php echo htmlspecialchars($office['office_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_members" class="form-label">Members *</label>
                            <select id="edit_members" name="edit_members" class="form-control" required>
                                <option value="">Select Members</option>
                                <option value="Head">Head</option>
                                <option value="Staff">Staff</option>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideEditModal()">Cancel</button>
                    <button type="submit" name="edit_user" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Office Modal -->
    <div class="modal" id="addOfficeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Add New Office</h2>
                <button class="modal-close" onclick="hideAddOfficeModal()">&times;</button>
            </div>
            <form method="post" autocomplete="off">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="office_name" class="form-label">Office Name *</label>
                        <input type="text" id="office_name" name="office_name" class="form-control" required placeholder="Enter office name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideAddOfficeModal()">Cancel</button>
                    <button type="submit" name="create_office" class="btn btn-primary">Add Office</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Office Confirmation Modal -->
    <div class="modal" id="deleteOfficeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Confirm Delete Office</h2>
                <button class="modal-close" onclick="hideDeleteOfficeModal()">&times;</button>
            </div>
            <form method="post">
                <input type="hidden" id="delete_office_id" name="delete_office_id">
                <div class="modal-body">
                    <p>Are you sure you want to delete office <strong id="delete_office_name"></strong>? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideDeleteOfficeModal()">Cancel</button>
                    <button type="submit" name="delete_office" class="btn btn-primary" style="background: #dc2626;">Delete Office</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Confirm Delete</h2>
                <button class="modal-close" onclick="hideDeleteModal()">&times;</button>
            </div>
            <form method="post">
                <input type="hidden" id="delete_user_id" name="delete_user_id">
                <div class="modal-body">
                    <p>Are you sure you want to delete user <strong id="delete_user_name"></strong>? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideDeleteModal()">Cancel</button>
                    <button type="submit" name="delete_user" class="btn btn-primary" style="background: #dc2626;">Delete User</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal Functions
        function showCreateModal() {
            document.getElementById('createModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function hideCreateModal() {
            document.getElementById('createModal').classList.remove('show');
            document.body.style.overflow = 'auto';
            document.querySelector('#createModal form').reset();
        }

        function showEditModal() {
            document.getElementById('editModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function hideEditModal() {
            document.getElementById('editModal').classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        function showDeleteModal() {
            document.getElementById('deleteModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function hideDeleteModal() {
            document.getElementById('deleteModal').classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        // Edit User Function
        function editUser(id, username, fullName, email, office, members, role) {
            document.getElementById('edit_user_id').value = id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_full_name').value = fullName;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_office').value = office;
            document.getElementById('edit_members').value = members;
            document.getElementById('edit_role').value = role;
            showEditModal();
        }

        // Delete User Function
        function confirmDelete(id, fullName) {
            document.getElementById('delete_user_id').value = id;
            document.getElementById('delete_user_name').textContent = fullName;
            showDeleteModal();
        }

        // Office Modal Functions
        function showAddOfficeModal() {
            document.getElementById('addOfficeModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function hideAddOfficeModal() {
            document.getElementById('addOfficeModal').classList.remove('show');
            document.body.style.overflow = 'auto';
            document.querySelector('#addOfficeModal form').reset();
        }

        function showDeleteOfficeModal() {
            document.getElementById('deleteOfficeModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function hideDeleteOfficeModal() {
            document.getElementById('deleteOfficeModal').classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        function confirmDeleteOffice(id, name) {
            document.getElementById('delete_office_id').value = id;
            document.getElementById('delete_office_name').textContent = name;
            showDeleteOfficeModal();
        }
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('show');
                document.body.style.overflow = 'auto';
            }
        }

        // Close modals with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.modal.show').forEach(modal => {
                    modal.classList.remove('show');
                });
                document.body.style.overflow = 'auto';
            }
        });
    </script>
</body>
</html>
