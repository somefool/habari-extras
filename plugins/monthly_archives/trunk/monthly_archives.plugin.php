<?php

/*
 * Monthly Archives Plugin
 * Usage: <?php $theme->monthly_archives(); ?>
 * 
 * Modified / fixed from the original by Raman Ng (a.k.a. tinyau): http://blog.tinyau.net
 * 				http://blog.tinyau.net/archives/2007/09/22/habari-rn-monthly-archives-plugin
 */

class Monthly_Archives extends Plugin
{
	
	const VERSION= '0.9';
	
	private $monthly_archives= '';			// stores the actual archives list
	private $config= array();				// stores our config options
	
	private $cache_expirey= 604800;			// one week, in seconds: 60 * 60 * 24 * 7
	
	
	public function info ( ) {
		
		return array(
			'name' => 'Monthly Archives',
			'url' => 'http://habariproject.org',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org',
			'version' => self::VERSION,
			'description' => 'Shows archives grouped by month.',
			'license' => 'Apache License 2.0'
		);
		
	}
	
	public function action_update_check ( ) {
		
		Update::add( 'MonthlyArchives', '726F35A4-16C2-11DD-AF4E-A64656D89593', self::VERSION );
		
	}
	
	public function action_plugin_deactivation ( $file = '' ) {
		
		if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
		
			$class_name= strtolower( get_class( $this ) );
			
			// dump our cached list
			Cache::expire( $class_name . ':list' );
			
		}
		
	}
	
	public function action_init ( ) {
		
		$class_name= strtolower( get_class( $this ) );
		
		$this->config[ 'num_month' ]= Options::get( $class_name . ':num_month' );
		$this->config[ 'display_month' ]= Options::get( $class_name . ':display_month' );
		$this->config[ 'show_count' ]= Options::get( $class_name . ':show_count' );
		$this->config[ 'detail_view' ]= Options::get( $class_name . ':detail_view' );
		$this->config[ 'delimiter' ]= Options::get( $class_name . ':delimiter' );
		
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
		
		$archives= $this->get_monthly_archives();
		
		$class_name= strtolower( get_class( $this ) );
		
		Cache::set( $class_name . ':list', $archives, $this->cache_expirey );
		
		$this->monthly_archives= $archives;
		
	}
	
	public function theme_get_monthly_archives ( ) {
		
		return $this->get_monthly_archives();
		
	}
	
	protected function get_monthly_archives ( ) {
		
		set_time_limit( ( 5 * 60 ) );
		
		if ( !empty( $this->config[ 'num_month' ] ) ) {
			$limit= 'LIMIT 0, ' . $this->config[ 'num_month' ];
		}
		else {
			$limit= '';
		}
		
		$q= 'SELECT YEAR( p.pubdate ) AS year, MONTH( p.pubdate ) AS month, COUNT( p.id ) AS cnt 
				FROM ' . DB::table( 'posts' ) . ' p 
				WHERE p.content_type = ? AND p.status = ? 
				GROUP BY year, month 
				ORDER BY p.pubdate DESC ' . $limit;
		$p[]= Post::type( 'entry' );
		$p[]= Post::status( 'published' );
		
		$results= DB::get_results( $q, $p );
				
		if ( empty( $results ) ) {
			$archives[]= '<ul id="monthly_archives">';
			$archives[]= '  <li>No Archives Found</li>';
			$archives[]= '</ul>';
		}
		else {
			
			$archives[]= '<ul id="monthly_archives">';
			
			foreach ( $results as $result ) {
				
				// make sure the month has a 0 on the front, if it doesn't
				$result->month= str_pad( $result->month, 2, 0, STR_PAD_LEFT );
				
				$result->month_ts= mktime( 0, 0, 0, $result->month );
				
				// what format do we want to show the month in?
				switch ( $this->config[ 'display_month' ] ) {
					
					// Full name
					case 'F':
						$result->display_month= date( 'F', $result->month_ts );
						break;
						
					// Abbreviation
					case 'A':
						$result->display_month= date( 'M', $result->month_ts );
						break;
						
					// Number
					case 'N':
					default:
						$result->display_month= $result->month;
						break;
					
				}
				
				// do we want to show the count of posts?
				if ( $this->config[ 'show_count' ] == 'Y' ) {
					$result->the_count= ' (' . $result->cnt . ')';
				}
				else {
					$result->the_count= '';
				}
				
				$archives[]= '  <li>';
				$archives[]= '    <a href="' . URL::get( 'display_entries_by_date', array( 'year' => $result->year, 'month' => $result->month ) ) . '" title="View entries in ' . $result->display_month . '/' . $result->year . '">' . $result->display_month . ' ' . $result->year . '</a>' . $result->the_count;
				
				// do we want to show all the posts as well?
				if ( $this->config[ 'detail_view' ] == 'Y' ) {
					
					$posts= Posts::get( 
										array( 
												'content_type' => Post::type( 'entry' ), 
												'status' => Post::type( 'published' ), 
												'year' => $result->year, 
												'month' => $result->month_ts, 
												'nolimit' => true 
										) 
					);
					
					if ( $posts ) {
						
						$archives[]= '    <ul class="archive_entry">';
						
						foreach ( $posts as $post ) {
							
							$day= date( 'd', strtotime( $post->pubdate ) );
							
							if ( empty( $this->config[ 'delimiter' ] ) ) {
								$delimiter= '&nbsp;&nbsp;';
							}
							else {
								$delimiter= $this->config[ 'delimiter' ];
							}
							
							$archives[]= '      <li>';
							$archives[]= '        ' . $day . $delimiter . '<a href="' . $post->permalink . '" title="View ' . $post->title . '">' . $post->title . '</a>';
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
				
				$class_name= strtolower( get_class( $this ) );
				
				$ui= new FormUI( $class_name );
				
				$num_month= $ui->add( 'text', 'num_month', _t( 'Number of most recent months to be shown (Blank for show all)' ) );
				
				$display_month= $ui->add( 'select', 'display_month', _t( 'Month displayed as' ) );
				$display_month->options= array( '' => '', 'F' => 'Full name', 'A' => 'Abbreviation', 'N' => 'Number' );
				
				$show_count= $ui->add( 'select', 'show_count', _t( 'Show Monthly Entries Count?' ) );
				$show_count->options= array( '' => '', 'Y' => 'Yes', 'N' => 'No' );
				$show_count->add_validator( 'validate_required' );
				
				$detail_view= $ui->add( 'select', 'detail_view', _t( 'Detail View?' ) );
				$detail_view->options= array( '' => '', 'Y' => 'Yes', 'N' => 'No' );
				$detail_view->add_validator( 'validate_detail_view' );
				
				$delimiter= $ui->add( 'text', 'delimiter', _t( 'Delimiter to separate day and post title in detail view (optional)' ) );
				$delimiter->add_validator( 'validate_delimiter' );
				
				$ui->on_success( array( $this, 'updated_config' ) );
				$ui->out();
				
			}
			
		}
		
	}
	
	public function updated_config ( $ui ) {
		
		// when the config has been updated, we need to update our cache - things could have changed
		$this->update_cache();
		
		return true;
		
	}
	
	public function filter_validate_detail_view ( $valid, $value ) {
		
		if ( empty( $value ) || $value == '' ) {
			
			return array( _t( 'A value for this field is required. ' ) );
			
		}
		
		$this->config[ 'detail_view' ]= $value;
		
		return array();
		
	}
	
	public function theme_monthly_archives ( $theme ) {
		
		$class_name= strtolower( get_class( $this ) );
		
		// first, see if we have the list already cached
		if ( Cache::has( $class_name . ':list' ) ) {
			
			$this->monthly_archives= Cache::get( $class_name . ':list' );
			
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