<!DOCTYPE html>
<html lang="en">

<head>
  <title>PSAU</title>
  <style rel="stylesheet" type="text/css">

</style>
</head>
<body onload=window.print()>

<form name="formid1" action="apply.php" method="post" enctype="multipart/form-data" >
</form>




<?php include 'connect.php';



$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) 
{
  die("Connection failed: " . $conn->connect_error);
}
$ReferenceID=strtoupper($_POST['RefID']);
$serial_code=$ReferenceID;
$query = "SELECT * FROM records_document_main where serial_code='$ReferenceID'";
$result = mysqli_query($conn, $query);

echo"<input hidden id='qr' value='http://campus.psau.edu.ph/records/receivedocument.php?filtertext=$ReferenceID'/>";

if ($result)
{
    $row = mysqli_num_rows($result);
       if ($row)
          {
            $count=mysqli_num_rows($result);
            while($data=mysqli_fetch_row($result))
            {
            ?>
            
            <?php
            echo
            "
            <table>
            <td>
            <center><b><u>
            <label style='font-size:18px';width:'auto'>PSAU<br></label>
            </u>
            <label style='font-size:12px';width:'auto'>Document<br></label>
            <label style='font-size:12px';width:'auto'>Control#:<br></label>
            <label style='font-size:12px';width:'auto'>$data[0]</label>
            </td>
            <td>
            <iframe frameborder='0' align='left' id='qrcode' src='' width='80' height='90'></iframe>
            </td>
            </table>
            ";
            } 
          }
    mysqli_free_result($result);
}
mysqli_close($conn);
?>




<!--
<form>
<input type="button" value="Print this page" onClick="window.print()">
</form>
-->






<script>
function UpdateQRCode(val){
    document.getElementById("qrcode").setAttribute("src","https://api.mimfa.net/qrcode?value="+encodeURIComponent(val)+"&as=value");
}
document.addEventListener("DOMContentLoaded", function(){
    UpdateQRCode(document.getElementById("qr").value);
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