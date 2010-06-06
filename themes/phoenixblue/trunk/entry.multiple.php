<?php include 'header.php'; ?>
<!-- entry.multiple -->
<div id="content">
  <?php
  $first= true;
  foreach ( $posts as $post ) {
    include 'entry.php';
    $first= false;
  }
  ?>
  <div class="navigation">
    Page: <?php $theme->page_selector(); ?>
  </div>
</div> <!-- #content -->

<?php include 'sidebar.php'; ?>
<!-- /entry.multiple -->
<?php include 'footer.php'; ?>
