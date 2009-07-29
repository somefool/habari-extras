<?
/**
 * Quick and dirty Google Analytics interface
 *
 */

class Google_Analytics
{
	
	// URLs that we use
	const login_url    = 'https://www.google.com/accounts/ClientLogin';
	const accounts_url = 'https://www.google.com/analytics/feeds/accounts/default';
	const reports_url  = 'https://www.google.com/analytics/feeds/data';
	const dxp_ns       = 'http://schemas.google.com/analytics/2009';
	
	private $auth = null;
	private $startDate;
	private $stopDate;
	private $profile;
	
	/**
	 * Constructor
	 *
	 * Set up authentication with Google and get $auth token
	 *
	 * @param string $email Email/Userid for GA login
	 * @param string $password Password for GA login
	 * @return GoogleAnalytics
	 */
	public function __construct( $email, $password )
	{
		$this->authenticate( $email, $password );
	}
	
	/**
	 * Authenticate Google Account
	 *
	 * @param string $email Email/Userid for login
	 * @param string $password Password for login
	 */
	private function authenticate( $email, $password )
	{
		$post = array(
			'accountType' => 'GOOGLE',
			'Email'       => $email,
			'Passwd'      => $password,
			'service'     => 'analytics'
			);
		
		$response = $this->request( self::login_url, $post );
		parse_str( str_replace( array( "\n", "\r\n" ), '&', $response ), $auth );
		if ( ! is_array( $auth ) || empty( $auth['Auth'] ) ) {
			// How are we supposed to throw errors with habari?
			throw new Exception( 'GoogleAnalytics::Authentication Error: "' . strip_tags( $response->get_response_body() ) . '"' );
		}
		$this->auth = $auth['Auth'];
	}
	
	/**
	 * Return the authentication token
	 *
	 * @return array
	 */
	private function auth_header()
	{
		return array( 'Authorization: GoogleLogin auth=' . $this->auth );
	}
	
	/**
	 * Set Profile ID  (Example: ga:12345)
	 *
	 * @param string $id ID of profile to use
	 **/
	public function set_profile( $id )
	{
		$this->profile = $id;
	}
	
	/**
	 * Sets the date range for Analytics data.
	 *
	 * @param string $startDate (YYYY-MM-DD)
	 * @param string $stopDate (YYYY-MM-DD)
	 */
	public function set_date_range( $startDate, $stopDate )
	{
		$this->startDate = $startDate;
		$this->stopDate = $stopDate;
	}
	
	/**
	 * Return a list of all profiles for the logged in account
	 *
	 * @return array
	 **/
	function get_profiles()
	{
		$response = $this->request( self::accounts_url, null, null, $this->auth_header() );
		$xml = simplexml_load_string( $response );
		
		$entries = $xml->entry;
		$profiles = array();
		foreach ( $entries as $entry ) {
			$tmp = array();
			$tmp['title'] = (string) $entry->title;
			$tmp['entryid'] = (string) $entry->id;
			
			$properties = $entry->children( self::dxp_ns );
			$tmp['tableid'] = (string) $properties->tableId;
			$tmp['accountId'] = (string) $properties->accountId;
			$tmp['accountName'] = (string) $properties->accountName;
			$tmp['profileId'] = (string) $properties->profileId;
			$tmp['webPropertyId'] = (string) $properties->webPropertyId;
			
			$profiles[] = $tmp;
		}
		
		// We only want to return the profile => display
		$ret = array();
		foreach ( $profiles as $profile ) {
			$ret[$profile['tableid']] = $profile['title'];
		}
		
		return $ret;
	}
	
	
	/**
	 * Parse Google Analytics XML to an array ( dimension => metric )
	 * Check http://code.google.com/intl/nl/apis/analytics/docs/gdata/gdataReferenceDimensionsMetrics.html
	 * for dimension and metrics
	 *
	 * @param array $dimensions Dimensions to use
	 * @param array $metrics Metrics to use
	 * @param array $sort OPTIONAL: Dimension or dimensions to sort by
	 * @param string $startDate OPTIONAL: Start of report period
	 * @param string $stopDate OPTIONAL: Stop of report period
	 * 
	 * @return array results
	 */
	public function getData( $dimensions, $metrics, $sort = null, $startDate = null, $stopDate = null )
	{
		$parameters = array( 'ids' => $this->profile );
		
		if ( is_array( $dimensions ) ) {
			$dimensions_string = '';
			foreach ( $dimensions as $dimension ) {
				$dimensions_string .= ',' . $dimension;
			}
			$parameters['dimensions'] = substr( $dimensions_string, 1 );
		} else {
			$parameters['dimensions'] = $dimensions;
		}
		
		if ( is_array( $metrics ) ) {
			$metrics_string = '';
			foreach ( $metrics as $metric ) {
				$metrics_string .= ',' . $metric;
			}
			$parameters['metrics'] = substr( $metrics_string, 1 );
		} else {
			$parameters['metrics'] = $metrics;
		}
		
		if ( $sort == null && isset( $parameters['metrics'] ) ) {
			$parameters['sort'] = $parameters['metrics'];
		} elseif ( is_array( $sort ) ) {
			$sort_string = '';
			foreach ( $sort as $s ) {
				$sort_string .= ',' . $s;
			}
			$parameters['sort'] = substr( $sort_string, 1 );
		} else {
			$parameters['sort'] = $sort;
		}
		
		if ( $startDate == null ) {
			$startDate = date( 'Y-m-d', strtotime( '1 month ago' ) );
		}
		$parameters['start-date'] = $startDate;
		
		if ( $stopDate == null ) {
			$stopDate = date( 'Y-m-d' );
		}
		$parameters['end-date'] = $stopDate;
		
		$request = $this->request( self::reports_url, null, $parameters, $this->auth_header() );
		
		$xml = simplexml_load_string( $request );
		
		$result = array();
		$entries = $xml->entry;
		foreach ( $entries  as $entry ) {
			$title = $entry->title;
			$dxp = $entry->children( self::dxp_ns );
			$d_dimension = $dxp->dimension->attributes();
			$d_metric = $dxp->metric->attributes();
			
			// Multiple dimensions given?
			// @todo double check this.  I'm not sure if it's going to work right or not.
			if ( strpos( $title, '|' ) !== false && strpos( $parameters['dimensions'], ',' ) !== false ) {
				$tmp = explode(',', $parameters['dimensions'] );
				$tmp[] = '|';
				$tmp[] = '=';
				$title = preg_replace( '/\s\s+/', ' ', trim( str_replace( $tmp, '', $title ) ) );
			}
			$title = str_replace( $parameters['dimensions'] . '=', '', $title );
			$result[$title] = (int) $d_metric->value;
			
		}
		return $result;
	}
    
	/**
	 * Wrapper for Habari's RemoteRequest call
	 *
	 * @param string $url URL to call
	 * @param array $get GET Variables
	 * @param array $post POST Variables
	 * @param array $headers HEADER Variables
	 * @return array 
	 **/
	private function request( $url, $post = null, $get = null, $headers = null )
	{
		if ( is_array( $post ) ) {
			$response = new RemoteRequest( $url, 'POST' );
			$response->set_postdata( $post );
		} else {
			$response = new RemoteRequest( $url, 'GET' );
			if ( is_array( $get ) ) {
				$response->set_params( $get );
			}
		}
		
		if ( is_array( $headers ) ) {
			$response->add_headers( $headers );
		}
		
		if ( $response->execute() ) {
			return $response->get_response_body();
		} else {
			return false;
		}
	}
} // GoogleAnalytics

?>