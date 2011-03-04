<?php
/**
 * Fresh Comments
 *
 * For those who really miss Brian’s Latest Comments. :)
 **/

class FreshComments extends Plugin
{
	private $cache_name = '';
	private $range_in_seconds = 0;
	private $color_diff = array( 'r' => 0, 'g' => 0, 'b' => 0 );
	
	private static function default_options( )
	{
		return array (
			'post_count' => 5,
			'comment_count' => 6,
			'show_trackbacks' => FALSE,
			'show_pingbacks' => FALSE,
			'fade_out' => TRUE,
			'range_in_days' => 10,
			'newest_color' => '#444444',
			'oldest_color' => '#cccccc'
		);
	}

	public function action_block_form_freshcomments( $form, $block )
	{
		// Load defaults
		foreach ( self::default_options( ) as $k => $v ) {
			if ( !isset( $block->$k ) )
				$block->$k = $v;
		}
		
		$form->append( 'fieldset', 'general', _t( 'General', 'freshcomments' ) );

		$form->general->append( 'text', 'post_count', $block, _t( '&#8470; of Posts', 'freshcomments' ) );
		$form->general->post_count->add_validator( array( $this, 'validate_uint' ) );
		$form->general->post_count->add_validator( 'validate_required' );

		$form->general->append( 'text', 'comment_count', $block, _t( '&#8470; of Comments', 'freshcomments' ) );
		$form->general->comment_count->add_validator( array( $this, 'validate_uint' ) );
		$form->general->comment_count->add_validator( 'validate_required' );

		$form->general->append( 'checkbox', 'show_trackbacks', $block, _t( 'Show Trackbacks', 'freshcomments' ) );

		$form->general->append( 'checkbox', 'show_pingbacks', $block, _t( 'Show Pingbacks', 'freshcomments' ) );

		$form->append( 'fieldset', 'fade', _t( 'Fade-out', 'freshcomments' ) );

		$form->fade->append( 'checkbox', 'fade_out', $block, _t( 'Fade-out Older Comment', 'freshcomments' ) );

		$form->fade->append( 'text', 'range_in_days', $block, _t( 'Fade-out Speed (bigger is slower )', 'freshcomments' ) );
		$form->fade->range_in_days->add_validator( array( $this, 'validate_uint' ) );
		$form->fade->range_in_days->add_validator( 'validate_required' );

		$form->fade->append( 'text', 'newest_color', $block, _t( 'Newest Color', 'freshcomments' ) );
		$form->fade->newest_color->add_validator( array( $this, 'validate_color' ) );
		$form->fade->newest_color->add_validator( 'validate_required' );

		$form->fade->append( 'text', 'oldest_color', $block, _t( 'Oldest Color', 'freshcomments' ) );
		$form->fade->oldest_color->add_validator( array( $this, 'validate_color' ) );
		$form->fade->oldest_color->add_validator( 'validate_required' );
	}

	public function validate_uint( $value )
	{
		if ( !ctype_digit( $value ) || strstr( $value, '.' ) || $value < 0 ) {
			return array( _t( 'This field must be positive integer.', 'freshcomments' ) );
		}
		return array( );
	}

	public function validate_color( $value )
	{
		if ( !preg_match( '/^#[0-9a-f]{6}$/i', $value ) ) {
			return array( _t( 'This field must be an HTML color hex code.', 'freshcomments' ) );
		}
		return array( );
	}

	public function action_block_content_freshcomments( $block, $theme )
	{
		// Load defaults
		foreach ( self::default_options( ) as $k => $v ) {
			if ( !isset( $block->$k ) )
				$block->$k = $v;
		}

		// Calculate colors
		if ( $block->fade_out ) {
			$this->range_in_seconds = $block->range_in_days * 24 * 60 * 60; 
			$newest_color = ColorUtils::hex_rgb( $block->newest_color );
			$oldest_color = ColorUtils::hex_rgb( $block->oldest_color );
			$this->color_diff = array(
				'r' => $oldest_color[ 'r' ] - $newest_color[ 'r' ],
				'g' => $oldest_color[ 'g' ] - $newest_color[ 'g' ],
				'b' => $oldest_color[ 'b' ] - $newest_color[ 'b' ]
			);
		}
		
		if ( Cache::has( $this->cache_name ) ) {
			$block->freshcomments = Cache::get( $this->cache_name );
		} else {
			$comment_types = array( Comment::COMMENT );
			if ( $block->show_trackbacks ) $comment_types[ ] = Comment::TRACKBACK;
			if ( $block->show_pingbacks ) $comment_types[ ] = Comment::PINGBACK;

			$query = 'SELECT {posts}.* FROM {posts} INNER JOIN {comments} ON ( {posts}.id = {comments}.post_id ) WHERE {posts}.status = ? AND {comments}.status = ? AND ( {comments}.type = ?' . str_repeat( ' OR {comments}.type = ?', count( $comment_types ) - 1 ) . ' ) GROUP BY {posts}.id ORDER BY {comments}.date DESC, {posts}.id DESC LIMIT ' . $block->post_count;
			$query_args = array_merge( array( Post::status( 'published' ), Comment::STATUS_APPROVED ), $comment_types );
			$commented_posts = DB::get_results( $query, $query_args, 'Post' );

			$freshcomments = array( );
			foreach ( $commented_posts as $i => $post ) {
				$query = 'SELECT * FROM {comments} WHERE post_id = ? AND status = ? AND ( type = ?' . str_repeat( ' OR type = ?', count( $comment_types ) - 1 ) . ' ) ORDER BY date DESC LIMIT ' . $block->comment_count;
				$query_args = array_merge( array( $post->id, Comment::STATUS_APPROVED ), $comment_types );
				$comments = DB::get_results( $query, $query_args, 'Comment' );

				$freshcomments[ $i ][ 'post' ] = $post;
				foreach ( $comments as $j => $comment ) {
					$freshcomments[ $i ][ 'comments' ][ $j ][ 'comment' ] = $comment;
					$freshcomments[ $i ][ 'comments' ][ $j ][ 'color' ] = $this->get_color( $comment->date->int, $block->newest_color, $block->fade_out );
				}
			}

			$block->freshcomments = $freshcomments;
			Cache::set( $this->cache_name, $block->freshcomments, 3600 );
		}
	}

	private static function sanitize_color( $new_color )
	{
		return round( max( 0, min( 255, $new_color ) ) );
	}

	private function get_color( $comment_date, $newest_color='#444444', $fade_out=TRUE )
	{
		if ( $fade_out ) {
			$time_span = ( $_SERVER[ 'REQUEST_TIME' ] - $comment_date ) / $this->range_in_seconds;
			$time_span = min( $time_span, 1 );
			$newest_color = ColorUtils::hex_rgb( $newest_color );
			$color = array(
				'r' => self::sanitize_color( $newest_color[ 'r' ] + $this->color_diff[ 'r' ] * $time_span ),
				'g' => self::sanitize_color( $newest_color[ 'g' ] + $this->color_diff[ 'g' ] * $time_span ),
				'b' => self::sanitize_color( $newest_color[ 'b' ] + $this->color_diff[ 'b' ] * $time_span )
			);
			return '#' . ColorUtils::rgb_hex( $color );
		} else {
			return $newest_color;
		}
	}

	/**
	 * After a new and approved comment is inserted, expire the cache
	 */
	public function action_comment_insert_after( $comment )
	{
		if ( $comment->status === COMMENT::STATUS_APPROVED ) {
			Cache::expire( $this->cache_name );
		}
	}

	/**
	 * After an approved comment is updated, expire the cache
	 */
	public function action_comment_update_after( $comment )
	{
		if ( $comment->status === COMMENT::STATUS_APPROVED ) {
			Cache::expire( $this->cache_name );
		}
	}

	/**
	 * After an approved comment is deleted, expire the cache
	 */
	public function action_comment_delete_after( $comment )
	{
		if ( $comment->status === COMMENT::STATUS_APPROVED ) {
			Cache::expire( $this->cache_name );
		}
	}

	/**
	 * On plugin init, add the template included with this plugin to the available templates in the theme
	 */
	public function action_init( )
	{
		$this->cache_name = Site::get_url( 'habari', true ) . 'freshcomments';
		$this->load_text_domain( 'freshcomments' );
		$this->add_template( 'block.freshcomments', dirname( __FILE__ ) . '/block.freshcomments.php' );
	}

	public function filter_block_list( $block_list )
	{
		$block_list[ 'freshcomments' ] = _t( 'Fresh Comments', 'freshcomments' );
		return $block_list;
	}
}
?>
