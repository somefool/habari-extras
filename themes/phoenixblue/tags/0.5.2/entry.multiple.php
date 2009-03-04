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
    Page: <?php echo Utils::page_selector( $page, Utils::archive_pages( $posts->count_all() ) ); ?>
  </div>
</div> <!-- #content -->

<?php include 'sidebar.php'; ?>
<!-- /entry.multiple -->
<?php include 'footer.php'; ?>
