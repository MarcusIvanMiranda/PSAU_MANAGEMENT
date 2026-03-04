<!DOCTYPE html>
<html lang="en">

<head>
  <title>PSAU</title>
  <style rel="stylesheet" type="text/css">
    .qr-container {
      width: 50mm;
      height: 50mm;
    }
    @media print {
      .qr-container {
        width: 50mm;
        height: 50mm;
      }
    }
    
    /* Scrollbar Styles */
    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    ::-webkit-scrollbar-thumb {
        background: #4a9d6a;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #3d7d54;
    }
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
            <center>
            <iframe frameborder='0' id='qrcode' src='' class='qr-container'></iframe>
            <br>
            <label style='font-size:12px'>$data[0]</label>
            </center>
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