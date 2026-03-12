<?php
session_start();
if (!isset($_SESSION['property_loggedin']) || $_SESSION['property_loggedin'] !== true) {
    header("location: login.php");
    exit;
}

include "connect.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Pagination
$results_per_page = 20;
$page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
if ($page < 1) $page = 1;
$start_from = ($page - 1) * $results_per_page;

// Filters
$filter_property = isset($_GET['filter_property']) ? trim($_GET['filter_property']) : '';
$filter_owner = isset($_GET['filter_owner']) ? trim($_GET['filter_owner']) : '';
$filter_type = isset($_GET['filter_type']) ? trim($_GET['filter_type']) : '';

// Build query conditions
$conditions = [];
if (!empty($filter_property)) {
    $conditions[] = "(pt.property_no LIKE '%$filter_property%' OR pt.property_tag LIKE '%$filter_property%' OR pl.property_item LIKE '%$filter_property%')";
}
if (!empty($filter_owner)) {
    $conditions[] = "(pt.previous_owner LIKE '%$filter_owner%' OR pt.new_owner LIKE '%$filter_owner%')";
}
if (!empty($filter_type)) {
    $conditions[] = "pt.transfer_type = '$filter_type'";
}

$where_clause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

// Get total count
$count_sql = "SELECT COUNT(*) AS total FROM property_transfers pt 
    LEFT JOIN property_list pl ON pt.property_id = pl.idproperty_list 
    $where_clause";
$count_result = $conn->query($count_sql);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $results_per_page);

// Get transfers
$transfers_sql = "SELECT pt.*, pl.property_item, pl.property_description, pl.property_serial_number
    FROM property_transfers pt 
    LEFT JOIN property_list pl ON pt.property_id = pl.idproperty_list 
    $where_clause
    ORDER BY pt.transfer_date DESC 
    LIMIT $start_from, $results_per_page";
$transfers_result = $conn->query($transfers_sql);

// Get transfer types for filter
$types_sql = "SELECT DISTINCT transfer_type FROM property_transfers ORDER BY transfer_type";
$types_result = $conn->query($types_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer History - PSAU Property Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
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
            --gray-900:  #111827;
            --gray-700:  #374151;
            --gray-500:  #6b7280;
            --gray-300:  #d1d5db;
            --gray-100:  #f3f4f6;
            --white:     #ffffff;
            --shadow:    0 4px 16px rgba(5,46,22,.10);
            --radius:    10px;
            --radius-lg: 16px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #eef5f0;
            background-image: radial-gradient(ellipse 80% 40% at 50% -10%, rgba(21,128,61,.13) 0%, transparent 70%);
            min-height: 100vh;
            color: var(--gray-900);
            padding: 28px 32px 48px;
        }

        /* Page Header */
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

        /* Toolbar */
        .toolbar {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-secondary {
            background: var(--white);
            color: var(--gray-700);
            border: 1.5px solid var(--gray-300);
        }
        .btn-secondary:hover {
            background: var(--gray-100);
            border-color: var(--gray-400);
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--green-700), var(--green-600));
            color: var(--white);
            box-shadow: 0 3px 10px rgba(21,128,61,.28);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--green-800), var(--green-700));
            transform: translateY(-1px);
        }

        /* Filter Form */
        .filter-form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            flex: 1;
        }
        .filter-input, .filter-select {
            padding: 9px 14px;
            border: 1.5px solid var(--gray-300);
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .875rem;
            background: var(--white);
        }
        .filter-input:focus, .filter-select:focus {
            border-color: var(--green-600);
            outline: none;
        }

        /* Table Card */
        .table-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            border: 1px solid rgba(21,128,61,.08);
            overflow: hidden;
        }
        .table-wrapper { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: .85rem;
        }
        thead {
            background: linear-gradient(90deg, var(--green-950), var(--green-800));
            position: sticky;
            top: 0;
        }
        thead th {
            padding: 14px 12px;
            text-align: left;
            color: rgba(255,255,255,.92);
            font-weight: 600;
            font-size: .75rem;
            letter-spacing: .06em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        tbody tr {
            border-bottom: 1px solid #e9f5ee;
            transition: background .15s;
        }
        tbody tr:hover { background: var(--green-50); }
        tbody tr:last-child { border-bottom: none; }
        tbody td {
            padding: 16px 12px;
            color: var(--gray-700);
            vertical-align: top;
        }

        /* Transfer Badges */
        .transfer-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 600;
        }
        .badge-transfer { background: #dbeafe; color: #1e40af; }
        .badge-reassignment { background: #fef3c7; color: #92400e; }
        .badge-return { background: #fce7f3; color: #be185d; }
        .badge-donation { background: #d1fae5; color: #065f46; }
        .badge-disposal { background: #fee2e2; color: #991b1b; }

        /* Owner Change */
        .owner-change {
            font-size: .85rem;
            line-height: 1.5;
        }
        .owner-change .from { color: var(--gray-500); }
        .owner-change .to { color: var(--green-700); font-weight: 600; }
        .owner-change .arrow { color: var(--gray-400); margin: 0 6px; }

        /* Property Info */
        .property-info .tag { font-weight: 600; color: var(--gray-900); font-size: .9rem; }
        .property-info .item { color: var(--gray-500); font-size: .8rem; }

        /* Pagination */
        .pagination-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 20px 0;
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
        .pg-dots { color: var(--gray-400); cursor: default; }

        /* Stats */
        .stats-bar {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-box {
            background: var(--white);
            padding: 16px 24px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            min-width: 140px;
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--green-700);
        }
        .stat-label {
            font-size: .75rem;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 64px 32px;
            color: var(--gray-500);
        }
        .empty-state-icon { font-size: 3rem; margin-bottom: 14px; opacity: .5; }

        /* Reason tooltip */
        .reason-preview {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            cursor: help;
            color: var(--gray-500);
            font-size: .8rem;
        }
        .tooltip { position: relative; }
        .tooltip:hover::after {
            content: attr(data-fulltext);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: var(--gray-900);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: .8rem;
            white-space: normal;
            max-width: 300px;
            z-index: 100;
        }
    </style>
</head>
<body>

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-icon">📚</div>
        <div>
            <div class="page-title">Transfer History</div>
            <div class="page-subtitle">Complete audit trail of all property transfers and ownership changes</div>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-bar">
        <div class="stat-box">
            <div class="stat-value"><?php echo number_format($total_rows); ?></div>
            <div class="stat-label">Total Transfers</div>
        </div>
        <div class="stat-box">
            <div class="stat-value"><?php echo $transfers_result ? number_format($transfers_result->num_rows) : 0; ?></div>
            <div class="stat-label">Showing</div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar">
        <a href="property_list.php" class="btn btn-secondary">← Back to Property List</a>
        <a href="transfer_property.php" class="btn btn-primary">+ New Transfer</a>
        
        <form class="filter-form" method="GET" action="">
            <input type="text" name="filter_property" class="filter-input" placeholder="Search property..." 
                value="<?php echo htmlspecialchars($filter_property); ?>">
            <input type="text" name="filter_owner" class="filter-input" placeholder="Search owner..." 
                value="<?php echo htmlspecialchars($filter_owner); ?>">
            <select name="filter_type" class="filter-select">
                <option value="">All Types</option>
                <?php while($type = $types_result->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($type['transfer_type']); ?>" 
                        <?php echo $filter_type == $type['transfer_type'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($type['transfer_type']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="btn btn-primary">🔍 Filter</button>
            <?php if (!empty($filter_property) || !empty($filter_owner) || !empty($filter_type)): ?>
                <a href="transfer_history.php" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Transfer Date</th>
                        <th>Property</th>
                        <th>From → To</th>
                        <th>Type</th>
                        <th>Location Change</th>
                        <th>Reason</th>
                        <th>Reference</th>
                        <th>Processed By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($transfers_result && $transfers_result->num_rows > 0): ?>
                        <?php while($transfer = $transfers_result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?php echo date('M d, Y', strtotime($transfer['transfer_date'])); ?></strong><br>
                                    <small style="color: var(--gray-500);">
                                        <?php echo date('h:i A', strtotime($transfer['transfer_date'])); ?>
                                    </small>
                                </td>
                                <td class="property-info">
                                    <div class="tag"><?php echo htmlspecialchars($transfer['property_tag'] ?? 'N/A'); ?></div>
                                    <div class="item"><?php echo htmlspecialchars($transfer['property_no'] ?? ''); ?></div>
                                    <div style="font-size: .8rem; color: var(--gray-600); margin-top: 4px;">
                                        <?php echo htmlspecialchars(substr($transfer['property_item'] ?? 'Unknown Item', 0, 40)); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="owner-change">
                                        <span class="from"><?php echo htmlspecialchars($transfer['previous_owner'] ?: 'Unassigned'); ?></span><br>
                                        <span class="arrow">↓</span>
                                        <span class="to"><?php echo htmlspecialchars($transfer['new_owner']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="transfer-badge badge-<?php echo strtolower($transfer['transfer_type']); ?>">
                                        <?php echo htmlspecialchars($transfer['transfer_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($transfer['previous_location'] != $transfer['new_location']): ?>
                                        <small style="color: var(--gray-500);">From:</small><br>
                                        <?php echo htmlspecialchars($transfer['previous_location'] ?: 'N/A'); ?><br>
                                        <small style="color: var(--green-600);">To:</small><br>
                                        <?php echo htmlspecialchars($transfer['new_location']); ?>
                                    <?php else: ?>
                                        <span style="color: var(--gray-400);">No change</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($transfer['transfer_reason'])): ?>
                                        <div class="tooltip reason-preview" data-fulltext="<?php echo htmlspecialchars($transfer['transfer_reason']); ?>">
                                            <?php echo htmlspecialchars(substr($transfer['transfer_reason'], 0, 30)) . (strlen($transfer['transfer_reason']) > 30 ? '...' : ''); ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: var(--gray-400);">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($transfer['reference_no'])): ?>
                                        <span style="font-family: monospace; font-size: .8rem;">
                                            <?php echo htmlspecialchars($transfer['reference_no']); ?>
                                        </span><br>
                                    <?php endif; ?>
                                    <?php if (!empty($transfer['approved_by'])): ?>
                                        <small style="color: var(--gray-500);">
                                            Approved: <?php echo htmlspecialchars($transfer['approved_by']); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($transfer['transferred_by'] ?: 'System'); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-state-icon">📋</div>
                                    <p>No transfer records found.</p>
                                    <?php if (!empty($filter_property) || !empty($filter_owner)): ?>
                                        <p style="font-size: .85rem; margin-top: 8px;">
                                            Try adjusting your search filters.
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination-wrap">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>&filter_property=<?php echo urlencode($filter_property); ?>&filter_owner=<?php echo urlencode($filter_owner); ?>&filter_type=<?php echo urlencode($filter_type); ?>" class="pg-nav">‹</a>
        <?php endif; ?>
        
        <?php
        $max_pages = 10;
        $start_page = max(1, $page - floor($max_pages / 2));
        $end_page = min($total_pages, $start_page + $max_pages - 1);
        if ($end_page - $start_page < $max_pages - 1) $start_page = max(1, $end_page - $max_pages + 1);
        
        if ($start_page > 1): ?>
            <a href="?page=1&filter_property=<?php echo urlencode($filter_property); ?>&filter_owner=<?php echo urlencode($filter_owner); ?>&filter_type=<?php echo urlencode($filter_type); ?>">1</a>
            <?php if ($start_page > 2): ?><span class="pg-dots">…</span><?php endif; ?>
        <?php endif; ?>
        
        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
            <a href="?page=<?php echo $i; ?>&filter_property=<?php echo urlencode($filter_property); ?>&filter_owner=<?php echo urlencode($filter_owner); ?>&filter_type=<?php echo urlencode($filter_type); ?>" 
               class="<?php echo $i == $page ? 'curPage' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        
        <?php if ($end_page < $total_pages): ?>
            <?php if ($end_page < $total_pages - 1): ?><span class="pg-dots">…</span><?php endif; ?>
            <a href="?page=<?php echo $total_pages; ?>&filter_property=<?php echo urlencode($filter_property); ?>&filter_owner=<?php echo urlencode($filter_owner); ?>&filter_type=<?php echo urlencode($filter_type); ?>"><?php echo $total_pages; ?></a>
        <?php endif; ?>
        
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>&filter_property=<?php echo urlencode($filter_property); ?>&filter_owner=<?php echo urlencode($filter_owner); ?>&filter_type=<?php echo urlencode($filter_type); ?>" class="pg-nav">›</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</body>
</html>
