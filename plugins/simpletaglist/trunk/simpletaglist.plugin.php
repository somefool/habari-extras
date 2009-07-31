<?php
class SimpleTagList extends Plugin
{
	/**
	 * Add help text to plugin configuration page
	 **/
	public function help() 
	{
		$help = _t( 'To use, add <code>&lt;?php $theme->tag_links(); ?&gt;</code> to your theme where you want the list output.' );
		return $help;
	}

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
		 Update::add( 'Simple Tag List', 'da67e437-e3b9-4d7a-bad8-5ab55b6d801b', $this->info->version );
	}

	/**
	 * On plugin init, add the template included with this plugin to the available templates in the theme
	 */
	public function action_init()
	{
		$this->add_template( 'simpletaglist', dirname(__FILE__) . '/simpletaglist.php' );
	}

	/**
	 * Add simplified tag array to the available template vars
	 * @param Theme $theme The theme that will display the template
	 **/
	public function theme_tag_links( $theme )
	{
		$tags = Tag::get();
		$tagarray = array();

		if ( count( $theme->tags ) != 0 ) 
		{ 
			foreach ( $tags as $tag ) {
				if( !empty( $tag->slug ) ) 
				{
					$tagarray[] = array( 'tag' => $tag->tag, 'slug' => $tag->slug );
				}
			}
		}
		$theme->tag_links = $tagarray;
		return $theme->fetch( 'simpletaglist' );
	}
}

?>
