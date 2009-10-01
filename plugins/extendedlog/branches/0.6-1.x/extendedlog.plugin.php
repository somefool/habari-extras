<?php

class ExtendedLog extends Plugin
{
	/**
	 * function info
	 * Returns information about this plugin
	 * @return array Plugin info array
	 **/
	function info()
	{
		return array (
			'name' => 'Extended Log',
			'url' => 'http://habariproject.org/',
			'author' => 'Owen Winkler',
			'authorurl' => 'http://asymptomatic.net/',
			'version' => '1.0',
			'description' => 'Displays more information about log entries upon click',
			'license' => 'Apache License 2.0',
		);
	}

	function action_admin_header()
	{
		$url = URL::get('auth_ajax', 'context=extendedlog');

		$script = <<< SCRIPT
var initi = itemManage.initItems;
itemManage.initItems = function(){
	initi();
	$('.item .less').unbind('click').click(function(){
		$('.extendedlog').remove();
		$(this).parents('.item').after('<div class="extendedlog"><textarea readonly>Loading...</textarea></div>');
		$('.extendedlog textarea').resizeable();
		$.post(
			'{$url}',
			{
				log_id: $('.checkbox input', $(this).parents('.item')).attr('id').match(/\[([0-9]+)\]/)[1]
			},
			function(result){
				$('.extendedlog textarea').val(result)
			}
		);
	});
}
SCRIPT;
		Stack::add('admin_header_javascript', $script, 'extendedlog', array('jquery', 'admin'));
	}

	function action_auth_ajax_extendedlog($handler)
	{
		$log = EventLog::get(array('fetch_fn' => 'get_row', 'id' => $handler->handler_vars['log_id'], 'return_data' => true));
		echo $log->data;
	}

}
?>