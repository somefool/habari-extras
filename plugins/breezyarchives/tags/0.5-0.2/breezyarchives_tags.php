<?php
	if (count($tags) > 0) {
		echo '<ul id="breezy-taxonomy-archive">';
		if ($show_tag_post_count) {
			foreach ($tags as $tag) {
				if ($tag->count > 0) {
					printf('<li class="tag"><a href="%1$s" rel="tag">%2$s</a> <span class="post-count" title="%3$s">%4$d</span></li>',
							URL::get('display_entries_by_tag', array('tag' => $tag->slug)),
							$tag->tag,
							sprintf(_n('%1$d Post', '%1$d Posts', $tag->count, $this->class_name), $tag->count),
							$tag->count);
				}
			}
		} else {
			foreach ($months as $month => $count) {
				if ($tag->count > 0) {
					printf('<li class="tag"><a href="%1$s" rel="tag">%2$s</a></li>',
							URL::get('display_entries_by_tag', array('tag' => $tag->slug)),
							$tag->tag);
				}
			}
		}
		echo '</ul>';
	}
?>