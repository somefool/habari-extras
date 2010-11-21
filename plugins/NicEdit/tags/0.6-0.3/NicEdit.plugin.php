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
      'version' => '0.3',
      'url' => 'http://habariproject.org/',
      'author' => 'The Habari Community',
      'authorurl' => 'http://habariproject.org/',
      'license' => 'Apache License 2.0',
      'description' => 'NicEditor Plugin for Habari',
      'copyright' => '2010'
    );
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
	$iconsPath = $this->get_url() . '/nicEditor/nicEditorIcons.gif';
      echo <<<NICEDIT
      <script type="text/javascript">
      $('[@for=content]').removeAttr('for');
      new nicEditor({iconsPath : "$iconsPath"}).panelInstance('content');
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
