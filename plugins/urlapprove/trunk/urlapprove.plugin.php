<?php

class URLApprove extends Plugin
{
	const DIRECT = 1;
	const REDIRECT = 2;


	private $fetch_real = false;

	/**
	 * function info
	 * Returns information about this plugin
	 * @return array Plugin info array
	 **/
	function info()
	{
		return array (
			'name' => 'URL Approve',
			'url' => 'http://habariproject.org/',
			'author' => 'Owen Winkler',
			'authorurl' => 'http://asymptomatic.net/',
			'version' => '1.0.1',
			'description' => 'Allows an admin to mark commenter URLs to link through a redirector instead of directly linking',
			'license' => 'Apache License 2.0',
		);
	}

	public function action_init()
	{
		$this->load_text_domain('urlapprove');
	}

	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions['urlapprove'] = _t( 'Configure' );
		}
		return $actions;
	}

	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$ui = new FormUI( 'urlapprove' );
			$ui->append( 'checkbox', 'redirect_default', 'urlapprove__redirect_default', _t( 'Redirect all comment author links by default', 'urlapprove' ) );
			$ui->append( 'textarea', 'whitelist', 'urlapprove__whitelist', _t('Whitelist of domains that will not be redirected, one per line (does not auto-approve)') );
			$ui->append( 'submit', 'submit', 'Submit' );
			$ui->out();
		}
	}

	public function action_admin_header()
	{
		$script = <<< SCRIPT
		//Need to call this on ajax comment reload
		\$(function(){ \$('.comments .item:has(.commenter_redirected)').addClass('redirected')});
SCRIPT;

		$style = <<< STYLE
		#comments .redirected .author { color: red; font-style: italic; }
STYLE;

		Stack::add('admin_header_javascript', $script, 'urlapprove', array('jquery', 'admin'));
		Stack::add('admin_stylesheet', array($style, 'screen'), 'urlapprove');
	}

	public function filter_comment_actions($actions, $comment)
	{
		if($comment->url == '') {
			return $actions;
		}
		if($comment->info->redirecturl == URLApprove::REDIRECT || (!isset($comment->info->redirecturl) && Options::get('urlapprove__redirect_default') == true) ) {
			$actions['direct'] = array('url' => 'javascript:itemManage.update(\'direct\','. $comment->id . ');', 'title' => _t('Use Direct link to author URL'), 'label' => _t('Direct Link'));
		}
		else {
			$actions['redirect'] = array('url' => 'javascript:itemManage.update(\'redirect\','. $comment->id . ');', 'title' => _t('Use Redirected link to author URL'), 'label' => _t('Redirect Link'));
		}
		return $actions;
	}

	public function filter_admin_comments_action($status_msg, $action, $comments )
	{
		switch($action) {
			case 'direct':
				$value = URLApprove::DIRECT;
				$status_msg = _t('Comment set to link directly.');
				break;
			case 'redirect':
				$value = URLApprove::REDIRECT;
				$status_msg = _t('Comment set to use redirector.');
				break;
			default: return $status_msg;
		}
		foreach($comments as $comment) {
			$comment->info->redirecturl = $value;
			$comment->info->commit();
		}
		return $status_msg;
	}

	protected function get_hash($commentid)
	{
		return substr(md5($commentid . $_SERVER['REMOTE_ADDR'] . Options::get('GUID') . HabariDateTime::date_create()->yday), 0, 6);
	}

	private function quote_whitelist($value)
	{
		return preg_quote(trim($value), ':');
	}

	public function filter_comment_url_out($value, $comment)
	{
		$whitelist = explode("\n", Options::get('urlapprove__whitelist'));
		if($whitelist != '' && $comment->url) {
			$whitelist = array_map(array($this, 'quote_whitelist'), $whitelist);
			$whitelist = ':' . implode('|', $whitelist) . ':i';
			if(preg_match($whitelist, $value)) {
				return $value;
			}
		}

		if(isset($comment->info->redirecturl)){
			if($comment->info->redirecturl == URLApprove::REDIRECT) {
				$value = URL::get('comment_url_redirect', array('id' => $comment->id, 'ccode' => $this->get_hash($comment->id)));
			}
		}
		elseif(Options::get('urlapprove__redirect_default') == true) {
			$value = URL::get('comment_url_redirect', array('id' => $comment->id, 'ccode' => $this->get_hash($comment->id)));
		}
		return $value;
	}

	public function filter_rewrite_rules($rules)
	{
		$rules[] = new RewriteRule(array(
			'name' => 'comment_url_redirect',
			'parse_regex' => '/^(?P<id>([0-9]+))\/(?P<ccode>([0-9a-f]+))\/redirect[\/]{0,1}$/i',
			'build_str' => '{$id}/{$ccode}/redirect',
			'handler' => 'FeedbackHandler',
			'action' => 'comment_url_redirect',
			'priority' => 7,
			'is_active' => 1,
		));

		return $rules;
	}

	public function action_handler_comment_url_redirect($handler_vars)
	{
		$comment = Comment::get($handler_vars['id']);
		$hash = $this->get_hash($handler_vars['id']);
		if($hash == $handler_vars['ccode']) {
			Utils::redirect($comment->url);
			exit;
		}
		header('HTTP/1.1 410 Gone');
		exit;
	}

}
?>