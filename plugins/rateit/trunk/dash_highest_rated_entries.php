<ul class="items">
<?php @reset($highest_rated_posts); while(list(, $post) = @each($highest_rated_posts)): ?>
  <li class="item clear">
    <span class="date pct15 minor"><a href="<?php echo URL::get('display_entries_by_date', array('year' => date('Y', strtotime($post->pubdate)), 'month' => date('m', strtotime($post->pubdate)), 'day' => date('d', strtotime($post->pubdate)))); ?>" title="<?php printf(_t('Posted at %1$s'), date('g:m a \o\n F jS, Y', strtotime($post->pubdate))); ?>"><?php echo date('M j', strtotime($post->pubdate)); ?></a></span>
    <span class="title pct75"><a href="<?php echo $post->permalink; ?>"><?php echo $post->title; ?></a> <a class="minor" href="<?php Site::out_url('habari'); ?>/admin/user/<?php echo $post->author->username; ?>"> <?php _e('by'); ?> <?php echo $post->author->displayname; ?></a></span>
    <span class="comments pct10"><?php echo $post->info->rateit_rating; ?></span>
  </li>
<?php endwhile; ?>
</ul>
