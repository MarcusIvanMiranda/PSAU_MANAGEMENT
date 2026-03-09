<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit();
}

require_once 'connect.php';

// Get current user info
$conn = new mysqli($servername, $username, $password, $dbname);
$result = $conn->query("SELECT username, full_name, department, role, members FROM users WHERE id = " . $_SESSION['user_id']);
$current_user = $result->fetch_assoc();

// Handle document registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_document'])) {
    $title = $_POST['title'];
    $document_type = $_POST['document_type'];
    $received_from = $_POST['received_from'];
    $added_by = $_SESSION['user_id'];
    
    $sql = "INSERT INTO records_document_main (document_title, document_type, received_from, added_by) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $title, $document_type, $received_from, $added_by);
    
    if ($stmt->execute()) {
        $success_message = "Document registered successfully!";
    } else {
        $error_message = "Error registering document: " . $stmt->error;
    }
}

// Get all documents for display - exclude documents from user's own office
$documents = $conn->query("SELECT d.*, u.full_name as registered_by_name, u.department 
                          FROM records_document_main d 
                          LEFT JOIN users u ON d.added_by = u.id 
                          WHERE u.department != '" . $current_user['department'] . "' OR u.department IS NULL
                          ORDER BY d.date_added DESC");

// Get pending requests for current user
$pending_requests = $conn->query("SELECT document_id FROM document_requests 
                                 WHERE requester_id = " . $_SESSION['user_id'] . " 
                                 AND status = 'pending'");
$pending_docs = [];
while ($row = $pending_requests->fetch_assoc()) {
    $pending_docs[] = $row['document_id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Management - PSAU Records System</title>
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

        .btn-action.primary:hover {
            background: var(--psau-secondary);
            border-color: var(--psau-secondary);
        }

        .btn-action.disabled {
            background: var(--psau-gray-200);
            color: var(--psau-gray-400);
            border-color: var(--psau-gray-300);
            cursor: not-allowed;
            opacity: 0.7;
        }

        .pending-text {
            font-size: 0.75rem;
            color: #f59e0b;
            font-weight: 500;
            margin-top: 0.25rem;
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
    </style>
</head>
<body>
    <div class="page-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div>
                    <h1 class="page-title">Other Offices Documents</h1>
                    <p class="page-subtitle">Documents from other offices (Head Access Only)</p>
                    <div class="user-info">
                        <span>Logged in as: <strong><?php echo htmlspecialchars($current_user['full_name']); ?></strong></span>
                        <span class="badge"><?php echo htmlspecialchars($current_user['department']); ?></span>
                        <span class="badge" style="background: #dc2626;">👑 Head</span>
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
                
                <?php if ($documents->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="documents-table">
                            <thead>
                                <tr>
                                    <th>Document Title</th>
                                    <th>Office</th>
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
                                        <td><?php echo htmlspecialchars($document['department'] ?? 'Not Assigned'); ?></td>
                                        <td><?php echo htmlspecialchars($document['received_from']); ?></td>
                                        <td><?php echo htmlspecialchars($document['registered_by_name']); ?></td>
                                        <td><?php echo date('M d, Y h:i A', strtotime($document['date_added'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <?php if (in_array($document['idrecords_document_main'], $pending_docs)): ?>
                                                    <button class="btn-action disabled" disabled title="Request Pending">
                                                        📋 Requested
                                                    </button>
                                                <?php else: ?>
                                                    <?php if (!empty($document['department'])): ?>
                                                        <form action='request_document.php' method='POST' style="display: inline;">
                                                            <input type="hidden" name="document_id" value="<?php echo $document['idrecords_document_main']; ?>">
                                                            <input type="hidden" name="owner_department" value="<?php echo htmlspecialchars($document['department']); ?>">
                                                            <button type="submit" class="btn-action primary" title="Request Document">
                                                                📋 Request
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <button class="btn-action disabled" disabled title="No Office Assigned">
                                                            📋 No Dept
                                                        </button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (in_array($document['idrecords_document_main'], $pending_docs)): ?>
                                                <div class="pending-text">⏳ Pending</div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📄</div>
                        <div class="empty-state-title">No documents from other offices</div>
                        <div class="empty-state-text">
                            No documents have been registered by other offices yet.
                        </div>
                        <?php if ($current_user['role'] === 'user'): ?>
                            <button class="btn btn-primary" onclick="showRegisterModal()">
                                + Register Document
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Register Document Modal -->
    <?php if ($current_user['role'] === 'user'): ?>
    <div class="modal" id="registerModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Register New Document</h2>
                <button class="modal-close" onclick="hideRegisterModal()">&times;</button>
            </div>
            <form method="post" autocomplete="off">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="title" class="form-label">Document Title *</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="document_type" class="form-label">Document Type *</label>
                        <select id="document_type" name="document_type" class="form-control" required>
                            <option value="">Select Document Type</option>
                            <option value="Official Receipt">Official Receipt</option>
                            <option value="Purchase Order">Purchase Order</option>
                            <option value="Invoice">Invoice</option>
                            <option value="Memorandum">Memorandum</option>
                            <option value="Letter">Letter</option>
                            <option value="Report">Report</option>
                            <option value="Contract">Contract</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="received_from" class="form-label">Received From *</label>
                        <input type="text" id="received_from" name="received_from" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideRegisterModal()">Cancel</button>
                    <button type="submit" name="register_document" class="btn btn-primary">Register Document</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
    
    <script>
        // Modal functions
        function showRegisterModal() {
            document.getElementById('registerModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        
        function hideRegisterModal() {
            document.getElementById('registerModal').classList.remove('show');
            document.body.style.overflow = '';
            // Reset form
            document.querySelector('#registerModal form').reset();
        }
        
        // Track document function
        function trackDocument(documentId) {
            // Create tracking modal
            const modalHtml = `
                <div class="modal" id="trackModal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title">Track Document</h2>
                            <button class="modal-close" onclick="closeTrackModal()">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">Document ID:</label>
                        </div>
                    </div>
                </div>
            `;
            
            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            document.getElementById('trackModal').classList.add('show');
        }
        
        function closeTrackModal() {
            const modal = document.getElementById('trackModal');
            if (modal) {
                modal.remove();
            }
        }
        
        // Print document function
        function printDocument(documentId) {
            // Create print content
            const printContent = `
                <div style="padding: 20px; font-family: Arial, sans-serif;">
                    <h2 style="text-align: center; color: #1e5a3d;">Document Details</h2>
                    <div style="border: 2px solid #1e5a3d; padding: 20px; margin-top: 20px;">
                        <p><strong>Document ID:</strong> DOC-${documentId}</p>
                        <p><strong>Office:</strong> <?php echo htmlspecialchars($current_user['department']); ?></p>
                        <p><strong>Printed By:</strong> <?php echo htmlspecialchars($current_user['full_name']); ?></p>
                        <p><strong>Printed Date:</strong> ${new Date().toLocaleString()}</p>
                        <hr style="margin: 20px 0;">
                        <p style="text-align: center; font-size: 12px; color: #666;">
                            This is a system-generated document from PSAU Records Management System
                        </p>
                    </div>
                </div>
            `;
            
            // Open print window
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Document Print - DOC-${documentId}</title>
                        <style>
                            body { margin: 0; padding: 20px; }
                            @media print {
                                body { padding: 0; }
                            }
                        </style>
                    </head>
                    <body>
                        ${printContent}
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }
        
        // Close modal on outside click
        document.getElementById('registerModal').addEventListener('click', function(event) {
            if (event.target === this) {
                hideRegisterModal();
            }
        });
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                hideRegisterModal();
                closeTrackModal();
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>
