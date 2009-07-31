<?php
class SimpleTagList extends Plugin
{
	public function help() 
	{
		$help = _t( 'To use, add <code>&lt;?php $theme->tag_links(); ?&gt;</code> to your theme where you want the list output.' );
		return $help;
	}

	public function action_update_check()
	{
		 Update::add( 'Simple Tag List', 'da67e437-e3b9-4d7a-bad8-5ab55b6d801b', $this->info->version );
	}

	public function action_init()
	{
		$this->add_template( 'simpletaglist', dirname(__FILE__) . '/simpletaglist.php' );
	}

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
