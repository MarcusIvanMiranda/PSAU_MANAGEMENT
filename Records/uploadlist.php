<?php
$filtertext=$_GET['filtertext2'];
?>

<form action='uploadlist.php' method='GET'>
<table border='0'  width='100%'>
<tr>
<td align='center'><input hidden id='filtertext2' name='filtertext2' type='text' text-align='center' placeholder='Document Title / Document Type' value='' size='48'>   <button hidden type='submit'>SEARCH</button></td>
</tr>
</form>


<?php include 'connect.php';
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) 
{
  die("Connection failed: " . $conn->connect_error);
}
$ReferenceID=$filtertext;
$query = "SELECT * FROM records_files_upload where serial_code='$filtertext' order by date_time_uploaded desc";
$result = mysqli_query($conn, $query);

if ($result)
{
    $row = mysqli_num_rows($result);
       if ($row)
          {
            $count=mysqli_num_rows($result);

            echo
            "
            <table width='100%' border='0px solid green' align='left' style='font-size:12px','border-collapse:collapse'>
            <tr>
            <th style='border:1px solid black; text-align:left;font-family:tahoma;background-color:lightgrey' border-collapse='collapse'>ACTION</th>
                <th style='border:1px solid black; text-align:left;font-family:tahoma;background-color:lightgrey' border-collapse='collapse'>FILE NAME</th>
                <th style='border:1px solid black; text-align:left;font-family:tahoma;background-color:lightgrey' border-collapse='collapse'>UPLOAD DATE</th>
            </tr>
            ";

            while($data=mysqli_fetch_row($result))
            {
            echo
            "
                <tr>
                    <td style='border:0px solid black;vertical-align:top;font-size:12px;background-color:white;color:black;font-family:tahoma;text-align:left'> <form target='_parent' method='post' action='./uploads/".$data[3]."'><button>OPEN</button></form></td>  
                    <td style='border:0px solid black;vertical-align:top;font-size:12px;background-color:white;color:black;font-family:tahoma;text-align:left'>".$data[2]."</td>
                    <td style='border:0px solid black;vertical-align:top;font-size:12px;background-color:white;color:black;font-family:tahoma;text-align:left'>".$data[4]."</td>     
                </tr>
            ";
            } 
            echo "</table>";      
          }
    mysqli_free_result($result);
}
mysqli_close($conn);
?>