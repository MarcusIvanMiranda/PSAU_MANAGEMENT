<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit();
}

include "connect.php";
error_reporting(0);
$datatable = "records_document_main";
$results_per_page = 27;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get current user role
$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$filtertext = "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>For Releasing - PSAU Records System</title>
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
            max-width: 1400px;
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
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-card {
            background: var(--psau-white);
            padding: 1.25rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            border: 1px solid var(--psau-gray-200);
        }
        
        .stat-label {
            font-size: 0.75rem;
            color: var(--psau-gray-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }
        
        .stat-value {
            font-size: 1.875rem;
            font-weight: 600;
            color: var(--psau-primary);
        }
        
        /* Search Form */
        .search-section {
            background: var(--psau-white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--psau-gray-200);
        }
        
        .search-form {
            display: flex;
            gap: 1rem;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .search-group {
            flex: 1;
            min-width: 200px;
        }
        
        .search-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--psau-gray-700);
            font-size: 0.875rem;
        }
        
        .search-input {
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
        
        .search-input:focus {
            outline: 0;
            border-color: var(--psau-primary);
            box-shadow: 0 0 0 3px rgb(30 90 61 / 0.1);
        }
        
        .search-btn {
            padding: 0.625rem 1.25rem;
            background: var(--psau-primary);
            color: var(--psau-white);
            border: none;
            border-radius: var(--radius);
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        
        .search-btn:hover {
            background: var(--psau-secondary);
        }
        
        /* Table Section */
        .table-section {
            background: var(--psau-white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            border: 1px solid var(--psau-gray-200);
            overflow: hidden;
        }
        
        .table-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--psau-gray-200);
            background: var(--psau-gray-50);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table-title {
            font-weight: 600;
            color: var(--psau-gray-900);
        }
        
        .table-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .documents-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }
        
        .documents-table th,
        .documents-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--psau-gray-200);
            vertical-align: middle;
        }
        
        .documents-table th {
            font-weight: 600;
            color: var(--psau-gray-700);
            background-color: var(--psau-gray-50);
            border-bottom: 2px solid var(--psau-gray-200);
            white-space: nowrap;
        }
        
        .documents-table tbody tr:hover {
            background-color: var(--psau-gray-50);
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }
        
        .status-for-releasing {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        
        /* Serial Code */
        .serial-code {
            font-family: 'Courier New', monospace;
            background: var(--psau-gray-100);
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius);
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        /* Action Buttons */
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
        
        /* Pagination */
        .pagination {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--psau-gray-200);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
        }
        
        .page-link {
            padding: 0.375rem 0.75rem;
            border: 1px solid var(--psau-gray-300);
            background: var(--psau-white);
            color: var(--psau-gray-600);
            text-decoration: none;
            border-radius: var(--radius);
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        
        .page-link:hover {
            background: var(--psau-gray-50);
            border-color: var(--psau-gray-400);
        }
        
        .page-link.active {
            background: var(--psau-primary);
            color: var(--psau-white);
            border-color: var(--psau-primary);
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
        
        /* Responsive */
        @media (max-width: 1024px) {
            .page-container {
                padding: 1.25rem;
            }
            
            .stats-row {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .page-container {
                padding: 1rem;
            }
            
            .header-content {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .search-group {
                width: 100%;
            }
            
            .documents-table {
                font-size: 0.75rem;
            }
            
            .documents-table th,
            .documents-table td {
                padding: 0.5rem 0.25rem;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 0.25rem;
            }
            
            .btn-action {
                font-size: 0.625rem;
                padding: 0.25rem 0.5rem;
            }
            
            /* Hide less important columns on mobile */
            .documents-table th:nth-child(8),
            .documents-table td:nth-child(8),
            .documents-table th:nth-child(9),
            .documents-table td:nth-child(9),
            .documents-table th:nth-child(10),
            .documents-table td:nth-child(10) {
                display: none;
            }
        }
        
        @media (max-width: 480px) {
            .page-container {
                padding: 0.75rem;
            }
            
            .page-header {
                padding: var(--space-4);
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .search-section {
                padding: 1rem;
            }
            
            .table-header {
                padding: 0.75rem 1rem;
            }
            
            .pagination {
                padding: 0.75rem 1rem;
                flex-wrap: wrap;
            }
            
            .page-link {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
            }
            
            /* Mobile card view for table */
            .mobile-table-card {
                display: none;
                background: var(--psau-white);
                border: 1px solid var(--psau-gray-200);
                border-radius: var(--radius-lg);
                padding: 1rem;
                margin-bottom: 1rem;
                box-shadow: var(--shadow);
            }
            
            .mobile-card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 0.75rem;
                padding-bottom: 0.75rem;
                border-bottom: 1px solid var(--psau-gray-200);
            }
            
            .mobile-card-title {
                font-weight: 600;
                color: var(--psau-gray-900);
                font-size: 0.875rem;
            }
            
            .mobile-card-serial {
                font-family: 'Courier New', monospace;
                background: var(--psau-gray-100);
                padding: 0.25rem 0.5rem;
                border-radius: var(--radius);
                font-size: 0.75rem;
                font-weight: 500;
            }
            
            .mobile-card-content {
                display: grid;
                gap: 0.5rem;
                font-size: 0.75rem;
            }
            
            .mobile-card-row {
                display: flex;
                justify-content: space-between;
            }
            
            .mobile-card-label {
                font-weight: 600;
                color: var(--psau-gray-600);
            }
            
            .mobile-card-value {
                color: var(--psau-gray-900);
                text-align: right;
            }
            
            .mobile-card-actions {
                margin-top: 0.75rem;
                padding-top: 0.75rem;
                border-top: 1px solid var(--psau-gray-200);
                display: flex;
                gap: 0.5rem;
            }
            
            .mobile-table-card.show {
                display: block;
            }
            
            .documents-table {
                display: none;
            }
            
            .mobile-table-card.show ~ .documents-table {
                display: none;
            }
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
                    <h1 class="page-title">For Releasing</h1>
                    <p class="page-subtitle">Documents pending release</p>
                </div>
                <div class="header-actions">
                </div>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-label">Total Documents</div>
                <div class="stat-value" id="totalCount">-</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">For Releasing</div>
                <div class="stat-value" id="releasingCount">-</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Your Documents</div>
                <div class="stat-value" id="userCount">-</div>
            </div>
        </div>
        
        <!-- Search Section -->
        <div class="search-section">
            <form method="GET" class="search-form">
                <div class="search-group">
                    <label class="search-label">Search Documents</label>
                    <input type="text" name="filtertext" class="search-input" 
                           placeholder="Document title or type..." 
                           value="<?php echo htmlspecialchars($_GET['filtertext'] ?? ''); ?>">
                </div>
                <button type="submit" class="search-btn">Search</button>
                <?php if (!empty($_GET['filtertext'])): ?>
                    <a href="viewgrid.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Table Section -->
        <div class="table-section">
            <div class="table-header">
                <div class="table-title">Documents For Releasing</div>
                <div class="table-actions">
                    <span style="color: var(--psau-gray-500); font-size: 0.875rem;">
                        <?php echo $user_role === 'admin' ? 'All users' : 'Your documents only'; ?>
                    </span>
                </div>
            </div>
            
            <div class="table-container">
                <?php
                $filtertext = $_GET['filtertext'] ?? '';
                $filtertext = trim($filtertext);
                $delivered = 'FOR RELEASING';
                
                if (isset($_GET["page"])) { 
                    $page = $_GET["page"]; 
                } else { 
                    $page = 1; 
                }
                
                $start_from = ($page - 1) * $results_per_page;
                
                // Build SQL query based on user role
                if ($user_role === 'admin') {
                    $sql = "SELECT * FROM ".$datatable." where (document_title like '%".$filtertext."%' or document_type like '%".$filtertext."%') and document_status='$delivered' order by date_added desc LIMIT $start_from, ".$results_per_page;
                    $count_sql = "SELECT COUNT(*) AS total FROM ".$datatable." where (document_title like '%".$filtertext."%' or document_type like '%".$filtertext."%') and document_status='$delivered'";
                } else {
                    $sql = "SELECT * FROM ".$datatable." where (document_title like '%".$filtertext."%' or document_type like '%".$filtertext."%') and document_status='$delivered' and added_by = $user_id order by date_added desc LIMIT $start_from, ".$results_per_page;
                    $count_sql = "SELECT COUNT(*) AS total FROM ".$datatable." where (document_title like '%".$filtertext."%' or document_type like '%".$filtertext."%') and document_status='$delivered' and added_by = $user_id";
                }
                
                $rs_result = $conn->query($sql);
                $result = $conn->query($count_sql);
                $row = $result->fetch_assoc();
                $total_pages = ceil($row["total"] / $results_per_page);
                
                // Get statistics
                if ($user_role === 'admin') {
                    $total_sql = "SELECT COUNT(*) AS count FROM ".$datatable." WHERE document_status='$delivered'";
                    $user_sql = "SELECT COUNT(*) AS count FROM ".$datatable." WHERE document_status='$delivered' AND added_by = $user_id";
                } else {
                    $total_sql = "SELECT COUNT(*) AS count FROM ".$datatable." WHERE document_status='$delivered' AND added_by = $user_id";
                    $user_sql = $total_sql;
                }
                
                $total_result = $conn->query($total_sql);
                $total_count = $total_result->fetch_assoc()['count'];
                
                $user_result = $conn->query($user_sql);
                $user_count = $user_result->fetch_assoc()['count'];
                ?>
                
                <?php if ($rs_result->num_rows > 0): ?>
                    <!-- Mobile Card View -->
                    <div class="mobile-table-cards">
                        <?php while($row = $rs_result->fetch_assoc()): ?>
                            <div class="mobile-table-card">
                                <div class="mobile-card-header">
                                    <div class="mobile-card-title"><?php echo htmlspecialchars($row["document_title"]); ?></div>
                                    <div class="mobile-card-serial"><?php echo htmlspecialchars($row["serial_code"]); ?></div>
                                </div>
                                <div class="mobile-card-content">
                                    <div class="mobile-card-row">
                                        <span class="mobile-card-label">Type:</span>
                                        <span class="mobile-card-value"><?php echo htmlspecialchars($row["document_type"]); ?></span>
                                    </div>
                                    <div class="mobile-card-row">
                                        <span class="mobile-card-label">Status:</span>
                                        <span class="mobile-card-value">
                                            <span class="status-badge status-for-releasing">
                                                <?php echo htmlspecialchars($row["document_status"]); ?>
                                            </span>
                                        </span>
                                    </div>
                                    <div class="mobile-card-row">
                                        <span class="mobile-card-label">Date Added:</span>
                                        <span class="mobile-card-value"><?php echo date('M d, Y', strtotime($row["date_added"])); ?></span>
                                    </div>
                                    <div class="mobile-card-row">
                                        <span class="mobile-card-label">Received From:</span>
                                        <span class="mobile-card-value"><?php echo htmlspecialchars($row["received_from"]); ?></span>
                                    </div>
                                </div>
                                <div class="mobile-card-actions">
                                    <form action='printqrnow.php' method='POST' target='_blank' style="display: inline;">
                                        <button type="submit" name='RefID' value="<?php echo $row["serial_code"]; ?>" 
                                                class="btn-action primary" title="Print QR">
                                            🖨️ Print
                                        </button>
                                    </form>
                                    <form action='details.php' method='POST' style="display: inline;">
                                        <button type="submit" name='RefID' value="<?php echo $row["serial_code"]; ?>" 
                                                class="btn-action" title="Track Document">
                                            � Track
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <!-- Desktop Table View -->
                    <table class="documents-table">
                        <thead>
                            <tr>
                                <th>Actions</th>
                                <th>Rec#</th>
                                <th>Document Title</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Serial Code</th>
                                <th>Date Added</th>
                                <th>Received From</th>
                                <th>Delivered To</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Reset result pointer for desktop view
                            $rs_result->data_seek(0);
                            while($row = $rs_result->fetch_assoc()): 
                            ?>
                                <tr>
                                    <td>
                                        <div class="action-buttons">
                                            <form action='printqrnow.php' method='POST' target='_blank' style="display: inline;">
                                                <button type="submit" name='RefID' value="<?php echo $row["serial_code"]; ?>" 
                                                        class="btn-action primary" title="Print QR">
                                                    🖨️ Print
                                                </button>
                                            </form>
                                            <form action='details.php' method='POST' style="display: inline;">
                                                <button type="submit" name='RefID' value="<?php echo $row["serial_code"]; ?>" 
                                                        class="btn-action" title="Track Document">
                                                    � Track
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    <td><?php echo $row["idrecords_document_main"]; ?></td>
                                    <td><?php echo htmlspecialchars($row["document_title"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["document_type"]); ?></td>
                                    <td>
                                        <span class="status-badge status-for-releasing">
                                            <?php echo htmlspecialchars($row["document_status"]); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="serial-code"><?php echo htmlspecialchars($row["serial_code"]); ?></span>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($row["date_added"])); ?></td>
                                    <td><?php echo htmlspecialchars($row["received_from"] . " - " . $row["employee_receipt"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["delivered_to"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["document_remarks"]); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📄</div>
                        <div class="empty-state-title">No documents found</div>
                        <div class="empty-state-text">
                            <?php echo !empty($filtertext) ? 'No documents match your search criteria.' : 'No documents are currently for releasing.'; ?>
                        </div>
                        <?php if (!empty($filtertext)): ?>
                            <a href="viewgrid.php" class="btn btn-primary">Clear Search</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php
                    $current_filter = !empty($filtertext) ? "&filtertext=" . urlencode($filtertext) : "";
                    
                    // Previous button
                    if ($page > 1) {
                        $prev_page = $page - 1;
                        echo "<a href='viewgrid.php?page=$prev_page$current_filter' class='page-link' title='Previous'>← Previous</a> ";
                    }
                    
                    // Page numbers
                    for ($i = 1; $i <= $total_pages; $i++) {
                        $active_class = ($i == $page) ? 'active' : '';
                        echo "<a href='viewgrid.php?page=$i$current_filter' class='page-link $active_class'>$i</a> ";
                    }
                    
                    // Next button
                    if ($page < $total_pages) {
                        $next_page = $page + 1;
                        echo "<a href='viewgrid.php?page=$next_page$current_filter' class='page-link' title='Next'>Next →</a> ";
                    }
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Update statistics
        document.getElementById('totalCount').textContent = '<?php echo $total_count; ?>';
        document.getElementById('releasingCount').textContent = '<?php echo $total_count; ?>';
        document.getElementById('userCount').textContent = '<?php echo $user_count; ?>';
        
        // Auto-refresh every 30 seconds
        setTimeout(() => {
            window.location.reload();
        }, 30000);
        
        // Mobile view toggle
        function toggleMobileView() {
            const cards = document.querySelector('.mobile-table-cards');
            const table = document.querySelector('.documents-table');
            
            if (window.innerWidth <= 480) {
                if (cards) cards.style.display = 'block';
                if (table) table.style.display = 'none';
            } else {
                if (cards) cards.style.display = 'none';
                if (table) table.style.display = 'table';
            }
        }
        
        // Initialize mobile view
        toggleMobileView();
        
        // Handle resize
        window.addEventListener('resize', toggleMobileView);
    </script>
</body>
</html>
