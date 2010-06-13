<?php
/**
 * Socialink
 * adding Social Bookmark Links to your posts.
 *
 * @package socialink
 * @version $Id$
 * @author ayunyan <ayu@commun.jp>
 * @author rickc (@535)
 * @author dmondark (@582)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link http://ayu.commun.jp/habari-socialink
 */
class Socialink extends Plugin
{
	var $services = array(
		// Global
		'email' => array('name' => 'Email', 'url' => 'mailto:?subject=%TITLE%&body=%PERMALINK%'),
		'digg' => array('name' => 'Digg', 'url' => 'http://digg.com/submit?phase=2&url=%PERMALINK%'),
		'delicious' => array('name' => 'delicious', 'url' => 'http://delicious.com/save?url=%PERMALINK%&title=%TITLE%&v=5&jump=yes'),
		'technorati' => array('name' => 'Technorati', 'url' => 'http://technorati.com/faves?add=%PERMALINK%'),
		'google' => array('name' => 'Google', 'url' => "javascript:(function(){var a=window,b=document,c=encodeURIComponent,d=a.open('http://www.google.com/bookmarks/mark?op=edit&amp;output=popup&amp;bkmk=%PERMALINK%&amp;title=%TITLE%','bkmk_popup','left='+((a.screenX||a.screenLeft)+10)+',top='+((a.screenY||a.screenTop)+10)+',height=420px,width=550px,resizable=1,alwaysRaised=1');a.setTimeout(function(){d.focus()},300)})();"),
		'yahoo' => array('name' => 'Yahoo! My Web 2.0', 'url' => 'http://myweb2.search.yahoo.com/myresults/bookmarklet?u=%PERMALINK%&amp;t=%TITLE%'),
		'furl' => array('name' => 'furl', 'url' => 'http://www.furl.net/storeIt.jsp?u=%PERMALINK%'),
		'reddit' => array('name' => 'Reddit', 'url' => 'http://reddit.com/submit?url=%PERMALINK%&amp;title=%PERMALINK%'),
		'magnolia' => array('name' => 'Ma.gnolia', 'url' => 'http://ma.gnolia.com/bookmarklet/add?url=%PERMALINK%&amp;title=%TITLE%'),
		'faves' => array('name' => 'Faves', 'url' => 'http://faves.com/Authoring.aspx?u=%PERMALINK%&amp;t=%TITLE%'),
		'blinklist' => array('name' => 'blinklist', 'url' => 'http://www.blinklist.com/?Action=Blink/addblink.php&amp;Description=&amp;Url=%PERMALINK%&amp;Title=%TITLE%'),
		'stumbleupon' => array( 'name' => 'StumbleUpon', 'url'=> 'http://www.stumbleupon.com/submit?url=%PERMALINK%&amp;title=%TITLE%'),
		'diigo' => array( 'name' => 'Diigo', 'url'=> 'hhttp://www.diigo.com/post?url=%PERMALINK%&amp;title=%TITLE%'),
		'facebook' => array('name' => 'Facebook', 'url' => 'http://www.facebook.com/share.php?u=%PERMALINK%'),

		// Japan
		'hatena' => array('name' => 'Hatena Bookmark', 'url' => "javascript:(function(){window.open('http://b.hatena.ne.jp/add?mode=confirm&amp;is_bm=1&amp;title=%TITLE%&amp;url=%PERMALINK%','socialink','width=550,height=600,resizable=1,scrollbars=1');})();"),
		'yahoojbookmarks' => array('name' => 'Yahoo! JAPAN Bookmarks', 'url' => "javascript:(function(){window.open('http://bookmarks.yahoo.co.jp/bookmarklet/showpopup?t=%TITLE%&amp;u=%PERMALINK%&amp;opener=bm&amp;ei=UTF-8','socialink','width=550px,height=480px,status=1,location=0,resizable=1,scrollbars=0,left=100,top=50',0);})();"),
		'topicit' => array('name' => 'TopicIT@nifty', 'url' => "javascript:(function(){window.open('http://topic.nifty.com/up/add?mode=2&amp;topic_title=%TITLE%&amp;topic_url=%PERMALINK%');})();"),
		'buzzurl' => array('name' => 'Buzzurl', 'url' => 'http://buzzurl.jp/entry/%PERMALINK%'),
		'choix' => array('name' => 'Choix', 'url' => 'http://www.choix.jp/bloglink/%PERMALINK%'),
		'newsing' => array('name' => 'newsing', 'url' => 'http://newsing.jp/add?url=%PERMALINK%&amp;title=%TITLE%'),
		'livedoorclip' => array('name' => 'livedoor Clip', 'url' => 'http://clip.livedoor.com/redirect?link=%PERMALINK%&amp;title=%TITLE%&amp;ie=utf-8'),
		'pookmark' => array('name' => 'POOKMARK Airlines', 'url' => 'http://pookmark.jp/post?url=%PERMALINK%&amp;title=%TITLE%'),
        'goobookmark' => array('name' => 'goo Bookmark', 'url' => 'http://bookmark.goo.ne.jp/add/detail/?url=%PERMALINK%'),
		);

	/**
	 * action: plugin_activation
	 *
	 * @access public
	 * @param string $file
	 * @return void
	 */
	public function action_plugin_activation($file)
	{
		if ( Plugins::id_from_file( $file ) != Plugins::id_from_file( __FILE__ ) ) return;

		Options::set( 'socialink__link_pos', 'top' );
		Options::set( 'socialink__services', serialize( array( 'digg', 'delicious', 'technorati', 'google', 'yahoo', 'furl', 'reddit', 'magnolia' ) ) );
	}

	/**
	 * action: update_check
	 *
	 * @access public
	 * @return void
	 */
	public function action_update_check()
	{
		Update::add( 'Socialink', '58c939f3-26ae-11dd-b5d6-001b210f913f', $this->info->version );
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
			$ui_services = array();
			foreach ($this->services as $k => $service) {
				$ui_services[$k] = $service['name'];
			}
			$ui = new FormUI( strtolower( get_class( $this ) ) );
			$link_pos = $ui->append( 'radio', 'link_pos', 'option:socialink__link_pos', _t( 'Auto Insert: ' ) );
			$link_pos->options = array('none' => 'None', 'top' => 'Top', 'bottom' => 'Bottom');
			$services = $ui->append( 'select', 'services', 'option:socialink__services', _t( 'Services: ' ), $ui_services);
			$services->options = $ui_services;
			$services->multiple = true;
			$ui->append( 'submit', 'save', _t( 'Save' ) );
			$ui->out();
		}
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
	 * filter: post_content_out
	 *
	 * @access public
	 * @return string
	 */
	public function filter_post_content_out($content, $post)
	{
		$link_pos = Options::get('socialink__link_pos');
		if ($link_pos == 'top') {
			$content = $this->create_link($post) . $content;
		} elseif ($link_pos == 'bottom') {
			$content = $content . $this->create_link($post);
		}
		return $content;
	}

	/**
	 * theme: show_socialink
	 *
	 * @access public
	 * @param object $theme
	 * @param object $post
	 * @return string
	 */
	public function theme_show_socialink($theme, $post)
	{
		return $this->create_link($post);
	}

	private function create_link($post)
	{
		$link = '<div class="socialink">';
		$site_title = Options::get( 'title' );
		$s_services = Options::get( 'socialink__services' );
		@reset( $s_services );
		while ( list( , $k ) = @each( $s_services ) ) {
			if ( !isset( $this->services[$k] ) ) continue;
			$url = $this->services[$k]['url'];
			$url = str_replace( '%PERMALINK%', urlencode( $post->permalink ), $url );
			$url = str_replace( '%TITLE%', urlencode( $site_title . ' - ' . $post->title_out ), $url );
			$target = '';
			if ( substr( $url, 0, 11 ) != 'javascript:' ) {
				$target = ' target="_blank"';
			}
			$link.= '<a href="' . $url .'"' . $target . ' title="Post to ' . $this->services[$k]['name'] . '" rel="nofollow"><img src="' . $this->get_url() .'/img/icon/' . $k . '.png" width="16" height="16" alt="Post to ' . $this->services[$k]['name'] . '" style="padding:0 3px;" /></a>';
		}
		$link.= '</div>';
		return $link;
	}
}
?>
