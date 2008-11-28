<?php

class unButtonAdmin extends Plugin
{
	public function info()
	{
		return array (
			'name' => 'Un-button Admin',
			'version' => '0.1',
			'author' => 'Habari Community',
			'license' => 'Apache License 2.0',
			'description' => 'Reverts the ugly admin buttons to the default OS widgets',
		);
	}
	
	public function action_admin_header( $theme )
	{
		Stack::add(
			'admin_stylesheet',
			array(
				'
				#page input[type=button], #page input[type=submit], #page button { 
					padding: inherit; 
					cursor: inherit; 
					-webkit-border-radius: 0;     /* Webkit only supports up to 9px at present */ 
					-moz-border-radius: 0; 
					font-size: inherit; 
					background: inherit; 
					border: inherit; 
				} 
				 
				#page input[type=button]:hover, #page input[type=submit]:hover, #page button:hover { 
					background: inherit; 
					border: inherit; 
				}
				',
				'screen'
			),
			'unbuttonadmin',
			'admin'
		);
	}
}

?>