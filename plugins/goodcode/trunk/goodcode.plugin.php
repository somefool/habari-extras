<?php

class GoodCode extends Plugin
{ 
	
	private static $mappings;

	/**
	* Add update beacon support
	**/
	public function action_update_check()
	{
		Update::add( $this->info->name, '228B214C-A885-11DD-8FC0-65A455D89593', $this->info->version );
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
	
}

?>