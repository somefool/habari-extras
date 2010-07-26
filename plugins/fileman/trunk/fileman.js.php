  <script src="<?php Site::out_url('habari'); ?>/user/plugins/fileman/jqueryFileTree/jqueryFileTree.js" type="text/javascript"></script>
  <link rel="stylesheet" href="<?php Site::out_url('habari'); ?>/user/plugins/fileman/jqueryFileTree/jqueryFileTree.css" type="text/css" />
  <script type="text/javascript">
   $(document).ready( function() {
    $('#jQueryFileTree').fileTree({
     root: '<?php site::out_dir("user"); ?>/',
     script: '<?php Site::out_url("habari"); ?>/user/plugins/fileman/jqueryFileTree/jqueryFileTree.php',
     expandSpeed: 1000,
     collapseSpeed: 1000,
     multiFolder: true,
     loadMessage: 'Loading...'
     }, function(file) {
      window.location="?file="+file;
    });
    $('form.fileman').submit(function(){
     $.post(
      '<?php Site::out_url("habari"); ?>/user/plugins/fileman/save.php',
      'file='+$('form.fileman input.file').val()+'&contents='+$('form.fileman textarea').val(),
      function(data) {
       $('form.fileman p.status span').html(data);
      }
     )
     return false;
    });
   });
  </script>
