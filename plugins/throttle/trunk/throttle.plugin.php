<?php
class Throttle extends Plugin {
	
	const MAX_LOAD = 1.0;
	const KILL_LOAD = 3.0;
	
	private $load = null;

	private function current_load()
	{
		static $load;
		if(!isset($load)) {
			$uptime = `uptime`;
			$loads = substr($uptime, strrpos($uptime, ':') + 1);
			preg_match('/[0-9]+\.[0-9]+/', $loads, $match);
			$load = floatval($match[0]);
		}
		return $load;
	}

	public function action_init()
	{
		//Utils::debug(self::current_load());
	}

	public function filter_default_rewrite_rules($rules) 
	{
		if($this->current_load() > self::KILL_LOAD) {
			foreach($rules as $key => $rule) {
				if(strpos($rule['build_str'], 'admin') !== false) {
					$rules[$key]['handler'] = 'UserThemeHandler';
					$rules[$key]['action'] = 'display_throttle';
				}
			}
			if(Options::get('throttle') == '') {
				EventLog::log(sprintf(_t('Kill - Load is %s'), $this->current_load()));
				Options::set('throttle', 'kill');
			}
		}
		elseif($this->current_load() > self::MAX_LOAD) {
			foreach($rules as $key => $rule) {
				if($rule['name'] == 'search') {
					unset($rules[$key]);
				}
			}
			$rules[] = array( 
				'name' => 'search', 
				'parse_regex' => '%^search(?:/(?P<criteria>[^/]+))?(?:/page/(?P<page>\d+))?/?$%i', 
				'build_str' => 'search(/{$criteria})(/page/{$page})', 
				'handler' => 'UserThemeHandler', 
				'action' => 'display_throttle', 
				'priority' => 8, 
				'description' => 'Searches posts' 
			);
			if(Options::get('throttle') == '') {
				EventLog::log(sprintf(_t('Restrict - Load is %s'), $this->current_load()));
				Options::set('throttle', 'restrict');
			}
		}
		else {
			if(Options::get('throttle') != '') {
				EventLog::log(sprintf(_t('Normal - Load is %s'), $this->current_load()));
				Options::set('throttle', '');
			}
		} 
	
		return $rules;
	}

} 

?>
