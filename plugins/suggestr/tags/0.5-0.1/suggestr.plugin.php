<?php

class Suggestr extends Plugin
{
	var $rules;
	
	public function info()
	{
		return array(
			'name' => 'Suggestr',
			'author' => 'Habari Community',
			'description' => 'Provides tag suggestions for posts',
			'url' => 'http://habariproject.org',
			'version' => '0.1',
			'license' => 'Apache License 2.0'
		);
	}
	
	public function action_admin_header() {
		Stack::add( 'admin_header_javascript', URL::get_from_filesystem(__FILE__) . '/suggestr.js', 'suggestr' );
		Stack::add( 'admin_stylesheet', array(URL::get_from_filesystem(__FILE__) . '/suggestr.css', 'screen'), 'suggestr' );
	}
	
	public function action_ajax_tag_suggest( $handler ) {
		
		if(!isset($handler->handler_vars['text'])) {
			$text= '';
		} else {
			$text= $handler->handler_vars['text'];
		}
		$tags = array();
		
		$tags = $this->fetch_yahoo_tags($text);
		
		$tags = serialize($tags);
		$tags = Plugins::filter('tag_suggestions', $tags, $text);
		$tags = unserialize($tags);
		
		$count = count($tags);
		
		if($count == 0) {
			$message = _t('No tag suggestions could be found');
		} else {
			$message = sprintf( '%d tag ' . _n( _t( 'suggestion'), _t( 'suggestions' ), $count ) . ' could be found.', $count );
		}
		
		echo json_encode(array('count' => $count, 'tags' => $tags, 'message' => $message)); 	
	}
	
	public function fetch_yahoo_tags( $text ) {
		$appID = 'UZuNQnrV34En.c9itu77sQrdjp.FQU81t8azZeE5YmWjRkP9wVlPg.CPIc8eLZT68GI-';
		$context = $text;
		
		$request = new RemoteRequest('http://search.yahooapis.com/ContentAnalysisService/V1/termExtraction', 'POST');
		$request->set_params(array(
			'appid' => $appID,
			'context' => $context
		));
		
		$tags = array();
		
		// Utils::debug($request->execute());
		
		if(!is_object($request->execute())) {
			$response = $request->get_response_body();

			$xml = new SimpleXMLElement($response);

			foreach($xml->Result as $tag) {
				$tags[] = strval($tag);
			}
		}
		
		return $tags;
		
	}
	
}

?>
