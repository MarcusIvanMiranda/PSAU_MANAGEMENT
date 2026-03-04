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
  <img src="PSAU_10.jpg" alt="" style="width:auto;">
</picture>
</td>
<td valign="top">
    
<label style="font-family:calibri;font-size:28px;" d >PAMPANGA STATE AGRICULTURAL UNIVERSITY</label>
<hr style='border-top: 2px solid black'>
<label style="font-family:calibri;font-size:24px;" >PROPERTY MANAGEMENT SYSTEM</label>
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
$sql = "SELECT * FROM ".$datatable." where property_tag = '".$filtertext."' order by property_tag asc LIMIT $start_from, ".$results_per_page;
$rs_result = $conn->query($sql);
?>

<?php
$sql = "SELECT COUNT(property_tag) AS total FROM ".$datatable." where property_tag = '".$filtertext."' order by property_tag asc";
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


$query = "SELECT * FROM property_list where property_tag='$ReferenceID'";
$result = mysqli_query($conn, $query);
$fontadjust = "28px";
$fontgrid = "42px";
echo"<input hidden id='qr' value='$ReferenceID'/><br>";

if ($result)
{
    $row = mysqli_num_rows($result);
       if ($row)
          {
            $count=mysqli_num_rows($result);

            echo "<table width='100%' border='0px solid green' align='left' style='font-size:10px','border-collapse:collapse','font-weight:bold'>";

            while($data=mysqli_fetch_row($result))
            {
                $selected_status=$data[3];
                if ($selected_status=="RELEASED") {$hideme="hidden";$showme="";}
                if ($selected_status=="FOR RELEASING") {$hideme="";$showme="<br>";}
            echo
            "
            <td>

            <tr>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left' width='20%'>NUMBER</td>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left; valign:top' width='auto' height='20'>: $data[1]<br></td>

            
            </tr>

            <tr>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left' width='10%'>PROPERTY TAG</td>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left; valign:top' width='auto' height='20'>: $data[2]<br></td>
            </tr>

            <tr>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left' width='10%'>ITEM</td>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left; valign:top' width='auto' height='20'>: $data[3]<br></td>
            </tr>
            </td>
          
            <tr>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left' width='10%'>DESCRIPTION/MODEL NUMBER</td>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left; valign:top' width='auto' height='20'>: $data[4]<br></td>
            </tr>
            </td>

            <tr>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left' width='10%'>SERIAL NUMBER</td>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left; valign:top' width='auto' height='20'>: $data[5]<br></td>
            </tr>
            </td>

            <tr>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left' width='10%'>VALUE</td>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left; valign:top' width='auto' height='20'>: $data[6]<br></td>
            </tr>
            </td>

            <tr>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left' width='10%'>ACQUISITION DATE</td>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left; valign:top' width='auto' height='20'>: $data[7]<br></td>
            </tr>
            </td>

            <tr>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left' width='10%'>ACCOUNTABLE PERSON</td>
            <td style='font-size:$fontadjust;background-color:white;color:black;font-family:tahoma;text-align:left; valign:top' width='auto' height='20'>: $data[8]<br></td>
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






<form action='receiveadd.php' method='POST'>
    <label> </label>
    <br>
 
<table>

<tr>
<td>
<?php echo $showme; ?>
<label <?php echo $hideme; ?> for="ename" style="font-size:42px">...</label>
</td>
</tr>

<tr>
<td>



