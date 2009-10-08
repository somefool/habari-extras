<?php

class CommentIdent extends Plugin {
	
	public function action_init_theme() {
		Stack::add('template_header_javascript', $this->get_url() . '/ident/jquery-1.3.2.min.js', 'jquery');
		Stack::add('template_header_javascript', $this->get_url() . '/js/jquery-ui-1.7.2.custom.min.js', 'jqueryui', 'jquery');
		Stack::add('template_header_javascript', $this->get_url() . '/ident/ident-0.1.js', 'ident_lib');
		Stack::add('template_header_javascript', $this->get_url() . '/js/main.js', 'ident_main', 'jquery');
		Stack::add('template_stylesheet', array($this->get_url() . '/css/dialog.css', 'screen'), 'ident_dialog');
	}
	
	public function theme_footer() {
		echo '<script type="text/javascript">ident.iconPath = "' . $this->get_url() . '/ident/icons/";</script>';
	}
	
	public function help()
	{
		return <<< END_HELP
<p>To use this plugin currently you need to make one small change to your theme. Any anchor tags that link to a commenter's supplied URL should be given the class 'comment-url'. This plugin will take over from there.</p>
END_HELP;
	}
	
}
	
?>