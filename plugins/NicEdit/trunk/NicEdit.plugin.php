<?php
class NicEditor extends Plugin {

  /*
   * NicEditor
   *
   */

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
   * Instantiate the NicEdit editor
   */
  public function action_admin_footer($theme) {
    if ( $theme->page == 'publish' ) {
      $options = "fullPanel : true, iconsPath : '" . $this->get_url() . "/nicEditor/nicEditorIcons.gif'";
      $nicedit = <<<NICEDIT
$(document).ready(function() {
    var nic = new nicEditor({{$options}}).panelInstance('content');
    $('.nicEdit-main').css('outline', 'none');
    var inst = nic.instanceById('content');
    habari.editor = {
	insertSelection: function(value) {
	    $('.nicEdit-main').append(value);
	}
    }
    $('label[for=content]').hide();
    
});
NICEDIT;
      Stack::add( 'admin_footer_javascript', $nicedit, 'nicedit_footer', 'jquery' );
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
