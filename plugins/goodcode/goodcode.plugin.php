<?php

class GoodCode extends Plugin
{ 
	
	private static $mappings;
	
	/**
	 * Required plugin info() implementation provides info to Habari about this plugin.
	 */ 
	public function info()
	{
		return array (
			'name' => 'GoodCode',
			'url' => 'http://habariproject.org',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org',
			'version' => 0.1,
			'description' => 'Attempts to make your html better and removes or replaces presentational markup.',
			'license' => 'ASL 2.0',
		);
	}

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