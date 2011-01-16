<?php
  /**
  * Bit.ly Plugin
  *
  * Generates a bit.ly short URL for each post, and 
  * makes it available in the post's info. A default template is supplied
  * which can be displayed in the header of the theme.
  *
  **/

class Bitly extends Plugin {

  public function info() {
    return array(
      'name' => 'Bit.ly Plugin',
      'version' => '0.1',
      'url' => 'http://mgdm.net/',
      'author' => 'Michael Maclean',
      'authorurl' => 'http://mgdm.net/',
      'license' => 'Apache License 2.0',
      'description' => 'Makes bit.ly short URLs for each post'
    );
  }

  function action_update_check() {
    Update::add( 'Bit.ly short URLs', '6C935C68-21B5-11E0-AC90-F74CE0D72085', $this->info->version ); 
  }

  public function action_init() {
    $this->add_template('bitly_short_url', dirname(__FILE__) . '/bitly_short_url.php');
  }

  public function action_plugin_ui($plugin_id, $action) {
    if ($plugin_id == $this->plugin_id()) {
      switch ($action) {
      case _t('Configure'):
        $form = new FormUI(__CLASS__);
        $login = $form->append('text', 'login', 'bitly__login', _t('Login'));
        $login->add_validator('validate_required');
        $apiKey = $form->append('text', 'api_key', 'bitly__api_key', _t('API Key'));
        $apiKey->add_validator('validate_required');
        $form->append('submit', 'save', 'Save');
        $form->on_success( array( $this, 'updated_config' ) );
        $form->out();
        break;
      }
    }
  }

  public function updated_config( FormUI $ui )
  {
    Session::notice( _t( 'Bit.ly options saved.', 'bitly' ) );
    $ui->save();
  }

  public function filter_plugin_config( $actions, $plugin_id )
  {
    if ( $plugin_id == $this->plugin_id() ) {
      $actions[]= _t( 'Configure' );
    }
    return $actions;
  }

  public function theme_show_bitly_shorturl($theme, $post) {
    $theme->post = $post;
    return $theme->fetch('bitly_short_url');
  }

  public function action_post_insert_after($post) {
    if (Post::status('published') != $post->status) {
      return;
    }

    try {
      $bitly = new BitlyAPI(Options::get('bitly__login'), Options::get('bitly__api_key'));
      $result = $bitly->shorten($post->permalink);
      $post->info->short_url = $result->data->url;
    }

    catch (Exception $e) {
      Session::error('Could not communicate with bit.ly API.', 'Bit.ly API');
    }
  }
}

class BitlyAPI {
  public $endpoint = 'http://api.bit.ly/v3';
  public $format = 'json';
  protected $username = null;
  protected $apiKey = null;

  public function __construct($username, $apiKey) {
    $this->username = $username;
    $this->apiKey = $apiKey;
  }

  public function shorten($url) {
    $params = array(
      'login' => $this->username, 
      'apiKey' => $this->apiKey,
      'format' => $this->format,
      'longUrl' => $url,
    );

    $reqUrl = $this->endpoint . '/shorten?' . http_build_query($params);
    $call = new RemoteRequest($reqUrl);
    $call->set_timeout(5);
    $result = $call->execute();
    if (Error::is_error($result)) {
      throw $result;
    }

    $response = $call->get_response_body();
    $data = json_decode($response);
    if ($data === null) {
      throw new Exception("Could not communicate with bit.ly API");
    }

    return $data;
  }
}

?>
