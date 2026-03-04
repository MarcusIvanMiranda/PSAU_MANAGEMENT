<?php include 'connect.php';
ini_set('display_errors','Off');
ini_set('error_reporting', E_ALL );
define('WP_DEBUG', false);
define('WP_DEBUG_DISPLAY', false);
$serial_code=strtoupper($_POST['serialno']);
$target_dir = "./uploads/";
$target_file = $target_dir . basename($_FILES["efile"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
date_default_timezone_set("Asia/Manila");
$timehour=date("H");
$timeminute=date("i");
$timesecond=date("s");
$timecode=$timehour.$timeminute.$timesecond;

if ($uploadOk == 0) {
} else {
  if (move_uploaded_file($_FILES["efile"]["tmp_name"], $target_dir.$serial_code."-".$timecode.".".$imageFileType)) {
$applicationformuploadecho="The file ". basename( $_FILES["efile"]["name"]). " has been uploaded.";
$originalfilename=$_FILES["efile"]["name"];
$newfilename=$serial_code."-".$timecode.".".$imageFileType;

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: Registration unsuccessful" . $conn->connect_error);
}
$sql = "INSERT INTO records_files_upload (serial_code, original_filename, new_filename)
 VALUES ('$serial_code', '$originalfilename', '$newfilename')";
if ($conn->query($sql) === TRUE) {
//echo "<p style=font-family:verdana;font-size:12px;color:darkblue;align=center><br><br><br><br><br><br>$doc_title was added.<br><br></p>";
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}
$conn->close();

  } else {

  }
  echo "<center><br><br><br><br>";
  echo $applicationformuploadecho;
  echo "</center>";
}
?>

<form action="details.php" method="post">
  <center>
  <input hidden id="RefID" name="RefID" type="text" value="<?php echo  $serial_code; ?>">
  <input hidden type="submit"></input>
  <br>
  <button id="btn1" onClick="submit" style="background-color: gold;color: black;border: 1px solid green;padding: 4px;border-radius: 0px;cursor: pointer;height:25px; font-weight: bold">   CONFIRM   </button>
  </center>
</form>