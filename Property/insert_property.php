<?php
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
    <title>Add New Property - PSAU Management System</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-row {
            display: flex;
            gap: 15px;
        }
        .form-row .form-group {
            flex: 1;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .success {
            color: #28a745;
            background: #d4edda;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .error {
            color: #721c24;
            background: #f8d7da;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .form-header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h1>Add New Property</h1>
            <p>Fill in the details below to add a new property to the inventory</p>
        </div>

        <a href="index.php" class="back-link">← Back to Property List</a>

        <?php if (isset($success_message)): ?>
            <div class="success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group">
                    <label for="property_no">Property No:</label>
                    <input type="text" id="property_no" name="property_no" required>
                </div>
                <div class="form-group">
                    <label for="property_tag">Property Tag:</label>
                    <input type="text" id="property_tag" name="property_tag">
                </div>
            </div>

            <div class="form-group">
                <label for="property_item">Property Item:</label>
                <input type="text" id="property_item" name="property_item" required>
            </div>

            <div class="form-group">
                <label for="property_description">Property Description:</label>
                <textarea id="property_description" name="property_description" rows="3"></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="property_model_number">Model Number:</label>
                    <input type="text" id="property_model_number" name="property_model_number">
                </div>
                <div class="form-group">
                    <label for="property_serial_number">Serial Number:</label>
                    <input type="text" id="property_serial_number" name="property_serial_number">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="property_value">Property Value:</label>
                    <input type="text" id="property_value" name="property_value">
                </div>
                <div class="form-group">
                    <label for="property_acquisition_date">Acquisition Date:</label>
                    <input type="date" id="property_acquisition_date" name="property_acquisition_date">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="property_accountable_person">Accountable Person:</label>
                    <input type="text" id="property_accountable_person" name="property_accountable_person">
                </div>
                <div class="form-group">
                    <label for="property_actual_location">Actual Location:</label>
                    <input type="text" id="property_actual_location" name="property_actual_location">
                </div>
            </div>

            <div class="form-group">
                <label for="property_remarks">Remarks:</label>
                <textarea id="property_remarks" name="property_remarks" rows="2"></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="property_counted">Counted:</label>
                    <select id="property_counted" name="property_counted">
                        <option value="">Select...</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="property_condition">Condition:</label>
                    <select id="property_condition" name="property_condition">
                        <option value="">Select...</option>
                        <option value="Good">Good</option>
                        <option value="Fair">Fair</option>
                        <option value="Poor">Poor</option>
                        <option value="Damaged">Damaged</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="property_validated">Validated:</label>
                    <select id="property_validated" name="property_validated">
                        <option value="">Select...</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="property_status">Status:</label>
                    <select id="property_status" name="property_status">
                        <option value="">Select...</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                        <option value="Disposed">Disposed</option>
                        <option value="Lost">Lost</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="property_fund">Fund:</label>
                    <input type="text" id="property_fund" name="property_fund">
                </div>
                <div class="form-group">
                    <label for="property_year_purchased">Year Purchased:</label>
                    <input type="text" id="property_year_purchased" name="property_year_purchased">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="property_sm_group_account">SM Group Account:</label>
                    <input type="text" id="property_sm_group_account" name="property_sm_group_account">
                </div>
                <div class="form-group">
                    <label for="property_gl_account">GL Account:</label>
                    <input type="text" id="property_gl_account" name="property_gl_account">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="property_number">Property Number:</label>
                    <input type="text" id="property_number" name="property_number">
                </div>
                <div class="form-group">
                    <label for="property_loc">Location:</label>
                    <input type="text" id="property_loc" name="property_loc">
                </div>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <button type="submit" class="btn">Add Property</button>
                <button type="reset" class="btn" style="background: #6c757d; margin-left: 10px;">Clear Form</button>
            </div>
        </form>
    </div>
</body>
</html>
