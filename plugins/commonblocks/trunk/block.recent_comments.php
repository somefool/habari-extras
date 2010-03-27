<?php 
    if ( ! $limit = $content->quantity ) { $limit = 5; };

    $recent_comments = Comments::get( array('limit'=>$content->quantity, 'status'=>Comment::STATUS_APPROVED, 
                                   'type'=>Comment::COMMENT, 'orderby'=>'date DESC' ) );
?>
<h3>Recent Comments</h3>
<ul>
  <?php foreach($recent_comments as $comment): ?>
    <li>
      <a href="<?php echo $comment->url ?>">
        <?php echo $comment->name; ?>
      </a> on
      <a href="<?php echo $comment->post->permalink; ?>">
        <?php echo $comment->post->title; ?>
      </a>
    </li>
  <?php endforeach; ?>
</ul>