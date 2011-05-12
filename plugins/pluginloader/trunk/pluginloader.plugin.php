<?php

include_once(dirname(__FILE__) . '/simple_html_dom.php');

class PluginLoader extends Plugin
{
	const CORE_SVN_REPO = 'http://svn.habariproject.org/habari-extras/plugins/';
	
	public function action_init()
	{
		$this->add_template('plugins.loader', dirname(__FILE__) . '/plugins.loader.php');
	}
	
	public function filter_plugin_loader($existing_loader, Theme $theme)
	{
		$infos = array();
		foreach($this->get_repos() as $repo) {
			$infos = array_merge($infos, $this->scrape_repo($repo));
		}
			//Utils::debug($theme->inactive_plugins);
		$plugins = array();
		$id = 0;
		foreach($infos as $key => $info) {
			$xinfo = reset($info);
			
			$id = sprintf( '%x', crc32( $key ) );
			$plugins[$id] = array(
				'plugind_id' => $id,
				'file' => '',
				'debug' => false,
				'info' => new SimpleXMLElement($xinfo),
				'active' => false,
				'verb' => 'Download',
			);
			foreach($info as $version => $xinfo) {
				$plugins[$id]['actions'][$version] = array(
					'url' =>  URL::get( 'admin', 'page=plugin_download&plugin_id=' . $id . '&version=' . $version ),
					'caption' => _t('Get %s', array($version)),
					'action' => 'download-',
				);
			}
		}
		$theme->loadable = $plugins;
		$loader = $theme->fetch('plugins.loader');
		
		return $existing_loader . $loader;
	}
	
	public function scrape_repo($repo)
	{
		if(Cache::has('plugin.loader.repo.infos.' . $repo)) {
			$hrefs = Cache::get('plugin.loader.repo.infos.' . $repo);
		}
		else {
			$hrefs = array();
		}
		if(Cache::has('plugin.loader.repo.dirs.' . $repo)) {
			$donedirs = Cache::get('plugin.loader.repo.dirs.' . $repo);
		}
		else {
			$donedirs = array();
		}

		// Get the list of plugins
		foreach($this->get_links($repo) as $href) {
			$plugindirs[] = $href;
		}

		$ct = 0;		
		foreach($plugindirs as $plugindir) {
			if(in_array($plugindir, $donedirs)) continue;
			if(++$ct > 5) break;  // Only do 5 at a time
			foreach($this->get_links($repo . '/' . $plugindir . '/trunk') as $href) {
				if(preg_match('#\.plugin\.xml$#i', $href)) {
					// This is a plugin's info and the current directory is the plugin directory
					$info = RemoteRequest::get_contents($repo . '/' . $plugindir . '/trunk/' . $href);
					$hrefs[$repo . $plugindir] = is_array($hrefs[$repo. $plugindir]) ? $hrefs[$repo . $plugindir] : array();
					$hrefs[$repo . $plugindir]['trunk'] = $info;
				}
			}

			foreach($this->get_links($repo . '/' . $plugindir . '/tags') as $tag) {
				foreach($this->get_links($repo . '/' . $plugindir . '/tags/' . $tag) as $href) {
					if(preg_match('#\.plugin\.xml$', $href)) {
						// This is a plugin's info in a tag and the current directory is the plugin directory
						$info = RemoteRequest::get_contents($repo . '/' . $plugindir . '/trunk/' . $href);
						$hrefs[$repo . $plugindir] = is_array($hrefs[$repo . $plugindir]) ? $hrefs[$repo . $plugindir] : array();
						$hrefs[$repo . $plugindir][$tag] = $info;
					}
				}
			}
			
			$donedirs[] = $plugindir;
		}
		if($ct > 0) {
			Cache::set('plugin.loader.repo.infos.' . $repo, $hrefs);
			Cache::set('plugin.loader.repo.dirs.' . $repo, $donedirs);
		}
		
		return $hrefs;
	}
	
	function get_links($url)
	{
		$html = RemoteRequest::get_contents($url);
		$dom = str_get_html($html);
		$as = $dom->find('a');
		$hrefs = array();
		foreach($as as $a) {
			$href = rtrim($a->getAttribute('href'), '/');
			if(strpos($href, '..') !== false || strpos($href, '/') !== false) {
			}
			else {
				$hrefs[] = $href;
			}
		}
		$dom->clear();
		return $hrefs;
	}
	
	public function get_repos()
	{
		return array('http://svn.habariproject.org/habari-extras/plugins/');
	}
	
	public function filter_admin_access_tokens( array $require_any, $page )
	{
		switch ( $page ) {
			case 'plugin_download':
				$require_any = array( 'manage_plugins' => true );
				break;
		}
		return $require_any;
	}

	public function action_admin_theme_get_plugin_download( AdminHandler $handler, Theme $theme )
	{
		$theme->page_content = '';
		$plugin_id = $handler->handler_vars['plugin_id'];
		$version = $handler->handler_vars['version'];
		
		foreach($this->get_repos() as $repo) {
			$infos = $this->scrape_repo($repo);
			foreach($infos as $infokey => $info) {
				if(sprintf( '%x', crc32( $infokey ) ) == $plugin_id) {
					$downloadurl = $infokey;
					if($version == 'trunk') {
						$downloadurl .= '/trunk/';
					}
					else {
						$downloadurl .= '/tags/' . $version . '/';
					}
					if($this->download($downloadurl, HABARI_PATH . '/user/plugins/', basename($infokey))) {
						Session::notice(_t('Downloaded the "%s" plugin.  It must now be activated before use.', array(basename($infokey))));
					}
					else {
						Session::notice(_t('There was an error downloading the "%s" plugin', array(basename($infokey))));
					}
					
					//Utils::debug($downloadurl);die();
					Utils::redirect(URL::get('admin', 'page=plugins'));
				}
			}
		}

		
	}
	
	public function download($source, $destination, $as = null)
	{
		echo '<pre>';
		var_dump($source, $destination);
		$rr = new RemoteRequest($source);
		if($rr->execute()) {
			$response = $rr->get_response_body();
			$headers = $rr->get_response_headers();
			if(isset($headers['Location']) && $headers['Location'] != $source) {
				// This should probably count redirects and bail after some max value
				return $this->download($headers['Location'], $destination);
			}
			$basename = basename($source);
			if(isset($as)) {
				$basename = $as;
			}
			if(strpos($headers['Content-Type'], 'text/html') !== false) {
				if(file_exists($destination . $basename) || mkdir($destination . $basename)) {
					//Session::notice(_t('Created the "%s" directory', array($basename)));
					
					$dom = str_get_html($response);
					$as = $dom->find('a');
					$hrefs = array();
					foreach($as as $a) {
						$href = rtrim($a->getAttribute('href'), '/');
						if(strpos($href, '..') !== false || strpos($href, '/') !== false) {
						}
						else {
							$this->download($source . $href, $destination . $basename . '/');
						}
					}
					$dom->clear();
				}
				else {
					Session::error(_t('Could not create the directory for the plugin'));
					return false;
				}
			}
			else {
				//Session::notice(_t('Downloaded "%s" to the plugin directory', array($basename)));
				file_put_contents($destination . $basename, $response);
				//file_put_contents($destination . $basename . '.header', print_r($headers,1));
			}
		}
		return true;
	}
}

?>
