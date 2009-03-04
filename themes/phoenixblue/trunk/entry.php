<div id="post-<?php echo $post->id; ?>" class="<?php echo $post->statusname; ?>">
  <div class="post">

    <div class="post-info">
      <h2 class="posttitle">
        <a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>">
          <?php echo $post->title_out; ?>
        </a>
      </h2>
<p class="postmeta"> 
      Posted by <?php echo $post->author->username; ?> on <?php echo $post->pubdate_out; ?><br />
      <?php if ( is_array( $post->tags ) ) : ?>
        <?php echo  ' tagged ' . $post->tags_out; ?>
      <?php endif; ?>
      <?php if ( $loggedin ) : ?>
        
          <a href="<?php echo $post->editlink; ?>" title="Edit post"> (edit)</a>
        
      <?php endif; ?>
      
       <br /> <a href="<?php echo $post->permalink; ?>" title="Comments on this post"><?php echo $post->comments->approved->count; ?>
          <?php echo _n( 'Comment', 'Comments', $post->comments->approved->count ); ?>
        </a>
      </p>

    </div> <!-- post-info -->

    <div class="postentry">
      <?php
      if ( isset($first) && $first == false ) {
        echo $post->content_out;
      } else {
        echo $post->content_out;
      }
      ?>
    </div> <!-- .post-content -->
  </div> <!-- .post -->
</div> <!-- #post-id .status -->
