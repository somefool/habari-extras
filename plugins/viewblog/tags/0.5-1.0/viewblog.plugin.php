<?php

class ViewBlog extends Plugin
{
	const VERSION = '1.0';
	
	/**
	 * Required plugin info() implementation provides info to Habari about this plugin.
	 */
	public function info()
	{
		return array(
			'name' => 'View Blog Menu Item',
			'url' => 'http://blog.voodoolabs.net/',
			'author' =>'Thomas Coats',
			'authorurl' => 'http://blog.voodoolabs.net/',
			'version' => self::VERSION,
			'description' => 'Adds an extra menu item for viewing the blog',
			'license' => 'Apache License 2.0',
		);
	}
    
	public function filter_adminhandler_post_loadplugins_main_menu($mainmenus)
	{
        $mainmenus['view'] =
                array(
                    'url' => Site::get_url('habari'),
                    'title' => _t('View blog'),
                    'text' => _t('View Blog'),
                    'hotkey' => 'V',
                    'selected' => '');
        return $mainmenus;
	}
}
?>

