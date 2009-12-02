<?php include 'header.php'; ?>

  <div id="primary" class="twocol-stories">
    <div class="inside">
    		<!-- currently number of posts in home.php is controlled by $home_recent_posts limited to 2 in theme.php -->
			<!-- we choose not to have pagination in this theme -->
			<?php $first = true; ?>
			<?php foreach ( $home_recent_posts as $post ) { ?>
	      <?php include 'entry.php'; ?>
      <?php $first = false; ?>
      <?php } ?>
    </div>
        
        
      <div class="clear"></div>
  </div>
  <!-- end primary -->

<?php include 'sidebar.php'; ?>

<?php include 'footer.php'; ?>
<!-- end home -->