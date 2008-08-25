<?php 
Format::apply_with_hook_params( 'more', 'post_content_out', 'Read the rest &raquo;' );
Format::apply('autop', 'post_content_out');
Format::apply( 'autop', 'comment_content_out' );
Format::apply( 'nice_date', 'comment_date_out' );
Format::apply('tag_and_list', 'post_tags_out', ' , ', ' and ');
Format::apply( 'nice_date', 'post_pubdate_out', 'F j' );

// We must tell Habari to use MyTheme as the custom theme class: 
define( 'THEME_CLASS', 'MyTheme' );

/**
 * A custom theme for K2 output
 */ 
class MyTheme extends Theme
{ 	 	 	
	public function add_template_vars() {
		if( !$this->template_engine->assigned( 'pages' ) ) {
			$this->assign('pages', Posts::get( array( 'content_type' => 'page', 'status' => Post::status('published') ) ) );
		}
		if( !$this->template_engine->assigned( 'user' ) ) {
			$this->assign('user', User::identify() );
		}
		if( !$this->template_engine->assigned( 'page' ) ) {
			$this->assign('page', isset( $page ) ? $page : 1 );
		}

			//for recent comments loop in sidebar.php
			$this->assign('recent_comments', Comments::get( array('limit'=> 8, 'status'=>Comment::STATUS_APPROVED, 'orderby'=>'date DESC' ) ) );
			
			parent::add_template_vars();
	}
}
?>