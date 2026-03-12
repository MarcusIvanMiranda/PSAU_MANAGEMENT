<?php
session_start();
if (!isset($_SESSION['property_loggedin']) || $_SESSION['property_loggedin'] !== true) {
    header("location: login.php");
    exit;
}
include "connect.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get property tag from URL
$property_tag = isset($_GET['property_tag']) ? mysqli_real_escape_string($conn, $_GET['property_tag']) : '';

// Handle maintenance cost submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_maintenance_cost'])) {
    $property_id = mysqli_real_escape_string($conn, $_POST['property_id'] ?? '');
    $property_tag = mysqli_real_escape_string($conn, $_POST['property_tag'] ?? '');
    $cost_type = mysqli_real_escape_string($conn, $_POST['cost_type'] ?? '');
    $cost_description = mysqli_real_escape_string($conn, $_POST['cost_description'] ?? '');
    $cost_amount = mysqli_real_escape_string($conn, $_POST['cost_amount'] ?? '0');
    $cost_date = mysqli_real_escape_string($conn, $_POST['cost_date'] ?? '');
    $performed_by = mysqli_real_escape_string($conn, $_POST['performed_by'] ?? '');
    $supplier_vendor = mysqli_real_escape_string($conn, $_POST['supplier_vendor'] ?? '');
    $invoice_reference = mysqli_real_escape_string($conn, $_POST['invoice_reference'] ?? '');
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks'] ?? '');
    $created_by = $_SESSION['property_full_name'] ?? '';
    
    // Insert maintenance cost record
    $sql = "INSERT INTO property_maintenance_costs (
        property_id, property_tag, cost_type, cost_description, cost_amount, 
        cost_date, performed_by, supplier_vendor, invoice_reference, remarks, created_by
    ) VALUES (
        $property_id, '$property_tag', '$cost_type', '$cost_description', '$cost_amount',
        '$cost_date', '$performed_by', '$supplier_vendor', '$invoice_reference', '$remarks', '$created_by'
    )";
    
    if (mysqli_query($conn, $sql)) {
        // Update addition_cost in property_list
        $update_sql = "UPDATE property_list SET 
            addition_cost = (SELECT COALESCE(SUM(cost_amount), 0) FROM property_maintenance_costs WHERE property_id = $property_id)
            WHERE idproperty_list = $property_id";
        
        mysqli_query($conn, $update_sql);
        
        $success_message = "Maintenance cost added successfully!";
        echo "<script>
            setTimeout(function() {
                window.location.href = 'maintenance_costs.php?property_tag=" . urlencode($property_tag) . "&maintenance_success=1';
            }, 1500);
        </script>";
    } else {
        $error_message = "Error: " . mysqli_error($conn);
    }
}

// Handle maintenance success parameter
if (isset($_GET['maintenance_success']) && $_GET['maintenance_success'] == '1') {
    $success_message = "Maintenance cost added successfully!";
}

// Get property details
$property_query = "SELECT * FROM property_list WHERE property_tag = '$property_tag'";
$property_result = $conn->query($property_query);
$property = $property_result->fetch_assoc();

if (!$property) {
    echo "Property not found.";
    exit;
}

// Get maintenance costs
$maintenance_query = "SELECT * FROM property_maintenance_costs WHERE property_tag = '$property_tag' ORDER BY cost_date DESC";
$maintenance_result = $conn->query($maintenance_query);

// Calculate total costs
$total_cost_query = "SELECT SUM(cost_amount) as total FROM property_maintenance_costs WHERE property_tag = '$property_tag'";
$total_result = $conn->query($total_cost_query);
$total_row = $total_result->fetch_assoc();
$total_maintenance_cost = $total_row['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Cost History - <?php echo htmlspecialchars($property_tag); ?></title>
    <link rel="icon" href="PSAU.ico">
    <link rel="stylesheet" href="style.css">
    <style>
        .property-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }
        .cost-summary {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #b3d9ff;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .btn-back {
            background: #6c757d;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 20px;
        }
        .btn-back:hover {
            background: #5a6268;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <img src="PSAU_10.jpg" alt="PSAU Logo" class="header-logo">
            <div class="header-title">
                <h1>PAMPANGA STATE AGRICULTURAL UNIVERSITY</h1>
                <h2>Property Management System - Maintenance Cost History</h2>
            </div>
            <div class="header-user">
                <div style="text-align: right; margin-bottom: 0.5rem;">
                    <span class="user-info">Welcome, <?php echo htmlspecialchars($_SESSION['property_full_name']); ?></span>
                </div>
                <a href="#" onclick="parent.loadPage('property_list.php', this)" class="btn btn-secondary">← Back to Properties</a>
            </div>
        </div>
    </header>

    <div class="container">
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #c3e6cb;">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #f5c6cb;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="property-info">
            <h3>Property Information</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-top: 15px;">
                <div><strong>Property Tag:</strong> <?php echo htmlspecialchars($property['property_tag']); ?></div>
                <div><strong>Property No:</strong> <?php echo htmlspecialchars($property['property_no']); ?></div>
                <div><strong>Item:</strong> <?php echo htmlspecialchars($property['property_item']); ?></div>
                <div><strong>Description:</strong> <?php echo htmlspecialchars($property['property_description']); ?></div>
                <div><strong>Original Value:</strong> ₱<?php echo number_format((float)str_replace([',', ' '], '', $property['property_value'] ?? '0'), 2); ?></div>
                <div><strong>Addition Cost:</strong> ₱<?php echo number_format((float)($property['addition_cost'] ?? '0'), 2); ?></div>
            </div>
        </div>

        <div class="cost-summary">
            <h3>Cost Summary</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">
                <div><strong>Total Maintenance Costs:</strong> ₱<?php echo number_format((float)$total_maintenance_cost, 2); ?></div>
                <div><strong>Original Property Value:</strong> ₱<?php echo number_format((float)str_replace([',', ' '], '', $property['property_value'] ?? '0'), 2); ?></div>
            </div>
            <div style="text-align: right; margin-top: 15px;">
                <button type='button' onclick='openMaintenanceCostModal(<?php echo $property["idproperty_list"]; ?>, "<?php echo htmlspecialchars($property["property_tag"]); ?>")' class="btn btn-primary" style="background: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px;">
                    ➕ Add Cost
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Performed By</th>
                        <th>Supplier/Vendor</th>
                        <th>Invoice Reference</th>
                        <th>Created By</th>
                        <th>Date Created</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($maintenance_result && $maintenance_result->num_rows > 0): ?>
                        <?php while($row = $maintenance_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['cost_date']); ?></td>
                                <td>
                                    <span style="padding: 4px 8px; border-radius: 4px; font-size: 12px; 
                                        <?php 
                                        if ($row['cost_type'] == 'repair') echo 'background: #f8d7da; color: #721c24;';
                                        elseif ($row['cost_type'] == 'maintenance') echo 'background: #d1ecf1; color: #0c5460;';
                                        else echo 'background: #fff3cd; color: #856404;';
                                        ?>">
                                        <?php echo ucfirst(htmlspecialchars($row['cost_type'])); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['cost_description']); ?></td>
                                <td>₱<?php echo number_format((float)$row['cost_amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($row['performed_by'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['supplier_vendor'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['invoice_reference'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['created_by'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['date_created']); ?></td>
                                <td><?php echo htmlspecialchars($row['remarks'] ?? ''); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan='10' style='text-align: center; padding: 2rem;'>
                                <p style='color: var(--gray-500); font-size: 1.1rem;'>
                                    No maintenance costs found for this property.
                                </p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Maintenance Cost Modal -->
    <div id="maintenanceCostModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div class="modal-content" style="background-color: #fefefe; margin: 2% auto; padding: 20px; border: 1px solid #888; width: 90%; max-width: 700px; max-height: 90vh; overflow-y: auto; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #ddd;">
                <h2 style="margin: 0; color: #333;">Add Repair/Maintenance Cost</h2>
                <span class="close" onclick="closeMaintenanceCostModal()" style="color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
            </div>
            
            <form method="POST" action="" id="maintenanceCostForm">
                <input type="hidden" name="add_maintenance_cost" value="1">
                <input type="hidden" id="maintenance_property_id" name="property_id">
                <input type="hidden" id="maintenance_property_tag" name="property_tag">
                
                <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="maintenance_cost_type" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Cost Type:</label>
                        <select id="maintenance_cost_type" name="cost_type" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            <option value="">Select Type...</option>
                            <option value="repair">Repair</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="replace">Replace Parts</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="maintenance_cost_amount" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Cost Amount (₱):</label>
                        <input type="number" id="maintenance_cost_amount" name="cost_amount" step="0.01" min="0" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="maintenance_cost_description" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Description:</label>
                    <textarea id="maintenance_cost_description" name="cost_description" rows="3" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; resize: vertical;"></textarea>
                </div>

                <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="maintenance_cost_date" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Cost Date:</label>
                        <input type="date" id="maintenance_cost_date" name="cost_date" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="maintenance_performed_by" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Performed By:</label>
                        <input type="text" id="maintenance_performed_by" name="performed_by" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>
                </div>

                <div style="text-align: center; padding-top: 15px; border-top: 1px solid #ddd;">
                    <button type="submit" class="btn btn-primary" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-right: 10px;">Add Cost</button>
                    <button type="button" onclick="closeMaintenanceCostModal()" class="btn btn-secondary" style="background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openMaintenanceCostModal(propertyId, propertyTag) {
        document.getElementById('maintenance_property_id').value = propertyId;
        document.getElementById('maintenance_property_tag').value = propertyTag;
        document.getElementById('maintenanceCostModal').style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeMaintenanceCostModal() {
        document.getElementById('maintenanceCostModal').style.display = 'none';
        document.body.style.overflow = 'auto';
        document.getElementById('maintenanceCostForm').reset();
    }

    // Close modal when clicking outside of it
    window.onclick = function(event) {
        var maintenanceModal = document.getElementById('maintenanceCostModal');
        if (event.target == maintenanceModal) {
            closeMaintenanceCostModal();
        }
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeMaintenanceCostModal();
        }
    });

    // Check if page loaded with success parameter and ensure modal is closed
    window.onload = function() {
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('maintenance_success')) {
            closeMaintenanceCostModal();
            // Remove the success parameter from URL without page refresh
            var newUrl = window.location.pathname + '?property_tag=' + urlParams.get('property_tag');
            window.history.replaceState({}, '', newUrl);
        }
    };
    </script>
</body>
</html>
