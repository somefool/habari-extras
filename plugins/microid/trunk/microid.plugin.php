<?php
class MicroID extends Plugin {

  function info() {
    return array(
      'name' => 'MicroID Generator Plugin',
      'version' => '1.0',
      'url' => 'http://digitalspaghetti.me.uk/',
      'author' => 'Tane Piper',
      'authorurl' => 'http://digitalspaghetti.me.uk/',
      'license' => 'MIT Licence',
      'description' => 'Generates a MicroID for services such as ClaimID.',
    );
  }

  public function action_plugin_activation( $file ) {
    if ( realpath( $file ) == __FILE__ ) {
      Modules::add( 'MicroID' );
    }
  }

  public function action_plugin_deactivation( $file )
  {
    if ( realpath( $file ) == __FILE__ ) {
      Modules::remove_by_name( 'MicroID' );
    }
  }
  
  function action_update_check() {
    Update::add( 'MicroID', '0B8CC720-9057-11DD-A39C-BA6355D89593', $this->info->version ); 
  }

  function theme_header( $theme ) {
    
      $count = 0;
      foreach ($theme->posts as $post) {
        $user = User::get_by_id($post->user_id);
        $microid = $this->generate('mailto:' . $user->email, $this->currentURL(true));
        echo '<meta name="microid" content="'.$microid.'" />';
        $count++;
      }
      
      if ($count == 0) {
        $user = User::get_by_id($theme->posts->user_id);
        $microid = $this->generate('mailto:' . $user->email, $this->currentURL(true));
        echo '<meta name="microid" content="'.$microid.'" />';
      }
      
  }

  function generate($identity, $service, $algorithm = 'sha1') {
      $microid = "";
      $microid .= substr($identity, 0, strpos($identity, ':')) . "+" . substr($service, 0, strpos($service, ':')) . ":" . strtolower($algorithm) . ":";
      // try message digest engine
      if (function_exists('hash')) {
          if (in_array(strtolower($algorithm), hash_algos())) {
              return $microid .= hash($algorithm, hash($algorithm, $identity) . hash($algorithm, $service));
          }
      }
      // try mhash engine
      if (function_exists('mhash')) {
          $hash_method = @constant('MHASH_' . strtoupper($algorithm));
          if ($hash_method != null) {
              $identity_hash = bin2hex(mhash($hash_method, $identity));
              $service_hash = bin2hex(mhash($hash_method, $service));
              return $microid .= bin2hex(mhash($hash_method, $identity_hash . $service_hash));
          }
      }
      // direct string function
      if (function_exists($algorithm)) { 
          return $microid .= $algorithm($algorithm($identity) . $algorithm($service));
      }
      echo "MicroID: unable to find adequate function for algorithm '$algorithm'";
  }

  function currentURL($trim) {
      $pageURL = 'http';
      if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
      $pageURL .= "://";
    $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    if ($trim == true) {$pageURL = rtrim($pageURL, '/');}
    return $pageURL;
  }
}
?>
