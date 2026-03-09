<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit();
}

require_once 'connect.php';

// Get current user info
$conn = new mysqli($servername, $username, $password, $dbname);
$result = $conn->query("SELECT username, full_name, department, role FROM users WHERE id = " . $_SESSION['user_id']);
$current_user = $result->fetch_assoc();


// Get documents from user's office only
$documents = $conn->query("SELECT d.*, u.full_name as registered_by_name, u.department 
                          FROM records_document_main d 
                          LEFT JOIN users u ON d.added_by = u.id 
                          WHERE u.department = '" . $current_user['department'] . "'
                          ORDER BY d.date_added DESC");

// Get accepted requests for this office
$accepted_requests = $conn->query("SELECT dr.*, d.document_title, d.document_type, d.received_from, d.date_added, d.serial_code,
                                   u.full_name as requester_name, u.department as requester_department,
                                   owner.full_name as owner_name
                                   FROM document_requests dr
                                   JOIN records_document_main d ON dr.document_id = d.idrecords_document_main
                                   JOIN users u ON dr.requester_id = u.id
                                   JOIN users owner ON d.added_by = owner.id
                                   WHERE dr.requester_department = '" . $current_user['department'] . "' 
                                   AND dr.status = 'accepted'
                                   ORDER BY dr.responded_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Office Documents - PSAU Records System</title>
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
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
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

        .header-actions {
            display: flex;
            gap: 1rem;
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

        .btn-primary {
            background: var(--psau-primary);
            color: var(--psau-white);
        }

        .btn-primary:hover {
            background: #1a4e33;
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
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

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-action {
            padding: 0.375rem 0.75rem;
            border-radius: var(--radius);
            border: 1px solid var(--psau-gray-300);
            background: var(--psau-white);
            color: var(--psau-gray-600);
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.75rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .btn-action:hover {
            background: var(--psau-gray-50);
            border-color: var(--psau-gray-400);
        }
        
        .btn-action.primary {
            background: var(--psau-primary);
            color: var(--psau-white);
            border-color: var(--psau-primary);
        }
        
        .btn-action.primary:hover {
            background: var(--psau-secondary);
            border-color: var(--psau-secondary);
        }

        /* Tracking Status Styles */
        .tracking-status {
            background: var(--psau-gray-50);
            padding: 1rem;
            border-radius: var(--radius);
            border: 1px solid var(--psau-gray-200);
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .status-item:last-child {
            margin-bottom: 0;
        }

        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .status-dot.completed {
            background: #22c55e;
        }

        .status-dot.pending {
            background: #94a3b8;
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

        /* Table Styles */
        .documents-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .documents-table th {
            background: var(--psau-gray-50);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--psau-gray-700);
            border-bottom: 2px solid var(--psau-gray-200);
        }

        .documents-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--psau-gray-200);
        }

        .documents-table tbody tr:hover {
            background: var(--psau-gray-50);
        }

        .table-responsive {
            overflow-x: auto;
            border-radius: var(--radius);
            border: 1px solid var(--psau-gray-200);
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

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--psau-gray-600);
            font-size: 0.875rem;
        }

        .badge {
            background: var(--psau-accent);
            color: var(--psau-white);
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius);
            font-size: 0.75rem;
            font-weight: 500;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--psau-gray-50);
            padding: 1.5rem;
            border-radius: var(--radius);
            border: 1px solid var(--psau-gray-200);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 600;
            color: var(--psau-primary);
        }

        .stat-label {
            color: var(--psau-gray-600);
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div>
                    <h1 class="page-title">Office Documents</h1>
                    <p class="page-subtitle">Documents from your office</p>
                    <div class="department-badge">
                        🏢 <?php echo htmlspecialchars($current_user['department']); ?>
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
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <!-- Statistics -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $documents->num_rows; ?></div>
                        <div class="stat-label">Total Documents</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo htmlspecialchars($current_user['department']); ?></div>
                        <div class="stat-label">Your Office</div>
                    </div>
                </div>
                
                <?php if ($documents->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="documents-table">
                            <thead>
                                <tr>
                                    <th>Document Title</th>
                                    <th>Document Type</th>
                                    <th>Received From</th>
                                    <th>Registered By</th>
                                    <th>Date Added</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($document = $documents->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($document['document_title']); ?></strong></td>
                                        <td><span class="badge"><?php echo htmlspecialchars($document['document_type']); ?></span></td>
                                        <td><?php echo htmlspecialchars($document['received_from']); ?></td>
                                        <td><?php echo htmlspecialchars($document['registered_by_name']); ?></td>
                                        <td><?php echo date('M d, Y h:i A', strtotime($document['date_added'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <form action='printqrnow.php' method='POST' target='_blank' style="display: inline;">
                                                    <button type="submit" name='RefID' value="<?php echo $document['serial_code']; ?>" 
                                                            class="btn-action primary" title="Print QR">
                                                        🖨️ Print
                                                    </button>
                                                </form>
                                                <form action='details.php' method='POST' style="display: inline;">
                                                    <button type="submit" name='RefID' value="<?php echo $document['serial_code']; ?>" 
                                                            class="btn-action" title="Track Document">
                                                        🔍 Track
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📄</div>
                        <div class="empty-state-title">No office documents found</div>
                        <div class="empty-state-text">
                            No documents have been registered by your office yet.
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Accepted Requests Section -->
                <div class="section">
                    <h2 class="section-title">
                        📋 Accepted Document Requests
                    </h2>
                    
                    <?php if ($accepted_requests->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="documents-table">
                                <thead>
                                    <tr>
                                        <th>Document Title</th>
                                        <th>Document Type</th>
                                        <th>From Office</th>
                                        <th>Received From</th>
                                        <th>Accepted Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($request = $accepted_requests->fetch_assoc()): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($request['document_title']); ?></strong></td>
                                            <td><span class="badge"><?php echo htmlspecialchars($request['document_type']); ?></span></td>
                                            <td><?php echo htmlspecialchars($request['requester_department']); ?></td>
                                            <td><?php echo htmlspecialchars($request['received_from']); ?></td>
                                            <td><?php echo date('M d, Y h:i A', strtotime($request['responded_at'])); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <form action='printqrnow.php' method='POST' target='_blank' style="display: inline;">
                                                        <button type="submit" name='RefID' value="<?php echo $request['serial_code']; ?>" 
                                                                class="btn-action primary" title="Print QR">
                                                            🖨️ Print
                                                        </button>
                                                    </form>
                                                    <form action='details.php' method='POST' style="display: inline;">
                                                        <button type="submit" name='RefID' value="<?php echo $request['serial_code']; ?>" 
                                                                class="btn-action" title="Track Document">
                                                            � Track
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📋</div>
                            <div class="empty-state-title">No accepted requests</div>
                            <div class="empty-state-text">No document requests have been accepted yet.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // No registration functionality available for regular users
    </script>
</body>
</html>
<?php $conn->close(); ?>
