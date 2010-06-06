<?php include 'header.php'; ?>



			<div class="post">

			 <?php
  $first= true;
  foreach ( $posts as $post ) {
    include 'entry.php';
    $first= false;
  }
  ?>

			</div>


		<div class="footnav">
			Page: <?php $theme->page_selector(); ?>
		</div>


<?php include 'sidebar.php'; ?>

<?php include 'footer.php'; ?>
