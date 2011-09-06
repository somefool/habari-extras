<?php

/*
 * Monthly Archives Plugin
 * Usage: <?php $theme->monthly_archives(); ?>
 *
 * Modified / fixed from the original by Raman Ng (a.k.a. tinyau): http://blog.tinyau.net
 * http://blog.tinyau.net/archives/2007/09/22/habari-rn-monthly-archives-plugin
 */

class Monthly_Archives extends Plugin
{
	private $monthly_archives = ''; // stores the actual archives list
	private $config = array(); // stores our config options

	private $cache_expiry = 604800; // one week, in seconds: 60 * 60 * 24 * 7

	public function action_plugin_deactivation ( $file = '' ) {

		if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {

			$class_name = strtolower( get_class( $this ) );

			// dump our cached list
			Cache::expire( $class_name . ':list' );

		}

	}

	public function __get( $name ) {

		$options = array(
			'num_month' => 'archives__num__month',
			'display_month' => 'archives__display__month',
			'show_count' => 'archives__show__count',
			'detail_view' => 'archives__detail__view',
			'delimiter' => 'archives__delimiter'
		);

		return Options::get( $options[$name] );

	}

	public function filter_plugin_config ( $actions, $plugin_id ) {

		if ( $plugin_id == $this->plugin_id() ) {
			$actions[]= _t( 'Configure' );
		}

		return $actions;

	}

	/*
	 * Rebuilds the archives structure when a post is updated.
	 * @todo Perhaps this should be a cron. With lots of posts, it could take longer than we'd like to wait.
	 */
	public function action_post_update_after ( ) {

		$this->update_cache();

	}

	/*
	 * Rebuilds the archives structure when a post is published.
	 * @todo Perhaps this should be a cron. With lots of posts, it could take longer than we'd like to wait.
	 */
	public function action_post_insert_after ( ) {

		$this->update_cache();

	}

	private function update_cache ( ) {

		$archives = $this->get_monthly_archives();

		$class_name = strtolower( get_class( $this ) );

		Cache::set( $class_name . ':list', $archives, $this->cache_expiry );

		$this->monthly_archives = $archives;

	}

	public function theme_get_monthly_archives ( ) {

		return $this->get_monthly_archives();

	}

	protected function get_monthly_archives ( ) 
	{
	        if( !ini_get( 'safe_mode' ) ) {
		  set_time_limit( ( 5 * 60 ) );
	        }

		if ( !empty( $this->num_month ) ) {
			$limit = 'LIMIT 0, ' . $this->num_month;
		}
		else {
			$limit = '';
		}

		$q = "SELECT YEAR( FROM_UNIXTIME(pubdate) ) AS year, MONTH(  FROM_UNIXTIME(pubdate)  ) AS month, COUNT( id ) AS cnt
				FROM  {posts}
				WHERE content_type = ? AND status = ?
				GROUP BY year, month
				ORDER BY pubdate DESC {$limit}";
		$p[]= Post::type( 'entry' );
		$p[]= Post::status( 'published' );
		$results = DB::get_results( $q, $p );

		if ( empty( $results ) ) {
			$archives[]= '<ul id="monthly_archives">';
			$archives[]= '  <li>No Archives Found</li>';
			$archives[]= '</ul>';
		}
		else {

			$archives[]= '<ul id="monthly_archives">';

			foreach ( $results as $result ) {

				// make sure the month has a 0 on the front, if it doesn't
				$result->month = str_pad( $result->month, 2, 0, STR_PAD_LEFT );

				// use first day of the month to prevent doubling bug (see extras ticket #220)
				$result->month_ts = mktime( 0, 0, 0, $result->month, 1 );

				// what format do we want to show the month in?
				switch ( $this->display_month ) {

					// Full name
					case 'F':
						$result->display_month = date( 'F', $result->month_ts );
						break;

					// Abbreviation
					case 'A':
						$result->display_month = date( 'M', $result->month_ts );
						break;

					// Number
					case 'N':
					default:
						$result->display_month = $result->month;
						break;

				}

				// do we want to show the count of posts?
				if ( $this->show_count == 'Y' ) {
					$result->the_count = ' (' . $result->cnt . ')';
				}
				else {
					$result->the_count = '';
				}

				$archives[]= '  <li>';
				$archives[]= '    <a href="' . URL::get( 'display_entries_by_date', array( 'year' => $result->year, 'month' => $result->month ) ) . '" title="View entries in ' . $result->display_month . '/' . $result->year . '">' . $result->display_month . ' ' . $result->year . '</a>' . $result->the_count;

				// do we want to show all the posts as well?
				if ( $this->detail_view == 'Y' ) {

					$psts = Posts::get(
						array(
							'content_type' => Post::type( 'entry' ),
							'status' => Post::status( 'published' ),
							'year' => $result->year,
							'month' => $result->month,
							'nolimit' => true
						)
					);

					if ( $psts ) {

						$archives[]= '    <ul class="archive_entry">';

						foreach ( $psts as $pst ) {

							$day = $pst->pubdate->format('d');

							if ( empty( $this->delimiter ) ) {
								$delimiter = '&nbsp;&nbsp;';
							}
							else {
								$delimiter = $this->delimiter;
							}

							$archives[]= '      <li>';
							$archives[]= '        ' . $day . $delimiter . '<a href="' . $pst->permalink . '" title="View ' . $pst->title . '">' . $pst->title . '</a>';
							$archives[]= '      </li>';

						}

						$archives[]= '    </ul>';

					}

				}

				$archives[]= '  </li>';

			}

			$archives[]= '</ul>';

		}

		return implode( "\n", $archives );

	}

	public function action_plugin_ui ( $plugin_id, $action ) {

		if ( $plugin_id == $this->plugin_id() ) {

			if ( $action == _t( 'Configure' ) ) {

				$class_name = strtolower( get_class( $this ) );

				$form = new FormUI( $class_name );

				$form->append( 'text', 'num_month', 'archives__num__month', _t( 'Number of most recent months to be shown (Blank for show all)' ) );

				$form->append( 'select', 'display_month', 'archives__display__month', _t( 'Month displayed as' ) );
				$form->display_month->options = array( '' => '', 'F' => 'Full name', 'A' => 'Abbreviation', 'N' => 'Number' );

				$form->append( 'select', 'show_count', 'archives__show__count', _t( 'Show Monthly Entries Count?' ) );
				$form->show_count->options = array( '' => '', 'Y' => 'Yes', 'N' => 'No' );
				$form->show_count->add_validator( 'validate_required' );

				$form->append( 'select', 'detail_view', 'archives__detail__view', _t( 'Detail View?' ) );
				$form->detail_view->options = array( '' => '', 'Y' => 'Yes', 'N' => 'No' );
				$form->detail_view->add_validator( 'validate_detail_view' );

				$form->append( 'text', 'delimiter', 'archives__delimiter', _t( 'Delimiter to separate day and post title in detail view (optional)' ) );
				$form->delimiter->add_validator( 'validate_delimiter' );

				$form->append( 'submit', 'save', _t( 'Save' ) );

				$form->on_success( array( $this, 'updated_config' ) );
				$form->out();

			}

		}

	}

	public function updated_config ( $form ) {

		// Save the form data so it's used in the cached data we're about to generate
		$form->save();
		// when the config has been updated, we need to update our cache - things could have changed
		$this->update_cache();

	}

	public function filter_validate_detail_view ( $valid, $value ) {

		if ( empty( $value ) || $value == '' ) {

			return array( _t( 'A value for this field is required. ' ) );

		}

		Options::set('archives__detail__view', $value);

		return array();

	}

	public function theme_monthly_archives ( $theme ) {

		$class_name = strtolower( get_class( $this ) );

		// first, see if we have the list already cached
		if ( Cache::has( $class_name . ':list' ) ) {

			$this->monthly_archives = Cache::get( $class_name . ':list' );

			return $this->monthly_archives;

		}
		else {

			// update the cache
			$this->update_cache();

			return $this->monthly_archives;

		}

	}

}

?>
