<h3>Recent Comments</h3>
<ul>
  <?php foreach($content->recent_comments as $comment): ?>
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