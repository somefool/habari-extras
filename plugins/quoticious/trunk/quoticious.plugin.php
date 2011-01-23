<?php
class Quoticious extends Plugin
{
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
