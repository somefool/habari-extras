<?php
/*

  Rate It!

  Revision: $Id$
  Head URL: $URL$

*/
require_once('rateitlog.php');
require_once('rateitlogs.php');

class RateIt extends Plugin
{
	const DB_VERSION = 1;

	/**
	 * plugin information
	 *
	 * @access public
	 * @retrun void
	 */
	public function info()
	{
		return array(
			'name' => 'Rate It!',
			'version' => '0.01',
			'url' => 'http://ayu.commun.jp/habari-rateit',
			'author' => 'ayunyan',
			'authorurl' => 'http://ayu.commun.jp/',
			'license' => 'Apache License 2.0',
			'description' => 'adding Star Rating to your posts.',
			);
	}

	/**
	 * action: plugin_activation
	 *
	 * @access public
	 * @param string $file
	 * @return void
	 */
	public function action_plugin_activation( $file )
	{
		if ( Plugins::id_from_file( $file ) != Plugins::id_from_file( __FILE__ ) ) return;

		$db_version= Options::get( 'rateit__db_version' );
        if ( empty( $db_version ) ) $db_version= Options::get( 'rateit:db_version' );

		if ( empty( $db_version ) ) {
			if ( $this->install_db() ) {
				Options::set( 'rateit__db_version', self::DB_VERSION );
			}
			else {
				Session::error( _t('Rate It!: can\'t create table.') );
			}
		}
		elseif ( $db_version < self::DB_VERSION ) {
			// TODO: upgrade_db
		}

		Options::set( 'rateit__post_pos', 'bottom' );
	}

	/**
	 * action: init
	 *
	 * @access public
	 * @return void
	 */
	public function action_init()
	{
		$this->load_text_domain( 'rateit' );

		DB::register_table( 'rateit_log' );
	}

	/**
	 * action: update_check
	 *
	 * @access public
	 * @return void
	 */
	public function action_update_check()
	{
		Update::add( 'Rate It!', '5ffe55ac-2773-11dd-b5d6-001b210f913f', $this->info->version );
	}

	/**
	 * action: plugin_ui
	 *
	 * @access public
	 * @param string $plugin_id
	 * @param string $action
	 * @return void
	 */
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id != $this->plugin_id() ) return;
		if ( $action == _t( 'Configure' ) ) {
			$form= new FormUI( strtolower( get_class( $this ) ) );
			$form->append( 'radio', 'post_pos', 'rateit__post_pos', _t( 'Position: ' ), array( 'top' => 'Top', 'bottom' => 'Bottom' ) );
            $form->append( 'submit', 'save', _t( 'Save' ) );
			$form->out();
		}
	}

	/**
	 * action: template_header
	 *
	 * @access public
	 * @return void
	 */
	public function action_template_header()
	{
		Stack::add( 'template_header_javascript', Site::get_url( 'scripts', true ) . 'jquery.js' );
		Stack::add( 'template_header_javascript', $this->get_url() . '/js/rateit.js' );
		Stack::add( 'template_stylesheet', array($this->get_url() . '/css/rateit.css', 'all') );
		echo '<script type="text/javascript">var rateit_habari_url = \'' . Site::get_url( 'habari' ) . '\';var rateit_url = \'' . $this->get_url() . '\';</script>';
	}

	/**
	 * filter: plugin_config
	 *
	 * @access public
	 * @return array
	 */
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[]= _t( 'Configure' );
		}
		return $actions;
	}

	/**
	 * filter: rewrite_rules
	 *
	 * @access public
	 * @param array $rules
	 * @return array
	 */
	public function filter_rewrite_rules($rules)
	{
		// add rating rewrite rule
		$rules[]= new RewriteRule( array(
			'name' => 'rateit',
			'parse_regex' => '/^rateit\/rating$/i',
			'build_str' => 'rateit/rating',
			'handler' => 'RateIt',
			'action' => 'rating',
			'priority' => 2,
			'rule_class' => RewriteRule::RULE_PLUGIN,
			'is_active' => 1,
			'description' => 'Rewrite for Rate It! rating action.'
		));
		return $rules;
	}

	/**
	 * filter: post_content_out
	 *
	 * @access public
	 * @param string $content
	 * @param object $post
	 * @return string
	 */
	public function filter_post_content_out( $content, $post )
	{
	   $post_pos= Options::get( 'rateit__post_pos' );
	   if ( $post_pos == 'top' ) {
		   $content=  $this->create_rating($post) . $content;
	   }
	   else {
		   $content= $content .  $this->create_rating($post);
	   }
	   return $content;
	}

	private function create_rating( $post )
	{
		if ( !isset( $post->info->rateit_total ) || !isset( $post->info->rateit_count ) ) {
			$total= 0;
			$count= 0;
			$rating= 0.00;
		} else {
			$total= $post->info->rateit_total;
			$count= $post->info->rateit_count;
			$rating= sprintf( "%.2f", $post->info->rateit_total / $post->info->rateit_count );
		}
		$stars= round( $rating );
		$stars_classes= array(
			0 => 'nostar',
			1 => 'onestar',
			2 => 'twostar',
			3 => 'threestar',
			4 => 'fourstar',
			5 => 'fivestar'
		);

		if ( $this->check_rated( $post->id ) ) {
			$html= '
<div class="rateit" id="rateit-' . $post->id . '">
Rate It! (Average ' . $rating . ', ' . $count . ' votes)
<div class="rateit-stars rateit-' . $stars_classes[$stars] . '">
<div class="rateit-readonly"></div>
</div>
</div>';
		}
		else {
			$html= '
<div class="rateit" id="rateit-' . $post->id . '">
Rate It! (Average ' . $rating . ', ' . $count . ' votes)
<div class="rateit-stars rateit-' . $stars_classes[$stars] . '">
<ul id="rateit-list-' . $post->id . '">
  <li class="rateit-one"><a href="#" title="Poor">1</a></li>
  <li class="rateit-two"><a href="#" title="Fair">2</a></li>
  <li class="rateit-three"><a href="#" title="Good">3</a></li>
  <li class="rateit-four"><a href="#" title="Great">4</a></li>
  <li class="rateit-five"><a href="#" title="Excellent">5</a></li>
</ul>
</div>
<img src="' . $this->get_url() . '/img/loading.gif" class="rateit-loading" id="rateit-loading-' . $post->id . '" alt="" />
</div>';
		}
		return $html;
	}

	/**
	 * callback handler action
	 *
	 * @access public
	 * @param string $action
	 * @return void
	 */
	public function act( $action )
	{
		switch ( $action ) {
		case 'rating':
			if ( !isset( $this->handler_vars['post_id'] ) || !ctype_digit( $this->handler_vars['post_id'] ) ) {
				echo json_encode( array( 'error' => 1, 'message' => _t( 'Missing parameter: ' ) . 'post_id' ) );
				break;
			}

			if ( !isset( $this->handler_vars['rating'] ) || !ctype_digit( $this->handler_vars['rating'] ) ) {
				echo json_encode( array( 'error' => 1, 'message' => _t( 'Missing parameter: ' ) . 'rating' ) );
				break;
			}


			if ( $this->check_rated( $this->handler_vars['post_id'] ) ) {
				echo json_encode( array( 'error' => 1, 'message' => _t( 'You have already rated this post' ) ) );
				break;
			}

			if ( ( $post = $this->add_rating( $this->handler_vars['post_id'], $this->handler_vars['rating'] ) ) === false ) {
				echo json_encode( array( 'error' => 1, 'message' => _t( 'cannot rating this post' ) ) );
				break;
			}

			echo json_encode( array( 'error' => 0, 'message' => _t( 'Thank you for Rating' ), 'html' => $this->create_rating($post) ) );
			break;
		default:
			break;
		}
	}

	private function add_rating( $post_id, $rating )
	{
		$post= Posts::get( array( 'id' => $post_id, 'status' => Post::status( 'published' ), 'fetch_fn' => 'get_row' ) );
		if ( !$post ) return false;

		$log= new RateItLog( array( 'post_id' => $post_id, 'rating' => $rating, 'ip' => $_SERVER['REMOTE_ADDR'] ) );
		if ( !$log->insert() ) return false;

		$post->info->rateit_total+= $rating;
		$post->info->rateit_count+= 1;
		$post->info->commit();

		return $post;
	}

	private function check_rated( $post_id, $ip = null )
	{
		if ( !isset( $ip ) ) $ip= $_SERVER['REMOTE_ADDR'];
		$ip= sprintf( '%u', ip2long( $ip ) );
		if ( $ip === false ) return true;
		$result= DB::get_row( 'SELECT COUNT(*) AS count FROM ' . DB::table( 'rateit_log' ) . ' WHERE post_id = :post_id AND ip = :ip', array( ':post_id' => $post_id, ':ip' => $ip ) );
		if ( $result !== false && $result->count == 0 ) return false;
		return true;
	}

	private function install_db()
	{
		DB::register_table( 'rateit_log' );

		$query= 'CREATE TABLE `' . DB::table( 'rateit_log' ) . '` (
			`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`post_id` INT NOT NULL ,
			`rating` TINYINT NOT NULL ,
			`timestamp` DATETIME NOT NULL ,
			`ip` INT( 10 ) NOT NULL ,
			INDEX ( `post_id` , `ip` )
			);';
		return DB::dbdelta( $query );
	}
}
?>