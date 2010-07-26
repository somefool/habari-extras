<?php
 //Check if file is writable
 if(!is_writable($_POST['file'])){die("<span style=\"color:#B00;font-weight:bold;\">File not writable.</span>");}
 $contents = $_POST['contents'];
 //Write to file
 file_put_contents($_POST['file'],$contents) or die("<span style=\"color:#B00;font-weight:bold;\">Failed to write to file.</span>");
 //Send success signal
 echo "<span style=\"color:#0B0;font-weight:bold;\">File saved Successfully.</span>";
?>
