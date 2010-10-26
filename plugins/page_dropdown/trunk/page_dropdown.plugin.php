<?php

class Page_Dropown extends Plugin
{
	/**
	 * Build a selection input of paginated paths to be used for pagination.
	 *
	 * @param string The RewriteRule name used to build the links.
	 * @param array Various settings used by the method and the RewriteRule.
	 * @return string Collection of paginated URLs built by the RewriteRule.
	 */
	function theme_page_dropdown( $theme, $rr_name = NULL, $settings = array() ) {
		$output = "";
		$current = $theme->page;
		$items_per_page = isset( $theme->posts->get_param_cache['limit'] ) ?
			$theme->posts->get_param_cache['limit'] :
			Options::get( 'pagination' );
		$total = Utils::archive_pages( $theme->posts->count_all(), $items_per_page );

		// Make sure the current page is valid
		if ( $current > $total ) {
			$current = $total;
		}
		else if ( $current < 1 ) {
			$current = 1;
		}

		$output = '<select onchange="location.href=options[selectedIndex].value">';
		for ( $page = 1; $page < $total; ++$page ) {
			$settings[ 'page' ] = $page;
			$caption = ( $page == $current ) ?  $current  : $page;
			// Build the path using the supplied $settings and the found RewriteRules arguments.
			$url = URL::get( $rr_name, $settings, false, false, false );
			// Build the select option.
			$output .= '<option value="' . $url . '"' . ( ( $page == $current ) ? ' selected="selected"' : '' ) . '>' . $caption . '</option>' . "\n";
		}
		$output .= "</select>";

		return $output;
	}

	function action_update_check() {
		Update::add( $this->info->name, $this->info->guid, $this->info->version );
	}
}

?>
