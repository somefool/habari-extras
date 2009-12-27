<?php

/**
 * @todo generate nice report page in admin
 * @todo add email reports
 * @todo add cron to run DB optimization
 * @todo implement/include session manager code to keep session table clean
 * @todo check links in comments
 * @todo check for chosen type (html,xhtml,etc.) validation for post content_out
 * @todo lot's more
 */

class SiteMaintenance extends Plugin
{
	const CHECK_LINKS_CRON = 'sitemaintenance_checklinks';
	const TEXT_DOMAIN = 'sitemaintenance';
	
	public function action_init()
	{
		spl_autoload_register( array( __CLASS__, '_autoload') );
		
		// @todo move this to activation hook
		if ( ! CronTab::get_cronjob(self::CHECK_LINKS_CRON) ) {
			CronTab::add_weekly_cron( self::CHECK_LINKS_CRON, 'check_links_cron', _t('Check links in posts for errors', self::TEXT_DOMAIN) );
		}
	}
	
	public function filter_check_links_cron ( $result )
	{
		$this->check_links( Posts::get('content_type=1') );
	}
	
	public static function _autoload($class_name)
	{
		if ( strtolower( $class_name ) == 'sitemaintenancelog' ) {
			include( dirname(__FILE__) . '/sitemaintenancelog.php' );
		}
	}
	
	/**
	 * Check if links are 404 or 302
	 */
	protected function check_links( Posts $posts )
	{
		foreach ( $posts as $post ) {
			$tokenizer = new HTMLTokenizer($post->content_out, false);
			$nodes = $tokenizer->parse();
			$urls = array();
			
			foreach ( $nodes as $node ) {
				if ( $node['type'] == HTMLTokenizer::NODE_TYPE_ELEMENT_OPEN && strtolower($node['name']) == 'a' ) {
					$urls[] = $node['attrs']['href'];
				}
			}
			
			if ( count($urls) > 0 ) {
				$urls = array_unique($urls);
				foreach ( $urls as $url ) {
					$request = new RemoteRequest($url, 'HEAD');
					$request->execute();
					if ( $request->executed() ) {
						$headers = explode("\r\n", $request->get_response_headers(), 2);
						$status = $headers[0];
						// is it 404 not found?
						if ( strpos($status, '404') !== FALSE ) {
							$message = _t("404 at %s in post %s, got: %s", array($url, $post->slug, $status), 'sitemaintenance');
							$severity = 'err';
							$type = '404';
							$data = $headers;
							SiteMaintenanceLog::report_log($message, $severity, $type, $data = null);
						}
						// is it 301 moved permanently?
						elseif ( strpos($status, '301') !== FALSE ) {
							foreach ( $headers as $header ) {
								if ( preg_match('#location:\s*(.+)#i', $header, $m) ) {
									$location = $m[1];
								}
								else {
									$location = _t('unknown', self::TEXT_DOMAIN);
								}
							}
							$message = _t("301 at %s in post %s, moved to: %s", array($url, $post->slug, $location), 'sitemaintenance');
							$severity = 'err';
							$type = '301';
							$data = $headers;
							SiteMaintenanceLog::report_log($message, $severity, $type, $data = null);
						}
					}
				}
			}
		}
	}
}
?>
