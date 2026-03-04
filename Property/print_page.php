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

<form action='viewgrid.php' method='GET'>
<table border='0'  width='100%'>
<tr>
<td align='center'><br><input id='filtertext' name='filtertext' type='text' text-align='center' placeholder='Document Title / Document Type' value='' size='48'>   <button type='submit'>SEARCH</button></td>
</tr>
</form>

<?php
$filtertext=$_GET['filtertext'];
$filtertext=trim($filtertext);
$delivered='FOR RELEASING';
if (isset($_GET["page"])) { $page = $_GET["page"]; } else { $page=1; };
$start_from = ($page-1) * $results_per_page;
$sql = "SELECT * FROM ".$datatable." where (document_title like '%".$filtertext."%' or document_type like '%".$filtertext."%') and document_status='$delivered' order by date_added desc LIMIT $start_from, ".$results_per_page;
$rs_result = $conn->query($sql);
?>

<?php
$sql = "SELECT COUNT(document_title) AS total FROM ".$datatable." where (document_title like '%".$filtertext."%' or document_type like '%".$filtertext."%') and document_status='$delivered' order by date_added desc";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_pages = ceil($row["total"] / $results_per_page); // calculate total pages with results
//echo "<br>";
echo "<center>";
for ($i=1; $i<=$total_pages; $i++) { // print links for all pages
echo "<a href='viewgrid.php?filtertext=".$filtertext."&page=".$i."&".$delivered."=FOR DELIVERY'";
if ($i==$page) 
echo " class='curPage'";
echo ">".$i."</a> ";
};
?>


<table style="border-collapse:collapse" border="1px solid green"  width="100%" >
<tr style="font-family:tahoma;font-size:12px">
<td bgcolor="lightgrey" style="color:black;text-align:center">DETAILS</strong></td>
<!--
<td bgcolor="lightgrey" style="color:black;text-align:center">PRINT QR</strong></td>
-->
<td bgcolor="lightgrey" style="color:black">REC#</strong></td>
<td bgcolor="lightgrey" style="color:black">DOCUMENT TITLE</strong></td>
<td bgcolor="lightgrey" style="color:black">DOCUMENT TYPE</strong></td>
<td bgcolor="lightgrey" style="color:black">STATUS</strong></td>
<td bgcolor="lightgrey" style="color:black">SERIAL CODE</strong></td>
<td bgcolor="lightgrey" style="color:black">DATE/TIME ACCEPTED</strong></td>
<td bgcolor="lightgrey" style="color:black;text-align:center">RECEIVED FROM</strong></td>
<td bgcolor="lightgrey" style="color:black;text-align:center">DELIVERED TO</strong></td>
<td bgcolor="lightgrey" style="color:black">REMARK</strong></td>
</tr>

<?php
while($row = $rs_result->fetch_assoc()) {
?>
<form align='center' action='details.php' method='POST'>
<tr style="font-family:tahoma;font-size:12px">
<td width="5%" align='center'><?php echo "<button style='background-color: green;color: white;border: 0px solid #e4e4e4;padding: 3px;border-radius: 3px;cursor: pointer; font-size: 11px' height='10' name='RefID' value=".$row["serial_code"].">    TRACK    </button>"; ?></td>

<td width="auto"><?php echo $row["idrecords_document_main"]; ?></td>
<td width="auto"><?php echo $row["document_title"]; ?></td>
<td width="auto"><?php echo $row["document_type"]; ?></td>
<td width="auto"><?php echo $row["document_status"]; ?></td>
<td width="auto"><?php echo $row["serial_code"]; ?></td>
<td width="auto"><?php echo $row["date_added"]; ?></td>
<td width="auto" style="text-align:center"><?php echo $row["received_from"]; echo " - "; echo $row["employee_receipt"]; ?></td>
<td width="auto" style="text-align:center"><?php echo $row["delivered_to"]; ?></td>
<td width="auto"><?php echo $row["document_remarks"]; ?></td>
</tr>
</form>
<?php
};
?>

</table>