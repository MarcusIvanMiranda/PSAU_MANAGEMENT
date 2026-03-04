<?php include "connect.php";
error_reporting(0);
$datatable = "property_list"; // MySQL table name
$results_per_page = 27; // number of results per page
 
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$filtertext="";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Details - PSAU</title>
    <link rel="icon" href="PSAU.ico">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <img src="PSAU_10.jpg" alt="PSAU Logo" class="header-logo">
            <div class="header-title">
                <h1>PAMPANGA STATE AGRICULTURAL UNIVERSITY</h1>
                <h2>Property Management System</h2>
            </div>
        </div>
    </header>

    <div class="container">
        <nav style="margin-bottom: 2rem;">
            <a href="index.php" class="btn btn-primary">← Back to Property List</a>
        </nav>

<?php
$filtertext = isset($_GET['filtertext']) ? trim($_GET['filtertext']) : '';
if (isset($_GET["page"])) { $page = $_GET["page"]; } else { $page=1; };
$start_from = ($page-1) * $results_per_page;
$sql = "SELECT * FROM ".$datatable." WHERE property_tag = '".$filtertext."' ORDER BY property_tag ASC LIMIT $start_from, ".$results_per_page;
$rs_result = $conn->query($sql);

// Get property details
$ReferenceID = $filtertext;
$query = "SELECT * FROM property_list WHERE property_tag='$ReferenceID'";
$result = mysqli_query($conn, $query);
?>

        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while($data = mysqli_fetch_row($result)): ?>
                <?php
                $selected_status = $data[16]; // property_status field
                $is_released = stripos($selected_status, 'released') !== false;
                $show_release_section = !$is_released;
                ?>
                
                <div class="property-card">
                    <div class="property-header">
                        <h3>Property Details</h3>
                        <p>Property Tag: <strong><?php echo htmlspecialchars($data[2]); ?></strong></p>
                    </div>
                    
                    <div class="property-body">
                        <div class="property-details">
                            <div class="detail-item">
                                <span class="detail-label">Property Number</span>
                                <span class="detail-value"><?php echo htmlspecialchars($data[1]); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Property Tag</span>
                                <span class="detail-value"><?php echo htmlspecialchars($data[2]); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Item</span>
                                <span class="detail-value"><?php echo htmlspecialchars($data[3]); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Description/Model Number</span>
                                <span class="detail-value"><?php echo htmlspecialchars($data[4]); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Serial Number</span>
                                <span class="detail-value"><?php echo htmlspecialchars($data[5]); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Value</span>
                                <span class="detail-value">₱<?php echo !empty($data[6]) ? number_format($data[6], 2) : '0.00'; ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Acquisition Date</span>
                                <span class="detail-value"><?php echo htmlspecialchars($data[7]); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Accountable Person</span>
                                <span class="detail-value"><?php echo htmlspecialchars($data[8]); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Actual Location</span>
                                <span class="detail-value"><?php echo htmlspecialchars($data[10]); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Condition</span>
                                <span class="detail-value"><?php echo htmlspecialchars($data[14]); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Status</span>
                                <span class="detail-value">
                                    <?php 
                                    $status = htmlspecialchars($selected_status);
                                    $status_class = "";
                                    if (stripos($status, 'released') !== false) {
                                        $status_class = "status-released";
                                    } elseif (stripos($status, 'releasing') !== false) {
                                        $status_class = "status-for-releasing";
                                    }
                                    echo "<span class='status-badge $status_class'>$status</span>";
                                    ?>
                                </span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Fund</span>
                                <span class="detail-value"><?php echo htmlspecialchars($data[17]); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Year Purchased</span>
                                <span class="detail-value"><?php echo htmlspecialchars($data[18]); ?></span>
                            </div>
                            
                            <?php if (!empty($data[11])): ?>
                            <div class="detail-item" style="grid-column: 1 / -1;">
                                <span class="detail-label">Remarks</span>
                                <span class="detail-value"><?php echo htmlspecialchars($data[11]); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="qr-section">
                    <h3>QR Code</h3>
                    <div class="qr-code">
                        <iframe frameborder='0' id='qrcode' src='' width='200' height='200'></iframe>
                    </div>
                    <p>Scan this QR code to view property details</p>
                </div>
                
                <?php if ($show_release_section): ?>
                <div class="property-card" style="margin-top: 2rem;">
                    <div class="property-header" style="background: linear-gradient(135deg, var(--warning-color), #d97706);">
                        <h3>Release Property</h3>
                        <p>Process property release</p>
                    </div>
                    <div class="property-body">
                        <form action='receiveadd.php' method='POST'>
                            <div style="text-align: center; padding: 2rem;">
                                <label for="ename" style="font-size: 1.2rem; font-weight: 600;">...</label>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="property-card">
                <div class="property-body" style="text-align: center; padding: 3rem;">
                    <h3>Property Not Found</h3>
                    <p style="color: var(--gray-500); margin-top: 1rem;">
                        No property found with tag: <strong><?php echo htmlspecialchars($ReferenceID); ?></strong>
                    </p>
                    <a href="index.php" class="btn btn-primary" style="margin-top: 1rem;">← Back to Property List</a>
                </div>
            </div>
        <?php endif; ?>
        
        <input type="hidden" id="qr" value="<?php echo $ReferenceID; ?>">
    </div>

    <footer style="text-align: center; padding: 2rem; color: var(--gray-500); margin-top: 3rem;">
        <p>&copy; <?php echo date('Y'); ?> PAMPANGA STATE AGRICULTURAL UNIVERSITY - Property Management System</p>
    </footer>

    <script>
    function UpdateQRCode(val){
        document.getElementById("qrcode").setAttribute("src","https://api.mimfa.net/qrcode?value="+encodeURIComponent(val)+"&as=value");
    }
    document.addEventListener("DOMContentLoaded", function(){
        UpdateQRCode(document.getElementById("qr").value);
    });
    </script>
</body>
</html>
