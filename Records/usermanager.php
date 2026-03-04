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
    $role = $_POST['role'];
    
    $sql = "INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $username, $password, $full_name, $email, $role);
    
    if ($stmt->execute()) {
        $success_message = "User created successfully!";
    } else {
        $error_message = "Error creating user: " . $stmt->error;
    }
}

// Handle user editing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_user'])) {
    $user_id = $_POST['edit_user_id'];
    $username = $_POST['edit_username'];
    $full_name = $_POST['edit_full_name'];
    $email = $_POST['edit_email'];
    $role = $_POST['edit_role'];
    $password = $_POST['edit_password'];
    
    // Build update query
    if (!empty($password)) {
        // Update with new password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET username = ?, full_name = ?, email = ?, role = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $username, $full_name, $email, $role, $password_hash, $user_id);
    } else {
        // Update without changing password
        $sql = "UPDATE users SET username = ?, full_name = ?, email = ?, role = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $username, $full_name, $email, $role, $user_id);
    }
    
    if ($stmt->execute()) {
        $success_message = "User updated successfully!";
    } else {
        $error_message = "Error updating user: " . $stmt->error;
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

// Get all users
$users = $conn->query("SELECT id, username, full_name, email, role, created_at FROM users ORDER BY created_at DESC");
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
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($users->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Username</th>
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
    
    <script>
        // Modal functions
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
            if (confirm(`Are you sure you want to delete user "${userName}"? This action cannot be undone.`)) {
                window.location.href = `?delete=${userId}`;
            }
        }
        
        // Edit user function
        function editUser(userId) {
            // Get user data from the table row
            const row = document.querySelector(`tr:has(button[onclick="editUser(${userId})"])`);
            const cells = row.getElementsByTagName('td');
            
            // Extract user information
            const userInfo = cells[0].querySelector('.user-name').textContent;
            const userEmail = cells[0].querySelector('.user-email').textContent;
            const username = cells[1].textContent;
            const role = cells[2].querySelector('.role-badge').textContent.toLowerCase();
            
            // Fill the edit form
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_email').value = userEmail;
            document.getElementById('edit_full_name').value = userInfo;
            document.getElementById('edit_role').value = role;
            document.getElementById('edit_password').value = '';
            
            // Show the edit modal
            showEditModal();
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
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                hideCreateModal();
                hideEditModal();
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>
