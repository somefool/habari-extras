<?php
/**
 * Amazon
 * easily/quickly insert Amazon Products into your posts.
 *
 * @package amazon
 * @version $Id$
 * @author ayunyan <ayu@commun.jp>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link http://ayu.commun.jp/habari-amazon
 */
class Amazon extends Plugin
{
	var $countries = array(
		'ca' => 'Canada',
		'de' => 'Germany',
		'fr' => 'France',
		'jp' => 'Japan',
		'uk' => 'United Kingdom',
		'com' => 'United States');
	var $search_indexes = array(
		'ca' => array(
			'Blended',
			'Books',
			'Classical',
			'DVD',
			'ForeignBooks',
			'Music',
			'Software',
			'SoftwareVideoGames',
			'VHS',
			'Video',
			'VideoGames'),
		'de' => array(
			'Apparel',
			'Baby',
			'Blended',
			'Books',
			'Classical',
			'DVD',
			'Electronics',
			'ForeignBooks',
			'HealthPersonalCare',
			'HomeGarden',
			'Kitchen',
			'Magazines',
			'Music',
			'MusicTracks',
			'OutdoorLiving',
			'PCHardware',
			'Photo',
			'Software',
			'SoftwareVideoGames',
			'SportingGoods',
			'Tools',
			'Toys',
			'VHS',
			'Video',
			'VideoGames',
			'Watches'),
		'fr' => array(
			'Blended',
			'Books',
			'Classical',
			'DVD',
			'Electronics',
			'ForeignBooks',
			'Kitchen',
			'Music',
			'MusicTracks',
			'Software',
			'SoftwareVideoGames',
			'VHS',
			'Video',
			'VideoGames',
			'Watches'),
		'jp' => array(
			'Apparel',
			'Baby',
			'Blended',
			'Books',
			'Classical',
			'DVD',
			'Electronics',
			'ForeignBooks',
			'HealthPersonalCare',
			'Hobbies',
			'Kitchen',
			'Music',
			'MusicTracks',
			'Software',
			'SoftwareGoods',
			'Toys',
			'VHS',
			'Video',
			'VideoGames',
			'Watches'),
		'uk' => array(
			'Apparel',
			'Baby',
			'Blended',
			'Books',
			'Classical',
			'DVD',
			'Electronics',
			'HealthPersonalCare',
			'HomeGarden',
			'Kitchen',
			'Music',
			'MusicTracks',
			'OutdoorLiving',
			'Software',
			'SoftwareVideoGames',
			'Toys',
			'VHS',
			'Video',
			'VideoGames',
			'Watches'),
		'com' => array(
			'All',
			'Apparel',
			'Automotive',
			'Baby',
			'Beauty',
			'Blended',
			'Books',
			'Classical',
			'DigitalMusic',
			'DVD',
			'Electronics',
			'GourmetFood',
			'HealthPersonalCare',
			'HomeGarden',
			'Industrial',
			'Jewelry',
			'KindleStore',
			'Kitchen',
			'Magazines',
			'Merchants',
			'Miscellaneous',
			'MP3Downloads',
			'Music',
			'Musicallnstruments',
			'MusicTracs',
			'OfficeProducts',
			'OutdoorLiving',
			'PCHardware',
			'PetSupplies',
			'Photo',
			'SilverMerchants',
			'Software',
			'SportingGoods',
			'Tools',
			'Toys',
			'UnboxVideo',
			'VHS',
			'Video',
			'VideoGames',
			'Watches',
			'Wireless',
			'WirelessAccessories')
		);
	var $access_key = '1D19NAY95HR62NY7BAG2';
	var $star_image_url = 'http://g-ecx.images-amazon.com/images/G/01/x-locale/common/customer-reviews/stars-';

	/**
	 * plugin information
	 *
	 * @access public
	 * @retrun void
	 */
	public function info()
	{
		return array(
			'name' => 'Amazon',
			'version' => '0.02',
			'url' => 'http://ayu.commun.jp/habari-amazon',
			'author' => 'ayunyan',
			'authorurl' => 'http://ayu.commun.jp/',
			'license' => 'Apache License 2.0',
			'description' => 'easily/quickly insert Amazon Products into your posts.',
			'guid' => '4c91ed13-1fcd-11dd-b5d6-001b210f913f'
			);
	}

	/**
	 * action: plugin_activation
	 *
	 * @access public
	 * @param string $file
	 * @return void
	 */
	public function action_plugin_activation( $file )
	{
		if ( Plugins::id_from_file( $file ) != Plugins::id_from_file( __FILE__ ) ) return;

		Options::set('amazon__country',  'com');
		Options::set('amazon__associate_tag', '');
		Options::set('amazon__template', 'reviewsummary');
	}

	/**
	 * action: init
	 *
	 * @access public
	 * @return void
	 */
	public function action_init()
	{
		$this->load_text_domain('amazon');
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
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id != $this->plugin_id() ) return;
		if ( $action == _t( 'Configure' ) ) {
			$template_dir = dirname( $this->get_file() ) . DIRECTORY_SEPARATOR . 'templates';
			$templates = array();
			if ( $dh = opendir( $template_dir ) ) {
				while ( ( $file = readdir( $dh ) ) !== false ) {
					if ( substr( $file, -4 ) == '.php' ) {
						$template = substr( $file, 0, strlen( $file ) - 4 );
						$templates[$template]= $template;
					}
				}
			}

			$form = new FormUI( strtolower( get_class( $this ) ) );
			$form->append( 'select', 'country', 'amazon__country', _t('Country: ', 'amazon'), $this->countries);
			$form->append( 'text', 'associate_tag', 'amazon__associate_tag', _t('Associate Tag: ', 'amazon') );
			$form->append( 'select', 'template', 'amazon__template', _t('Template: ', 'amazon'), $templates);
			$form->append( 'submit', 'save', _t( 'Save' ) );
			$form->out();
		}
	}

	/**
	 * action: admin_header
	 *
	 * @access public
	 * @param object $theme
	 * @return void
	 */
	public function action_admin_header( $theme )
	{
		$handler_vars = Controller::get_handler_vars();
		if (!isset($handler_vars['page']) || $handler_vars['page'] != 'publish' ) return;
		Stack::add( 'admin_header_javascript', $this->get_url() . '/js/amazon.js' );
		Stack::add( 'admin_stylesheet', array($this->get_url() . '/css/amazon.css', 'screen') );
	}

	/**
	 * action: ajax_amazon_search
	 *
	 * @access public
	 * @return void
	 */
	public function action_before_act_admin_ajax()
	{
		$handler_vars = Controller::get_handler_vars();
		switch ($handler_vars['context']) {
		case 'amazon_search':
			if ( empty( $handler_vars['keywords'] ) ) {
				echo json_encode( array( 'errorMessage' => _t( 'please specify keywords.' ) ) );
				die();
			}

			if ( empty( $handler_vars['search_index'] ) ) {
				echo json_encode( array( 'errorMessage' => _t( 'please specify searchIndex.' ) ) );
				die();
			}

			$keywords = InputFilter::filter($handler_vars['keywords']);
			$search_index = InputFilter::filter($handler_vars['search_index']);

			if ( empty( $handler_vars['page'] ) ) {
				$page = 1;
			} else {
				$page = InputFilter::filter($handler_vars['page']);
			}

			$result = $this->item_search( $keywords, $search_index, $page );
			$xml = simplexml_load_string( $result );

			if ( (string)$xml->Items->Request->IsValid != 'True' ) {
				echo json_encode(array('errorMessage' => _t('following error reply existed from the server: ', 'amazon') . (string)$xml->Items->Request->Errors->Error->Message));
				die();
			}

			if ( (int)$xml->Items->TotalResults == 0) {
				echo json_encode(array('errorMessage' => _t('did not match any items.', 'amazon')));
				die();
			}

			$output = array();
			$output['TotalResults'] = (int)$xml->Items->TotalResults;
			$output['TotalPages'] = (int)$xml->Items->TotalPages;
			$output['CurrentPage'] = $page;
			$output['Start'] = ($page - 1) * 10 + 1;
			$output['End'] = $output['Start'] + 10;
			if ( $output['End'] > $output['TotalResults'] ) $output['End'] = $output['TotalResults'];
			$output['HasPrev'] = false;
			$output['HasNext'] = false;
			if ( $page != 1 ) $output['HasPrev'] = true;
			if ( $page < $output['TotalPages'] ) $output['HasNext'] = true;
			$output['Items'] = array();
			for ( $i = 0; $i < count( $xml->Items->Item ); $i++ ) {
				$item = array();
				$item['ASIN'] = (string)$xml->Items->Item[$i]->ASIN;
				$item['DetailPageURL'] = (string)$xml->Items->Item[$i]->DetailPageURL;
				$item['SmallImageURL'] = (string)$xml->Items->Item[$i]->SmallImage->URL;
				$item['SmallImageWidth'] = (int)$xml->Items->Item[$i]->SmallImage->Width;
				$item['SmallImageHeight'] = (int)$xml->Items->Item[$i]->SmallImage->Height;
				$item['Title'] = (string)$xml->Items->Item[$i]->ItemAttributes->Title;
				$item['Price'] = (string)$xml->Items->Item[$i]->ItemAttributes->ListPrice->FormattedPrice;
				$item['Binding'] = (string)$xml->Items->Item[$i]->ItemAttributes->Binding;
				$output['Items'][] = $item;
			}

			echo json_encode( $output );
			die();
		case 'amazon_insert':
			if (empty($handler_vars['asin'])) {
				echo json_encode(array('errorMessage' => _t('please specify ASIN.', 'amazon')));
				die();
			}

			$asin = InputFilter::filter($handler_vars['asin']);

			$result = $this->item_lookup( $asin );
			$xml = simplexml_load_string( $result );

			if ((string)$xml->Items->Request->IsValid != 'True') {
				echo json_encode(array('errorMessage' => _t( 'following error reply existed from the server: ', 'amazon') . (string)$xml->Items->Request->Errors->Error->Message ));
				die();
			}

			$item =& $xml->Items->Item;
			ob_start();
			$template = preg_replace( '/[^A-Za-z0-9\-\_]/', '', Options::get( 'amazon__template' ) );
			include( dirname( $this->get_file() ) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $template . '.php' );
			$output['html'] = ob_get_contents();
			ob_end_clean();

			echo json_encode( $output );
			//echo $output['html'];
			die();
		default:
			break;
		}
	}

	/**
	 * action: form_publish
	 *
	 * @access public
	 * @param object $form
	 * @return void
	 */
	public function action_form_publish($form)
	{
		$container = $form->publish_controls->append('fieldset', 'amazon', _t('Amazon'));
        $search_index = $this->search_indexes[Options::get('amazon__country')];
        $search_index = array_combine($search_index, $search_index);
		ob_start();
?>
<div class="container">
<?php echo Utils::html_select('amazon-search-index', $search_index, null); ?>
<input type="text" id="amazon-keywords" name="amazon-keywords" />
<input type="button" id="amazon-search" value="<?php echo _t('Search'); ?>" />
<div id="amazon-spinner"></div>
</div>
<a name="amazon-result" />
<div id="amazon-result">
</div>
<?php
        $container->append('static', 'static_amazon', ob_get_contents());
		ob_end_clean();
	}

	/**
	 * filter: plugin_config
	 *
	 * @access public
	 * @return array
	 */
	public function filter_plugin_config($actions, $plugin_id)
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[] = _t( 'Configure' );
		}
		return $actions;
	}

	/**
	 * AWS ItemSearch
	 *
	 * @access private
	 * @param string $keywords
	 * @param string $search_index
	 * @return string
	 */
	private function item_search($keywords, $search_index, $page = 1)
	{
		// TODO: Paging
		$url = 'http://ecs.amazonaws.' . Options::get( 'amazon__country' ) .  '/onca/xml?Service=AWSECommerceService&AWSAccessKeyId=' . $this->access_key . '&Operation=ItemSearch&ResponseGroup=Small,Images,ItemAttributes&Keywords=' . urlencode($keywords) . '&SearchIndex=' . $search_index . '&ItemPage=' . $page;
		$associate_tag = Options::get( 'amazon__associate_tag' );
		if ( !empty( $associate_tag ) ) $url .= '&AssociateTag=' . $associate_tag;

		$request = new RemoteRequest( $url, 'GET' );
		if ( $request->execute() === false ) return false;
		return $request->get_response_body();
	}

	/**
	 * AWS ItemLookup
	 *
	 * @access private
	 * @param string $asin
	 * @return string
	 */
	private function item_lookup($asin)
	{
		$url = 'http://ecs.amazonaws.' . Options::get( 'amazon__country' ) .  '/onca/xml?Service=AWSECommerceService&AWSAccessKeyId=' . $this->access_key . '&Operation=ItemLookup&ResponseGroup=Large&ItemId=' . $asin;
		$associate_tag = Options::get( 'amazon__associate_tag' );
		if ( !empty( $associate_tag ) ) $url .= '&AssociateTag=' . $associate_tag;

		$request = new RemoteRequest( $url, 'GET' );
		if ( $request->execute() === false ) return false;
		return $request->get_response_body();
	}

	/**
	 * Rating to Star Image URL
	 *
	 * @access private
	 * @param float $rating
	 * @return string
	 */
	private function ratingToStarImage($rating)
	{
		$rating = sprintf( "%.1f", $rating );
		$rating_str = str_replace( '.', '-', $rating );
		return '<img src="' . $this->star_image_url . $rating_str . '.gif" alt="' . $rating . '" />';
	}
}
?>