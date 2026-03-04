<!DOCTYPE html>
<html lang="en">

<head>
    <title>PSAU Records Unit - Document Details</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="PSAU.ico">
    <link rel="stylesheet" href="assets/css/psau-style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-sans);
            background-color: var(--psau-gray-50);
            color: var(--psau-gray-700);
            padding: 20px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--psau-white);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
        }

        .header {
            background: var(--psau-primary);
            color: var(--psau-white);
            padding: var(--space-8);
            text-align: center;
        }

        .header h1 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: var(--space-2);
            color: var(--psau-white);
        }

        .header p {
            opacity: 0.9;
            font-size: 1.1rem;
            color: var(--psau-white);
        }

        .content {
            padding: var(--space-10);
        }

        .document-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-10);
            margin-bottom: var(--space-10);
        }

        .document-details {
            background: var(--psau-lighter);
            padding: var(--space-8);
            border-radius: var(--radius-xl);
            border: 1px solid var(--psau-gray-200);
        }

        .detail-item {
            display: flex;
            margin-bottom: var(--space-5);
            align-items: flex-start;
        }

        .detail-label {
            font-weight: 600;
            color: var(--psau-gray-600);
            min-width: 150px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-value {
            color: var(--psau-gray-800);
            font-weight: 500;
            flex: 1;
            word-break: break-word;
        }

        .qr-section {
            text-align: center;
        }

        .qr-container {
            background: var(--psau-white);
            padding: var(--space-5);
            border-radius: var(--radius-xl);
            border: 2px solid var(--psau-gray-200);
            margin-bottom: var(--space-5);
            min-height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .files-section {
            background: var(--psau-lighter);
            padding: var(--space-5);
            border-radius: var(--radius-xl);
            border: 1px solid var(--psau-gray-200);
            min-height: 250px;
        }

        .status-section {
            background: var(--psau-primary);
            padding: var(--space-8);
            border-radius: var(--radius-xl);
            color: var(--psau-white);
            margin-bottom: var(--space-8);
        }

        .status-form {
            display: flex;
            align-items: center;
            gap: var(--space-5);
            flex-wrap: wrap;
        }

        .status-select {
            padding: var(--space-3) var(--space-5);
            border: none;
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: 500;
            background: var(--psau-white);
            color: var(--psau-gray-700);
            min-width: 200px;
            cursor: pointer;
        }

        .update-btn {
            background: var(--psau-warning);
            color: var(--psau-white);
            border: none;
            padding: var(--space-3) var(--space-8);
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .update-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .upload-section {
            background: var(--psau-light);
            padding: var(--space-6);
            border-radius: var(--radius-xl);
            border: 2px dashed var(--psau-success);
            margin-bottom: var(--space-8);
        }

        .upload-form {
            display: flex;
            align-items: center;
            gap: var(--space-5);
            flex-wrap: wrap;
        }

        .file-input {
            padding: var(--space-2);
            border: 2px solid var(--psau-success);
            border-radius: var(--radius);
            background: var(--psau-white);
            font-size: 1rem;
        }

        .upload-btn {
            background: var(--psau-success);
            color: var(--psau-white);
            border: none;
            padding: var(--space-3) var(--space-8);
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .upload-btn:hover {
            background: var(--psau-accent);
            transform: translateY(-2px);
        }

        .tracking-table {
            width: 100%;
            background: var(--psau-white);
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .tracking-table thead {
            background: var(--psau-primary);
            color: var(--psau-white);
        }

        .tracking-table th {
            padding: var(--space-5);
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .tracking-table td {
            padding: var(--space-4) var(--space-5);
            border-bottom: 1px solid var(--psau-gray-200);
        }

        .tracking-table tbody tr:hover {
            background: var(--psau-lighter);
        }

        .tracking-table tbody tr:last-child td {
            border-bottom: none;
        }

        @media (max-width: 768px) {
            .document-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .content {
                padding: 20px;
            }
            
            .status-form, .upload-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .detail-item {
                flex-direction: column;
                margin-bottom: 15px;
            }
            
            .detail-label {
                margin-bottom: 5px;
            }
        }

        iframe {
            border: none;
            border-radius: var(--radius-xl);
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: var(--space-5);
            color: var(--psau-gray-900);
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }

        .section-title::before {
            content: '';
            width: 4px;
            height: 24px;
            background: var(--psau-primary);
            border-radius: 2px;
        }
        
        /* Scrollbar Styles */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--psau-accent);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #3d7d54;
        }
    </style>
</head>

<body onload=loaduploadlist()>
    <div class="container">
        <div class="header">
            <h1>📄 Document Details</h1>
            <p>PSAU Records Unit - Document Tracking System</p>
        </div>
        
        <div class="content">


<?php include 'connect.php';

ini_set('display_errors','Off');
ini_set('error_reporting', E_ALL );
define('WP_DEBUG', false);
define('WP_DEBUG_DISPLAY', false);

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) 
{
  die("Connection failed: " . $conn->connect_error);
}
$ReferenceID=strtoupper($_POST['RefID']);
$serial_code=$ReferenceID;
$query = "SELECT * FROM records_document_main where serial_code='$ReferenceID'";
$result = mysqli_query($conn, $query);

echo"<input hidden id='qr' value='http://campus.psau.edu.ph/records/receivedocument.php?filtertext=$ReferenceID'/><br>";

if ($result)
{
    $row = mysqli_num_rows($result);
       if ($row)
          {
            $count=mysqli_num_rows($result);

            while($data=mysqli_fetch_row($result))
            {
              $selected_status=$data[3];
              if ($selected_status=="RELEASED") {$hideme="hidden";$showme="<span class='status-released'>".$data[3]."</span>";}
              if ($selected_status=="FOR RELEASING") {$hideme="";}
              
            echo "<div class='status-section'>
                <h2 class='section-title'>📊 Document Status</h2>
                <form id='updateform' name='updateform' action='updatestatus.php' method='post' class='status-form'>
                    <label for='doc_status' style='font-size: 1.1rem; font-weight: 500;'>Current Status:</label>
                    <select $hideme name='doc_status' id='doc_status' class='status-select'>
                        <option "; if($selected_status == 'FOR RELEASING'){echo('selected');} echo " value='FOR RELEASING'>FOR RELEASING</option>
                        <option "; if($selected_status == 'RELEASED'){echo('selected');} echo " value='RELEASED'>RELEASED</option>
                    </select>
                    $showme
                    <button type='submit' $hideme id='updatebutton' name='updatebutton' class='update-btn'>UPDATE STATUS</button>
                    <input hidden id='RefID2' name ='RefID2' value='$ReferenceID'/>
                </form>
            </div>

            <div class='document-grid'>
                <div class='document-details'>
                    <h2 class='section-title'>📋 Document Information</h2>
                    <div class='detail-item'>
                        <div class='detail-label'>Serial Code:</div>
                        <div class='detail-value'>$data[6]</div>
                    </div>
                    <div class='detail-item'>
                        <div class='detail-label'>Record #:</div>
                        <div class='detail-value'>$data[0]</div>
                    </div>
                    <div class='detail-item'>
                        <div class='detail-label'>Document Title:</div>
                        <div class='detail-value'>$data[1]</div>
                    </div>
                    <div class='detail-item'>
                        <div class='detail-label'>Document Type:</div>
                        <div class='detail-value'>$data[2]</div>
                    </div>
                    <div class='detail-item'>
                        <div class='detail-label'>Date & Time:</div>
                        <div class='detail-value'>$data[4]</div>
                    </div>
                </div>

                <div class='qr-section'>
                    <h2 class='section-title'>📱 QR Code</h2>
                    <div class='qr-container'>
                        <iframe frameborder='0' id='qrcode' src='' width='100%' height='200'></iframe>
                    </div>
                </div>
            </div>

            <div class='upload-section'>
                <h2 class='section-title'>📁 Upload File</h2>
                <form action='uploadfile.php' id='uform' name='uform' method='post' enctype='multipart/form-data' class='upload-form'>
                    <input id='serialno' name='serialno' hidden type='text' value='$ReferenceID'/>
                    <input $hideme id='efile' name='efile' type='file' class='file-input'/>
                    <button $hideme type='submit' class='upload-btn'>📤 Upload File</button>
                </form>
            </div>

            <div class='files-section'>
                <h2 class='section-title'>📎 Uploaded Files</h2>
                <iframe frameborder='0' id='QQ' src='' width='100%' height='250'></iframe>
            </div>
            ";
            } 
            echo "</div>";      
          }
    mysqli_free_result($result);
}
mysqli_close($conn);
?>






<style>
    th {
        font-size:12px;background-color:green;color:white;font-family:tahoma;text-align:left;font-weight:bold; valign:top;
    }
    td {
        font-size:12px;background-color:white;color:black;font-family:tahoma;text-align:left;font-weight:bold; valign:top;
    }
</style>


<?php include 'connect.php';
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) 
{
  die("Connection failed: " . $conn->connect_error);
}
$ReferenceID=strtoupper($_POST['RefID']);
$query = "SELECT * FROM records_document_received where serial_code='$ReferenceID' order by date_received desc";
$result = mysqli_query($conn, $query);

if ($result)
{
    $row = mysqli_num_rows($result);
       if ($row)
          {
            $count=mysqli_num_rows($result);

            echo "
            <div class='tracking-section'>
                <h2 class='section-title'>📍 Document Tracking History</h2>
                <table class='tracking-table'>
                    <thead>
                        <tr>
                            <th>Office Name</th>
                            <th>Received By</th>
                            <th>Date Received</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
            ";

            while($data=mysqli_fetch_row($result))
            {
            echo
            "
                <tr>  
                    <td>".$data[1]."</td>   
                    <td>".$data[2]."</td>   
                    <td>".$data[3]."</td>   
                    <td>".$data[6]."</td> 
                </tr>
            ";
            } 
            echo "
                    </tbody>
                </table>
            </div>
            ";      
          }
    mysqli_free_result($result);
}
mysqli_close($conn);
?>


        </div>
    </div>

<script>
function UpdateQRCode(val){
    document.getElementById("qrcode").setAttribute("src","https://api.mimfa.net/qrcode?value="+encodeURIComponent(val)+"&as=value");
}
document.addEventListener("DOMContentLoaded", function(){
    UpdateQRCode(document.getElementById("qr").value);
    var iframe=document.getElementById("QQ");
    iframe.src = "uploadlist.php?filtertext2=<?php echo $ReferenceID; ?>";
});
</script>


<script>
var iframe=document.getElementById("QQ");
function loaduploadlist() {
iframe.src = "uploadlist.php?filtertext2=<?php echo $ReferenceID; ?>";
//iframe.scrolling = "no";
//iframe.height="1600";
}
</script>


</body>
</html>