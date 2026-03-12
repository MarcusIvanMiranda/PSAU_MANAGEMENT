<?php
session_start();
if (!isset($_SESSION['property_loggedin']) || $_SESSION['property_loggedin'] !== true) {
    header("location: login.php");
    exit;
}

require_once 'connect.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    // Insert query
    $sql = "INSERT INTO property_list (
        property_no, property_tag, property_item, property_description, 
        property_model_number, property_serial_number, property_value, 
        property_acquisition_date, property_accountable_person, property_actual_location, 
        property_remarks, property_counted, property_condition, property_validated, 
        property_status, property_fund, property_year_purchased, property_sm_group_account, 
        property_gl_account, property_number, property_loc
    ) VALUES (
        '$property_no', '$property_tag', '$property_item', '$property_description', 
        '$property_model_number', '$property_serial_number', '$property_value', 
        '$property_acquisition_date', '$property_accountable_person', '$property_actual_location', 
        '$property_remarks', '$property_counted', '$property_condition', '$property_validated', 
        '$property_status', '$property_fund', '$property_year_purchased', '$property_sm_group_account', 
        '$property_gl_account', '$property_number', '$property_loc'
    )";

    if (mysqli_query($conn, $sql)) {
        $success_message = "Property added successfully!";
    } else {
        $error_message = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property - PSAU Property Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
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

        .form-container {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow);
        }
    </style>
</head>
<body>
    <div class="page-header">
        <h1 class="page-title">Add New Property</h1>
        <p class="page-subtitle">Fill in the details below to add a new property to the inventory</p>
    </div>

    <div class="form-container">

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

        <form method="POST" action="">
            <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label for="property_no" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Property No:</label>
                    <input type="text" id="property_no" name="property_no" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="property_tag" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Property Tag:</label>
                    <input type="text" id="property_tag" name="property_tag" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="property_item" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Property Item:</label>
                <input type="text" id="property_item" name="property_item" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="property_description" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Property Description:</label>
                <textarea id="property_description" name="property_description" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; resize: vertical;"></textarea>
            </div>

            <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label for="property_model_number" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Model Number:</label>
                    <input type="text" id="property_model_number" name="property_model_number" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="property_serial_number" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Serial Number:</label>
                    <input type="text" id="property_serial_number" name="property_serial_number" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
            </div>

            <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label for="property_value" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Property Value:</label>
                    <input type="text" id="property_value" name="property_value" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="property_acquisition_date" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Acquisition Date:</label>
                    <input type="date" id="property_acquisition_date" name="property_acquisition_date" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
            </div>

            <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label for="property_accountable_person" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Accountable Person:</label>
                    <input type="text" id="property_accountable_person" name="property_accountable_person" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="property_actual_location" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Actual Location:</label>
                    <input type="text" id="property_actual_location" name="property_actual_location" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="property_remarks" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Remarks:</label>
                <textarea id="property_remarks" name="property_remarks" rows="2" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; resize: vertical;"></textarea>
            </div>

            <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label for="property_counted" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Counted:</label>
                    <select id="property_counted" name="property_counted" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="">Select...</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="property_condition" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Condition:</label>
                    <select id="property_condition" name="property_condition" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
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
                    <label for="property_validated" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Validated:</label>
                    <select id="property_validated" name="property_validated" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="">Select...</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="property_status" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Status:</label>
                    <select id="property_status" name="property_status" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="">Select...</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                        <option value="Disposed">Disposed</option>
                        <option value="Lost">Lost</option>
                    </select>
                </div>
            </div>

            <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label for="property_fund" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Fund:</label>
                    <input type="text" id="property_fund" name="property_fund" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="property_year_purchased" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Year Purchased:</label>
                    <input type="text" id="property_year_purchased" name="property_year_purchased" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
            </div>

            <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label for="property_sm_group_account" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">SM Group Account:</label>
                    <input type="text" id="property_sm_group_account" name="property_sm_group_account" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="property_gl_account" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">GL Account:</label>
                    <input type="text" id="property_gl_account" name="property_gl_account" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
            </div>

            <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 20px;">
                <div class="form-group" style="flex: 1;">
                    <label for="property_number" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Property Number:</label>
                    <input type="text" id="property_number" name="property_number" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="property_loc" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Location:</label>
                    <input type="text" id="property_loc" name="property_loc" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
            </div>

            <div style="text-align: center; padding-top: 15px; border-top: 1px solid #ddd;">
                <button type="submit" class="btn btn-primary">Add Property</button>
                <button type="reset" class="btn btn-secondary" style="margin-left: 10px;">Clear Form</button>
            </div>
        </form>
    </div>
</body>
</html>
