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

// Ensure transfer table exists
$conn->query("CREATE TABLE IF NOT EXISTS `property_transfers` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `property_id` int(11) NOT NULL,
    `property_tag` varchar(45) DEFAULT NULL,
    `property_no` varchar(45) DEFAULT NULL,
    `previous_owner` varchar(200) DEFAULT NULL,
    `new_owner` varchar(200) NOT NULL,
    `previous_location` varchar(200) DEFAULT NULL,
    `new_location` varchar(200) DEFAULT NULL,
    `transfer_reason` text DEFAULT NULL,
    `transfer_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `transferred_by` varchar(100) DEFAULT NULL,
    `transfer_type` varchar(50) DEFAULT 'Transfer',
    `approved_by` varchar(200) DEFAULT NULL,
    `reference_no` varchar(100) DEFAULT NULL,
    `remarks` text DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `property_id` (`property_id`),
    KEY `property_tag` (`property_tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

// Handle transfer submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transfer_property'])) {
    $property_id = mysqli_real_escape_string($conn, $_POST['property_id'] ?? '');
    $property_tag = mysqli_real_escape_string($conn, $_POST['property_tag'] ?? '');
    $property_no = mysqli_real_escape_string($conn, $_POST['property_no'] ?? '');
    $previous_owner = mysqli_real_escape_string($conn, $_POST['previous_owner'] ?? '');
    $new_owner = mysqli_real_escape_string($conn, $_POST['new_owner'] ?? '');
    $previous_location = mysqli_real_escape_string($conn, $_POST['previous_location'] ?? '');
    $new_location = mysqli_real_escape_string($conn, $_POST['new_location'] ?? '');
    $transfer_reason = mysqli_real_escape_string($conn, $_POST['transfer_reason'] ?? '');
    $transfer_type = mysqli_real_escape_string($conn, $_POST['transfer_type'] ?? 'Transfer');
    $approved_by = mysqli_real_escape_string($conn, $_POST['approved_by'] ?? '');
    $reference_no = mysqli_real_escape_string($conn, $_POST['reference_no'] ?? '');
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks'] ?? '');
    $transferred_by = $_SESSION['property_full_name'] ?? $_SESSION['property_username'] ?? 'Unknown';

    // Insert transfer record
    $sql = "INSERT INTO property_transfers (
        property_id, property_tag, property_no, previous_owner, new_owner,
        previous_location, new_location, transfer_reason, transferred_by,
        transfer_type, approved_by, reference_no, remarks
    ) VALUES (
        '$property_id', '$property_tag', '$property_no', '$previous_owner', '$new_owner',
        '$previous_location', '$new_location', '$transfer_reason', '$transferred_by',
        '$transfer_type', '$approved_by', '$reference_no', '$remarks'
    )";

    if (mysqli_query($conn, $sql)) {
        // Update property with new owner and location
        $update_sql = "UPDATE property_list SET 
            property_accountable_person = '$new_owner',
            property_actual_location = '$new_location'
            WHERE idproperty_list = $property_id";
        
        if (mysqli_query($conn, $update_sql)) {
            $success_message = "Property transferred successfully!";
            echo "<script>setTimeout(function() { window.location.href = 'transfer_property.php?success=1'; }, 1500);</script>";
        } else {
            $error_message = "Transfer logged but error updating property: " . mysqli_error($conn);
        }
    } else {
        $error_message = "Error recording transfer: " . mysqli_error($conn);
    }
}

// Get property data if ID is provided
$property_data = null;
if (isset($_GET['property_id']) && is_numeric($_GET['property_id'])) {
    $prop_id = (int)$_GET['property_id'];
    $prop_sql = "SELECT * FROM property_list WHERE idproperty_list = $prop_id";
    $prop_result = $conn->query($prop_sql);
    if ($prop_result && $prop_result->num_rows > 0) {
        $property_data = $prop_result->fetch_assoc();
    }
}

// Fetch recent transfers
$transfers_sql = "SELECT pt.*, pl.property_item, pl.property_description 
    FROM property_transfers pt 
    LEFT JOIN property_list pl ON pt.property_id = pl.idproperty_list 
    ORDER BY pt.transfer_date DESC 
    LIMIT 50";
$transfers_result = $conn->query($transfers_sql);

// Fetch users for accountable person dropdown
$users_sql = "SELECT DISTINCT full_name, members FROM property_users WHERE full_name IS NOT NULL AND full_name != '' ORDER BY full_name";
$users_result = $conn->query($users_sql);

// Check if property_id is provided
if (!isset($_GET['property_id']) || !is_numeric($_GET['property_id'])) {
    header("location: property_list.php");
    exit;
}

$prop_id = (int)$_GET['property_id'];
$prop_sql = "SELECT * FROM property_list WHERE idproperty_list = $prop_id";
$prop_result = $conn->query($prop_sql);
if (!$prop_result || $prop_result->num_rows == 0) {
    header("location: property_list.php");
    exit;
}
$property_data = $prop_result->fetch_assoc();

if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success_message = "Property transferred successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Property - PSAU Property Management</title>
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
            --gold-light:#f5e4a8;
            --gray-900:  #111827;
            --gray-700:  #374151;
            --gray-500:  #6b7280;
            --gray-300:  #d1d5db;
            --gray-100:  #f3f4f6;
            --white:     #ffffff;
            --shadow-sm: 0 1px 3px rgba(0,0,0,.08);
            --shadow:    0 4px 16px rgba(5,46,22,.10);
            --shadow-lg: 0 12px 40px rgba(5,46,22,.18);
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

        /* Alerts */
        .alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 18px;
            border-radius: var(--radius);
            font-size: .9rem;
            font-weight: 500;
            margin-bottom: 18px;
            animation: slideIn .3s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .alert-success {
            background: var(--green-50);
            color: var(--green-800);
            border: 1px solid #bbf7d0;
        }
        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* Main Layout */
        .main-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        @media (max-width: 1200px) {
            .main-container {
                grid-template-columns: 1fr;
            }
        }

        /* Card */
        .card {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            border: 1px solid rgba(21,128,61,.08);
            overflow: hidden;
        }
        .card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px 24px;
            border-bottom: 1px solid #e8f5ed;
            background: var(--green-50);
        }
        .card-icon {
            width: 36px; height: 36px;
            background: var(--green-700);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            color: white;
        }
        .card-title {
            font-family: 'DM Serif Display', serif;
            font-size: 1.2rem;
            color: var(--green-900);
        }
        .card-body {
            padding: 24px;
        }

        /* Form Styles */
        .form-section-label {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--green-700);
            margin: 20px 0 12px;
            padding-bottom: 6px;
            border-bottom: 1.5px solid var(--green-100);
        }
        .form-section-label:first-child { margin-top: 0; }

        .form-row { display: flex; gap: 14px; }
        .form-row .form-group { flex: 1; min-width: 0; }
        .form-group { margin-bottom: 16px; }

        label {
            display: block;
            margin-bottom: 6px;
            font-size: .82rem;
            font-weight: 600;
            color: var(--gray-700);
        }
        .req { color: #ef4444; margin-left: 2px; }

        input[type="text"],
        input[type="date"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--gray-300);
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .875rem;
            color: var(--gray-900);
            background: var(--white);
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }
        input:focus, textarea:focus, select:focus {
            border-color: var(--green-600);
            box-shadow: 0 0 0 3px rgba(22,163,74,.13);
        }
        textarea { resize: vertical; min-height: 80px; }
        select { cursor: pointer; }

        .readonly-field {
            background: var(--gray-100) !important;
            color: var(--gray-500) !important;
            cursor: not-allowed;
        }

        /* Buttons */
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 24px;
        }
        .btn {
            padding: 11px 24px;
            border: none;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--green-700), var(--green-600));
            color: var(--white);
            box-shadow: 0 3px 10px rgba(21,128,61,.28);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--green-800), var(--green-700));
            box-shadow: 0 5px 16px rgba(21,128,61,.36);
            transform: translateY(-1px);
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
        .btn-view {
            background: var(--green-800);
            color: var(--white);
            padding: 6px 14px;
            font-size: .8rem;
            border-radius: 6px;
            text-decoration: none;
        }
        .btn-view:hover {
            background: var(--green-900);
        }

        /* Property Selector */
        .property-selector {
            margin-bottom: 20px;
        }

        /* Current Property Info */
        .current-info {
            background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
            border: 1px solid #bbf7d0;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 20px;
        }
        .current-info-title {
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--green-700);
            margin-bottom: 12px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px dashed #bbf7d0;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: var(--gray-500); font-size: .85rem; }
        .info-value { color: var(--gray-900); font-weight: 500; font-size: .85rem; text-align: right; }

        /* Transfer Arrow */
        .transfer-arrow {
            text-align: center;
            color: var(--green-600);
            font-size: 24px;
            margin: 16px 0;
        }

        /* History Table */
        .history-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .82rem;
        }
        .history-table th {
            background: linear-gradient(90deg, var(--green-950), var(--green-800));
            color: rgba(255,255,255,.92);
            padding: 12px 14px;
            text-align: left;
            font-weight: 600;
            font-size: .72rem;
            letter-spacing: .06em;
            text-transform: uppercase;
        }
        .history-table td {
            padding: 14px;
            border-bottom: 1px solid #e9f5ee;
            color: var(--gray-700);
        }
        .history-table tr:hover { background: var(--green-50); }
        .history-table tr:last-child td { border-bottom: none; }

        .transfer-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 600;
        }
        .badge-transfer {
            background: #dbeafe;
            color: #1e40af;
        }
        .badge-reassignment {
            background: #fef3c7;
            color: #92400e;
        }
        .badge-return {
            background: #fce7f3;
            color: #be185d;
        }

        .owner-change {
            font-size: .8rem;
            line-height: 1.4;
        }
        .owner-change .from { color: var(--gray-500); }
        .owner-change .to { color: var(--green-700); font-weight: 600; }
        .owner-change .arrow { color: var(--gray-400); margin: 0 4px; }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--gray-500);
        }
        .empty-state-icon { font-size: 2.5rem; margin-bottom: 12px; opacity: .5; }

        /* Toolbar */
        .toolbar {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }

        /* Scrollbar */
        .card-body::-webkit-scrollbar { width: 6px; }
        .card-body::-webkit-scrollbar-track { background: transparent; }
        .card-body::-webkit-scrollbar-thumb { background: var(--gray-300); border-radius: 99px; }
    </style>
</head>
<body>

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-icon">🔄</div>
        <div>
            <div class="page-title">Transfer Property</div>
            <div class="page-subtitle">Transfer items between accountable persons with complete audit trail</div>
        </div>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success">✅ <?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <div class="alert alert-error">⚠️ <?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Toolbar -->
    <div class="toolbar">
        <a href="property_list.php" class="btn btn-secondary">← Back to Property List</a>
        <a href="transfer_history.php" class="btn btn-secondary">📋 View Full History</a>
    </div>

    <div class="main-container">
        <!-- Transfer Form -->
        <div class="card">
            <div class="card-header">
                <div class="card-icon">📝</div>
                <div class="card-title">New Transfer</div>
            </div>
            <div class="card-body">
                <form method="POST" action="" id="transferForm">
                    <input type="hidden" name="transfer_property" value="1">
                    
                    <input type="hidden" name="property_id" id="propertyIdInput" value="<?php echo $property_data ? htmlspecialchars($property_data['idproperty_list']) : ''; ?>">
                    <input type="hidden" name="property_tag" id="propertyTagInput" value="<?php echo $property_data ? htmlspecialchars($property_data['property_tag']) : ''; ?>">
                    <input type="hidden" name="property_no" id="propertyNoInput" value="<?php echo $property_data ? htmlspecialchars($property_data['property_no']) : ''; ?>">

                    <!-- Current Property Info Display -->
                    <div class="current-info">
                        <div class="current-info-title">📦 Property to Transfer</div>
                        <div class="info-row">
                            <span class="info-label">Property Tag:</span>
                            <span class="info-value"><?php echo htmlspecialchars($property_data['property_tag']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Property No:</span>
                            <span class="info-value"><?php echo htmlspecialchars($property_data['property_no']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Item:</span>
                            <span class="info-value"><?php echo htmlspecialchars($property_data['property_item']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Current Owner:</span>
                            <span class="info-value"><?php echo htmlspecialchars($property_data['property_accountable_person']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Current Location:</span>
                            <span class="info-value"><?php echo htmlspecialchars($property_data['property_actual_location']); ?></span>
                        </div>
                    </div>

                    <input type="hidden" name="previous_owner" value="<?php echo htmlspecialchars($property_data['property_accountable_person']); ?>">
                    <input type="hidden" name="previous_location" value="<?php echo htmlspecialchars($property_data['property_actual_location']); ?>">

                    <!-- Transfer Details -->
                    <div class="form-section-label">Transfer Details</div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Transfer Type <span class="req">*</span></label>
                            <select name="transfer_type" required>
                                <option value="Transfer">Transfer</option>
                                <option value="Reassignment">Reassignment</option>
                                <option value="Return">Return to Stock</option>
                                <option value="Donation">Donation</option>
                                <option value="Disposal">Disposal</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Transfer Date <span class="req">*</span></label>
                            <input type="date" name="transfer_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>New Accountable Person <span class="req">*</span></label>
                            <select name="new_owner" required>
                                <option value="">-- Select new owner --</option>
                                <?php 
                                if ($users_result) {
                                    while($user = $users_result->fetch_assoc()): 
                                        $display_name = $user['full_name'];
                                        if (!empty($user['members'])) {
                                            $display_name .= ' - ' . $user['members'];
                                        }
                                ?>
                                    <option value="<?php echo htmlspecialchars($display_name); ?>">
                                        <?php echo htmlspecialchars($display_name); ?>
                                    </option>
                                <?php 
                                    endwhile;
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>New Location <span class="req">*</span></label>
                            <input type="text" name="new_location" id="newLocationInput" placeholder="Enter new location/office" value="<?php echo htmlspecialchars($property_data['property_actual_location']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Transfer Reason / Justification <span class="req">*</span></label>
                        <textarea name="transfer_reason" placeholder="Explain the reason for this transfer..." required></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Approved By</label>
                            <input type="text" name="approved_by" placeholder="Name of approving authority">
                        </div>
                        <div class="form-group">
                            <label>Reference No.</label>
                            <input type="text" name="reference_no" placeholder="Memo number, order number, etc.">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Additional Remarks</label>
                        <textarea name="remarks" placeholder="Any additional notes..."></textarea>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">🔄 Confirm Transfer</button>
                        <a href="property_list.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Recent Transfers -->
        <div class="card">
            <div class="card-header">
                <div class="card-icon">📚</div>
                <div class="card-title">Recent Transfer History</div>
            </div>
            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                <?php if ($transfers_result && $transfers_result->num_rows > 0): ?>
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Property</th>
                                <th>Owner Change</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($transfer = $transfers_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($transfer['transfer_date'])); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($transfer['property_no'] ?? 'N/A'); ?></strong><br>
                                        <small style="color: var(--gray-500);">
                                            <?php echo htmlspecialchars(substr($transfer['property_item'] ?? 'Unknown', 0, 30)); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="owner-change">
                                            <span class="from"><?php echo htmlspecialchars($transfer['previous_owner'] ?: 'Unassigned'); ?></span>
                                            <span class="arrow">→</span>
                                            <span class="to"><?php echo htmlspecialchars($transfer['new_owner']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="transfer-badge badge-<?php echo strtolower($transfer['transfer_type']); ?>">
                                            <?php echo htmlspecialchars($transfer['transfer_type']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📋</div>
                        <p>No transfer records found.</p>
                        <p style="font-size: .8rem; margin-top: 8px;">Transfer history will appear here once items are moved.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Pre-fill new location with current location as default on page load
        window.onload = function() {
            var locationInput = document.getElementById('newLocationInput');
            if (!locationInput.value) {
                locationInput.value = "<?php echo htmlspecialchars($property_data['property_actual_location']); ?>";
            }
        };
    </script>

</body>
</html>
