<?php

/**
 * Clickheat is a heatmap generator for Habari.
 * You can also use the javascript and add to external,
 * non-habari pages if you want.
 *
 * Include this file in header:
 * http://path/to/habari/scripts/clickheat.js
 * And put this in the footer:
 * <script type="text/javascript">clickheat.init();</script>
 */
class Clickheat extends Plugin
{

	private $screenSizes = array(
		0 => 'Full',
		640 => 640,
		800 => 800,
		1024 => 1024,
		1280 => 1280,
		1440 => 1440,
		1600 => 1600,
		1800 => 1800
	);
	private $groups;
	private $is_clickheat= false;
	private $logs, $cache;


	/**
	 * Plugin info
	 */
	public function info()
	{
		return array(
			'name' => 'Clickheat',
			'version' => '1.0',
			'url' => 'http://www.xvolter.com/project/habari-clickheat/',
			'author' => 'Benjamin Hutchins',
			'authorurl' => 'http://www.xvolter.com',
			'license' => 'MIT License',
			'description' => 'Generate clickheat graphs based off your visitors.'
		);
	}


	/**
	 * Initialize by added directory variables
	 */
	public function action_init()
	{
		$this->logs= dirname(__FILE__) . '/logs';
		$this->cache= dirname(__FILE__) . '/cache';

		if ( ! $this->confirm_dirs($error) ) {
			Session::error( "Clickheat error: $error" );
			Plugins::deactivate_plugin(__FILE__); // Deactivate plugin
			Utils::redirect(); //Refresh page
			exit();
		}
	}


	/**
	 * Add default options when plugin is activated
	 */
	public function action_plugin_activation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			Options::set( 'clickheat__week_start_on_monday', false );
			Options::set( 'clickheat__yesterday', false );
			Options::set( 'clickheat__quota', -1 );
			Options::set( 'clickheat__wait', 500 );
			Options::set( 'clickheat__memory', 16 );
			Options::set( 'clickheat__palette', false );
			Options::set( 'clickheat__dot_size', 19 );
			Options::set( 'clickheat__step', 5 );
			Options::set( 'clickheat__heatmap', true );
			Options::set( 'clickheat__rainbow', true );
			Options::set( 'clickheat__flush', 40 );
		}
	}


	/**
	 * Add configure tab to action list
	 */
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() )
			$actions[]= _t( 'Configure' );

		return $actions;
	}


	/**
	 * Create the configuration FromUI
	 */
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() )
		{
			switch ( $action ) {
				case _t( 'Configure' ):
					$ui= new FormUI( strtolower( get_class( $this ) ) );

					$ui->append( 'checkbox', 'heatmap', 'option:clickheat__heatmap', _t( 'Use heatmap by default?' ));
					$ui->append( 'checkbox', 'week_start_on_monday', 'option:clickheat__week_start_on_monday', _t( 'Weeks start on Monday?' ));
					$ui->append( 'checkbox', 'yesterday', 'option:clickheat__yesterday', _t( 'Show yesterday\'s stats by default?' ));
					$ui->append( 'text', 'quota', 'option:clickheat__quota', _t( 'Click quota per person, per page (-1 = unlimited)' ));
					$ui->append( 'text', 'wait', 'option:clickheat__wait', _t( 'Wait time after click to verify success' ));
					$ui->append( 'text', 'memory', 'option:clickheat__memory', _t( 'Memory limit in megabytes' ));
					$ui->append( 'checkbox', 'palette', 'option:clickheat__palette', _t( 'If you see red squares on heatmaps check this box' ));
					$ui->append( 'text', 'dot_size', 'option:clickheat__dot_size', _t( 'Heatmap dot size (pixels)' ));
					$ui->append( 'text', 'step', 'option:clickheat__step', _t( 'Group clicks within X*X pixel zones (speed up display of heatmaps)' ));
					$ui->append( 'checkbox', 'rainbow', 'option:clickheat__rainbow', _t( 'Show rainbow click count in heatmaps?' ));
					$ui->append( 'text', 'flush', 'option:clickheat__flush', _t( 'Automatic flush of statistics older than X days (0 = keep all files, not recommended)' ));

					$ui->append( 'submit', 'save', 'Save' );
					$ui->out();
				break;
			}
		}
	}


	/**
	 * Add rewrite rules
	 */
	public function filter_rewrite_rules( $db_rules )
	{
		// clickheat admin javascript
		$db_rules[] = new RewriteRule ( array(
			'name' => 'clickheat__admin_js',
			'parse_regex' => '%^3rdparty/clickheat\.js$%i',
			'build_str' => '3rdparty/clickheat.js',
			'handler' => 'UserThemeHandler',
			'action' => 'clickheat_admin_js',
			'priority' => 7,
			'is_active' => 1,
			'rule_class' => RewriteRule::RULE_CUSTOM,
			'description' => 'Clickheat: Admin JavaScript Output'
			) );

		// clickheat stylesheet
		$db_rules[] = new RewriteRule ( array(
			'name' => 'clickheat__admin_js',
			'parse_regex' => '%^3rdparty/clickheat\.css$%i',
			'build_str' => 'scripts/clickheat.css',
			'handler' => 'UserThemeHandler',
			'action' => 'clickheat_css',
			'priority' => 7,
			'is_active' => 1,
			'rule_class' => RewriteRule::RULE_CUSTOM,
			'description' => 'Clickheat: Stylesheet Output'
			) );

		// clickheat theme javascript
		$db_rules[] = new RewriteRule ( array(
			'name' => 'clickheat__js',
			'parse_regex' => '%^scripts/clickheat\.js$%i',
			'build_str' => 'scripts/clickheat.js',
			'handler' => 'UserThemeHandler',
			'action' => 'clickheat_js',
			'priority' => 7,
			'is_active' => 1,
			'rule_class' => RewriteRule::RULE_CUSTOM,
			'description' => 'Clickheat: JavaScript Output'
			) );
		return $db_rules;
	}


	/**
	 * Here we add a menu item for clickheat
	 * Position it after "Logs"
	 */
	public function filter_adminhandler_post_loadplugins_main_menu( $mainmenus ) {
		$menu= array();
		foreach($mainmenus as $k=>$m) {
			$menu[$k]= $m;
			if ( $k == 'logs' )
				$menu['clickheat'] = array(
					'url' => URL::get( 'admin', 'page=clickheat' ),
					'title' => _t( 'Clickheat' ),
					'text' => _t( 'Clickheat' ),
					'hotkey' => 'C',
					'selected' => false
				);
		}

		return $menu;
	}


	/**
	 * Send out theme header and footer addons
	 */
	public function action_template_header()
	{
		Stack::add('template_header_javascript', Site::get_url( 'scripts' ) . '/clickheat.js', 'clickheat');
	}
	public function action_template_footer()
	{
		echo "<script type=\"text/javascript\">clickheat.init();</script>";
	}


	/**
	 * Get list of available groups
	 */
	private function load_groups()
	{
		$groups = array();

		// Get all groups
		$glob = Utils::glob( $this->logs . '/*/url.xml' );

		foreach( $glob as $file ) {
			$dir= dirname( $file );
			$dir= substr($dir, strrpos($dir, "/")+1); // remove full path
			$xml= simplexml_load_file( $file );
			$groups[ $dir ] = "{$xml->title} ({$xml->url})";
		}

		$this->groups= $groups;
	}


	/**
	 * Create an HTML table calendar for date selection
	 */
	private function create_calendar()
	{
		$weekStartOnMonday= Options::get( 'clickheat__week_start_on_monday' );

		$cal = '<table class="clickheat-calendar"><tr>';
		$days = explode(',', 'M,T,W,T,F,S,S');
		for ($d = 0; $d < 7; $d++)
		{
			$D = $d + ($weekStartOnMonday ? 0 : 6);
			if ($D > 6)
				$D -= 7;

			$cal .= '<th>'.$days[$D].'</th>';
		}
		$cal .= '</tr><tr>';

		$before = date('w', mktime(0, 0, 0, $this->month, 1, $this->year)) - ($weekStartOnMonday ? 1 : 0);
		if ($before < 0)
			$before += 7;

		$this->lastDayOfMonth = date('t', mktime(0, 0, 0, $this->month - 1, 1, $this->year));
		for ($d = 1; $d <= $before; $d++)
			$cal .= '<td id="clickheat-calendar-10'.$d.'">'.($this->lastDayOfMonth - $before + $d).'</td>';

		$cols = $before - 1;
		$this->js = 'var weekDays = [';
		for ($d = 1, $days = date('t', $this->date); $d <= $days; $d++) {
			$D = mktime(0, 0, 0, $this->month, $d, $this->year);
			if (++$cols === 7) {
				$cal .= '</tr><tr>';
				$cols = 0;
			}

			$cal .= '<td id="clickheat-calendar-'.$d.'"><a href="#" onclick="clickheat.updateCalendar('.$d.'); return false;">'.$d.'</a></td>';
			$this->js .= ','.(date('W', $D) + (date('w', $D) == 0 && !$weekStartOnMonday ? 1 : 0));
		}
		$this->js .= '];';

		for ($d = 1; $cols < 6; $cols++, $d++)
			$cal .= '<td id="clickheat-calendar-11'.$d.'">'.$d.'</td>';

		$cal .= '</tr></table>';
		return $cal;
	}


	/**
	 * Handle admin header and footer
	 */
	public function action_admin_header()
	{
		if ( $this->is_clickheat ) {
			Stack::add('admin_header_javascript', Site::get_url( 'habari' ) . '/3rdparty/clickheat.js', 'clickheat');
			Stack::add('admin_stylesheet', array(Site::get_url( 'habari' ) . '/3rdparty/clickheat.css', 'screen'), 'clickheat');
		}
	}
	public function action_admin_footer()
	{
		if ( $this->is_clickheat )
			echo "<script type=\"text/javascript\">{$this->js}clickheat.lastDayOfMonth={$this->lastDayOfMonth};clickheat.date=[{$this->day},{$this->month},{$this->year},{$this->day},{$this->month},{$this->year}];clickheat.init();</script>";
	}


	/**
	 * Handle clickheat page
	 */
	public function action_admin_theme_post_clickheat( $handler, $theme )
	{
		$this->action_admin_theme_get_clickheat( $handler, $theme );
	}
	public function action_admin_theme_get_clickheat( $handler, $theme )
	{
		$vars= $handler->handler_vars;
		$this->is_clickheat= true;
		$this->theme= $theme;

		// load required items from logs
		$this->load_groups();

		// confirm group count
		if ( count($this->groups) == 0 ) {
			$this->is_clickheat= false;
			$theme->display( 'header' );
			echo "<div class=\"container\"><span class=\"error\">" . _t("No logs exist. Please wait until someone clicks somewhere.") . "</div>";
			$theme->display( 'footer' );
			exit();
		}

		// Date 
		$this->date = isset($vars['date']) ? strtotime($vars['date']) : (Options::get( 'clickheat__yesterday' ) ? mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')) : false);
		if ($this->date === false)
			$this->date = time();

		$this->day = (int) date('d', $this->date);
		$this->month = (int) date('m', $this->date);
		$this->year = (int) date('Y', $this->date);

		$option_fields= array(
			'group' => array(
				'label' => _t('Group'),
				'type' => 'select',
				'selectarray' => $this->groups,
				),
			'screen' => array(
				'label' => _t('Screen Size'),
				'type' => 'select',
				'selectarray' => $this->screenSizes
				),
			'heatmap' => array(
				'label' => _t('Heatmap'),
				'type' => 'checkbox',
				'value' => Options::get( 'clickheat__heatmap' )
				),
			);

		$form = new FormUI('Clickheat View');

		foreach ( $option_fields as $option_name => $option ) {
			$field = $form->append( $option['type'], $option_name, $option_name, $option['label'] );
			$field->template = 'optionscontrol_' . $option['type'];
			$field->class = 'item clear nomargin';
			if ( $option['type'] == 'select' && isset( $option['selectarray'] ) ) {
				$field->options = $option['selectarray'];
			} else {
				$field->value= $option['value'];
			}
		}

		$field = $form->append('static', 'alpha', '<div class="item clear nomargin" id="alpha">
					<span class="pct25"><label>' . _t('Transparency') . '</label></span>
					<span class="pct25"><span id="alphaSelector"></span></span>
					</div>');

		$theme->form= $form->get();
		require "view.php";
		exit();
	}


	/**
	 * Send out stylesheet for admin
	 */
	public function filter_theme_act_clickheat_css()
	{
		header("Content-Type: text/css");
		require dirname(__FILE__) . "/clickheat.css";
		exit();
	}


	/**
	 * Send out clickheat for admin
	 */
	public function filter_theme_act_clickheat_admin_js()
	{
		header("Content-Type: text/javascript");
		echo "habari.url.clickheat='" . URL::get('ajax', 'context=clickheat') . "';\n";
		require dirname(__FILE__) . "/js/admin.js";
		exit();
	}


	/**
	 * Sent out clickheat for users
	 */
	public function filter_theme_act_clickheat_js()
	{
		header("Content-Type: text/javascript");
		require dirname(__FILE__) . "/js/clickheat.js";
		exit();
	}


	/**
	 * Make sure log directories exist
	 */
	private function confirm_dirs( &$error, $dir = null, $vars = array() )
	{
		// make sure logs directory exists
		if ( !is_dir( $this->logs ) ) {
			if ( !mkdir( $this->logs) ) {
				$error= _t("Logs directory doesn't exist and cannot be created. Please create the directory: {$this->logs}");
				return false;
			}
		}

		// make sure logs is writeable
		if ( ! is_writeable( $this->logs ) ) {
			$error= _t("Logs directory is not writable, please give write ability to: {$this->logs}");
			return false;
		}

		// make sure cache directory exists
		if ( !is_dir( $this->cache ) ) {
			if ( !mkdir( $this->cache ) ) {
				$error= _t("Cache directory doesn't exist and cannot be created. Please create the directory: {$this->cache}");
				return false;
			}
		}

		// make sure cache is writeable
		if ( ! is_writeable( $this->logs ) ) {
			$error= _t("Cache directory is not writable, please give write ability to: {$this->cache}");
			return false;
		}

		// make sure we're not just checking the logs directory
		if ( $dir === null ) return true;

		// make sure group directory exists
		if ( !is_dir( $this->logs . "/$dir" ) ) {
			if ( !mkdir($this->logs . "/$dir" ) ) {
				$error= _t("Cannot create log directory: {$this->logs}/{$dir}");
				return false;
			}
		}

		// Create the url.xml
		$xml= new SimpleXMLElement('<root></root>');
		$xml->addChild('url', $vars['href']);
		$xml->addChild('title', $vars['title']);

		// Save the url.xml
		$f = fopen( "{$this->logs}/{$dir}/url.xml", 'w' );
		fputs($f, $xml->asXML());
		fclose($f);
		return true;
	}


	/**
	 * Handle all Ajax requests
	 */
	public function action_ajax_clickheat( $handler )
	{
		$vars= $handler->handler_vars;

		switch( $vars['action'] )
		{
			case 'click':
				// Check parameters
				if (!isset($vars['x']) || !isset($vars['y']) || !isset($vars['w']) || !isset($vars['href']) || !isset($vars['title']) || !isset($vars['which']))
					exit('Parameters or config error');

				// Format href to a directory name
				$dir= preg_replace("/^http(s)?\:\/\/(www)?(\.)?/", "", strtolower($vars['href'])); // remove http(s)://www.
				$dir= explode("?", $dir); $dir= $dir[0]; // remove query string
				$dir= rtrim(preg_replace('/[^a-z_0-9\-]+/', '.', $dir), "."); // remove odd characters

				// Confirm directories
				$status= $this->confirm_dirs( $error, $dir, $vars );
				if ( $status === false ) exit ( $error );

				// Log the click
				$f = fopen($this->logs . "/$dir/" . date('Y-m-d') . '.log', 'a');
				fputs($f, ((int) $vars['x']) . '|' . ((int) $vars['y']) . '|' . ((int) $vars['w']) . "|" . ((int) $vars['which']) . "\n");
				fclose($f);
				echo 'success';
				break;

			case 'cleaner':
				echo $this->cleaner();
				break;
				
			case 'generate':
				echo $this->generate( $vars );
				break;

			case 'iframe':
				if ( !isset( $vars['group'] ) ) break;
				$group= $this->logs . '/' . $vars['group'];
				if ( !is_dir( $group ) ) break;

				$file= "$group/url.xml";
				if ( file_exists( $file ) ) {
					$xml= simplexml_load_file( $file );
					echo $xml->url;
				} else {
					echo "/";
				}
				break;

			case 'png':
				$imagePath= $this->cache . "/" . (isset($vars['file']) ? $vars['file'] : '**unknown**');

				header('Content-Type: image/png');
 				readfile( file_exists($imagePath) ? $imagePath : (dirname(__FILE__) . '/warning.png') );
			break;

		}

		exit();
	}


	/**
	 * Generate image
	 */
	private function generate( $vars )
	{
		/**
 		* Class files
		 */
		include dirname(__FILE__) . '/classes/Heatmap.class.php';
		include dirname(__FILE__) . '/classes/HeatmapFromClicks.class.php';

		/**
		 * Screen size
		 */
		$screen = isset($vars['screen']) ? (int) $vars['screen'] : 0;
		$minScreen = 0;
		if ($screen < 0) {
			$width = abs($screen);
			$maxScreen = 3000;
		} else {
			$maxScreen = $screen;
			if (!in_array($screen, $this->screenSizes) || $screen === 0)
				$this->error( _t('Non-standard screen size') . ": $screen" );

			$psize= 0;
			foreach($this->screenSizes as $size) {
				if ($size === $screen) {
					$minScreen = $psize;
					break;
				}
				$psize= $size;
			}
			$width = $screen - 25;
		}

		/**
		 * Time and memory limits
		 */
		$memory= Options::get( 'clickheat__memory' );
		@set_time_limit(120);
		@ini_set('memory_limit', $memory . 'M');

		/**
		 * Selected Group
		 */
		$group = isset($vars['group']) ? $vars['group'] : false;
		if ( $group === false || !is_dir( $this->logs . "/" . $group) )
			return $this->error( _t('Unknown group') );

		/**
		 * Show clicks or heatmap
		 */
		$heatmap = (isset($vars['heatmap']) && $vars['heatmap'] == 1);

		/**
		 * Date and days
		 */
		$time = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();
		$dateStamp = isset($vars['date']) ? strtotime($vars['date']) : $time;
		$range = isset($vars['range']) && in_array($vars['range'], array('d', 'w', 'm')) ? $vars['range'] : 'd';
		$date = date('Y-m-d', $dateStamp);
		switch ($range) {
			case 'd':
				$days = 1;
				$delay = date('dmy', $dateStamp) !== date('dmy') ? 86400 : 120;
				break;

			case 'w':
				$days = 7;
				$delay = date('Wy', $dateStamp) !== date('Wy') ? 86400 : 120;
				break;

			case 'm':
				$days = date('t', $dateStamp);
				$delay = date('my', $dateStamp) !== date('my') ? 86400 : 120;
				break;
		}

		$imagePath = $group.'-'.$date.'-'.$range.'-'.$screen.'-'.'-'.($heatmap ? 'heat' : 'click');
		$htmlPath = $this->cache . "/" . $imagePath.'.html';

		/**
		 * If images are already created,
		 * just stop script here if these have less
		 * than 120 seconds (today's log) or 86400 seconds (old logs)
		 */
		if (file_exists($htmlPath) && filemtime($htmlPath) > $time - $delay) {
			readfile($htmlPath);
			exit();
		}

		/**
		 * Call the Heatmap class
		 */
		$clicksHeatmap = new HeatmapFromClicks();
		$clicksHeatmap->minScreen = $minScreen;
		$clicksHeatmap->maxScreen = $maxScreen;
		$clicksHeatmap->memory = $memory * 1048576;
		$clicksHeatmap->step = Options::get( 'clickheat__step' );
		$clicksHeatmap->dot = Options::get( 'clickheat__dot_size' );
		$clicksHeatmap->palette = Options::get( 'clickheat__palette' );
		$clicksHeatmap->rainbow = Options::get( 'clickheat__rainbow' );
		$clicksHeatmap->heatmap = $heatmap;
		$clicksHeatmap->path = $this->cache;
		$clicksHeatmap->cache = $this->cache;
		$clicksHeatmap->file = $imagePath.'-%d.png';

		/**
		 * Add files
		 */
		for ($day = 0; $day < $days; $day++) {
			$currentDate = date('Y-m-d', mktime(0, 0, 0, date('m', $dateStamp), date('d', $dateStamp) + $day, date('Y', $dateStamp)));
			$clicksHeatmap->addFile($this->logs . "/$group/$currentDate.log");
		}

		if ( ( $result = $clicksHeatmap->generate($width) ) === false)
			return $this->error( $clicksHeatmap->error );

		$html = '';
		for ($i = 0; $i < $result['count']; $i++)
			$html .= '<img src="'. URL::get('ajax', array('context'=>'clickheat', 'action'=>'png', 'file'=>$result['filenames'][$i], 'rand'=>$time)) . '"
				width="'.$result['width'].'"
				height="'.$result['height'].'"
				alt=""
				id="heatmap-'.$i.'" /><br />';

		/**
 		* Save the HTML code to speed up following queries (only over two minutes)
		 */
		$f = fopen($htmlPath, 'w');
		fputs($f, $html);
		fclose($f);

		return $html;
	}


	/**
	 * Delete old logs
	 */
	private function cleaner()
	{
		$deletedFiles = 0;
		$deletedDirs = 0;

		/**
		 * Clean the logs' directory according to configuration data
		 */
		$flush= Options::get( 'clickheat__flush' );
		if ($flush >= 0)
		{
			$logs= Utils::glob( $this->logs . "/*/*.log" );
			$oldestDate= mktime(0, 0, 0, date('m'), date('d') - $flush, date('Y'));
			$deletedAll= array();

			foreach($logs as $log) {
				// dont process .htaccess
				if (count(explode('.', $log)) !== 2)
					continue;

				$dir= dirname( $log );
				if ( !isset( $deletedAll[ $dir ] ) )
					$deletedAll[ $dir ]= true;

				if (filemtime($log) <= $oldestDate) {
					@unlink($log);
					$deletedFiles++;
					continue;
				}

				$deletedAll[$dir]= false;
			}

			/**
			 * If every log file (but the url.txt) has been deleted,
			 * then we should delete the directory too
			 */
			foreach ($deletedAll as $dir=>$do) {
				if ($do === true) {
					@unlink( $dir . '/url.xml');
					$deletedFiles++;
					@rmdir($dir);
					$deletedDirs++;
				}
			}
		}

		/**
		 * Clean the cache directory for every file older than 2 minutes
		 * 2 minutes is to make sure images are kept up to date with all
		 * new clicks.
		 */
		$glob= Utils::glob($this->cache . "/*");
		foreach( $glob as $file ) {	
			$ext = explode('.', $file);

			// dont process .htaccess
			if (count($ext) !== 2)
				continue;

			$filemtime = filemtime($file);
			switch ($ext[1])
			{
				case 'html':
				case 'png':
				case 'png_temp':
				case 'png_log':
					if ($filemtime + 86400 < time()) {
						@unlink($file);
						$deletedFiles++;
					}
			}
		}

		// Did we delete anything?
		if ($deletedDirs + $deletedFiles === 0)
			return 'OK';
		else
			return sprintf(_t("Cleaning finished: %d files and %d directories have been deleted"), $deletedFiles, $deletedDirs);
	}


	/**
	 * Put an error in some HTML
	 */
	private function error( $msg )
	{
		return "<span class='error'>$msg</span>";
	}

}

?>
