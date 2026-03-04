<!DOCTYPE html>
<link rel="icon" href="PSAU.ico">
<html>
<head>
 <title>PSAU</title>
 <!--
 <p style="background-image: url('Head.jpg');background-repeat: no-repeat">        
 <script src='https://code.jquery.com/jquery-2.1.3.min.js'></script>
-->



<div>
<table>
<tr>
<td>
<picture>
  <img src="PSAU_10.jpg" alt="PAMPANGA STATE AGRICULTURAL UNIVERSITY" style="width:auto;">
</picture>
</td>
<td valign="top">
    
<label style="font-family:calibri;font-size:28px;font-weight:bold;" d >PAMPANGA STATE AGRICULTURAL UNIVERSITY</label>
<hr style='border-top: 2px solid black'>
<label style="font-family:calibri;font-size:20px;font-weight:bold;" >RECORDS UNIT - DOCUMENT TRACKING SYSTEM</label>
</td>

</tr>

<body onload=loaduploadlist()></body>
</head>
</html>

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



<style>
    th {
        font-size:22px;background-color:green;color:white;font-family:tahoma;text-align:left;font-weight:bold; valign:top;
    }
    td {
        font-size:22px;background-color:white;color:black;font-family:tahoma;text-align:left;font-weight:bold; valign:top;
    }
</style>


<form action='receiveadd.php' method='POST'>
    <label> </label>
    <br>
 
<table>

<tr>
<td>
<?php echo $showme; ?>
<label <?php echo $hideme; ?> for="ename" style="font-size:42px">Employee Name:</label>
</td>
</tr>

<tr>
<td>

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
            echo "<select $hideme id='employeedeatils' name='employeedeatils' style='font-size:42px;border-width:4px'>";
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

</td>
</tr>

<tr>
<td>
<?php echo $showme; ?>
<label <?php echo $hideme; ?> for="eid"  style="font-size:42px">Employee ID:</label>
</td>
</tr>
<tr>
<td>
    <input <?php echo $hideme; ?> required name="empid" id="empid" type="password" style="font-size: 42px;border-width:4px;size:10"></input>
</td>
</tr>

<?php echo $showme; ?>


<tr>
<td>
<?php echo $showme; ?>
<label <?php echo $hideme; ?> for="remrem"  style="font-size:42px">Remark:</label>
</td>
</tr>
<tr>
<td>
    <textarea <?php echo $hideme; ?> rows="3" cols="31" name="remnote" id="remnote" style="font-size: 42px;border-width:4px;size:250"></textarea>
</td>
</tr>

<?php echo $showme; ?>




<tr>
<td>
<label> </label>
<?php echo $showme; ?>
<form action='receiveadd.php' method='post'>
<button type='submit' <?php echo $hideme; ?> style='background-color: green;color: white;border: 6px solid gold;padding: 14px;border-radius: 10px;cursor: pointer; font-size: 42px'>
    CONFIRM <br> DOCUMENT <br> RECEIPT
</button>
</form>
</td>
</tr>

</table>
<?php echo $showme; ?>
<label> </label>
</form>


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

            echo
            "
            <table width='100%'>
            <tr>
                <th style='border:1px solid black' border-collapse='collapse'>OFFICE NAME</th>
                <th style='border:1px solid black' border-collapse='collapse'>RECEIVED BY</th>
                <th style='border:1px solid black' border-collapse='collapse'>DATE RECEIVED</th>
                <th style='border:1px solid black' border-collapse='collapse'>REMARKS</th>
            </tr>
            ";

            while($data=mysqli_fetch_row($result))
            {
            echo
            "
                <tr>  
                    <td style='border:1px solid black'>".$data[1]."</td>   
                    <td style='border:1px solid black'>".$data[2]."</td>   
                    <td style='border:1px solid black;width:14%'>".$data[3]."</td>   
                    <td style='border:1px solid black'>$data[6]</td> 
                </tr>
            ";
            } 
            echo "</table>";      
          }
    mysqli_free_result($result);
}
mysqli_close($conn);
?>


<iframe frameborder='0' align='left' id='QQ' src='' width='100%' height='650'></iframe>

<script>
var iframe=document.getElementById("QQ");
function loaduploadlist() {
iframe.src = "uploadlistview.php?filtertext2=<?php echo $ReferenceID; ?>";
//iframe.scrolling = "no";
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