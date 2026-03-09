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

$property_tag = isset($_GET['filtertext']) ? trim($_GET['filtertext']) : '';
$property_data = null;

if (!empty($property_tag)) {
    $query = "SELECT * FROM property_list WHERE property_tag = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $property_tag);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $property_data = $result->fetch_assoc();
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Document - PSAU</title>
    <link rel="icon" href="PSAU.ico">
    <link rel="stylesheet" href="style.css">
    <style>
        .document-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .document-header {
            text-align: center;
            border-bottom: 3px solid #059669;
            padding-bottom: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .document-header h1 {
            color: #059669;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .document-header p {
            color: #6b7280;
            font-size: 1.1rem;
        }
        
        .property-details {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .details-section {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        
        .details-section h3 {
            color: #059669;
            margin-bottom: 1rem;
            font-size: 1.2rem;
            border-bottom: 2px solid #059669;
            padding-bottom: 0.5rem;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e2e8f0;
            align-items: flex-start;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: #374151;
            min-width: 180px;
            flex-shrink: 0;
            padding-top: 0.5rem;
        }
        
        .detail-value {
            color: #1f2937;
            text-align: left;
            flex: 1;
            padding: 0.5rem 0.75rem;
            background: #f8fafc;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            line-height: 1.5;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .qr-section {
            text-align: center;
            background: #f0f9ff;
            padding: 1.5rem;
            border-radius: 8px;
            border: 2px solid #059669;
        }
        
        .qr-section h3 {
            color: #059669;
            margin-bottom: 1rem;
        }
        
        .qr-code {
            margin: 1rem 0;
            display: flex;
            justify-content: center;
        }
        
        .qr-code iframe {
            border: 2px solid #059669;
            border-radius: 8px;
            width: 200px;
            height: 200px;
        }
        
        .property-tag-display {
            font-size: 1.2rem;
            font-weight: 700;
            color: #059669;
            margin: 1rem 0;
            padding: 0.5rem;
            background: #eff6ff;
            border-radius: 4px;
            border: 1px solid #bfdbfe;
        }
        
        .action-buttons {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #e2e8f0;
        }
        
        .btn {
            margin: 0 0.5rem;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #059669;
            color: white;
        }
        
        .btn-primary:hover {
            background: #047857;
        }
        
        .btn-success {
            background: #059669;
            color: white;
        }
        
        .btn-success:hover {
            background: #047857;
        }
        
        .not-found {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }
        
        .not-found h2 {
            color: #dc2626;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .property-details {
                grid-template-columns: 1fr;
            }
            
            .document-container {
                margin: 1rem;
                padding: 1rem;
            }
        }
        
        @media print {
            .action-buttons {
                display: none;
            }
            
            .document-container {
                box-shadow: none;
                margin: 0;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <img src="PSAU_10.jpg" alt="PSAU Logo" class="header-logo">
            <div class="header-title">
                <h1>PAMPANGA STATE AGRICULTURAL UNIVERSITY</h1>
                <h2>Property Management System</h2>
            </div>
            <div class="header-user">
                <div style="text-align: right; margin-bottom: 0.5rem;">
                    <span class="user-info">Welcome, <?php echo htmlspecialchars($_SESSION['property_full_name']); ?></span>
                    <?php if (!empty($_SESSION['property_office'])): ?>
                        <div class="user-role">
                            🏢 <?php echo htmlspecialchars($_SESSION['property_office']); ?>
                            <?php if (!empty($_SESSION['property_members'])): ?>
                                | 👑 <?php echo htmlspecialchars($_SESSION['property_members']); ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <a href="index.php" class="btn btn-secondary">← Back to Properties</a>
            </div>
        </div>
    </header>

    <div class="document-container">
        <?php if ($property_data): ?>
            <div class="document-header">
                <h1>PROPERTY DOCUMENT</h1>
                <p>Official Property Record and Tracking Information</p>
            </div>

            <div class="property-details">
                <div class="details-section">
                    <h3>Property Information</h3>
                    <div class="detail-row">
                        <span class="detail-label">Property #:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($property_data['property_no']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Property Tag:</span>
                        <span class="detail-value"><strong><?php echo htmlspecialchars($property_data['property_tag']); ?></strong></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Item Name:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($property_data['property_item']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Description:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($property_data['property_description']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Serial Number:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($property_data['property_serial_number'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Value:</span>
                        <span class="detail-value">₱<?php 
                            $propertyValue = $property_data['property_value'] ?? '0';
                            $cleanedValue = str_replace([',', ' '], '', $propertyValue);
                            if (is_numeric($cleanedValue)) {
                                echo number_format((float)$cleanedValue, 2); 
                            } else {
                                echo htmlspecialchars($propertyValue);
                            }
                        ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Acquisition Date:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($property_data['property_acquisition_date'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Accountable Person:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($property_data['property_accountable_person'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($property_data['property_status'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Remarks:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($property_data['property_remarks'] ?? 'N/A'); ?></span>
                    </div>
                </div>

                <div class="qr-section">
                    <h3>Track This Property</h3>
                    <div class="qr-code">
                        <iframe frameborder='0' id='qrcode' src='' width='200' height='200'></iframe>
                    </div>
                    <div class="property-tag-display">
                        <?php echo htmlspecialchars($property_data['property_tag']); ?>
                    </div>
                    <p style="color: #6b7280; font-size: 0.9rem; margin-top: 0.5rem;">
                        Scan QR code to track this property
                    </p>
                </div>
            </div>

            <div class="action-buttons">
                <form action='printqrnow.php' method='POST' target='_blank' style="display: inline;">
                    <button type='submit' name='RefID' value='<?php echo $property_data["property_tag"]; ?>' class="btn btn-success">
                        🖨️ Print QR Code
                    </button>
                </form>
                <a href="index.php" class="btn btn-primary">← Back to Property List</a>
            </div>

            <input type="hidden" id="qr" value="<?php echo "http://campus.psau.edu.ph/property/propertydocument.php?filtertext=" . urlencode($property_data['property_tag']); ?>">

        <?php else: ?>
            <div class="not-found">
                <h2>Property Not Found</h2>
                <p>No property found with tag: <strong><?php echo htmlspecialchars($property_tag); ?></strong></p>
                <p>Please check the property tag and try again.</p>
                <div style="margin-top: 2rem;">
                    <a href="index.php" class="btn btn-primary">← Back to Property List</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
    function UpdateQRCode(val, elementId) {
        document.getElementById(elementId).setAttribute("src", "https://api.mimfa.net/qrcode?value=" + encodeURIComponent(val) + "&as=value");
    }
    
    document.addEventListener("DOMContentLoaded", function() {
        const qrValue = document.getElementById("qr");
        if (qrValue) {
            UpdateQRCode(qrValue.value, 'qrcode');
        }
    });
    </script>
</body>
</html>