<?php
/**
 * Calendar
 *
 * @package calendar
 * @version $Id$
 * @author ayunyan <ayu@commun.jp>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link http://ayu.commun.jp/habari-calendar
 */
class Calendar extends Plugin
{
	private $week_starts = array(6 => 'Saturday', 0 => 'Sunday', 1 => 'Monday');

	/**
	 * plugin information
	 *
	 * @access public
	 * @retrun void
	 */
	public function info()
	{
		return array(
			'name' => 'Calendar',
			'version' => '0.01-alpha',
			'url' => 'http://ayu.commun.jp/habari-calendar',
			'author' => 'ayunyan',
			'authorurl' => 'http://ayu.commun.jp/',
			'license' => 'Apache License 2.0',
			'description' => 'Displays calendar on your blog.',
			'guid' => '4f97ad6b-c0a5-11dd-aff6-001b210f913f'
			);
	}

	/**
	 * action: plugin_activation
	 *
	 * @access public
	 * @param string $file
	 * @return void
	 */
	public function action_plugin_activation($file)
	{
		if (Plugins::id_from_file($file) != Plugins::id_from_file(__FILE__)) return;

		Options::set('calendar__week_start', 0);
	}

	/**
	 * action: init
	 *
	 * @access public
	 * @return void
	 */
	public function action_init()
	{
		$this->load_text_domain('calendar');

		$this->add_template('calendar', dirname(__FILE__) . '/templates/calendar.php');
	}

	/**
	 * action: update_check
	 *
	 * @access public
	 * @return void
	 */
	public function action_update_check()
	{
		Update::add($this->info->name, $this->info->guid, $this->info->version);
	}

	/**
	 * action: plugin_ui
	 *
	 * @access public
	 * @param string $plugin_id
	 * @param string $action
	 * @return void
	 */
	public function action_plugin_ui($plugin_id, $action)
	{
		if ($plugin_id != $this->plugin_id()) return;
		if ($action == _t('Configure')) {
			$form = new FormUI(strtolower(get_class($this)));
			$form->append('select', 'week_start', 'calendar__week_start', _t('Week starts on: ', 'calendar'), $this->week_starts);
			$form->append('submit', 'save', _t('Save'));
			$form->out();
		}
	}

	/**
	 * filter: plugin_config
	 *
	 * @access public
	 * @return array
	 */
	public function filter_plugin_config($actions, $plugin_id)
	{
		if ($plugin_id == $this->plugin_id()) {
			$actions[] = _t('Configure');
		}
		return $actions;
	}

	/**
	 * theme: show_calendar
	 *
	 * @access public
	 * @param object $theme
	 * @return string
	 */
	public function theme_show_calendar($theme)
	{
		$handler_vars = Controller::get_handler_vars();
		if (Controller::get_action() == 'display_date' && isset($handler_vars['month'])) {
			$year = (int)$handler_vars['year'];
			$month = (int)$handler_vars['month'];
		} else {
			$year = date('Y');
			$month = date('m');
		}

		$next_year = $year;
		$prev_year = $year;
		$next_month = $month + 1;
		$prev_month = $month - 1;
		if ($next_month == 13) {
			$next_month = 1;
			$next_year++;
		}
		if ($prev_month == 0) {
			$prev_month = 12;
			$prev_year--;
		}

		$start_time = mktime(0, 0, 0, $month, 1, $year);
		$end_time = mktime(0, 0, 0, $next_month, 1, $next_year);
		$t_posts_day = DB::get_column('SELECT pubdate FROM {posts} WHERE pubdate >= ? AND pubdate < ? AND status = ?', array($start_time, $end_time, Post::status('published')));
                $posts_day = array();
		@reset($t_posts_day);
		while (list(, $pubdate) = @each($t_posts_day)) {
                    $posts_day[] = (int)date("j", $pubdate);
		}

		$month_start_week = date("w", $start_time);
		$month_days = date("t", $start_time);
		$week_days = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
		$week_start = Options::get('calendar__week_start');

		$calendar = array();
		$day = 0;
		while (1) {
			$week = array();

			for ($i = 0; $i < 7; $i++) {
				$wday = ($i + $week_start) % 7;
				if (count($calendar) == 0) {
					$week[] = array('label' => $week_days[($i + $week_start) % 7]);
                                        continue;
				} elseif ($day == 0) {
					if ($wday == $month_start_week) {
						$day = 1;
						if (array_search($day, $posts_day) !== false) {
							$week[] = array('label' => $day, 'url' => URL::get('display_entries_by_date', array('year' => $year, 'month' => sprintf('%02d', $month), 'day' => sprintf('%02d', $day))));
						} else {
							$week[] = array('label' => $day);
						}
					} else {
						$week[] = array('label' => null);
						continue;
					}
				} elseif ($day >= $month_days) {
					$week[] = array('label' => null);
					continue;
				} else {
					$day++;
					if (array_search($day, $posts_day) !== false) {
						$week[] = array('label' => $day, 'url' => URL::get('display_entries_by_date', array('year' => $year, 'month' => sprintf('%02d', $month), 'day' => sprintf('%02d', $day))));
					} else {
						$week[] = array('label' => $day);
					}
				}
			}
			$calendar[] = $week;
			if ($day == $month_days) break;
		}

		$theme->year = $year;
		$theme->month = $month;
		$theme->prev_month_url = URL::get('display_entries_by_date', array('year' => $prev_year, 'month' => sprintf('%02d', $prev_month)));
		$theme->next_month_url = URL::get('display_entries_by_date', array('year' => $next_year, 'month' => sprintf('%02d', $next_month)));
		$theme->calendar = $calendar;

		return $theme->fetch('calendar');
	}
}
?>