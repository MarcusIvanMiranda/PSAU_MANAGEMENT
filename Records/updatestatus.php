<!DOCTYPE html>

<?php include "connect.php";
$doc_status=strtoupper($_POST['doc_status']);
$doc_serial=strtoupper($_POST['RefID2']);

if($doc_status=='FOR RELEASING'){$viewpage='viewdelivered.php';}
if($doc_status=='RELEASED'){$viewpage='viewgrid.php';}

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: Registration unsuccessful" . $conn->connect_error);
}
$sql = "update records_document_main set document_status='$doc_status' where serial_code='$doc_serial'";
if ($conn->query($sql) === TRUE) {
//echo "<p style=font-family:verdana;font-size:12px;color:darkblue;align=center><br><br><br><br><br><br>$doc_title was added.<br><br></p>";
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}
$conn->close();

?>

<html>
  <body onload="submit">
    <form id="formIT" method="POST" action="<?php echo $viewpage ?>">
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
  