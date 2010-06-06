<?php include ('header.php'); ?>
<!-- page.single -->
<div id="content">
  <div id="post-<?php echo $post->id; ?>" class="page">
    <div class="post">
      <div class="post-info">
        <h2 class="posttitle">
          <a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>">
            <?php echo $post->title_out; ?>
          </a>
        </h2>

        
       
        <?php if ( $loggedin ) : ?>
          <span class="entry-edit">
	    <a href="<?php echo $post->editlink; ?>" title="Edit post"> (edit)</a>
          </span>
        <?php endif; ?><br/>

      </div> <!-- post-info -->

      <div class="postentry">
        <?php if (Plugins::is_loaded('monthlyarchives')) $theme->monthly_archives(); ?><br/>
        <h1>Tags</h1>
		<div id="tags"><?php if (Plugins::is_loaded('tagcloud')) $theme->tag_cloud(); ?></div>

      </div> <!-- .post-content -->

    </div> <!-- .post -->
  </div> <!-- #post-id .post -->

</div> <!-- #content -->

<?php include('sidebar.php'); ?>
<!-- /page.single -->
<?php include('footer.php'); ?>
