<form id="uform" name="uform" method="post" enctype="multipart/form-data">
<label for="lfile">File</label>
<input id="efile" name="efile" type="file" />
<button>Upload</button>
</label>
</form>


<?php
$target_dir = "./uploads/";

$target_file = $target_dir . basename($_FILES["efile"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

if ($uploadOk == 0) {
} else {
  if (move_uploaded_file($_FILES["efile"]["tmp_name"], $target_dir."B.".$imageFileType)) {
    //echo "The file ". basename( $_FILES["applicationformUpload"]["name"]). " has been uploaded.";
  $applicationformuploadecho="The file ". basename( $_FILES["efile"]["name"]). " has been uploaded.";
  } else {
    //echo "Sorry, there was an error uploading your file.";
  }
}
?>