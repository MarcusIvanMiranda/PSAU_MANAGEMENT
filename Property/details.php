<?php include "connect.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$RefID = isset($_POST['RefID']) ? $_POST['RefID'] : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Property Details - PSAU</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .detail-table { border-collapse: collapse; width: 100%; }
        .detail-table td { border: 1px solid #ddd; padding: 8px; }
        .detail-table td:first-child { font-weight: bold; background-color: #f2f2f2; width: 30%; }
        .back-btn { background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; }
        
        /* Scrollbar Styles */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #4a9d6a;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #3d7d54;
        }
    </style>
</head>
<body>

<h2>Property Details</h2>

<?php
if (!empty($RefID)) {
    $query = "SELECT * FROM property_list WHERE property_serial_number = '$RefID'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        ?>
        
        <table class="detail-table">
            <tr>
                <td>Property ID:</td>
                <td><?php echo $row['idproperty_list']; ?></td>
            </tr>
            <tr>
                <td>Property No:</td>
                <td><?php echo $row['property_no']; ?></td>
            </tr>
            <tr>
                <td>Property Tag:</td>
                <td><?php echo $row['property_tag']; ?></td>
            </tr>
            <tr>
                <td>Item:</td>
                <td><?php echo $row['property_item']; ?></td>
            </tr>
            <tr>
                <td>Description:</td>
                <td><?php echo $row['property_description']; ?></td>
            </tr>
            <tr>
                <td>Model Number:</td>
                <td><?php echo $row['property_model_number']; ?></td>
            </tr>
            <tr>
                <td>Serial Number:</td>
                <td><?php echo $row['property_serial_number']; ?></td>
            </tr>
            <tr>
                <td>Value:</td>
                <td><?php echo $row['property_value']; ?></td>
            </tr>
            <tr>
                <td>Acquisition Date:</td>
                <td><?php echo $row['property_acquisition_date']; ?></td>
            </tr>
            <tr>
                <td>Accountable Person:</td>
                <td><?php echo $row['property_accountable_person']; ?></td>
            </tr>
            <tr>
                <td>Actual Location:</td>
                <td><?php echo $row['property_actual_location']; ?></td>
            </tr>
            <tr>
                <td>Status:</td>
                <td><?php echo $row['property_status']; ?></td>
            </tr>
            <tr>
                <td>Condition:</td>
                <td><?php echo $row['property_condition']; ?></td>
            </tr>
            <tr>
                <td>Remarks:</td>
                <td><?php echo $row['property_remarks']; ?></td>
            </tr>
        </table>
        
        <?php
    } else {
        echo "<p>No property found with Serial Number: " . htmlspecialchars($RefID) . "</p>";
    }
} else {
    echo "<p>No property ID provided.</p>";
}
?>

<br><br>
<a href="index.php" class="back-btn">Back to Property List</a>

</body>
</html>

<?php
if (isset($conn)) {
    mysqli_close($conn);
}
?>
