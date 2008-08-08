<?php

/**
 * Flickr Gallr - Display Flickr photos in a gallery on your site
 *
 */ 

class FeedList extends Plugin
{ 
	// Version info
	const VERSION= '0.1';
	
	/**
	 * Required plugin info() implementation provides info to Habari about this plugin.
	 */ 
	public function info()
	{
		return array (
			'name' => 'Flickr Gallr',
			'url' => 'http://habariproject.org',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org',
			'version' => self::VERSION,
			'description' => 'Outputs an RSS feed as an unordered list.',
			'license' => 'ASL',
		);
	}

	/**
	* Add update beacon support
	**/
	public function action_update_check()
	{
		Update::add( $this->info->name, '45D26E5A-64E5-11DD-92F3-D09255D89593', $this->info->version );
	}

}	

?>