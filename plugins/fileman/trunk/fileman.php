<?php
 // Display Habari header
 $theme->display('header');
 // Set file to work with
 if(!isset($_GET['file'])){
  // If no file selected, set default theme's
  // home page as edit file
  $file=Site::get_dir("theme")."/home.php";
 }else{
  // Otherwise set the chosen file
  $file=$_GET['file'];
 }
 // Get Javascripts
 include('fileman.js.php');
 // Get CSS
 include('fileman.css.php');
?>
<div class="container">
 <h2>File Manager</h2>
 <table class="fileman">
  <tr>
   <td class="editor">
    <div class="fileman">
     <form class="fileman">
      <p style="margin-bottom:7px;">Currently editing: <?php echo $file; ?></p>
      <p class="status">Status: <span>File not saved.</span></p>
      <textarea name="contents"><?php echo htmlspecialchars(file_get_contents($file)); ?></textarea>
      <p><input type="submit" name="submit" value="Save Changes" /><?php if(!is_writable($file)){echo " &nbsp; <span style=\"color:#B00;font-weight:bold;\">File not writable!</span>";} ?></p>
      <input type="hidden" name="file" class="file" value="<?php echo $file; ?>" />
     </form>
    </div>
   </td>
   <td class="browser">
    <div class="fileman">
     <div id="jQueryFileTree"></div>
    </div>
   </td>
  </tr>
 </table>
 <p style="text-align:center;color:#555;font-size:10px;">FileMan Plugin for <a href="http://habariproject.org">Habari</a> made by <a href="http://mattsd.com">Matt-SD</a></p>
</div>
<?php $theme->display('footer'); //Get Habari footer ?>
