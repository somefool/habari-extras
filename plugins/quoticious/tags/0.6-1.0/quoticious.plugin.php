<?php
class Quoticious extends Plugin {
	function info() {
		return array(
			'name' => 'Quoticious',
			'version' => '1.0',
			'url' => 'http://habariproject.org',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org',
			'license' => 'Apache License 2.0',
			'description' => 'Allows quotes to be collected'
		);
	}
	
	/**
	* Registers this plugin for updates against the beacon
	*/
	public function action_update_check()
	{
		Update::add('Quoticious', 'cffb07c5-ab53-42e7-b42d-08133097244c', $this->info->version);
	}
	
	public function action_init() {		
		Post::add_new_type('quote');
	}
	
	public function action_form_publish ($form, $post) {
		
		if($post->content_type == Post::type('quote') || $form->content_type->value == Post::type('quote')) {
			$quote = $form->publish_controls->append('fieldset', 'quote', _t('Quote'));
		
			$quote->append('text', 'quote_author', 'null:null', _t('Author'), 'tabcontrol_text');
			$quote->quote_author->value = $post->info->quote_author;

			$quote->append('text', 'quote_url', 'null:null', _t('URL'), 'tabcontrol_text');
			$quote->quote_url->value = $post->info->quote_url;
			
			return $form;
		}
		
	}
	
	public function action_publish_post ( $post, $form ) {
		
		if($post->content_type == Post::type('quote') || $form->content_type->value == Post::type('quote')) {
				
			if( strlen( $form->quote->quote_author->value ) ) {
				$post->info->quote_author = $form->quote->quote_author->value;
			}
	
			if( strlen( $form->quote->quote_url->value ) ) {
				$post->info->quote_url = $form->quote->quote_url->value;
			}
		
		}
		
	}

}
?>