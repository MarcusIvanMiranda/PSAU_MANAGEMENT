
<?php include 'connect.php';
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) 
{
  die("Connection failed: " . $conn->connect_error);
}
//$ReferenceID=strtoupper($_POST['RefID']);
$query = "SELECT * FROM records_document_main where idrecords_document_main between '5' and '10'";
$result = mysqli_query($conn, $query);

if ($result)
{
    $row = mysqli_num_rows($result);
       if ($row)
          {
            $count=mysqli_num_rows($result);
            //echo "$row['serial_code']";
          }
    mysqli_free_result($result);
}
mysqli_close($conn);
?>


<input hidden id='qr' value='http://campus.psau.edu.ph/records/receivedocument.php?filtertext=ARTHURAGUSTIN'/>
<br>

<iframe frameborder='0' align='left' id='qrcode' src='' width='50%' height='250'></iframe>
<iframe frameborder='0' align='left' id='QQ' src='' width='50%' height='250'></iframe>



<script>
function UpdateQRCode(val){
    document.getElementById("qrcode").setAttribute("src","https://api.mimfa.net/qrcode?value="+encodeURIComponent(val)+"&as=value");
}
document.addEventListener("DOMContentLoaded", function(){
    UpdateQRCode(document.getElementById("qr").value);
});
</script>