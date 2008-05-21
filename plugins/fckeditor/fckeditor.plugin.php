<?php
/*

  FCKeditor for Habari

  Revision: $Id$
  Head URL: $URL$

*/
class FCKeditor extends Plugin
{
    /**
     * info
     *
     * @access public
     * @return array
     */
    public function info()
    {
        return array(
          'name' => 'FCKeditor',
          'version' => '0.01',
          'url' => 'http://ayu.commun.jp/',
          'author' => 'ayunyan',
          'authorurl' => 'http://ayu.commun.jp/',
          'description' => 'FCKeditor for Habari',
        );
    }

    /**
     * action: update_check
     *
     * @access public
     * @return void
     */
    public function action_update_check()
    {
        Update::add( 'FCKeditor', '99d1907b-1e6d-11dd-b5d6-001b210f913f', $this->info->version );
    }

    /**
     * action: admin_header
     *
     * @access public
     * @param object $theme
     * @return void
     */
    public function action_admin_header($theme)
    {
        if ( $theme->admin_page != 'publish' ) return;
        Stack::add( 'admin_header_javascript', $this->get_url() . '/fckeditor/fckeditor.js' );
    }

    /**
     * action: admin_footer
     *
     * @access public
     * @param object $theme
     * @return void
     */
    public function action_admin_footer($theme)
    {
        if ( $theme->admin_page != 'publish' ) return;
?>
<script type="text/javascript">
if ($('#content').filter('.islabeled').size() > 0) {
	$('#content').filter('.islabeled').removeClass('islabeled');
}
FCKeditor = new FCKeditor('content');
FCKeditor.BasePath = '<?php echo $this->get_url(); ?>/fckeditor/';
FCKeditor.Height = 300;
FCKeditor.ToolbarSet = 'Habari';
FCKeditor.Config['CustomConfigurationsPath'] = '<?php echo $this->get_url(); ?>/fckconfig-js.php';
FCKeditor.Config['ImageBrowser'] = false;
FCKeditor.Config['ImageUpload'] = false;
FCKeditor.Config['LinkBrowser'] = false;
FCKeditor.Config['LinkUpload'] = false;
FCKeditor.ReplaceTextarea();
habari.editor = {
    insertSelection: function(value) {
        var oEditor = FCKeditorAPI.GetInstance('content') ;
        oEditor.InsertHtml(value);
    }
}
</script>
<?php
    }
}
?>