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
                <a href="index.php" class="btn btn-secondary">← Back to Properties</a>
            </div>
        </div>
    </header>

    <div class="container">
        <a href="index.php" class="btn-back">← Back to Properties</a>
        
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
</body>
</html>
