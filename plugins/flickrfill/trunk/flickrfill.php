<?php
if (empty($post2date)) {
		$prevpost = $posts->ascend($post);
		if (empty($prevpost)) {
			$post1date = date('Y-m-d');
		} else {
			$post1date = $prevpost->pubdate->format('Y-m-d');
		}
	} else {
		$post1date = $post2date;
	}
$post2date = $post->pubdate->format('Y-m-d');

/*$theme->flickrfill( $post1date, $post2date );*/
?>