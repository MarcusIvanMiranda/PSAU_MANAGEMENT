<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit();
}

require_once 'connect.php';

// Get current user info
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SELECT username, full_name, department, role, members FROM users WHERE id = " . $_SESSION['user_id']);
if (!$result) {
    die("Query failed: " . $conn->error);
}
$current_user = $result->fetch_assoc();

// Check if user is Head
if ($current_user['members'] !== 'Head') {
    header("location: index.php");
    exit();
}

// Handle request responses
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['accept_request'])) {
        $request_id = $_POST['request_id'];
        $sql = "UPDATE document_requests SET status = 'accepted', responded_at = NOW(), responded_by = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $_SESSION['user_id'], $request_id);
        $stmt->execute();
        $success_message = "Request accepted successfully!";
    } elseif (isset($_POST['reject_request'])) {
        $request_id = $_POST['request_id'];
        $sql = "UPDATE document_requests SET status = 'rejected', responded_at = NOW(), responded_by = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $_SESSION['user_id'], $request_id);
        $stmt->execute();
        $success_message = "Request rejected successfully!";
    }
}

// Get pending requests FOR this office's documents (can accept/reject)
$pending_for_department = $conn->query("SELECT dr.*, d.document_title, d.document_type, d.received_from, 
                                      u.full_name as requester_name, u.department as requester_department
                                      FROM document_requests dr
                                      JOIN records_document_main d ON dr.document_id = d.idrecords_document_main
                                      JOIN users u ON dr.requester_id = u.id
                                      WHERE dr.owner_department = '" . $current_user['department'] . "' 
                                      AND dr.status = 'pending'
                                      ORDER BY dr.requested_at DESC");

// Get requests FROM this office (can't accept/reject, just view status)
$requests_from_department = $conn->query("SELECT dr.*, d.document_title, d.document_type, d.received_from, 
                                         u.full_name as requester_name, u.department as requester_department,
                                         owner.full_name as owner_name, owner.department as owner_department,
                                         responder.full_name as responder_name, responder.department as responder_department
                                         FROM document_requests dr
                                         JOIN records_document_main d ON dr.document_id = d.idrecords_document_main
                                         JOIN users u ON dr.requester_id = u.id
                                         JOIN users owner ON d.added_by = owner.id
                                         LEFT JOIN users responder ON dr.responded_by = responder.id
                                         WHERE dr.requester_department = '" . $current_user['department'] . "'
                                         ORDER BY dr.requested_at DESC");

// Get all requests history FOR this office's documents
$all_requests_for_department = $conn->query("SELECT dr.*, d.document_title, d.document_type, d.received_from, 
                                           u.full_name as requester_name, u.department as requester_department,
                                           responder.full_name as responder_name, responder.department as responder_department
                                           FROM document_requests dr
                                           JOIN records_document_main d ON dr.document_id = d.idrecords_document_main
                                           JOIN users u ON dr.requester_id = u.id
                                           LEFT JOIN users responder ON dr.responded_by = responder.id
                                           WHERE dr.owner_department = '" . $current_user['department'] . "'
                                           ORDER BY dr.requested_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Requests - PSAU Records System</title>
    <link rel="icon" href="PSAU.ico">
    <link rel="stylesheet" href="assets/css/psau-style.css">
    <style>
        :root {
            --psau-primary: #1e5a3d;
            --psau-accent: #4a9d6f;
            --psau-secondary: #f8f9fa;
            --psau-white: #ffffff;
            --psau-gray-100: #f8f9fa;
            --psau-gray-200: #e9ecef;
            --psau-gray-300: #dee2e6;
            --psau-gray-400: #ced4da;
            --psau-gray-500: #adb5bd;
            --psau-gray-600: #6c757d;
            --psau-gray-700: #495057;
            --psau-gray-800: #343a40;
            --psau-gray-900: #212529;
            --radius: 0.375rem;
            --radius-lg: 0.5rem;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            background: var(--psau-white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            padding: 2rem;
            margin-bottom: 2rem;
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
            font-weight: 600;
            color: var(--psau-primary);
            margin: 0;
        }

        .page-subtitle {
            color: var(--psau-gray-600);
            margin: 0.5rem 0 0 0;
        }

        .department-badge {
            background: var(--psau-accent);
            color: var(--psau-white);
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .main-content {
            background: var(--psau-white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .content-body {
            padding: 2rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-success {
            background: #22c55e;
            color: var(--psau-white);
        }

        .btn-success:hover {
            background: #16a34a;
        }

        .btn-danger {
            background: #ef4444;
            color: var(--psau-white);
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-secondary {
            background: var(--psau-gray-200);
            color: var(--psau-gray-700);
        }

        .btn-secondary:hover {
            background: var(--psau-gray-300);
        }

        .btn-sm {
            padding: 0.5rem 0.75rem;
            font-size: 0.75rem;
        }

        .section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--psau-gray-800);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .badge {
            background: var(--psau-accent);
            color: var(--psau-white);
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius);
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-pending {
            background: #f59e0b;
        }

        .badge-accepted {
            background: #22c55e;
        }

        .badge-rejected {
            background: #ef4444;
        }

        .request-card {
            background: var(--psau-gray-50);
            border: 1px solid var(--psau-gray-200);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .request-info {
            flex: 1;
        }

        .request-title {
            font-weight: 600;
            color: var(--psau-gray-800);
            margin-bottom: 0.5rem;
        }

        .request-meta {
            font-size: 0.875rem;
            color: var(--psau-gray-600);
        }

        .request-actions {
            display: flex;
            gap: 0.5rem;
        }

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

        .table-responsive {
            overflow-x: auto;
            border-radius: var(--radius);
            border: 1px solid var(--psau-gray-200);
        }

        .requests-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .requests-table th {
            background: var(--psau-gray-50);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--psau-gray-700);
            border-bottom: 2px solid var(--psau-gray-200);
        }

        .requests-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--psau-gray-200);
        }

        .requests-table tbody tr:hover {
            background: var(--psau-gray-50);
        }

        .text-muted {
            color: var(--psau-gray-500);
        }
    </style>
</head>
<body>
    <div class="page-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div>
                    <h1 class="page-title">Manage Document Requests</h1>
                    <p class="page-subtitle">Review and respond to document requests for your office</p>
                    <div class="department-badge">
                        👑 <?php echo htmlspecialchars($current_user['department']); ?>
                    </div>
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
                
                <!-- Pending Requests Section -->
                <div class="section">
                    <h2 class="section-title">
                        ⏳ Pending Requests FOR Your Department 
                        <?php if ($pending_for_department->num_rows > 0): ?>
                            <span class="badge badge-pending"><?php echo $pending_for_department->num_rows; ?></span>
                        <?php endif; ?>
                    </h2>
                    
                    <?php if ($pending_for_department->num_rows > 0): ?>
                        <?php while ($request = $pending_for_department->fetch_assoc()): ?>
                            <div class="request-card">
                                <div class="request-header">
                                    <div class="request-info">
                                        <div class="request-title"><?php echo htmlspecialchars($request['document_title']); ?></div>
                                        <div class="request-meta">
                                            <strong>From:</strong> <?php echo htmlspecialchars($request['requester_name']); ?> 
                                            (<?php echo htmlspecialchars($request['requester_department']); ?>)<br>
                                            <strong>Document Type:</strong> <?php echo htmlspecialchars($request['document_type']); ?><br>
                                            <strong>Received From:</strong> <?php echo htmlspecialchars($request['received_from']); ?><br>
                                            <strong>Requested:</strong> <?php echo date('M d, Y h:i A', strtotime($request['requested_at'])); ?>
                                        </div>
                                    </div>
                                    <div class="request-actions">
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                            <button type="submit" name="accept_request" class="btn btn-success btn-sm">
                                                ✅ Accept
                                            </button>
                                        </form>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                            <button type="submit" name="reject_request" class="btn btn-danger btn-sm">
                                                ❌ Reject
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📋</div>
                            <div class="empty-state-title">No pending requests for your department</div>
                            <div class="empty-state-text">No departments have requested your documents yet.</div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Your Request History Section -->
                <div class="section">
                    <h2 class="section-title">
                        📋 Your Request History 
                        <?php if ($requests_from_department->num_rows > 0): ?>
                            <span class="badge"><?php echo $requests_from_department->num_rows; ?></span>
                        <?php endif; ?>
                    </h2>
                    
                    <?php if ($requests_from_department->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="requests-table">
                                <thead>
                                    <tr>
                                        <th>Document</th>
                                        <th>From Department</th>
                                        <th>Requested</th>
                                        <th>Status</th>
                                        <th>Response Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($request = $requests_from_department->fetch_assoc()): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($request['document_title']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($request['owner_department']); ?></td>
                                            <td><?php echo date('M d, Y h:i A', strtotime($request['requested_at'])); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $request['status']; ?>">
                                                    <?php echo ucfirst($request['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($request['responded_by']): ?>
                                                    <div>
                                                        <strong><?php echo ucfirst($request['status']); ?> by:</strong><br>
                                                        <?php echo htmlspecialchars($request['responder_name']); ?><br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($request['responder_department']); ?></small><br>
                                                        <small class="text-muted"><?php echo date('M d, Y h:i A', strtotime($request['responded_at'])); ?></small>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">Not yet responded</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📋</div>
                            <div class="empty-state-title">No requests from your department</div>
                            <div class="empty-state-text">Your department hasn't requested any documents yet.</div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Department Request History -->
                <div class="section">
                    <h2 class="section-title">
                        📊 Department Request History 
                        <?php if ($all_requests_for_department->num_rows > 0): ?>
                            <span class="badge"><?php echo $all_requests_for_department->num_rows; ?></span>
                        <?php endif; ?>
                    </h2>
                    
                    <?php if ($all_requests_for_department->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="requests-table">
                                <thead>
                                    <tr>
                                        <th>Document</th>
                                        <th>Requester</th>
                                        <th>Department</th>
                                        <th>Requested</th>
                                        <th>Status</th>
                                        <th>Response Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($request = $all_requests_for_department->fetch_assoc()): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($request['document_title']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($request['requester_name']); ?></td>
                                            <td><?php echo htmlspecialchars($request['requester_department']); ?></td>
                                            <td><?php echo date('M d, Y h:i A', strtotime($request['requested_at'])); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $request['status']; ?>">
                                                    <?php echo ucfirst($request['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($request['responded_by']): ?>
                                                    <div>
                                                        <strong><?php echo ucfirst($request['status']); ?> by:</strong><br>
                                                        <?php echo htmlspecialchars($request['responder_name']); ?><br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($request['responder_department']); ?></small><br>
                                                        <small class="text-muted"><?php echo date('M d, Y h:i A', strtotime($request['responded_at'])); ?></small>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">Not yet responded</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📋</div>
                            <div class="empty-state-title">No requests found</div>
                            <div class="empty-state-text">No document requests have been made for your department's documents.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
