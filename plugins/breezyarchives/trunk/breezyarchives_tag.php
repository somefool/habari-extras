<?php
	if ($posts) {
		echo '<ul class="breezy-tag-archive">';

		foreach ($posts as $post) {
			if ($show_comment_count) {
				printf('<li class="post"><a href="%1$s" rel="bookmark">%2$s</a> <span class="comment-count" title="%3$s">%4$d</span></li>',
						$post->permalink,
						$post->title_out,
						sprintf(_n('%1$d Comment', '%1$d Comments', $post->comments->approved->count, $this->class_name), $post->comments->approved->count),
						$post->comments->approved->count);
			} else {
				printf('<li class="post"><a href="%1$s" rel="bookmark">%2$s</a></li>',
						$post->permalink,
						$post->title_out);
			}
		}

		if ($current_page > 1 || $page_total > 1) {
			echo '<li class="pagination">';
			if ($current_page > 1) {
				printf('<a href="%1$s" class="pager-prev">%2$s</a>',
						$prev_page_link,
						$prev_page_text);
			}
			if ($page_total > $current_page) {
				printf('<a href="%1$s" class="pager-next">%2$s</a>',
						$next_page_link,
						$next_page_text);
			}
			echo '</li>';
		}

		echo '</ul>';
	}
?>