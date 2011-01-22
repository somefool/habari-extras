<?php
class PrePublish extends Plugin
{
	public function action_publish_post( $post ) {
		if ( Post::status_name( $post->status ) == 'scheduled' ) {
			$post->status = Post::status( 'published' );
		}
	}
}
?>
