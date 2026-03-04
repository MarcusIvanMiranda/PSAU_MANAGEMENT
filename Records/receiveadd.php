<!DOCTYPE html>

<?php include "connect.php";
$doc_receipt=strtoupper($_POST['employeedeatils']);
echo $doc_office=str_replace("'","",$doc_office);
$doc_office=substr($doc_receipt,0,strrpos($doc_receipt,"-"));
$doc_employee=substr($doc_receipt,strrpos($doc_receipt,"-")+1,250);
$doc_received=strtoupper($_POST['empid']);
$doc_serial=strtoupper($_POST['scode']);
$doc_remark=strtoupper($_POST['remnote']);
$doc_datecode = "".date("Ymd");

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: Registration unsuccessful" . $conn->connect_error);
}
$sql = "INSERT INTO records_document_received (office_name, received_by, serial_code, received_remark)
 VALUES ('$doc_office', '$doc_employee', '$doc_serial', '$doc_remark')";
if ($conn->query($sql) === TRUE) {

echo "<br>$doc_office";
echo "<br>$doc_received";
echo "<br>$doc_serial";
echo "<br>$doc_remark";
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}
$conn->close();
?>

<?php include "connect.php";
//$doc_office=strtoupper($_POST['employeedeatils']);
//$doc_received=strtoupper($_POST['empid']);
//$doc_serial=strtoupper($_POST['scode']);
//$doc_remark=strtoupper($_POST['remnote']);
//$doc_datecode = "".date("Ymd");
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: Registration unsuccessful" . $conn->connect_error);
}
$sql = "update records_document_main set delivered_to='$doc_office - $doc_employee', document_remarks='$doc_remark' where serial_code='$doc_serial'";
if ($conn->query($sql) === TRUE) {

} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}
$conn->close();
?>


<html>
  <body onload="submit">
    <form id="formIT" method="POST" action="http://campus.psau.edu.ph/records/receivedocument.php?filtertext=<?php echo $doc_serial;?>">
    </form>
</body>
  </html>

  <script>
  window.onload = function(){
  document.forms['formIT'].submit();
}
</script>

  <!--
<script>
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
</script>
  -->
  