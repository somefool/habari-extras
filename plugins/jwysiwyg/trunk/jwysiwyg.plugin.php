<?php

class JWYSIWYG extends Plugin
{

	public function action_admin_header($theme)
	{
		if ( $theme->page == 'publish' ) {
			Plugins::act('add_jwysiwyg_admin');
		}
	}
	
	public function action_add_jwysiwyg_admin()
	{
		Stack::add('admin_header_javascript', $this->get_url() . '/jwysiwyg/jquery.wysiwyg.js');
		Stack::add('admin_stylesheet', array($this->get_url() . '/jwysiwyg/jquery.wysiwyg.css', 'screen'));
	}
	
	public function action_add_jwysiwyg_template()
	{
		Stack::add('template_header_javascript', $this->get_url() . '/jwysiwyg/jquery.wysiwyg.js');
		Stack::add('template_stylesheet', array($this->get_url() . '/jwysiwyg/jquery.wysiwyg.css', 'screen'));
	}

	public function action_admin_footer($theme)
	{
		if ( ( $theme->page == 'publish' ) && User::identify()->info->jwysiwyg_activate ) {
			echo <<<JWYSIWYG
			<script type="text/javascript">
			$('label[for=content]').hide();
			$(function()
			{
				$('#content').wysiwyg({
				    resizeOptions: {},
				    controls : {html : {visible: true}}
				});
			});
			habari.editor = {
				insertSelection: function(value) {
					var instance = $.data($('#content')[0], 'wysiwyg');
					instance.setContent(instance.getContent() + value);
				}
			}
			</script>
JWYSIWYG;
		}
	}

	/**
	 * Add the configuration to the user page
	 **/
	public function action_form_user( $form, $user )
	{
		$fieldset = $form->append( 'wrapper', 'jwysiwyg', 'JWYSIWYG' );
		$fieldset->class = 'container settings';
		$fieldset->append( 'static', 'jwysiwyg', '<h2>' . htmlentities( 'JWYSIWYG', ENT_COMPAT, 'UTF-8' ) . '</h2>' );
	
		$activate = $fieldset->append( 'checkbox', 'jwysiwyg_activate', 'null:null', _t('Enable JWYSIWYG:'), 'optionscontrol_checkbox' );
		$activate->class[] = 'item clear';
		$activate->value = $user->info->jwysiwyg_activate;
	
		$form->move_before( $fieldset, $form->page_controls );
	
	}

	/**
	 * Save authentication fields
	 **/
	public function filter_adminhandler_post_user_fields( $fields )
	{
		$fields[] = 'jwysiwyg_activate';
	
		return $fields;
	}

}

?>
