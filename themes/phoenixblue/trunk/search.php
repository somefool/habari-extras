<?php include 'header.php'; ?>
<!-- search -->
<div id="content">
  <?php if ( $posts ): ?>
    <h3>Search Results for '<?php echo htmlspecialchars( $criteria ); ?>'</h3>
    <div class="post-info">Did you find what you wanted?</div>
    <?php foreach ( $posts as $post ): ?>
      <div id="post-<?php echo $post->id; ?>" class="<?php echo $post->statusname; ?>">
        <div class="post">
          <p class="post-date"><?php echo $post->pubdate_out; ?></p>
          <div class="post-info">
            <h2 class="post-title">
              <a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>">
                <?php echo $post->title_out; ?>
              </a>
            </h2>

            Posted by <?php echo $post->author->username; ?>
            <?php if ( is_array( $post->tags ) ) : ?>
              <span class="entry-tags"><?php echo  ' tagged ' . $post->tags_out; ?></span>
            <?php endif; ?>
            <?php if ( $loggedin ) : ?>
              <span class="entry-edit">
	 	<a href="<?php echo $post->editlink; ?>" title="Edit post"> (edit)</a>
              </span>
            <?php endif; ?><br/>
              <?php if ( $post->content_type == Post::type('entry') ): ?>

                <span class="commentslink">
                  <a href="<?php echo $post->permalink; ?>" title="Comments on this post"><?php echo $post->comments->approved->count; ?>
                    <?php echo _n( 'Comment', 'Comments', $post->comments->approved->count ); ?>
                  </a>
                </span>
              <?php endif; ?>

          </div> <!-- post-info -->

          <div class="postentry">
            <?php
              echo $post->content_excerpt;
            ?>
          </div> <!-- .post-content -->
        </div> <!-- .post -->
      </div> <!-- #post-id .status -->
    <?php endforeach; ?>
    <div class="navigation">
      Page: <?php $theme->page_selector(); ?>
    </div>
  <?php else: ?>
    <h2 class="center">Not Found</h2>
    <p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
  <?php endif; ?>
</div> <!-- #content -->

<?php include 'sidebar.php'; ?>
<!-- /entry.single -->
<?php include 'footer.php'; ?>
