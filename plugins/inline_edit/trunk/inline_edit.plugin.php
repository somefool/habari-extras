<?php

class InlineEdit extends Plugin
{
	
	public function action_update_check()
	{
		Update::add( $this->info->name, 'BFEA4AD0-F4BA-11DE-A09B-1B9856D89593', $this->info->version );
	}
	
	public function action_admin_header( $theme )
	{
		if( 'comments' == $theme->page ) {
			$edit_url = URL::get( 'auth_ajax', array( 'context' => 'in_edit' ) );
			$urls = <<< AJAX_URLS
habari.url.ajaxInEdit='$edit_url';
AJAX_URLS;
			Stack::add( 'admin_header_javascript', $urls, 'inline_edit_urls', array( 'jquery' ) );
			Stack::add( 'admin_header_javascript', $this->get_url( true ) . 'inline_edit.js', 'inline_edit', array( 'jquery' ) );
			Stack::add( 'admin_stylesheet', array( $this->get_url( true ) . 'inline_edit.css', 'screen' ), 'inline_edit' );

		}
	}

	/**
	 * Add inline edit items to the dropdown menu on the comments page
	 */
	public function filter_comment_actions( $baseactions, $comments )
	{
		$baseactions['submit'] = array('url' => 'javascript:inEdit.update();', 'title' => _t('Submit changes'), 'label' => _t('Update'), 'nodisplay' => TRUE, 'access' => 'edit' );
		$baseactions['cancel'] = array('url' => 'javascript:inEdit.deactivate();', 'title' => _t('Cancel changes'), 'label' => _t('Cancel'), 'nodisplay' => TRUE);
		return $baseactions;

	}

	/**
	 * Handles AJAX from /comments.
	 * Used to edit comments inline.
	 */
	public function action_auth_ajax_in_edit( ActionHandler $handler )
	{
		Utils::check_request_method( array( 'POST' ) );
		$handler_vars = $handler->handler_vars;

		$wsse = Utils::WSSE( $handler_vars['nonce'], $handler_vars['timestamp'] );
		if ( $handler_vars['digest'] != $wsse['digest'] ) {
			Session::error( _t('WSSE authentication failed.') );
			echo Session::messages_get( true, array( 'Format', 'json_messages' ) );
			return;
		}

		$comment = Comment::get($handler_vars['id']);
		if ( !ACL::access_check( $comment->get_access(), 'edit' ) ) {
			Session::error( _t('You do not have permission to edit this comment.') );
			echo Session::messages_get( true, array( 'Format', 'json_messages' ) );
			return;
		}

		if ( isset($handler_vars['author']) && $handler_vars['author'] != '' ) {
			$comment->name = $handler_vars['author'];
		}
		if ( isset($handler_vars['url']) ) {
			$comment->url = $handler_vars['url'];
		}
		if ( isset($handler_vars['email']) && $handler_vars['email'] != '' ) {
			$comment->email = $handler_vars['email'];
		}
		if ( isset($handler_vars['content']) && $handler_vars['content'] != '' ) {
			$comment->content = $handler_vars['content'];
		}
		if ( isset($handler_vars['time']) && $handler_vars['time'] != '' && isset($handler_vars['date']) && $handler_vars['date'] != '' ) {
			$seconds = date('s', strtotime($comment->date));
			$date = date('Y-m-d H:i:s', strtotime($handler_vars['date'] . ' ' . $handler_vars['time'] . ':' . $seconds));
			$comment->date = $date;
		}

		$comment->update();

		Session::notice( _t('Updated 1 comment.') );
		echo Session::messages_get( true, array( 'Format', 'json_messages' ) );
	}

}

?>
