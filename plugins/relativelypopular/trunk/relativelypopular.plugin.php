<?php

class RelativelyPopular extends Plugin
{
	public $n_periods;
	public $interval;
	public $day;
	public $now;

	/**
	 * Add the necessary template as a block
	 *
	 **/
	public function action_init()
	{
		// init
		$n = Options::get( 'relativelypopular__number_of_periods' ); // number of periods
		if( $n == null || strval($n)!=strval(intval($n))) $n = 30;
		$this->n_periods = $n;

		$interval = Options::get( 'relativelypopular__period_length_days' ); // period length in days
		if( $interval == null || strval($interval)!=strval(floatval($interval))) $interval = 1;
		$this->interval = $interval;

		// a figure that can be reliably used in integer division to obtain a continuous sequence 
		// of numbers across a discontinuity, such as at the end of a year if we're counting days 0-365
		$this->day = intval(time()/(60*60*24*$interval));

		$this->now = $this->day%(2*$n); // store 2n time periods so we accurately track provided there is one visit in n
		
		$this->add_template( 'block.relativelypopular', dirname(__FILE__) . '/block.relativelypopular.php' );
	}

	/**
	 * Add to the list of possible block types
	 *
 	 **/
	public function filter_block_list( $block_list )
	{
		$block_list[ 'relativelypopular' ] = _t( 'Relatively Popular Posts', 'relativelypopular' );
		return $block_list;
	}

	/**
	 * Create a configuration form for this plugin
	 *
	 **/
	public function configure()
	{
		$form = new FormUI( 'relativelypopular' );
		$form->append( 'checkbox', 'loggedintoo', 'relativelypopular__loggedintoo', _t( 'Track visits of logged-in users too', 'relativelypopular' ) );
		$form->append( 'text', 'number_of_periods', 'relativelypopular__number_of_periods', _t( 'Number of periods to track [default=30]', 'relativelypopular' ) );
		$form->append( 'text', 'period_length_days', 'relativelypopular__period_length_days', _t( 'Tracking period length (days) [default=1]', 'relativelypopular' ) );
		$form->append( 'submit', 'save', 'Save' );
		$form->out();
	}

	/**
	 * Create a configuration form for the block
	 **/
	public function action_block_form_relativelypopular( $form, $block )
	{
		$content = $form->append( 'text', 'quantity', $block, _t( 'Posts to show:', 'relativelypopular' ) );
		$form->append( 'submit', 'save', _t( 'Save', 'relativelypopular' ) );
	}

	/**
	 * Log the entry page view, when appropriate.
	 *
	 */
	public function action_add_template_vars( $theme, $handler_vars )
	{
		// If there is only one post
		if ( $theme->post instanceof Post && count( $theme->posts ) == 1 ) {

			// Only track users that aren't logged in, unless specifically overridden
			if ( !User::identify()->loggedin || Options::get( 'relativelypopular__loggedintoo' ) ) {
				$set = Session::get_set( 'relativelypopular', false );
				$post = $theme->post;
				// this code is actually executed about 9 times per page request on my system, 
				// so this check here is essential otherwise we bias the results by a factor of 9
				if ( !in_array( $post->id, $set ) ){
					// load fields
					$visits = $post->info->visits;
					$visits_activity = $post->info->visits_activity;
					// check if fields currently exist and contain the requsite valid data, otherwise reinitalise
					if ( $visits_activity == null || count(explode('#', $visits_activity))!=2*$this->n_periods) {
						$visits_activity = implode('#', array_fill(0, 2*$this->n_periods, 0));
					}
					$activity = explode('#', $visits_activity);
					if(!array_key_exists($this->now, $activity)) {
						$activity += array($this->now=>0);
					}
					// increment the quantity for the period we're currently in and blank the $n_periods fields following it
					$activity[$this->now] += 1;
					for($i=1; $i<=$this->n_periods; $i++) {
						$next = ($this->day+$i)%(2*$this->n_periods);
						if(!array_key_exists($next, $activity)) {
							$activity += array($next=>0);
						}
						$activity[$next] = 0;
					}
					// evaluate the total hits for this time period and store it along with the activity trace
					$post->info->visits = array_sum($activity);
					$post->info->visits_activity = implode('#', $activity);
					$post->info->commit();

					Session::add_to_set( 'relativelypopular', $post->id );
				}
			}

		}
		if(!isset($theme->RelativelyPopular)) {
			$theme->RelativelyPopular = $this;
		}
	}

	/**
	 * Display a template with the popular entries
	 */
	public function theme_relativelypopular($theme, $limit = 5)
	{
		$theme->relativelypopular = Posts::get( array(
			'content_type' => 'entry',
			'has:info' => 'visits',
			'orderby' => 'CAST(hipi1.value AS UNSIGNED) DESC', // As the postinfo value column is TEXT, ABS() forces the sorting to be numeric
			'limit' => $limit
		) );
		return $theme->display( 'relativelypopular' );
	}

	/**
	 * Populate a block with the popular entries
	 **/
	public function action_block_content_relativelypopular( $block, $theme )
	{
		if ( ! $limit = $block->quantity ) {
			$limit = 5;
		};

		$block->relativelypopular = Posts::get( array(
			'content_type' => 'entry',
			'has:info' => 'visits',
			'orderby' => 'CAST(hipi1.value as UNSIGNED) DESC', // As the postinfo value column is TEXT, ABS() forces the sorting to be numeric
			'limit' => $limit
		) );
	}

	/**
	 * Display a histogram of blocks (sparkline) wherever the theme requests it
	 **/
	public function sparkline($content) {
		$act = explode('#', $content->info->visits_activity);
		$max = max($act);
		$sparkblocks = array('▁', '▂', '▃', '▄', '▅', '▆', '▇');
		for($i=0; $i<$this->n_periods; $i++) {
			$k = ($this->now+$this->n_periods+1+$i)%(2*$this->n_periods);
			$this_act = (array_key_exists($k, $act) ? $act[$k] : 1);
			echo $sparkblocks[round(6*$this_act/$max)];
		}
	}
}

?>
