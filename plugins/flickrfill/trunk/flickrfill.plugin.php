<?php
class FlickrFill extends Plugin
{
	private $flickr= null;
	private $person_id= null;
	private $photo= null;
	private $size= 't';
	private $number= '5';
 
	public function setup()
	{
		$this->person_id= Options::get( 'flickrfill__person' );
		$this->size= Options::get ( 'flickrfill__size' );
		$this->number= Options::get ( 'flickrfill__number' );
	}

	/**
	* function info
	* Returns information about this plugin
	* @return array Plugin info array
	**/
	function info()
	{
		return array(
			'name' => 'FlickrFill',
			'url' => 'http://642Design.com/',
			'author' => 'Sean T Evans',
			'authorurl' => 'http://morydd.net/',
			'version' => '0.2',
			'license' => 'Apache License 2.0',
			'description' => 'Displays flickr images before each post based on date'
		);
	}
 
	public function help()
	{
		return <<< END_HELP
		<p>Add this code to your theme where you would like your photos displayed. This must be inside the post loop.</p>
		<code>&lt;?php \$theme-&gt;flickrfill(\$post,\$posts); ?&gt;</code>
		<p>CSS Classes</p>
		<ul><li>flickrfill: container div for images</li>
		<li>flickrfillimg: class assigned to each img</li></ul>
		<p>You can use <a href="http://idgettr.com/">idgetter.com</a> to find your Flickr User ID Number.</p>
END_HELP;
	}

	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[]= _t('Configure');
			$actions[]= _t('Refresh');
		}
		return $actions;
	}
 
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t('Configure') :
					$ui = new FormUI( strtolower( get_class( $this ) ) );
					$person= $ui->append( 'text', 'person', 'flickrfill__person',  _t('Flickr ID Number: ' ) );
					// $ui->on_success( array( $this, 'updated_config' ) );
					$number= $ui->append ('text', 'number', 'flickrfill__number', _t('Number of Pictures to Display: ' ) );
					$size= $ui->append( 'select', 'size', 'flickrfill__size', _t('Size of Images to Display' ) ); 
					/**
					TODO Make this tranlateable
					**/
					$size->options= array( '_s' => 'Square (75px Each Side)', '_t' => 'Thumbnail (Longest Side 100px)', '_m' => 'Small (Longest Side 240px )', '' => 'Medium (Longest Side 500px)' );
					$ui->append( 'submit', 'save', _t('Save') );
					$ui->out();
					break;
				case _t('Refresh') :
					$this->theme_flickrfill();
					Utils::redirect( URL::get('admin', 'page=plugins') );
					break;
				}
			}
	}
 
	public function updated_config( $ui )
	{
		return true;
	}

	function action_init()
	{
		$this->add_template( 'flickrfill', dirname(__FILE__) . '/flickrfill.php' );
	}
	
	public function theme_flickrfill_bydate( $theme, $post1date, $post2date )
	{
		$this->person_id= Options::get( 'flickrfill__person' );
		$this->size= Options::get ( 'flickrfill__size' );
		$this->number= Options::get ( 'flickrfill__number' );
		
		$feed_url = 'http://api.flickr.com/services/rest/?method=flickr.photos.search&format=rest&api_key=39b1bcf1b0c84a24435677252085d436&user_id=' . $this->person_id . '&min_taken_date=' . $post2date . '&max_taken_date=' . $post1date . '&sort=interestingness-desc&media=photos&per_page=' . $this->number;
		
		if(Cache::has($feed_url)) {
			$response = Cache::get($feed_url);
		}
		if(!Cache::has($feed_url) || Cache::expired($feed_url)) {  // Cache::expired() is a 0.7 feature.
			$request = new RemoteRequest( $feed_url );
		$request->set_timeout( 5 );
		$result= $request->execute();
		if ( Error::is_error( $result ) ) {
			EventLog::log( 'Error getting photo from Flickr', 'err', 'default', 'habari' );
		}
			else {
		$response= $request->get_response_body();
			}
		}
		
		$output = '';
		$xml= new SimpleXMLElement( $response );
		if ( ! $xml ) { 
			return 'no xml'; 
		}
		
		$output .= '<div class="flickrfill">';
		foreach ( $xml->photos->photo as $photo ) {
			if ( ! $photo ) { 
				return; 
			}
			if ( ! $photo['id'] ) { 
				return; 
			}
			$output .= '<a href="http://www.flickr.com/photos/' . $this->person_id . '/' . $photo['id'] . '"><img class="flickrfeedimg" src="http://farm' . $photo['farm'] . '.static.flickr.com/' . $photo['server'] . '/' . $photo['id'] . '_' . $photo['secret'] . $this->size . '.jpg"></a>';
		}
		$output .= '</div>';
		return $output;
		}
		
	public function theme_flickrfill( $theme, $post, $posts )
	{
		$prevpost = $posts->ascend($post);
		if (empty($prevpost)) {
			$post1date = date('Y-m-d');
		} else {
			$post1date = $prevpost->pubdate->format('Y-m-d');
		}
		$post2date = $post->pubdate->format('Y-m-d');
		return $this->theme_flickrfill_bydate( $theme, $post1date, $post2date );
	}
	
	/**
	* Enable update notices to be sent using the Habari beacon
	**/
	public function action_update_check()
	{
		Update::add( 'FlickrFill', '98347b74-6a00-4601-bd7b-76e5476e193f',  $this->info->version );
	}
}
?>