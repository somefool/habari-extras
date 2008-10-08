<?php

class Dateyurl extends Plugin
{ 
	
	/**
	 * Required plugin info() implementation provides info to Habari about this plugin.
	 */ 
	public function info()
	{
		return array (
			'name' => 'DateYURL',
			'url' => 'http://habariproject.org',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org',
			'version' => 0.1,
			'description' => 'Makes entry urls in the form /{year}/{month}/{day}/{slug}',
			'license' => 'ASL 2.0',
		);
	}

	public function action_init() {
		$rule= RewriteRules::by_name('display_entry');
		$rule= $rule[0];
		
		$rule->parse_regex= '%(?P<year>\d{4})/(?P<mon0>\d{2})/(?P<mday0>\d{2})/(?P<slug>[^/]+)(?:/page/(?P<page>\d+))?/?$%i';
		$rule->build_str= '{$year}/{$mon0}/{$mday0}/{$slug}(/page/{$page})';
		
		// Utils::debug($rule);
	}

}	

?>