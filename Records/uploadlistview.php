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
            <table width='100%' border='0px solid green' align='left' style='border-spacing:1px;padding:1px;font-size:42px','border-collapse:collapse'>
            <tr>
                <th style='border:1px solid black; text-align:left;font-family:tahoma;background-color:lightgrey' border-collapse='collapse'>LINK<br> </th>
                <th style='border:1px solid black; text-align:left;font-family:tahoma;background-color:lightgrey' border-collapse='collapse'>FILE NAME<br> </th>
                <th style='border:1px solid black; text-align:left;font-family:tahoma;background-color:lightgrey' border-collapse='collapse'>UPLOAD DATE<br> </th>
            </tr>
            ";

            while($data=mysqli_fetch_row($result))
            {
            echo
            "
                <tr>
                    <td style='vertical-align:top;border-spacing:1px;padding:1px;font-size:42px;background-color:white;color:black;font-family:tahoma;text-align:left'> <form target='_parent' method='post' action='./uploads/".$data[3]."'><button style='font-size:42'>OPEN</button></form></td>  
                    <td style='vertical-align:top;border-spacing:1px;padding:1px;font-size:42px;background-color:white;color:black;font-family:tahoma;text-align:left'>".$data[2]."</td>
                    <td style='vertical-align:top;border-spacing:1px;padding:1px;font-size:42px;background-color:white;color:black;font-family:tahoma;text-align:left'>".$data[4]."</td>     
                </tr>
            ";
            } 
            echo "</table>";      
          }
    mysqli_free_result($result);
}
mysqli_close($conn);
?>