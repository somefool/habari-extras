<?php
class PrePublish extends Plugin
{
	public function info() {
		return array(
			'name' => 'PrePublish',
			'version' => '1.0',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Publish all "published" posts, regardless of pubdate',
			'copyright' => '2011'
		);
	}

	public function action_publish_post( $post ) {
		if ( Post::status_name( $post->status ) == 'scheduled' ) {
			$post->status = Post::status( 'published' );
		}
	}
}
?> 
