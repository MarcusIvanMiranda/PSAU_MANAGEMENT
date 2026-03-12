<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property QR Code - PSAU</title>
    <link rel="icon" href="PSAU.ico">
    <?php
    session_start();
    if (!isset($_SESSION['property_loggedin']) || $_SESSION['property_loggedin'] !== true) {
        header("location: login.php");
        exit;
    }
    include 'connect.php';
    ?>
    <style>
        @page {
            margin: 0.3in;
            size: auto;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: white;
            color: black;
        }

        .print-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0.5in;
        }

        .property-tag {
            width: 3.8in;
            border: 2px solid black;
            background: white;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* HEADER */
        .tag-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.07in 0.1in 0.07in;
            border-bottom: 2px solid black;
            gap: 0.07in;
        }

        .university-seal {
            width: 0.5in;
            height: 0.5in;
            object-fit: contain;
            flex-shrink: 0;
        }

        .header-title {
            flex: 1;
            text-align: center;
        }

        .header-title .uni-name {
            font-size: 0.12in;
            font-weight: bold;
            line-height: 1.25;
            text-transform: uppercase;
            letter-spacing: 0.005in;
        }

        .header-title .prop-tag-label {
            font-size: 0.14in;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.01in;
            margin-top: 0.02in;
        }

        /* QR — shrunk to match smaller tag */
        .qr-code {
            width: 0.65in;
            height: 0.65in;
            flex-shrink: 0;
            border: none;
            display: block;
        }

        /* FIELDS TABLE */
        .fields-table {
            width: 100%;
            border-collapse: collapse;
        }

        .fields-table tr td {
            border-top: 1px solid black;
            padding: 0.04in 0.07in;
            font-size: 0.09in;
            line-height: 1.2;
            vertical-align: middle;
        }

        .fields-table tr:first-child td {
            border-top: none;
        }

        .fields-table td.label {
            font-weight: bold;
            width: 42%;
            border-right: 1px solid black;
            font-size: 0.09in;
        }

        .fields-table td.value {
            width: 58%;
            font-size: 0.09in;
            word-break: break-word;
        }

        .fields-table tr.sig-row td {
            padding-top: 0.05in;
            padding-bottom: 0.09in;
        }

        /* FOOTER */
        .disclaimer {
            border-top: 1px solid black;
            text-align: center;
            font-size: 0.07in;
            font-style: italic;
            padding: 0.04in 0.07in;
        }

        .not-found {
            text-align: center;
            padding: 2rem;
        }

        @media print {
            body { margin: 0; padding: 0; }
            .print-wrapper { padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">
<?php
    include 'connect.php';
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $ReferenceID = strtoupper($_POST['RefID']);
    $query = "SELECT * FROM property_list WHERE property_tag='$ReferenceID'";
    $result = mysqli_query($conn, $query);

    $qr_url = "http://campus.psau.edu.ph/property/propertydocument.php?filtertext=" . $ReferenceID;
?>

<?php if ($result && mysqli_num_rows($result) > 0): ?>
    <?php while ($data = mysqli_fetch_assoc($result)): ?>
    <div class="print-wrapper">
        <div class="property-tag">

            <!-- HEADER -->
            <div class="tag-header">
                <img src="PSAU.ico" alt="PSAU Seal" class="university-seal">
                <div class="header-title">
                    <div class="uni-name">Pampanga State<br>Agricultural University</div>
                    <div class="prop-tag-label">Property Tag</div>
                </div>
                <iframe class="qr-code" frameborder="0" id="qrcode0" src="" scrolling="no"></iframe>
            </div>

            <!-- FIELDS — using column names, no index guessing -->
            <table class="fields-table">
                <tr>
                    <td class="label">PROPERTY NUMBER</td>
                    <td class="value"><?php echo htmlspecialchars($data['property_no'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td class="label">DESCRIPTION/MODEL<br>NUMBER</td>
                    <td class="value">
                        <?php
                            $item  = trim($data['property_item'] ?? '');
                            $model = trim($data['property_model_number'] ?? '');
                            if ($item && $model) {
                                echo htmlspecialchars($item . ' - ' . $model);
                            } else {
                                echo htmlspecialchars($item ?: $model);
                            }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="label">SERIAL NUMBER</td>
                    <td class="value"><?php echo htmlspecialchars($data['property_serial_number'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td class="label">ACQUISITION COST</td>
                    <td class="value"><?php echo htmlspecialchars($data['addition_cost'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td class="label">ACQUISITION DATE</td>
                    <td class="value"><?php echo htmlspecialchars($data['property_acquisition_date'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td class="label">PERSON ACCOUNTABLE</td>
                    <td class="value"><?php echo htmlspecialchars($data['property_accountable_person'] ?? ''); ?></td>
                </tr>
                <tr class="sig-row">
                    <td class="label">VALIDATION/SIGNATURE</td>
                    <td class="value"></td>
                </tr>
            </table>

            <div class="disclaimer">
                "Removing or tampering of this sticker is punishable by law"
            </div>

        </div>
    </div>
    <?php endwhile; ?>

<?php else: ?>
    <div class="not-found">
        <h2>Property Not Found</h2>
        <p>No property found with tag: <strong><?php echo htmlspecialchars($ReferenceID); ?></strong></p>
    </div>
<?php endif; ?>

<input type="hidden" id="qr" value="<?php echo $qr_url; ?>">

<script>
function UpdateQRCode(val, elementId) {
    document.getElementById(elementId).setAttribute(
        "src",
        "https://api.mimfa.net/qrcode?value=" + encodeURIComponent(val) + "&as=value"
    );
}
document.addEventListener("DOMContentLoaded", function () {
    UpdateQRCode(document.getElementById("qr").value, "qrcode0");
});
</script>
</body>
</html>