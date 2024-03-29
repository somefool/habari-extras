<?php
/*
 * URL Bouncer Plugin
 */

class URLBounce extends Plugin
{
	function info()
	{
		return array(
			'name' => 'URL Bouncer',
			'version' => '0.3',
			'url' => 'http://hacman.org.uk/urlbounce',
			'author' => 'Bob Clough',
			'authorurl' => 'http://thinkl33t.co.uk',
			'license' => 'Apache License 2.0',
			'description' => 'Allow user to set up URL bouncers, to forward local URLs on to remote URLs'
		);
	}

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'URL Bounce', '7d09c910-b921-4958-9f02-c1e06a20b756', $this->info->version );
	}

	//when plugin is activated, create urlbounce type, let anon users access it
	public function action_plugin_activation( $plugin_file )
	{
		Post::add_new_type( 'urlbounce' );
	
		$group = UserGroup::get_by_name( 'anonymous' );
		$group->grant( 'post_urlbounce', 'read');
	}
	 
	//when plugin is deactivated, disable urlbounce type
	public function action_plugin_deactivation( $plugin_file )
	{
		Post::deactivate_post_type( 'urlbounce' );
	}

	//allow our new type to be rendered
	public function action_init()
	{
		$this->add_template('urlbounce.single', dirname(__FILE__) . '/urlbounce.single.php');
	}
	 
	// add a url field to the form
	public function action_form_publish($form, $post, $context)
	{
		if ($form->content_type->value == Post::type('urlbounce')) 
		{
		        //add URL field
		        $form->insert('content', 'text', 'url', 'null:null', _t('External URL'), 'admincontrol_text');
		        $form->url->value = $post->info->url;
		        $form->url->tabindex = 2;
		        
		        //disable comments by default
		        $form->settings->comments_enabled->value = false;

		        //hide tags
		        $form->tags->template = 'hidden';

		        //remove silos
		        if ($form->silos) $form->silos->remove();

		        //modify content to be hidden - it cant be done the same way as tags, form becomes unsubmittable
		        $form->content->template = 'admincontrol_text';
		        $form->content->class[] = 'hidden';
		        $form->content->class = array_diff($form->content->class, array('resizable'));

		        //promote post slug to main section
		        $form->settings->newslug->move_after($form->url);
		        $form->newslug->template = 'admincontrol_text';
		        $form->newslug->caption = _t('Local URL');
		        $form->newslug->tabindex = 3;
		}
	}

	// take to url field and append it to the post
	public function action_publish_post( $post, $form )
	{
		if ($post->content_type == Post::type('urlbounce')) 
		{
			$post->info->url = $form->url->value;
		}
	}

	//prettify the urlbouncer text whenever it appears
	public function filter_post_type_display($type, $foruse) 
	{ 
		$names = array( 
			'urlbounce' => array(
				'singular' => _t('URL Bouncer'),
				'plural' => _t('URL Bouncers'),
			)
		); 
		return isset($names[$type][$foruse]) ? $names[$type][$foruse] : $type; 
	}

	//allow us to use $object->url instead of $object->info->url
	public function filter_post_url($url, $post) 
	{
		if($post->content_type == Post::type('urlbounce')) 
		{
			return $post->info->url;
		}
		else 
		{
			return $url;
		}
	}
}
?>
