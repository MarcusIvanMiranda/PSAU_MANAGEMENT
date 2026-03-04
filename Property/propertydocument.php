<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}
include "connect.php";
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
            <a href="index.php" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back to Property List
            </a>
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
            <?php while($data = mysqli_fetch_assoc($result)): ?>
                <?php
                $selected_status = $data['property_status']; // property_status field
                $is_released = stripos($selected_status, 'released') !== false;
                $show_release_section = !$is_released;
                ?>
                
                <div class="property-card">
                    <div class="property-header">
                        <div class="property-header-content">
                            <div class="property-icon">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                    <polyline points="9,22 9,12 15,12 15,22"/>
                                </svg>
                            </div>
                            <div class="property-title">
                                <h3>Property Details</h3>
                                <p>Property Tag: <strong><?php echo htmlspecialchars($data[2]); ?></strong></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="property-body">
                        <div class="property-details-grid">
                            <div class="details-section">
                                <h4 class="section-title">Basic Information</h4>
                                <div class="property-details">
                            <div class="detail-item">
                                <span class="detail-label">Property Number</span>
                                <span class="detail-value"><?php echo htmlspecialchars($data['property_no']); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Property Tag</span>
                                <span class="detail-value"><?php echo htmlspecialchars($data['property_tag']); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Item</span>
                                <span class="detail-value"><?php echo htmlspecialchars($data['property_item']); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Description/Model Number</span>
                                <span class="detail-value"><?php echo htmlspecialchars($data['property_description']); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Serial Number</span>
                                <span class="detail-value"><?php echo htmlspecialchars($data['property_serial_number']); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Value</span>
                                <span class="detail-value">₱<?php echo !empty($data['property_value']) ? number_format($data['property_value'], 2) : '0.00'; ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Acquisition Date</span>
                                <span class="detail-value"><?php echo htmlspecialchars($data['property_acquisition_date']); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Accountable Person</span>
                                <span class="detail-value"><?php echo htmlspecialchars($data['property_accountable_person']); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <span class="detail-label">Actual Location</span>
                                <span class="detail-value"><?php echo htmlspecialchars($data['property_actual_location']); ?></span>
                            </div>
                            
                            <?php if (!empty($data['property_remarks'])): ?>
                            <div class="detail-item" style="grid-column: 1 / -1;">
                                <span class="detail-label">Remarks</span>
                                <span class="detail-value"><?php echo htmlspecialchars($data['property_remarks']); ?></span>
                            </div>
                            <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="details-section">
                                <h4 class="section-title">Status & Location</h4>
                                <div class="property-details">
                                    <div class="detail-item">
                                        <span class="detail-label">Condition</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($data['property_condition']); ?></span>
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
                                        <span class="detail-label">Actual Location</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($data['property_actual_location']); ?></span>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <span class="detail-label">Accountable Person</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($data['property_accountable_person']); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="details-section">
                                <h4 class="section-title">Financial Information</h4>
                                <div class="property-details">
                                    <div class="detail-item">
                                        <span class="detail-label">Value</span>
                                        <span class="detail-value value-highlight">₱<?php echo !empty($data['property_value']) ? number_format($data['property_value'], 2) : '0.00'; ?></span>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <span class="detail-label">Fund</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($data['property_fund']); ?></span>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <span class="detail-label">Year Purchased</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($data['property_year_purchased']); ?></span>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <span class="detail-label">Acquisition Date</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($data['property_acquisition_date']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="qr-section">
                    <div class="qr-header">
                        <h3>QR Code</h3>
                        <p>Scan this QR code to view property details</p>
                    </div>
                    <div class="qr-code-container">
                        <div class="qr-code">
                            <iframe frameborder='0' id='qrcode' src='' width='200' height='200'></iframe>
                        </div>
                        <div class="qr-actions">
                            <button class="btn btn-primary btn-sm" onclick="window.print()">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6,9 6,2 18,2 18,9"/>
                                    <path d="M6,18H4a2,2,0,0,1-2-2V8a2,2,0,0,1,2-2H16a2,2,0,0,1,2,2v2"/>
                                    <path d="M18,14h1a2,2,0,0,1,2,2v4a2,2,0,0,1-2,2H6a2,2,0,0,1-2-2V20a2,2,0,0,1,2-2h1"/>
                                    <rect x="6" y="14" width="12" height="8"/>
                                </svg>
                                Print QR Code
                            </button>
                        </div>
                    </div>
                </div>
                
                <?php if ($show_release_section): ?>
                <div class="property-card release-card" style="margin-top: 2rem;">
                    <div class="property-header release-header">
                        <div class="property-header-content">
                            <div class="property-icon release-icon">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="7,10 12,15 17,10"/>
                                    <line x1="12" y1="15" x2="12" y2="3"/>
                                </svg>
                            </div>
                            <div class="property-title">
                                <h3>Release Property</h3>
                                <p>Process property release</p>
                            </div>
                        </div>
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
