<?php

class PrettifyPlugin extends Plugin
{
	public function filter_post_content_out($content)
	{
		$content = preg_replace_callback('%(<\s*code [^>]*class\s*=\s*(["\'])[^\'"]*prettyprint[^\'"]*\2[^>]*>)(.*?)(</\s*code\s*>)%si', array($this, 'content_callback'), $content);
		
		return $content;
	}
	
	public function filter_post_content_long($content)
	{
		$content = preg_replace_callback('%(<\s*code [^>]*class\s*=\s*(["\'])[^\'"]*prettyprint[^\'"]*\2[^>]*>)(.*?)(</\s*code\s*>)%si', array($this, 'content_callback'), $content);
		
		return $content;
	}
	
	public function content_callback($matches)
	{
		$code = trim($matches[3], "\r\n");
		$code = str_replace( "\r\n", "\n", $code);
		$code = htmlentities($code);
		$code = str_replace( "\n", "<br class=\"pretty\">", $code);
		return $matches[1] . $code . $matches[4];
	}
	
	public function action_template_header()
	{
		Stack::add('template_stylesheet', array($this->get_url(true) . 'prettify.css', 'screen'));
		Stack::add('template_header_javascript', Site::get_url('scripts', true) . 'jquery.js', 'jquery');
		Stack::add('template_header_javascript', $this->get_url(true) . 'prettify.js', 'prettify', 'jquery');
		Stack::add('template_header_javascript', '$(function(){
				$("code.prettyprint").each(function(){
					l=$("br.pretty", this).length+1;oz="";for(z=1;z<=l;z++) oz+=z+"<br>";
					$(this).wrap("<div class=\\"linewrapper\\"><code>").before("<code class=\\"prettylines\\">"+oz+"</code>");
				});
				PR_TAB_WIDTH = 2;prettyPrint()});', 'prettify_inline', array('jquery', 'prettify'));
	}
	
}
?>
