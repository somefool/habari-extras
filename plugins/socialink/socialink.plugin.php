<?php
/*

  Socialink

  Revision: $Id$
  Head URL: $URL$

*/
class Socialink extends Plugin
{
    var $services= array(
        // Global
        'digg' => array('name' => 'Digg', 'url' => 'http://digg.com/submit?phase=2&url=%PERMALINK%'),
        'delicious' => array('name' => 'del.icio.us', 'url' => 'http://del.icio.us/post?url=%PERMALINK%'),
        'technorati' => array('name' => 'Technorati', 'url' => 'http://technorati.com/faves?add=%PERMALINK%'),
        'google' => array('name' => 'Google', 'url' => "javascript:(function(){var a=window,b=document,c=encodeURIComponent,d=a.open('http://www.google.com/bookmarks/mark?op=edit&output=popup&bkmk=%PERMALINK%&title=%TITLE%','bkmk_popup','left='+((a.screenX||a.screenLeft)+10)+',top='+((a.screenY||a.screenTop)+10)+',height=420px,width=550px,resizable=1,alwaysRaised=1');a.setTimeout(function(){d.focus()},300)})();"),
        'yahoo' => array('name' => 'Yahoo! My Web 2.0', 'url' => 'http://myweb2.search.yahoo.com/myresults/bookmarklet?u=%PERMALINK%'),
        'furl' => array('name' => 'furl', 'url' => 'http://www.furl.net/storeIt.jsp?u=%PERMALINK%'),
        'reddit' => array('name' => 'Reddit', 'url' => 'http://reddit.com/submit?url=%PERMALINK%'),
        'magnolia' => array('name' => 'Ma.gnolia', 'url' => 'http://ma.gnolia.com/bookmarklet/add?url=%PERMALINK%&title=%TITLE%'),
        'faves' => array('name' => 'Faves', 'url' => 'http://faves.com/Authoring.aspx?u=%PERMALINK%&t=%TITLE%'),

        // Japan
        'hatena' => array('name' => 'Hatena Bookmark', 'url' => "javascript:(function(){window.open('http://b.hatena.ne.jp/add?mode=confirm&is_bm=1&title=%TITLE%&url=%PERMALINK%','socialink','width=550,height=600,resizable=1,scrollbars=1');})();"),
        'yahoojbookmarks' => array('name' => 'Yahoo! JAPAN Bookmarks', 'url' => "javascript:(function(){window.open('http://bookmarks.yahoo.co.jp/bookmarklet/showpopup?t=%TITLE%&amp;u=%PERMALINK%&amp;opener=bm&amp;ei=UTF-8','socialink','width=550px,height=480px,status=1,location=0,resizable=1,scrollbars=0,left=100,top=50',0);})();"),
        'topicit' => array('name' => 'TopicIT@nifty', 'url' => "javascript:(function(){window.open('http://topic.nifty.com/up/add?mode=2&amp;topic_title=%TITLE%&topic_url=%PERMALINK%');})();"),
        'buzzurl' => array('name' => 'Buzzurl', 'url' => 'http://buzzurl.jp/entry/%PERMALINK%'),
        'choix' => array('name' => 'Choix', 'url' => 'http://www.choix.jp/bloglink/%PERMALINK%'),
        'newsing' => array('name' => 'newsing', 'url' => 'http://newsing.jp/add?url=%PERMALINK%&title=%TITLE%'),
        'livedoorclip' => array('name' => 'livedoor Clip', '' => 'http://clip.livedoor.com/redirect?link=%PERMALINK%&title=%TITLE%&ie=utf-8'),
        'pookmark' => array('name' => 'POOKMARK Airlines', 'url' => 'http://pookmark.jp/post?url=%PERMALINK%&title=%TITLE%'),
        );

    /**
     * plugin information
     *
     * @access public
     * @retrun void
     */
    public function info()
    {
        return array(
            'name' => 'Socialink',
            'version' => '0.01',
            'url' => 'http://ayu.commun.jp/',
            'author' => 'ayunyan',
            'authorurl' => 'http://ayu.commun.jp/',
            'license' => 'Apache License 2.0',
            'description' => 'adding Social Bookmark Links to your posts.',
            );
    }

    /**
     * action: plugin_activation
     *
     * @access public
     * @param string $file
     * @return void
     */
    public function action_plugin_activation($file)
    {
        if ( $file != $this->get_file() ) return;

        Options::set( 'socialink:link_pos',  'top' );
        Options::set( 'socialink:services', serialize( array( 'digg', 'delicious', 'technorati', 'google', 'yahoo', 'furl', 'reddit', 'magnolia' ) ) );
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
    public function action_plugin_ui($plugin_id, $action)
    {
        if ( $plugin_id != $this->plugin_id() ) return;
        if ( $action == _t( 'Configure' ) ) {
            $ui_services= array();
            foreach ($this->services as $k => $service) {
                $ui_services[$k] = $service['name'];
            }
            $ui= new FormUI( strtolower( get_class( $this ) ) );
            $link_pos = $ui->add( 'radio', 'link_pos', _t( 'Link Position: ' ), array('top' => 'Top', 'bottom' => 'Bottom'), Options::get( 'socialink:link_pos' ) );
            $services= $ui->add( 'select', 'services', _t( 'Services: ' ), $ui_services, Options::get( 'socialink:services' ));
            $services->multiple = true;
            $ui->on_success( array( $this, 'updated_config' ) );
            $ui->out();
        }
    }

    /**
     * FormUI callback
     *
     * @access public
     * @return boolean
     */
    public function updated_config($ui)
    {
        return true;
    }

    /**
     * filter: plugin_config
     *
     * @access public
     * @return array
     */
    public function filter_plugin_config($actions, $plugin_id)
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
var_dump(Controller::get_action());
       $link_pos= Options::get( 'socialink:link_pos' );
       if ( $link_pos == 'top' ) {
           $content= $this->create_link($post) . $content;
       }
       else {
           $content= $content . $this->create_link($post);
       }
       return $content;
    }

    private function create_link($post)
    {
        $link = '<div class="socialink">';
        $site_title= Options::get( 'title' );
        $s_services= Options::get( 'socialink:services' );
        @reset($s_services);
        while (list(, $k) = @each($s_services)) {
            $url= $this->services[$k]['url'];
            $url= str_replace('%PERMALINK%', urlencode($post->permalink), $url);
            $url= str_replace('%TITLE%', urlencode($site_title . ' - ' . $post->title_out), $url);
            $target= '';
            if ( substr( $url, 0, 10 ) == 'javascript' ) {
                $target=' target="_blank"';
            }
            $link.= '<a href="' . $url .'"' . $target . ' title="Post to ' . $this->services[$k]['name'] . '" rel="nofollow"><img src="' . $this->get_url() .'/img/icon/' . $k . '.png" width="16" height="16" alt="Post to ' . $this->services[$k]['name'] . '" /></a>';
        }
        $link.= '</div>';
        return $link;
    }
}
?>