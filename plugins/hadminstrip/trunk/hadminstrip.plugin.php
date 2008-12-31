<?php
/**
 * Hadminstrip Plugin
 *
 * A plugin for Habari that displays and admin strip
 * at the bottom of every page of your theme, for easy access to
 * the admin section.
 * Adapted from habminbar - http://habariproject.org
 * Inspired by wp-adminstrip - http://www.somefoolwitha.com/search/adminstrip
 * 
 * @package hadminstrip
 */


class HadminStrip extends Plugin
{
	const VERSION= '1.0';
	
	/**
	 * function info
	 *
	 * Returns information about this plugin
	 * @return array Plugin info array
	 */
	public function info()
	{
		return array (
			'name' => 'Hadmin Strip',
			'url' => 'http://www.somefoolwitha.com',
			'author' => 'MatthewM',
			'authorurl' => 'http://somefoolwitha.com',
			'version' => self::VERSION,
			'description' => 'An admin strip for Habari.',
			'license' => 'Apache License 2.0',
		);
	}

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'Hadmin Strip', 'A39FB26A-B024-11DD-8832-E9A256D89593', $this->info->version );
	}
	
	/**
	 * Adds the admin bar stylesheet to the template_stylesheet Stack if the user is logged in.
	 */
	public function action_add_template_vars()
	{
		if ( User::identify()->loggedin ) {
			Stack::add( 'template_stylesheet', array($this->get_url() . '/hadminstrip.css', 'screen'), 'hadminstrip.css' );
		}
	}
	
	/**
	 * Filters the hadminstrip via Plugin API to add the edit menu item.
	 *
	 * @param array $menu The hadminstrip array
	 * @return array The modified hadminstrip array
	 */
	public function filter_hadminstrip( $menu )
	{
		if ( Controller::get_var('slug') ) {
			$menu['write']= array( 'Edit', URL::get( 'admin', 'page=publish&slug=' . Controller::get_var('slug') ) );
		}
		return $menu;
	}
	
	/**
	 * Ouputs the default menu in the template footer, and runs the 'hadminstrip' plugin filter.
	 * You can add menu items via the filter. See the 'filter_hadminstrip' method for
	 * an example.
	 */
	public function action_template_footer()
	{
		if ( User::identify()->loggedin ) { ?>
			
			<div id="adminstrip">
				
				<?php $unapprovedcomments=Comments::count_total( Comment::STATUS_UNAPPROVED, FALSE ); 
			
				$postnumber=Posts::get( array( 'count' => 1, 'content_type' => Post::type('entry'), 'status' => Post::status('published') ) ); 
				$commentnumber=Comments::count_total( Comment::STATUS_APPROVED, FALSE );
				$spamnumber=Comments::count_total( Comment::STATUS_SPAM );
				$tagcount=DB::get_value('SELECT count(id) FROM {tags}');
				$pagecount=Posts::get( array( 'count' => 1, 'content_type' => Post::type('page'), 'status' => Post::status('published') ) );?>
				
				
				<a id="striplink" href="<?php echo ( URL::get( 'admin', 'page=dashboard' )); ?>" title="Visit the Dashboard.."><span id="admin">DASHBOARD</span></a>
				&middot; <a id="striplink" href="<?php echo (URL::get( 'user', 'page=logout')); ?>" title="Log out..">Logout</a> 
				&middot; <a id="striplink" href="<?php echo (URL::get( 'admin', 'page=publish')); ?>" title="Write an entry..">Write</a> 
				&middot; <a id="striplink" href="<?php echo (URL::get( 'admin', 'page=plugins'));?>" title="Plugins">Plugins</a> 
				&middot; <a id="striplink" href="<?php echo (URL::get( 'admin', 'page=options' )); ?>" title="Update settings..">Options</a> 
					
				<?php	if ( $unapprovedcomments!=0) { ?> &middot; <a id="modcomments" href="<?php echo (URL::get( 'admin', 'page=comments' )); ?>" title="Unapproved Comments"><?php echo ($unapprovedcomments) ?> moderate</a><?php } ?>
				
				<?php	if ( $spamnumber!=0) { ?> <a id="admincomments" href="<?php echo (URL::get( 'admin', 'page=comments' )); ?>" title="Spam Comments"><?php echo ($spamnumber) ?> spam</a><?php } ?>
				 
				&middot; There are <a id="striplink" href="<?php echo (URL::get( 'admin', 'page=posts&type=' . Post::type('entry')));?>" title="<?php echo($postnumber) ?> posts"><?php echo($postnumber) ?> posts</a>, <a id="striplink" href="<?php echo (URL::get( 'admin', 'page=posts&type=' . Post::type('page')));?>" title="<?php echo($pagecount) ?> pages"><?php echo($pagecount) ?> pages</a> and <a id="striplink" href="<?php echo (URL::get( 'admin', 'page=comments' )); ?>" title="Comments"><?php echo($commentnumber) ?> comments</a> within <a id="striplink" href="<?php echo (URL::get( 'admin', 'page=tags'));?>" Tags="Tags"><?php echo($tagcount) ?> tags</a></a>
								
				 </div>
				
				
		
		<?php 		}
	}
}

?>
