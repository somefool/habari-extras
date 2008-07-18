<?php
class NicEditor extends Plugin {

  /*
   * NicEditor
   *
   */

  /* Required Plugin Informations */
  public function info() {
    return array(
      'name' => 'NicEditor',
      'version' => '0.2.5',
      'url' => 'http://habariproject.org/',
      'author' => 'Habari Community',
      'authorurl' => 'http://habariproject.org/',
      'license' => 'Apache License 2.0',
      'description' => 'NicEditor Plugin for Habari',
      'copyright' => '2007'
    );
  }

  /**
    * Add actions to the plugin page for this plugin
    * @param array $actions An array of actions that apply to this plugin
    * @param string $plugin_id The string id of a plugin, generated by the system
    * @return array The array of actions to attach to the specified $plugin_id
    **/
  public function filter_plugin_config( $actions, $plugin_id )
  {
    if ( $plugin_id == $this->plugin_id() ) {
      $actions[] = 'Configure';
    }

    return $actions;
  }

  /**
    * Respond to the user selecting an action on the plugin page
    * @param string $plugin_id The string id of the acted-upon plugin
    * @param string $action The action string supplied via the filter_plugin_config hook
    **/
  public function action_plugin_ui( $plugin_id, $action )
  {
    if ( $plugin_id == $this->plugin_id() ) {
      switch ( $action ) {
        case 'Configure' :
          $ui = new FormUI( strtolower( get_class( $this ) ) );
          // Add configurable options here
          $ui->on_success( array( $this, 'updated_config' ) );
          $ui->out();
          break;
      }
    }
  }

  /**
    * Respond to the submitted configure form
    * @param FormUI $ui The form that was submitted
    * @return boolean Whether to save the returned values.
    **/
  public function updated_config( $ui )
  {
    $options= array();
    // Get the configurable options from the UI with $ui->controls['name_of_control_set_in_ui']->value and add them to the $options array
    $options[]= 'fullPanel : true';
    // Save configurable options for this user
    Options::set(strtolower(get_class($this)) . ':options_' . User::identify()->id, '{' . implode($options, ',') . '}');
    // No need to save input values
    return false;
  }


  /**
   * Add the JavaScript required for the NicEdit editor to the publish page
   */
  public function action_admin_header($theme)
  {
    if ( $theme->page == 'publish' ) {
      Stack::add( 'admin_header_javascript', $this->get_url() . '/nicEditor/nicEdit.js', 'niceditor' );
    }
  }

  /**
   * Instantiate the NicEdit editor and enable media silos
   */
  public function action_admin_footer($theme) {
    if ( $theme->page == 'publish' ) {
      $options= Options::get(strtolower(get_class($this) . ':options_' . User::identify()->id));
      echo <<<NICEDIT
      <script type="text/javascript">
      new nicEditor({$options}).panelInstance('content');
      habari.editor = {
        insertSelection: function(value) {
          $(".nicEdit").append(value);
        }
      }
      </script>
NICEDIT;
    }
  }

  /**
   * Enable update notices to be sent using the Habari beacon
   */
  public function action_update_check() {
    Update::add( 'NicEdit', '2ba6f5fe-b634-4407-bea1-a358c20b146a',  $this->info->version );
  }
}
?>
