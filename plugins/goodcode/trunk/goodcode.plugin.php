<?php

class GoodCode extends Plugin
{ 
	
	private static $mappings;

	/**
	* Add update beacon support
	**/
	public function action_update_check()
	{
		Update::add( $this->info->name, '9bfb17aa-f8f0-4638-a549-af4f86a9d412', $this->info->version );
	}
	
	public function filter_post_content($content) {
		self::$mappings= array(
			'i' => array('em'),
			'b' => array('strong')
		);
		
		foreach(self::$mappings as $from => $properties) {
			$content= str_replace('<' . $from . '>', '<' . $properties[0] . '>', $content);
			$content= str_replace('</' . $from . '>', '</' . $properties[0] . '>', $content);
		}
		return $content;
	}
	
	/**
	 * Stop post content from being butchered by the overzealous nanny software
	 **/
	public function action_form_publish( $form, $post, $context )
	{
		$form->title->raw = true;
	}
	
	private function filter( $string ) {
		return htmlentities( $string );
	}
	
	/**
	 * Properly escape in Atom feed
	 */
	public function filter_post_title_atom( $title )
	{
		return $this->filter( $title );
	}
	
	/**
	 * Properly escape title content
	 */
	public function filter_post_title_out( $title )
	{
		return $this->filter( $title );
	}
	
}

?>