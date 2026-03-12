<?php
session_start();
if (!isset($_SESSION['property_loggedin']) || $_SESSION['property_loggedin'] !== true) {
    header("location: login.php");
    exit;
}

include "connect.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

$datatable = "property_list"; // MySQL table name
$results_per_page = 20; // number of results per page
 
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$filtertext="";

// Handle form submission for adding new property
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_property'])) {
    // Sanitize and collect form data
    $property_no = mysqli_real_escape_string($conn, $_POST['property_no'] ?? '');
    $property_tag = mysqli_real_escape_string($conn, $_POST['property_tag'] ?? '');
    $property_item = mysqli_real_escape_string($conn, $_POST['property_item'] ?? '');
    $property_description = mysqli_real_escape_string($conn, $_POST['property_description'] ?? '');
    $property_model_number = mysqli_real_escape_string($conn, $_POST['property_model_number'] ?? '');
    $property_serial_number = mysqli_real_escape_string($conn, $_POST['property_serial_number'] ?? '');
    $property_value = mysqli_real_escape_string($conn, $_POST['property_value'] ?? '');
    $property_acquisition_date = mysqli_real_escape_string($conn, $_POST['property_acquisition_date'] ?? '');
    $property_accountable_person = mysqli_real_escape_string($conn, $_POST['property_accountable_person'] ?? '');
    $property_actual_location = mysqli_real_escape_string($conn, $_POST['property_actual_location'] ?? '');
    $property_remarks = mysqli_real_escape_string($conn, $_POST['property_remarks'] ?? '');
    $property_counted = mysqli_real_escape_string($conn, $_POST['property_counted'] ?? '');
    $property_condition = mysqli_real_escape_string($conn, $_POST['property_condition'] ?? '');
    $property_validated = mysqli_real_escape_string($conn, $_POST['property_validated'] ?? '');
    $property_status = mysqli_real_escape_string($conn, $_POST['property_status'] ?? '');
    $property_fund = mysqli_real_escape_string($conn, $_POST['property_fund'] ?? '');
    $property_year_purchased = mysqli_real_escape_string($conn, $_POST['property_year_purchased'] ?? '');
    $property_sm_group_account = mysqli_real_escape_string($conn, $_POST['property_sm_group_account'] ?? '');
    $property_gl_account = mysqli_real_escape_string($conn, $_POST['property_gl_account'] ?? '');
    $property_number = mysqli_real_escape_string($conn, $_POST['property_number'] ?? '');
    $property_loc = mysqli_real_escape_string($conn, $_POST['property_loc'] ?? '');

    // Get the next ID for idproperty_list
    $next_id_query = "SELECT MAX(idproperty_list) + 1 as next_id FROM property_list";
    $next_id_result = $conn->query($next_id_query);
    $next_id_row = $next_id_result->fetch_assoc();
    $next_id = $next_id_row['next_id'] ?? 1;

    // Insert query
    $sql = "INSERT INTO property_list (
        idproperty_list, property_no, property_tag, property_item, property_description, 
        property_model_number, property_serial_number, property_value, 
        property_acquisition_date, property_accountable_person, property_actual_location, 
        property_remarks, property_counted, property_condition, property_validated, 
        property_status, property_fund, property_year_purchased, property_sm_group_account, 
        property_gl_account, property_number, property_loc
    ) VALUES (
        $next_id, '$property_no', '$property_tag', '$property_item', '$property_description', 
        '$property_model_number', '$property_serial_number', '$property_value', 
        '$property_acquisition_date', '$property_accountable_person', '$property_actual_location', 
        '$property_remarks', '$property_counted', '$property_condition', '$property_validated', 
        '$property_status', '$property_fund', '$property_year_purchased', '$property_sm_group_account', 
        '$property_gl_account', '$property_number', '$property_loc'
    )";

    if (mysqli_query($conn, $sql)) {
        $success_message = "Property added successfully!";
        echo "<script>
            setTimeout(function() {
                window.location.href = 'property_list.php?success=1';
            }, 1500);
        </script>";
    } else {
        $error_message = "Error: " . mysqli_error($conn);
    }
}

// Handle success parameter from redirect
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success_message = "Property added successfully!";
}

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
                window.location.href = 'property_list.php?maintenance_success=1';
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

$filtertext = isset($_GET['filtertext']) ? trim($_GET['filtertext']) : '';
if (isset($_GET["page"])) { $page = $_GET["page"]; } else { $page=1; };
$start_from = ($page-1) * $results_per_page;

// Build search condition
$search_condition = "";
if (!empty($filtertext)) {
    $search_condition = " WHERE (property_no LIKE '%$filtertext%' OR property_tag LIKE '%$filtertext%' OR property_item LIKE '%$filtertext%' OR property_description LIKE '%$filtertext%')";
}

$sql = "SELECT * FROM ".$datatable.$search_condition." ORDER BY property_no DESC LIMIT $start_from, ".$results_per_page;
$rs_result = $conn->query($sql);

// Get total count for pagination
$count_sql = "SELECT COUNT(*) AS total FROM ".$datatable.$search_condition;
$result = $conn->query($count_sql);
$row = $result->fetch_assoc();
$total_pages = ceil($row["total"] / $results_per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property List - PSAU Property Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .tooltip {
            position: relative;
            display: inline-block;
            cursor: help;
        }
        
        .tooltip .tooltiptext {
            visibility: hidden;
            width: 300px;
            background-color: #333;
            color: #fff;
            text-align: left;
            border-radius: 6px;
            padding: 10px;
            position: absolute;
            z-index: 1000;
            bottom: 125%;
            left: 50%;
            margin-left: -150px;
            opacity: 0;
            transition: opacity 0.3s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            font-size: 14px;
            line-height: 1.4;
            word-wrap: break-word;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .tooltip .tooltiptext::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #333 transparent transparent transparent;
        }
        
        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }
        
        .truncate-text {
            color: #0066cc;
            text-decoration: underline dotted;
        }
        
        .truncate-text:hover {
            color: #004499;
        }

        body {
            background: #f8f9f8;
            padding: 20px;
        }

        .page-header {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
        }

        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--green-900);
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: var(--gray-600);
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <div class="page-header">
        <h1 class="page-title">Property List</h1>
        <p class="page-subtitle">Manage and view all property records</p>
    </div>

    <section class="search-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <form class="search-form" action='property_list.php' method='GET' style="flex: 1; margin-right: 1rem;">
                <input 
                    type="text" 
                    name="filtertext" 
                    class="search-input" 
                    placeholder="Search by Property No, Tag, Item, or Description..." 
                    value="<?php echo isset($_GET['filtertext']) ? htmlspecialchars($_GET['filtertext']) : ''; ?>"
                >
                <button type="submit" class="btn btn-primary">
                    🔍 Search Properties
                </button>
            </form>
            <button onclick="openAddPropertyModal()" class="btn btn-success">
                ➕ Add New Property
            </button>
        </div>
    </section>

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

    <!-- Add Property Modal -->
    <div id="addPropertyModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div class="modal-content" style="background-color: #fefefe; margin: 2% auto; padding: 20px; border: 1px solid #888; width: 90%; max-width: 800px; max-height: 90vh; overflow-y: auto; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #ddd;">
                <h2 style="margin: 0; color: #333;">Add New Property</h2>
                <span class="close" onclick="closeAddPropertyModal()" style="color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
            </div>
            
            <form method="POST" action="" id="addPropertyForm">
                <input type="hidden" name="add_property" value="1">
                
                <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="modal_property_no" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Property No:</label>
                        <input type="text" id="modal_property_no" name="property_no" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="modal_property_tag" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Property Tag:</label>
                        <input type="text" id="modal_property_tag" name="property_tag" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="modal_property_item" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Property Item:</label>
                    <input type="text" id="modal_property_item" name="property_item" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="modal_property_description" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Property Description:</label>
                    <textarea id="modal_property_description" name="property_description" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; resize: vertical;"></textarea>
                </div>

                <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="modal_property_model_number" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Model Number:</label>
                        <input type="text" id="modal_property_model_number" name="property_model_number" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="modal_property_serial_number" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Serial Number:</label>
                        <input type="text" id="modal_property_serial_number" name="property_serial_number" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>
                </div>

                <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="modal_property_value" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Property Value:</label>
                        <input type="text" id="modal_property_value" name="property_value" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="modal_property_acquisition_date" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Acquisition Date:</label>
                        <input type="date" id="modal_property_acquisition_date" name="property_acquisition_date" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>
                </div>

                <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="modal_property_accountable_person" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Accountable Person:</label>
                        <input type="text" id="modal_property_accountable_person" name="property_accountable_person" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="modal_property_actual_location" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Actual Location:</label>
                        <input type="text" id="modal_property_actual_location" name="property_actual_location" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="modal_property_remarks" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Remarks:</label>
                    <textarea id="modal_property_remarks" name="property_remarks" rows="2" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; resize: vertical;"></textarea>
                </div>

                <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="modal_property_counted" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Counted:</label>
                        <select id="modal_property_counted" name="property_counted" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            <option value="">Select...</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="modal_property_condition" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Condition:</label>
                        <select id="modal_property_condition" name="property_condition" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            <option value="">Select...</option>
                            <option value="Good">Good</option>
                            <option value="Fair">Fair</option>
                            <option value="Poor">Poor</option>
                            <option value="Damaged">Damaged</option>
                        </select>
                    </div>
                </div>

                <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="modal_property_validated" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Validated:</label>
                        <select id="modal_property_validated" name="property_validated" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            <option value="">Select...</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="modal_property_status" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Status:</label>
                        <select id="modal_property_status" name="property_status" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            <option value="">Select...</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Disposed">Disposed</option>
                            <option value="Lost">Lost</option>
                        </select>
                    </div>
                </div>

                <div style="text-align: center; padding-top: 15px; border-top: 1px solid #ddd;">
                    <button type="submit" class="btn btn-primary" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-right: 10px;">Add Property</button>
                    <button type="button" onclick="closeAddPropertyModal()" class="btn btn-secondary" style="background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;">Cancel</button>
                </div>
            </form>
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

    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php
        // Previous button
        if ($page > 1):
        ?>
            <a href='property_list.php?filtertext=<?php echo urlencode($filtertext); ?>&page=<?php echo $page - 1; ?>' class="nav-btn">
                &lt;
            </a>
        <?php endif; ?>
        
        <?php
        // Calculate page range (show max 10 pages)
        $max_pages = 10;
        $start_page = max(1, $page - floor($max_pages / 2));
        $end_page = min($total_pages, $start_page + $max_pages - 1);
        
        // Adjust start page if we're near the end
        if ($end_page - $start_page < $max_pages - 1) {
            $start_page = max(1, $end_page - $max_pages + 1);
        }
        
        // Show first page if not in range
        if ($start_page > 1):
        ?>
            <a href='property_list.php?filtertext=<?php echo urlencode($filtertext); ?>&page=1' 
               class='<?php if (1==$page) echo "curPage"; ?>'>
                1
            </a>
            <?php if ($start_page > 2): ?>
                <span class="pagination-dots">...</span>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php
        // Show page range
        for ($i=$start_page; $i<=$end_page; $i++):
        ?>
            <a href='property_list.php?filtertext=<?php echo urlencode($filtertext); ?>&page=<?php echo $i; ?>' 
               class='<?php if ($i==$page) echo "curPage"; ?>'>
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
        
        <?php
        // Show last page if not in range
        if ($end_page < $total_pages):
            if ($end_page < $total_pages - 1):
        ?>
                <span class="pagination-dots">...</span>
            <?php endif; ?>
            <a href='property_list.php?filtertext=<?php echo urlencode($filtertext); ?>&page=<?php echo $total_pages; ?>' 
               class='<?php if ($total_pages==$page) echo "curPage"; ?>'>
                <?php echo $total_pages; ?>
            </a>
        <?php endif; ?>
        
        <?php
        // Next button
        if ($page < $total_pages):
        ?>
            <a href='property_list.php?filtertext=<?php echo urlencode($filtertext); ?>&page=<?php echo $page + 1; ?>' class="nav-btn">
                &gt;
            </a>
        <?php endif; ?>
        
    </div>
    <?php endif; ?>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th style="text-align: center; width: 120px;">Actions</th>
                    <th>Property #</th>
                    <th>Property Tag</th>
                    <th>Item</th>
                    <th>Description</th>
                    <th>Serial Number</th>
                    <th>Value</th>
                    <th>Addition Cost</th>
                    <th>Acquisition Date</th>
                    <th style="text-align: center;">Accountable Person</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rs_result && $rs_result->num_rows > 0): ?>
                    <?php while($row = $rs_result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="action-buttons">
                                    <form action='printqrnow.php' method='POST' target='_blank' style="display: inline;">
                                        <button type='submit' name='RefID' value='<?php echo $row["property_tag"]; ?>' class="btn btn-success btn-sm">
                                             Print QR
                                        </button>
                                    </form>
                                    <form action='propertydocument.php' method='GET' style="display: inline;">
                                        <button type='submit' name='filtertext' value='<?php echo $row["property_tag"]; ?>' class="btn btn-primary btn-sm">
                                             View
                                        </button>
                                    </form>
                                    <a href='maintenance_costs.php?property_tag=<?php echo urlencode($row["property_tag"]); ?>' class="btn btn-info btn-sm" style="background: #17a2b8; color: white; text-decoration: none; padding: 4px 8px; border-radius: 4px; font-size: 12px; display: inline-block; text-align: center;">
                                         View Costs
                                    </a>
                                    <button type='button' onclick='openMaintenanceCostModal(<?php echo $row["idproperty_list"]; ?>, "<?php echo htmlspecialchars($row["property_tag"]); ?>")' class="btn btn-warning btn-sm" style="background: #ffc107; color: #212529; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 12px;">
                                         Add Cost
                                    </button>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($row["property_no"]); ?></td>
                            <td><strong><?php echo htmlspecialchars($row["property_tag"]); ?></strong></td>
                            <td><?php echo htmlspecialchars($row["property_item"]); ?></td>
                            <td><?php 
                                $description = htmlspecialchars($row["property_description"] ?? '');
                                if (strlen($description) > 20) {
                                    echo '<div class="tooltip">
                                        <span class="truncate-text">' . substr($description, 0, 20) . '...</span>
                                        <span class="tooltiptext">' . $description . '</span>
                                    </div>';
                                } else {
                                    echo $description;
                                }
                            ?></td>
                            <td><?php echo htmlspecialchars($row["property_serial_number"] ?? ''); ?></td>
                            <td>₱<?php 
                                $propertyValue = $row["property_value"] ?? '0';
                                $cleanedValue = str_replace([',', ' '], '', $propertyValue); // Remove commas and spaces
                                if (is_numeric($cleanedValue)) {
                                    echo number_format((float)$cleanedValue, 2); 
                                } else {
                                    echo htmlspecialchars($propertyValue); // Display as is if not a valid number
                                }
                            ?></td>
                            <td>₱<?php 
                                $additionCost = $row["addition_cost"] ?? '0';
                                $cleanedCost = str_replace([',', ' '], '', $additionCost); // Remove commas and spaces
                                if (is_numeric($cleanedCost)) {
                                    echo number_format((float)$cleanedCost, 2); 
                                } else {
                                    echo htmlspecialchars($additionCost); // Display as is if not a valid number
                                }
                            ?></td>
                            <td><?php echo htmlspecialchars($row["property_acquisition_date"] ?? ''); ?></td>
                            <td style="text-align: center;"><?php echo htmlspecialchars($row["property_accountable_person"] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row["property_status"] ?? ''); ?></td>
                            <td><?php 
                                $remarks = htmlspecialchars($row["property_remarks"] ?? '');
                                if (strlen($remarks) > 20) {
                                    echo '<div class="tooltip">
                                        <span class="truncate-text">' . substr($remarks, 0, 20) . '...</span>
                                        <span class="tooltiptext">' . $remarks . '</span>
                                    </div>';
                                } else {
                                    echo $remarks;
                                }
                            ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan='12' style='text-align: center; padding: 2rem;'>
                            <p style='color: var(--gray-500); font-size: 1.1rem;'>
                                <?php if (!empty($filtertext)): ?>
                                    No properties found matching "<strong><?php echo htmlspecialchars($filtertext); ?></strong>"
                                <?php else: ?>
                                    No properties found in the system.
                                <?php endif; ?>
                            </p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php
        // Previous button
        if ($page > 1):
        ?>
            <a href='property_list.php?filtertext=<?php echo urlencode($filtertext); ?>&page=<?php echo $page - 1; ?>' class="nav-btn">
                &lt;
            </a>
        <?php endif; ?>
        
        <?php
        // Calculate page range (show max 10 pages)
        $max_pages = 10;
        $start_page = max(1, $page - floor($max_pages / 2));
        $end_page = min($total_pages, $start_page + $max_pages - 1);
        
        // Adjust start page if we're near the end
        if ($end_page - $start_page < $max_pages - 1) {
            $start_page = max(1, $end_page - $max_pages + 1);
        }
        
        // Show first page if not in range
        if ($start_page > 1):
        ?>
            <a href='property_list.php?filtertext=<?php echo urlencode($filtertext); ?>&page=1' 
               class='<?php if (1==$page) echo "curPage"; ?>'>
                1
            </a>
            <?php if ($start_page > 2): ?>
                <span class="pagination-dots">...</span>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php
        // Show page range
        for ($i=$start_page; $i<=$end_page; $i++):
        ?>
            <a href='property_list.php?filtertext=<?php echo urlencode($filtertext); ?>&page=<?php echo $i; ?>' 
               class='<?php if ($i==$page) echo "curPage"; ?>'>
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
        
        <?php
        // Show last page if not in range
        if ($end_page < $total_pages):
            if ($end_page < $total_pages - 1):
        ?>
                <span class="pagination-dots">...</span>
            <?php endif; ?>
            <a href='property_list.php?filtertext=<?php echo urlencode($filtertext); ?>&page=<?php echo $total_pages; ?>' 
               class='<?php if ($total_pages==$page) echo "curPage"; ?>'>
                <?php echo $total_pages; ?>
            </a>
        <?php endif; ?>
        
        <?php
        // Next button
        if ($page < $total_pages):
        ?>
            <a href='property_list.php?filtertext=<?php echo urlencode($filtertext); ?>&page=<?php echo $page + 1; ?>' class="nav-btn">
                &gt;
            </a>
        <?php endif; ?>
       
    </div>
    <?php endif; ?>

    <script>
    function openAddPropertyModal() {
        document.getElementById('addPropertyModal').style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeAddPropertyModal() {
        document.getElementById('addPropertyModal').style.display = 'none';
        document.body.style.overflow = 'auto';
        document.getElementById('addPropertyForm').reset();
    }

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
        var addModal = document.getElementById('addPropertyModal');
        var maintenanceModal = document.getElementById('maintenanceCostModal');
        if (event.target == addModal) {
            closeAddPropertyModal();
        }
        if (event.target == maintenanceModal) {
            closeMaintenanceCostModal();
        }
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeAddPropertyModal();
            closeMaintenanceCostModal();
        }
    });

    // Check if page loaded with success parameter and ensure modal is closed
    window.onload = function() {
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success') || urlParams.has('maintenance_success')) {
            closeAddPropertyModal();
            closeMaintenanceCostModal();
            // Remove the success parameter from URL without page refresh
            var newUrl = window.location.pathname;
            window.history.replaceState({}, '', newUrl);
        }
    };
    </script>
</body>
</html>
