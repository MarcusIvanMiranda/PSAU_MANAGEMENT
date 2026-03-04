<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Records QR Code - PSAU</title>
    <link rel="icon" href="PSAU.ico">
    <style>
        @page {
            margin: 0.5in;
            size: auto;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: white;
            color: #1f2937;
            line-height: 1.4;
        }
        
        .qr-container {
            display: flex;
            justify-content: center;
            padding: 2rem;
            max-width: 8.5in;
            margin: 0 auto;
        }
        
        .qr-card {
            border: 2px solid #1e40af;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            background: white;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        
        .qr-header {
            border-bottom: 2px solid #1e40af;
            padding-bottom: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .qr-header h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 0.25rem;
        }
        
        .qr-header p {
            font-size: 0.8rem;
            color: #6b7280;
            font-weight: 500;
        }
        
        .qr-code {
            margin: 0.75rem 0;
            display: flex;
            justify-content: center;
        }
        
        .qr-code iframe {
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            width: 180px;
            height: 180px;
        }
        
        .qr-details {
            font-size: 0.75rem;
            color: #374151;
            margin-top: 0.5rem;
        }
        
        .qr-details strong {
            color: #1f2937;
            font-weight: 600;
        }
        
        .property-tag {
            font-size: 0.9rem;
            font-weight: 700;
            color: #1e40af;
            margin: 0.5rem 0;
            padding: 0.25rem;
            background: #eff6ff;
            border-radius: 4px;
            border: 1px solid #bfdbfe;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .qr-container {
                gap: 0.5rem;
                padding: 0.5rem;
            }
            
            .qr-card {
                border: 1px solid #1e40af;
                padding: 0.5rem;
            }
        }
        
        @media (max-width: 768px) {
            .qr-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 480px) {
            .qr-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <?php include 'connect.php';

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $ReferenceID = strtoupper($_POST['RefID']);
    $serial_code = $ReferenceID;
    $query = "SELECT * FROM records_document_main WHERE serial_code='$ReferenceID'";
    $result = mysqli_query($conn, $query);

    $qr_url = "http://campus.psau.edu.ph/records/receivedocument.php?filtertext=$ReferenceID";
    ?>
    
    <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <?php while($data = mysqli_fetch_row($result)): ?>
            <div class="qr-container">
                <div class="qr-card">
                    <div class="qr-code">
                        <iframe frameborder='0' id='qrcode0' src='' width='180' height='180'></iframe>
                    </div>
                    
                    <div class="property-tag">
                        Record Number: <?php echo htmlspecialchars($data[0]); ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 2rem; font-family: Arial, sans-serif;">
            <h2>Record Not Found</h2>
            <p>No record found with serial code: <strong><?php echo htmlspecialchars($ReferenceID); ?></strong></p>
        </div>
    <?php endif; ?>
    
    <input type="hidden" id="qr" value="<?php echo $qr_url; ?>">

    <script>
    function UpdateQRCode(val, elementId) {
        document.getElementById(elementId).setAttribute("src", "https://api.mimfa.net/qrcode?value=" + encodeURIComponent(val) + "&as=value");
    }
    
    document.addEventListener("DOMContentLoaded", function() {
        const qrValue = document.getElementById("qr").value;
        // Update the single QR code iframe
        UpdateQRCode(qrValue, 'qrcode0');
    });
    </script>
</body>
</html>