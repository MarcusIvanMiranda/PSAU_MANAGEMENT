<!DOCTYPE html>
<link rel="icon" href="PSAU.ico">
<html>
<head>
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PSAU</title>
<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }
    
    body {
        font-family: Arial, sans-serif;
        background-color: #f5f5f5;
    }
    
    .header-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 10px;
        background-color: #2E7D32;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    
    .header-image {
        width: 80px;
        height: auto;
        margin-bottom: 10px;
        border-radius: 50%;
    }
    
    .header-title {
        font-family: calibri;
        font-size: 18px;
        font-weight: bold;
        text-align: center;
        color: white;
    }
    
    .header-subtitle {
        font-family: calibri;
        font-size: 14px;
        font-weight: bold;
        text-align: center;
        color: #E8F5E8;
        margin-top: 5px;
    }
    
    .content-container {
        padding: 15px;
        max-width: 100%;
        margin: 0 auto;
    }
    
    .document-info {
        background-color: white;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .info-row {
        display: flex;
        margin-bottom: 10px;
        border-bottom: 1px solid #eee;
        padding-bottom: 8px;
    }
    
    .info-label {
        font-weight: bold;
        min-width: 80px;
        font-size: 14px;
        color: #333;
    }
    
    .info-value {
        font-size: 14px;
        color: #000;
        word-break: break-word;
    }
    
    .form-container {
        background-color: white;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-label {
        display: block;
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 5px;
        color: #333;
    }
    
    .form-input, .form-select, .form-textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #ddd;
        border-radius: 6px;
        font-size: 16px;
        background-color: #fff;
    }
    
    .form-input:focus, .form-select:focus, .form-textarea:focus {
        outline: none;
        border-color: #4CAF50;
    }
    
    .submit-btn {
        width: 100%;
        background-color: #4CAF50;
        color: white;
        border: none;
        padding: 15px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
        transition: background-color 0.3s;
    }
    
    .submit-btn:hover {
        background-color: #45a049;
    }
    
    .history-table {
        background-color: white;
        border-radius: 8px;
        overflow-x: auto;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        -webkit-overflow-scrolling: touch;
    }
    
    .history-table table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 12px;
        min-width: 600px;
        border: 2px solid #2E7D32;
    }
    
    .history-table th {
        background-color: #2E7D32;
        color: white;
        padding: 8px 5px;
        text-align: left;
        font-weight: bold;
        white-space: nowrap;
        border: 1px solid #2E7D32;
    }
    
    .history-table td {
        padding: 8px 5px;
        border: 1px solid #ddd;
        word-break: break-word;
        white-space: nowrap;
        background-color: white;
    }
    
    .history-table tr:last-child td {
        border-bottom: 1px solid #ddd;
    }
    
    .iframe-container {
        width: 100%;
        margin-top: 20px;
    }
    
    .iframe-container iframe {
        width: 100%;
        border: none;
        min-height: 400px;
    }
    
    /* Desktop styles */
    @media (min-width: 768px) {
        .header-container {
            flex-direction: row;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .header-image {
            width: 120px;
            margin-right: 20px;
            margin-bottom: 0;
        }
        
        .header-title {
            font-size: 28px;
            text-align: center;
        }
        
        .header-subtitle {
            font-size: 20px;
            text-align: center;
        }
        
        .content-container {
            padding: 20px;
            max-width: 1200px;
        }
        
        .document-info {
            padding: 20px;
        }
        
        .info-row {
            margin-bottom: 15px;
            padding-bottom: 10px;
        }
        
        .info-label {
            font-size: 16px;
            min-width: 100px;
        }
        
        .info-value {
            font-size: 16px;
        }
        
        .form-container {
            padding: 20px;
        }
        
        .form-label {
            font-size: 18px;
        }
        
        .form-input, .form-select, .form-textarea {
            font-size: 18px;
            padding: 15px;
        }
        
        .submit-btn {
            font-size: 18px;
            padding: 18px;
        }
        
        .history-table table {
            font-size: 14px;
        }
        
        .history-table th {
            padding: 12px 8px;
        }
        
        .history-table td {
            padding: 10px 8px;
        }
        
        .history-table-inner {
            font-size: 14px !important;
        }
        
        .history-table-inner .table-header {
            font-size: 14px !important;
            padding: 12px 8px !important;
        }
        
        .history-table-inner .table-data {
            font-size: 14px !important;
            padding: 10px 8px !important;
        }
        
        .iframe-container iframe {
            min-height: 650px;
        }
    }
    
    /* Large desktop styles */
    @media (min-width: 1024px) {
        .header-title {
            font-size: 28px;
        }
        
        .header-subtitle {
            font-size: 20px;
        }
        
        .form-label {
            font-size: 42px;
        }
        
        .form-input, .form-select, .form-textarea {
            font-size: 42px;
            padding: 20px;
            border-width: 4px;
        }
        
        .submit-btn {
            font-size: 42px;
            padding: 20px;
            border: 6px solid gold;
        }
        
        .info-label {
            font-size: 42px;
        }
        
        .info-value {
            font-size: 42px;
        }
        
        .history-table table {
            font-size: 22px;
        }
        
        .history-table th {
            font-size: 22px;
            padding: 15px 10px;
        }
        
        .history-table td {
            font-size: 22px;
            padding: 12px 10px;
        }
        
        .history-table-inner {
            font-size: 22px !important;
        }
        
        .history-table-inner .table-header {
            font-size: 22px !important;
            padding: 15px 10px !important;
        }
        
        .history-table-inner .table-data {
            font-size: 22px !important;
            padding: 12px 10px !important;
        }
    }
</style>

<div class="header-container">
    <img src="PSAU_10.jpg" alt="PAMPANGA STATE AGRICULTURAL UNIVERSITY" class="header-image">
    <div>
        <div class="header-title">PAMPANGA STATE AGRICULTURAL UNIVERSITY</div>
        <div class="header-subtitle">RECORDS UNIT - DOCUMENT TRACKING SYSTEM</div>
    </div>
</div>

<?php include "connect.php";
error_reporting(0);
//$servername = "";
//$username = "";
//$password = "";
//$dbname = "";
$datatable = "records_document_main"; // MySQL table name
$results_per_page = 27; // number of results per page
 
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$filtertext="";
?>

<form action='receiveadd.php' method='GET'>
<table border='0'  width='100%'>
<tr>
<td align='center'><input hidden id='filtertext' name='filtertext' type='text' text-align='center' placeholder='Document Title / Document Type' value='' size='48'>   <button hidden type='submit'>SEARCH</button></td>
</tr>
</form>

<?php
$filtertext=$_GET['filtertext'];
$filtertext=trim($filtertext);
if (isset($_GET["page"])) { $page = $_GET["page"]; } else { $page=1; };
$start_from = ($page-1) * $results_per_page;
$sql = "SELECT * FROM ".$datatable." where serial_code = '".$filtertext."' order by date_added desc LIMIT $start_from, ".$results_per_page;
$rs_result = $conn->query($sql);
?>

<?php
$sql = "SELECT COUNT(document_title) AS total FROM ".$datatable." where serial_code = '".$filtertext."' order by date_added desc";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_pages = ceil($row["total"] / $results_per_page); // calculate total pages with results
//echo "<br>";
//echo "<center>";
//for ($i=1; $i<=$total_pages; $i++) { // print links for all pages
//echo "<a href='viewgrid.php?filtertext=".$filtertext."&page=".$i."'";
//if ($i==$page) 
//echo " class='curPage'";
//echo ">".$i."</a> ";
//};

?>






<?php include 'connect.php';
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) 
{
  die("Connection failed: " . $conn->connect_error);
}
$ReferenceID=$filtertext;


$query = "SELECT * FROM records_document_main where serial_code='$ReferenceID'";
$result = mysqli_query($conn, $query);
$fontadjust = "42px";
$fontgrid = "42px";
echo"<input hidden id='qr' value='$ReferenceID'/><br>";

if ($result)
{
    $row = mysqli_num_rows($result);
       if ($row)
          {
            $count=mysqli_num_rows($result);

            echo "<table width='100%' border='0px solid green' align='left' style='font-size:12px','border-collapse:collapse'>";

            while($data=mysqli_fetch_row($result))
            {
                $selected_status=$data[3];
                if ($selected_status=="RELEASED") {$hideme="hidden";$showme="";}
                if ($selected_status=="FOR RELEASING") {$hideme="";$showme="<br>";}
            echo
            "
            <td>

            <tr>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left' width='10%'>SERIAL</td>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left;font-weight:bold; valign:top' width='auto' height='20'>: $data[6]<br></td>

            
            </tr>

            <tr>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left' width='10%'>REC#</td>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left;font-weight:bold; valign:top' width='auto' height='20'>: $data[0]<br></td>
            </tr>

            <tr>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left' width='10%'>TITLE</td>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left;font-weight:bold; valign:top' width='auto' height='20'>: $data[1]<br></td>
            </tr>
            </td>
          
            <tr>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left' width='10%'>TYPE</td>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left;font-weight:bold; valign:top' width='auto' height='20'>: $data[2]<br></td>
            </tr>
            </td>

            <tr>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left' width='10%'>STAUS</td>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left;font-weight:bold; valign:top' width='auto' height='20'>: $data[3]<br></td>
            </tr>
            </td>

            <tr>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left' width='10%'>DATE</td>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left;font-weight:bold; valign:top' width='auto' height='20'>: $data[4]<br></td>
            </tr>
            </td>

            <iframe hidden frameborder='0' align='left' id='qrcode' src='' width='250' height='250'></iframe>

            ";
            } 
            echo "</table>";      
          }
    mysqli_free_result($result);
}
mysqli_close($conn);
?>

<div class="content-container">
    <div class="document-info">
        <?php 
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) 
        {
          die("Connection failed: " . $conn->connect_error);
        }
        $ReferenceID=$filtertext;

        $query = "SELECT * FROM records_document_main where serial_code='$ReferenceID'";
        $result = mysqli_query($conn, $query);
        echo"<input hidden id='qr' value='$ReferenceID'/><br>";

        if ($result)
        {
            $row = mysqli_num_rows($result);
               if ($row)
                  {
                    $count=mysqli_num_rows($result);

                    while($data=mysqli_fetch_row($result))
                    {
                        $selected_status=$data[3];
                        if ($selected_status=="RELEASED") {$hideme="hidden";$showme="";}
                        if ($selected_status=="FOR RELEASING") {$hideme="";$showme="<br>";}
                    echo
                    "
                    <div class='info-row'>
                        <div class='info-label'>SERIAL</div>
                        <div class='info-value'>$data[6]</div>
                    </div>
                    <div class='info-row'>
                        <div class='info-label'>REC#</div>
                        <div class='info-value'>$data[0]</div>
                    </div>
                    <div class='info-row'>
                        <div class='info-label'>TITLE</div>
                        <div class='info-value'>$data[1]</div>
                    </div>
                    <div class='info-row'>
                        <div class='info-label'>TYPE</div>
                        <div class='info-value'>$data[2]</div>
                    </div>
                    <div class='info-row'>
                        <div class='info-label'>STATUS</div>
                        <div class='info-value'>$data[3]</div>
                    </div>
                    <div class='info-row'>
                        <div class='info-label'>DATE</div>
                        <div class='info-value'>$data[4]</div>
                    </div>
                    <iframe hidden frameborder='0' align='left' id='qrcode' src='' width='250' height='250'></iframe>
                    ";
                    }       
                  }
            mysqli_free_result($result);
        }
        mysqli_close($conn);
        ?>
    </div>

    <div class="form-container">
        <?php echo $showme; ?>
        <form action='receiveadd.php' method='POST'>
            <div class="form-group">
                <label <?php echo $hideme; ?> for="ename" class="form-label">Employee Name:</label>
                <?php include 'connect.php';
                $conn = new mysqli($servername, $username, $password, $dbname);
                if ($conn->connect_error) 
                {
                  die("Connection failed: " . $conn->connect_error);
                }
                $query = "SELECT * FROM employees order by fname";
                $result = mysqli_query($conn, $query);

                if ($result)
                {
                    $row = mysqli_num_rows($result);
                       if ($row)
                          {
                            $count=mysqli_num_rows($result);
                            echo "<select $hideme id='employeedeatils' name='employeedeatils' class='form-select'>";
                            while($data=mysqli_fetch_row($result))
                            {
                            echo "<option value='".str_replace("'","",$data[6])."-".$data[3]." ".$data[2]."'>" . strtoupper($data[3]) ." ". strtoupper($data[2]) . "</option>";   
                            }
                            echo "</select>";
                          mysqli_free_result($result);
                          }
                mysqli_close($conn);
                }
                echo "<input hidden type='text' id='scode' name='scode' value='$ReferenceID'></input>";
                ?>
            </div>

            <div class="form-group">
                <label <?php echo $hideme; ?> for="eid" class="form-label">Employee ID:</label>
                <input <?php echo $hideme; ?> required name="empid" id="empid" type="password" class="form-input">
            </div>

            <div class="form-group">
                <label <?php echo $hideme; ?> for="remrem" class="form-label">Remark:</label>
                <textarea <?php echo $hideme; ?> rows="3" name="remnote" id="remnote" class="form-textarea"></textarea>
            </div>

            <?php echo $showme; ?>
            <button type='submit' <?php echo $hideme; ?> class="submit-btn">
                CONFIRM DOCUMENT RECEIPT
            </button>
        </form>
    </div>
</div>

<div class="history-table">
<?php include 'connect.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) 
{
  die("Connection failed: " . $conn->connect_error);
}
$ReferenceID=$filtertext;
$query = "SELECT * FROM records_document_received where serial_code='$ReferenceID' order by date_received desc";
$result = mysqli_query($conn, $query);

if ($result)
{
    $row = mysqli_num_rows($result);
       if ($row)
          {
            $count=mysqli_num_rows($result);

            echo "<table class='history-table-inner' style='border-collapse: separate; border-spacing: 0; border: 2px solid #2E7D32; width: 100%; font-size: 10px;'>
            <tr>
                <th class='table-header' style='background-color: #2E7D32; color: white; border: 1px solid #2E7D32; padding: 6px 4px; font-size: 10px;'>OFFICE NAME</th>
                <th class='table-header' style='background-color: #2E7D32; color: white; border: 1px solid #2E7D32; padding: 6px 4px; font-size: 10px;'>RECEIVED BY</th>
                <th class='table-header' style='background-color: #2E7D32; color: white; border: 1px solid #2E7D32; padding: 6px 4px; font-size: 10px;'>DATE RECEIVED</th>
                <th class='table-header' style='background-color: #2E7D32; color: white; border: 1px solid #2E7D32; padding: 6px 4px; font-size: 10px;'>REMARKS</th>
            </tr>";

            while($data=mysqli_fetch_row($result))
            {
            echo "
                <tr>  
                    <td class='table-data' style='border: 1px solid #ddd; padding: 6px 4px; font-size: 10px;'>".$data[1]."</td>   
                    <td class='table-data' style='border: 1px solid #ddd; padding: 6px 4px; font-size: 10px;'>".$data[2]."</td>   
                    <td class='table-data' style='border: 1px solid #ddd; padding: 6px 4px; font-size: 10px;'>".$data[3]."</td>   
                    <td class='table-data' style='border: 1px solid #ddd; padding: 6px 4px; font-size: 10px;'>".$data[6]."</td> 
                </tr>";
            } 
            echo "</table>";      
          }
    mysqli_free_result($result);
}
mysqli_close($conn);
?>
</div>

<div class="iframe-container">
    <iframe frameborder='0' align='left' id='QQ' src=''></iframe>
</div>

<script>
var iframe=document.getElementById("QQ");
function loaduploadlist() {
iframe.src = "uploadlistview.php?filtertext2=<?php echo $ReferenceID; ?>";
//iframe.height="1600";
}
</script>

<script>
function UpdateQRCode(val){
    document.getElementById("qrcode").setAttribute("src","https://api.mimfa.net/qrcode?value="+encodeURIComponent(val)+"&as=value");
}
document.addEventListener("DOMContentLoaded", function(){
    UpdateQRCode(document.getElementById("qr").value);
});
</script>