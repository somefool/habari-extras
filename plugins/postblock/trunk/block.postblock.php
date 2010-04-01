<?php foreach((array)$content->posts as $post): ?>
<?php $theme->content($post, $content->context); ?>
<?php endforeach; ?>