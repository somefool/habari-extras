<?php
/*
 * Blogroll Plugin
 * Usage: <?php $theme->show_blogroll(); ?>
 * A sample blogroll.php template is included with the plugin.  This can be copied to your
 * active theme and modified to fit your preference.
 *
 * @todo Update wiki docs, and inline code docs
 * url, feedurl, ownername, relationship
 */

class Blogroll extends Plugin
{
	const API_VERSION = 005;
	const CONTENT_TYPE = 'blogroll';

	private $relationships = array(
		'external' => 'External',
		'nofollow' => 'Nofollow',
		'bookmark' => 'Bookmark'
	);

	private $info_fields = array(
		'feedurl',
		'ownername',
		'relationship',
		'url',
		'xfn_identity',
		'xfn_friendship',
		'xfn_physical',
		'xfn_professional',
		'xfn_geographical',
		'xfn_family',
		'xfn_romantic'
	);

	/**
	 * Run activation routines, and setup default options.
	 */
	public function action_plugin_activation()
	{
		if ( ! CronTab::get_cronjob( 'blogroll:update' ) ) {
			CronTab::add_hourly_cron( 'blogroll:update', 'blogroll_update_cron', 'Updates the blog updated timestamp from weblogs.com' );
		}

		Options::set( 'blogroll__api_version', self::API_VERSION );
		Options::set( 'blogroll__use_updated', true );
		Options::set( 'blogroll__max_links', '10' );
		Options::set( 'blogroll__sort_by', 'id' );
		Options::set( 'blogroll__direction', 'ASC' );
		Options::set( 'blogroll__list_title', 'Blogroll' );

		Post::add_new_type( self::CONTENT_TYPE );

		// Give anonymous users access, if the group exists
		$group = UserGroup::get_by_name( 'anonymous' );
		if ( $group ) {
			$group->grant( self::CONTENT_TYPE, 'read' );
		}
	}

	/**
	 * Run deactivation routines.
	 */
	public function action_plugin_deactivation( $file )
	{
		CronTab::delete_cronjob( 'blogroll:update' );
		Options::delete( 'blogroll__api_version' );
	}

	public function filter_post_type_display($type, $foruse)
	{
		$names = array(
			self::CONTENT_TYPE => array(
				'singular' => _t( 'Blogroll Link', self::CONTENT_TYPE ),
				'plural' => _t( 'Blogroll', self::CONTENT_TYPE ),
			)
		);
		return isset($names[$type][$foruse]) ? $names[$type][$foruse] : $type;
	}

	public function action_init() {
		// remove legacy tables and import
		if ( Options::get( 'blogroll__db_version' ) || Options::get( 'blogroll__api_version' ) < 004 ) {
			$this->upgrade_pre_004();
		}
		$this->add_template( 'blogroll', dirname($this->get_file()) . '/templates/blogroll.php' );
		$this->add_template( 'formcontrol_opml_file', dirname($this->get_file()) . '/templates/formcontrol_file.php' );
		$this->add_template( 'blogroll__tabcontrol_checkboxes', dirname($this->get_file()) . '/templates/tabcontrol_checkboxes.php' );
		$this->add_template( 'blogroll__tabcontrol_radio', dirname($this->get_file()) . '/templates/tabcontrol_radio.php' );
		$this->add_template( 'block.blogroll', dirname($this->get_file()) . '/templates/block.blogroll.php' );
		$this->add_template( 'blogroll.single', dirname($this->get_file()) . '/templates/blogroll.single.php' );
	}

	public function action_admin_header( $theme )
	{
		if ( 'publish' == $theme->page && !empty($theme->form) && $theme->form->content_type->value == Post::type(self::CONTENT_TYPE) ) {
			Stack::add( 'admin_stylesheet', array( $this->get_url() . '/blogroll.css', 'screen' ), 'blogroll' );
		}
	}

	/**
	 *
	 */
	public function action_update_check()
	{
		Update::add( 'blogroll', '0420cf10-db83-11dc-95ff-0800200c9a66',  $this->info->version );
	}

	/**
	 *
	 */
	public function filter_plugin_config( $actions, $plugin_id )
	{

		if ( $this->plugin_id() == $plugin_id ){
			$actions[]= _t( 'Configure', 'blogroll' );
		}
		return $actions;
	}

	/**
	 *
	 */
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $this->plugin_id() == $plugin_id ) {
			switch ( $action ) {
				case _t( 'Configure', 'blogroll' ):
					$form = new FormUI( 'blogroll' );

					// display settings
					$display_wrap = $form->append( 'fieldset', 'display', _t( 'Display Settings', 'blogroll' ) );
					$title = $display_wrap->append(
						'text', 'list_title', 'option:blogroll__list_title', _t( 'List title: ', 'blogroll' )
					);
					$max = $display_wrap->append(
						'text', 'max_links', 'option:blogroll__max_links',
						_t( 'Max. displayed links: ', 'blogroll' )
					);
					$sort_bys = array_merge(
						array_combine(
							array_keys(Post::default_fields()),
							array_map( 'ucwords', array_keys(Post::default_fields()) )
						),
						array( 'RAND()' => _t( 'Randomly', 'blogroll' ) )
						);
					$sortby = $display_wrap->append(
						'select', 'sort_by', 'option:blogroll__sort_by',
						_t( 'Sort By: ', 'blogroll' ), $sort_bys
					);
					$orders = array( 'ASC' => _t( 'Ascending' ,'blogroll' ), 'DESC' => _t( 'Descending' ,'blogroll' ) );
					$order = $display_wrap->append(
						'select', 'direction', 'option:blogroll__direction',
						_t( 'Order: ', 'blogroll' ), $orders
					);

					// other settings
					$other_wrap = $form->append( 'fieldset', 'settings', _t( 'More Settings', 'blogroll' ) );
					$update = $other_wrap->append(
						'checkbox', 'use_updated', 'option:blogroll__use_updated',
						_t( 'Use Weblogs.com to get updates? ', 'blogroll' )
					);

					$form->append( 'submit', 'save', 'Save' );
					$form->on_success( array($this, 'formui_submit' ) );
					$form->out();
					break;
			}
		}
	}

	public function formui_submit( FormUI $form )
	{
		Session::notice( _t( 'Blogroll options saved.', 'blogroll' ) );
		$form->save();
	}

	public function action_publish_post( Post $post, FormUI $form ) {
		if ( $post->content_type == Post::type(self::CONTENT_TYPE) ) {
			foreach ($this->info_fields as $field_name) {
				$post->info->$field_name= $form->$field_name->value;
			}
			if(isset($form->quick_url) && $form->quick_url->value != '' ) {
				$data = $this->get_info_from_url($form->quick_url->value);
				if ( $data ) {
					$data = array_map( create_function( '$a', 'return InputFilter::filter($a);' ), $data );
					$post->title= $data['name'];
					$post->info->url= $data['url'];
					$post->content= $data['description'];
					$post->info->feedurl= $data['feed'];
					$post->slug= Utils::slugify($data['name']);
					$post->status= Post::status( 'published' );
				}
				else {
					Session::error( _t("Could not find information for {$form->quick_url->value}. Please enter the information manually.", 'blogroll' ) );
					$title = parse_url($form->quick_url->value, PHP_URL_HOST);
					$post->title = ( $title ) ? $title : $form->quick_url->value;
					$post->info->url = $form->quick_url->value;
					return;
				}
			}
		}
	}

	public function action_form_publish( FormUI $form, Post $post) {

		if( $form->content_type->value == Post::type(self::CONTENT_TYPE) ) {

			if ( !Controller::get_var( 'id' ) ) {
				// Quick link button to automagically discover info
				$quicklink_controls= $form->append( 'tabs', 'quicklink_controls' );

				$quicklink_tab= $quicklink_controls->append( 'fieldset', 'quicklink_tab', _t( 'Quick Link' ));
				$quicklink_wrapper= $quicklink_tab->append( 'wrapper', 'quicklink_wrapper' );
				$quicklink_wrapper->class='container';

				$quicklink_wrapper->append( 'text', 'quick_url', 'null:null', _t( 'Quick URL' ), 'tabcontrol_text' );
				$quicklink_wrapper->append( 'static', 'quick_url_info', '<p class="column span-15">Enter a url or feed url and other information will be automatically discovered.</p>' );
				$quicklink_wrapper->append( 'submit', 'addquick', _t( 'Add' ), 'admincontrol_submit' );

				$quicklink_controls->move_before($quicklink_controls, $form);
			}

			// Remove fields we don't need
			if ( $form->silos instanceof FormControl ) {
				$form->silos->remove();
			}
			$form->comments_enabled->value = 0;

			// Add the url field
			$form->append( 'text', 'url', 'null:null', _t( 'URL' ), 'admincontrol_text' );
			$form->url->class= 'important';
			$form->url->tabindex = 2;
			$form->url->value = $post->info->url;
			$form->url->move_after($form->title);

			// Retitle fields
			$form->title->caption= _t( 'Blog Name' );
			$form->content->caption= _t( 'Description' );
			$form->content->tabindex= 3;
			$form->tags->tabindex= 4;

			// Create the extras splitter & fields
			$extras = $form->settings;

			$extras->append( 'text', 'feedurl', 'null:null', _t( 'Feed URL' ), 'tabcontrol_text' );
			$extras->feedurl->value = $post->info->feedurl;

			$extras->append( 'text', 'ownername', 'null:null', _t( 'Owner Name' ), 'tabcontrol_text' );
			$extras->ownername->value = $post->info->ownername;

			$relationships = Plugins::filter( 'blogroll_relationships', $this->relationships );
			$extras->append( 'select', 'relationship', 'null:null', _t( 'Relationship' ), $relationships, 'tabcontrol_select' );
			$extras->relationship->value = $post->info->relationship;

			// Create the XFN Selector
			$xfnselector = $form->publish_controls->append( 'fieldset', 'xfnselector', _t( 'XFN' ));

			$xfnselector->append( 'checkboxes', 'xfn_identity', 'null:null', _t( 'Identity' ), array( 'me' => _t( 'Another web address of mine' )), 'blogroll__tabcontrol_checkboxes' );
			$xfnselector->xfn_identity->value = $post->info->xfn_identity;

			$xfnselector->append( 'radio', 'xfn_friendship', 'null:null', _t( 'Friendship' ), array(
				'contact' => _t( 'Contact' ),
				'acquaintance' => _t( 'Acquaintance' ),
				'friend' => _t( 'Friend' ),
				'null:null' => _t( 'None' )
			), 'blogroll__tabcontrol_radio' );
			$xfnselector->xfn_friendship->value = $post->info->xfn_friendship;

			$xfnselector->append( 'checkboxes', 'xfn_physical', 'null:null', _t( 'Physical' ), array( 'met' => _t( 'Met' )), 'blogroll__tabcontrol_checkboxes' );
			$xfnselector->xfn_physical->value = $post->info->xfn_physical;

			$xfnselector->append( 'checkboxes', 'xfn_professional', 'null:null', _t( 'Professional' ), array(
				'co-worker' => _t( 'Co-worker' ),
				'colleague' => _t( 'Colleague' ),
			), 'blogroll__tabcontrol_checkboxes' );
			$xfnselector->xfn_professional->value = $post->info->xfn_professional;

			$xfnselector->append( 'radio', 'xfn_geographical', 'null:null', _t( 'Geographical' ), array(
				'co-resident' => _t( 'Co-resident' ),
				'neighbor' => _t( 'Neighbor' ),
				'null:null' => _t( 'None' )
			), 'blogroll__tabcontrol_radio' );
			$xfnselector->xfn_geographical->value = $post->info->xfn_geographical;

			$xfnselector->append( 'radio', 'xfn_family', 'null:null', _t( 'Family' ), array(
				'child' => _t( 'Child' ),
				'parent' => _t( 'Parent' ),
				'sibling' => _t( 'Sibling' ),
				'spouse' => _t( 'Spouse' ),
				'kin' => _t( 'Kin' ),
				'null:null' => _t( 'None' )
			), 'blogroll__tabcontrol_radio' );
			$xfnselector->xfn_family->value = $post->info->xfn_family;

			$xfnselector->append( 'checkboxes', 'xfn_romantic', 'null:null', _t( 'Romantic' ), array(
				'muse' => _t( 'Muse' ),
				'crush' => _t( 'Crush' ),
				'date' => _t( 'Date' ),
				'sweetheart' => _t( 'Sweetheart' )
			), 'blogroll__tabcontrol_checkboxes' );
			$xfnselector->xfn_romantic->value = $post->info->xfn_romantic;

		}
	}

	public function filter_post_xfn_relationships( $relationships, Post $post )
	{
		if ($post->content_type == Post::type(self::CONTENT_TYPE)) {
			$xfn_rel = array($relationships);

			// "me" is exclusive of all other XFN values.
			if ( is_array($post->info->xfn_identity) && count($post->info->xfn_identity) > 0 ) {
				$xfn_rel = array_merge($post->info->xfn_identity, $xfn_rel);
			}
			else {
				foreach( $this->info_fields as $info_field ) {
					if ( strpos( $info_field, 'xfn_' ) === 0 && $post->info->$info_field ) {
						if ( is_array($post->info->$info_field) ) {
							$xfn_rel = array_merge( $post->info->$info_field, $xfn_rel );
						}
						else {
							$xfn_rel[] = $post->info->$info_field;
						}
					}
				}
			}

			return str_replace( ' null:null', '', implode( ' ', $xfn_rel) );
		}
	}

	public static function get_info_from_url( $url )
	{
		$info= array();
		$data= RemoteRequest::get_contents( $url );
		$feed= self::get_feed_location( $data, $url );

		if ( $feed ) {
			$info['feed']= $feed;
			$data= RemoteRequest::get_contents( $feed );
		}
		else {
			$info['feed']= $url;
		}
		// try and parse the xml
		try {
			$xml= new SimpleXMLElement( $data );
			switch ( $xml->getName() ) {
				case 'RDF':
				case 'rss':
					$info['name']= (string) $xml->channel->title;
					$info['url']= (string) $xml->channel->link;
					if ( (string) $xml->channel->description ) $info['description']= (string) $xml->channel->description;
					break;
				case 'feed':
					$info['name']= (string) $xml->title;
					if ( (string) $xml->subtitle ) $info['description']= (string) $xml->subtitle;
					foreach ( $xml->link as $link ) {
						$atts= $link->attributes();
						if ( $atts['rel'] == 'alternate' ) {
							$info['url']= (string) $atts['href'];
							break;
						}
					}
					break;
			}
		}
		catch ( Exception $e ) {
			return null;
		}
		return $info;
	}

	public static function get_feed_location( $html, $url )
	{
		preg_match_all( '/<link\s+(.*?)\s*\/?>/si', $html, $matches );
		$links= $matches[1];
		$final_links= array();
		$href= '';
		$link_count= count( $links );
		for( $n= 0; $n < $link_count; $n++ ) {
			$attributes= preg_split( '/\s+/s', $links[$n]);
			foreach ( $attributes as $attribute ) {
				$att= preg_split( '/\s*=\s*/s', $attribute, 2 );
				if ( isset( $att[1] ) ) {
					$att[1]= preg_replace( '/([\'"]?)(.*)\1/', '$2', $att[1] );
					$final_link[strtolower( $att[0] )]= $att[1];
				}
			}
			$final_links[$n]= $final_link;
		}
		for ( $n= 0; $n < $link_count; $n++ ) {
			if ( isset($final_links[$n]['rel']) && strtolower( $final_links[$n]['rel'] ) == 'alternate' ) {
				if ( isset($final_links[$n]['type']) && in_array( strtolower( $final_links[$n]['type'] ), array( 'application/rss+xml', 'application/atom+xml', 'text/xml' ) ) ) {
					$href= $final_links[$n]['href'];
				}
				if ( $href ) {
					if ( strstr( $href, "http://" ) !== false ) {
						$full_url= $href;
					}
					else {
						$url_parts= parse_url( $url );
						$full_url= "http://$url_parts[host]";
						if ( isset( $url_parts['port'] ) ) {
							$full_url.= ":$url_parts[port]";
						}
						if ( $href{0} != '/' ) {
							$full_url.= dirname( $url_parts['path'] );
							if ( substr( $full_url, -1 ) != '/' ) {
								$full_url.= '/';
							}
						}
						$full_url.= $href;
					}
					return $full_url;
				}
			}
		}
		return false;
	}

	public function theme_show_blogroll( $theme, $user_params = array() )
	{
		$theme->blogroll_title = Options::get( 'blogroll__list_title' );

		// Build the params array to pass it to the get() method
		$order_by = Options::get( 'blogroll__sort_by' );
		$direction = Options::get( 'blogroll__direction' );

		$params = array(
			'limit' => Options::get( 'blogroll__max_links' ),
			'orderby' => $order_by . ' ' . $direction,
			'status' => Post::status( 'published' ),
			'content_type' => Post::type(self::CONTENT_TYPE),
		);

		$theme->blogs = Posts::get( $params );

		return $theme->fetch( 'blogroll' );
	}

	public function filter_habminbar( array $menu )
	{
		$menu['blogroll']= array( 'Blogroll', URL::get( 'admin', 'page=publish&content_type='.self::CONTENT_TYPE ) );
		return $menu;
	}

	public function filter_rewrite_rules( array $rules )
	{
		$rules[] = new RewriteRule(array(
			'name' => 'blogroll_opml',
			'parse_regex' => '/^blogroll\/opml\/?$/i',
			'build_str' => 'blogroll/opml',
			'handler' => 'ActionHandler',
			'action' => 'blogroll_opml',
			'priority' => 2,
			'rule_class' => RewriteRule::RULE_PLUGIN,
			'is_active' => 1,
			'description' => 'Rewrite for Blogroll OPML feed.'
		));
		return $rules;
	}

	/**
	 * Handler for opml output
	 * 
	 * @todo add tags
	 */
	public function action_handler_blogroll_opml( SuperGlobal $handler_vars )
	{
		$opml = new SimpleXMLElement( '<opml version="1.1"></opml>' );

		$head = $opml->addChild( 'head' );
		$head->addChild( 'title', Options::get( 'title' ) );
		$head->addChild( 'dateCreated', gmdate( 'D, d M Y G:i:s e' ) );

		$body = $opml->addChild( 'body' );

		$blogs = Posts::get(
			array(
				'content_type' => Post::type(self::CONTENT_TYPE),
				'nolimit' => TRUE,
				'status' => Post::status( 'published' )
			)
		);

		foreach ( $blogs as $blog ) {
			$outline = $body->addChild( 'outline' );
			$data = array(
				'text' => $blog->title,
				'htmlUrl' => $blog->info->url,
				'xmlUrl' => $blog->info->feedurl,
				'ownername' => $blog->info->ownername,
				'relationship' => $blog->info->relationship,
				'pubdate' => $blog->pubdate,
				'updated' => $blog->updated,
				'description' => htmlentities($blog->content, ENT_QUOTES, 'UTF-8' )
			);
			
			foreach ( $data as $att => $value ) {
				if ( $value ) {
					$outline->addAttribute( $att, $value );
				}
			}
			$outline->addAttribute( 'type', 'link' );
		}
		$opml = Plugins::filter( 'blogroll_opml', $opml, $handler_vars );
		$opml = $opml->asXML();

		ob_clean();
		header( 'Content-Type: application/opml+xml' );
		print $opml;
	}

	public function filter_import_names( array $import_names )
	{
		return array_merge( $import_names, array(_t( 'BlogRoll OPML file', 'blogroll' )) );
	}

	/**
	 * Plugin filter that supplies the UI for the Blogroll importer
	 *
	 * @param string $stageoutput The output stage UI
	 * @param string $import_name The name of the selected importer
	 * @param string $stage The stage of the import in progress
	 * @param string $step The step of the stage in progress
	 * @return output for this stage of the import
	 */
	public function filter_import_stage( $stageoutput, $import_name, $stage, $step )
	{
		// Only act on this filter if the import_name is one we handle...
		if( $import_name != _t( 'BlogRoll OPML file', 'blogroll' ) ) {
			// Must return $stageoutput as it may contain the stage HTML of another importer
			return $stageoutput;
		}

		$inputs = array();

		// Validate input from various stages...
		switch( $stage ) {
		case 1:
			if( isset($_POST['opml_url']) && $_POST['opml_url'] ) {
				$inputs['opml_url'] = $_POST['opml_url'];
				$stage = 2;
			}
			elseif ( isset($_FILES['opml_file']) && is_uploaded_file($_FILES['opml_file']['tmp_name']) ) {
				Options::set(
					"blogroll_{$_FILES['opml_file']['tmp_name']}",
					file_get_contents($_FILES['opml_file']['tmp_name'])
				);
				$inputs['opml_file'] = $_FILES['opml_file']['tmp_name'];
				$stage = 2;
			}
			else {
				$inputs['warning']= _t( 'You did not provide an OPML file.', 'blogroll' );
			}
			break;
		}

		// Based on the stage of the import we're on, do different things...
		switch( $stage ) {
		case 1:
		default:
			$output = $this->stage1( $inputs );
			break;
		case 2:
			$output = $this->stage2( $inputs );
		}

		return $output;
	}

	private function stage1( array $inputs )
	{
		$default_values = array(
			'opml_url' => '',
			'opml_file' => '',
			'warning' => ''
		 );
		$inputs = array_merge( $default_values, $inputs );
		extract( $inputs );
		if( $warning != '' ) {
			Session::error($warning);
		}

		$output = <<< BR_IMPORT_STAGE1
			</form><form method="post" action="" enctype="multipart/form-data">
			<p>Please provide the URI, or upload your OPML file</p>
			<div class="item clear" id="opmlurl">
				<span class="pct25"><label for="opml_url">URI To OPML</label></span>
				<span class="pct50"><input type="text" name="opml_url" value="{$opml_url}"></span>
				<span class="pct25 helptext">The URL to the OPML file you want to import.</span>
			</div>
			<div class="item clear" id="opmlfile">
				<span class="pct25"><label for="opml_file">Upload OPML</label></span>
				<span class="pct50"><input type="file" name="opml_file" value=""></span>
				<span class="pct25 helptext">Or you can upload a OPML file to import.</span>
			</div>
			<input type="hidden" name="stage" value="1">

			<div class="item formcontrol"  id="apply"><input type="submit" name="import" class="button" value="Import">
			</div>

BR_IMPORT_STAGE1;
		return $output;
	}

	private function stage2( array $inputs )
	{
		$default_values = array(
			'opml_url' => '',
			'opml_file' => '',
			'warning' => ''
		 );
		$inputs = array_merge( $default_values, $inputs );
		extract( $inputs );

		$ajax_url = URL::get( 'auth_ajax', array( 'context' => 'blogroll_import_opml' ) );
		EventLog::log(_t( 'Starting OPML Blogroll import' ));
		Options::set( 'import_errors', array());

		$output = <<< WP_IMPORT_STAGE2
			<p>Import In Progress</p>
			<div id="import_progress">Starting Import...</div>
			<script type="text/javascript">
			// A lot of ajax stuff goes here.
			$( document ).ready( function(){
				$( '#import_progress' ).load(
					"{$ajax_url}",
					{
						opml_url: "{$opml_url}",
						opml_file: "{$opml_file}"
					}
				 );
			} );
			</script>
WP_IMPORT_STAGE2;
		return $output;
	}

	public function action_auth_ajax_blogroll_import_opml( ActionHandler $handler )
	{
		$valid_fields = array( 'opml_url', 'opml_file' );
		$inputs = array_intersect_key( $_POST->getArrayCopy(), array_flip( $valid_fields ) );
		extract( $inputs );

		if ( ! empty($opml_url) ) {
			$file = RemoteRequest::get_contents( $opml_url );
		}
		elseif ( ! empty($opml_file) ) {
			$file = Options::get("blogroll_$opml_file");
			Options::delete("blogroll_$opml_file");
		}
		try {
			if ( empty($file) ) {
				throw new Exception;
			}
			$xml =@ new SimpleXMLElement( $file ); // errors as exceptions++
			$count = $this->import_opml( $xml->body );
			echo '<p>';
			printf(
				_n( 'Imported %d link from %s', 'Imported %d links from %s', $count, 'blogroll' ),
				$count,
				(string) $xml->head->title
			);
			echo '</p>';
		}
		catch ( Exception $e ) {
			_e( 'Sorry, could not parse that OPML file. It may be malformed.', 'blogroll' );
		}
	}

	/**
	 * Import the <outline> data from simplexml obj. $xml->body.
	 * @todo support tags/categories
	 * @todo don't be strict on duplicate matching.
	 */
	private function import_opml( SimpleXMLElement $xml )
	{
		if ( ! $xml->outline ) {
			throw new Exception( 'Not a valid OPML resource' );
		}

		$count = 0;
		foreach ( $xml->outline as $outline ) {
			$atts = (array) $outline->attributes();
			$params = $this->map_opml_atts( $atts['@attributes'] );
			if ( isset( $params['url'] ) && isset( $params['title'] ) ) {
				if ( count( Posts::get( array( 'all:info' => array( 'url' => $params['url'] ) ) ) ) >= 1 ) {
					continue;
				}
				$params = array_map( create_function( '$a', 'return InputFilter::filter($a);' ), $params );
				extract($params);
				$user = User::identify();
				$params = array(
					'title' => $title,
					'pubdate' => isset($pubdate) ? HabariDateTime::date_create($pubdate) : HabariDateTime::date_create(),
					'updated' => isset($updated) ? HabariDateTime::date_create($updated) : HabariDateTime::date_create(),
					'content' => isset($content) ? $content : '',
					'status' => Post::status( 'published' ),
					'content_type' => Post::type(self::CONTENT_TYPE),
					'user_id' => $user->id,
				);
				$blog = Post::create($params);

				foreach($this->info_fields as $field ) {
					if ( isset(${$field}) && ${$field} ) {
						$blog->info->{$field} = ${$field};
					}
				}
				$blog->update();
				$count++;
			}
			if ( $outline->outline ) {
				$count += $this->import_opml( $outline );
			}
		}
		return $count;
	}

	/**
	 * Maps standard OPML link attributes to Post fields.
	 */
	private function map_opml_atts( array $atts )
	{
		$atts = array_map( 'strval', $atts );
		$valid_atts = array_intersect_key(
			$atts,
			array_flip(
				array_merge(
					$this->info_fields,
					array( 'title', 'pubdate', 'updated', 'content' )
				)
			)
		);
		foreach ( $atts as $key => $val ) {
			switch ( $key ) {
				case 'htmlUrl':
					$valid_atts['url']= $atts['htmlUrl'];
					break;
				case 'xmlUrl':
					$valid_atts['feedurl']= $atts['xmlUrl'];
					break;
				case 'text':
					$valid_atts['title']= $atts['text'];
					break;
				case 'description':
					$valid_atts['content']= $atts['description'];
					break;
			}
		}
		return $valid_atts;
	}

	/**
	 * Grabs update times from weblogs.com
	 * @todo use update time from weblogs.com instead of gmdate.
	 * @todo parse urls so we search for only '%host.domain.tld/path%' with no http://
	 */
	public function filter_blogroll_update_cron( $success )
	{
		if ( Options::get( 'blogroll__use_updated' ) ) {
			$request = new RemoteRequest( 'http://rpc.weblogs.com/rssUpdates/changes.xml', 'GET' );
			$request->add_header( array( 'If-Modified-Since', Options::get( 'blogroll__last_update' ) ) );
			if ( $request->execute() ) {
				try {
					$xml = new SimpleXMLElement( $request->get_response_body() );
				}
				catch ( Exception $e ) {
					EventLog::log( 'Could not parse weblogs.com Changes XML file' );
					return false;
				}
				$atts = $xml->attributes();
				$updated = strtotime( (string) $atts['updated'] );
				foreach ( $xml->weblog as $weblog ) {
					$atts = $weblog->attributes();
					$match = array();
					$match['url'] = (string) $atts['url'];
					$match['feedurl'] = (string) $atts['rssUrl'];
					$update = $updated - (int) $atts['when'];
					// use LIKE for info matching
					$posts = DB::get_results(
						'SELECT * FROM {posts}
						WHERE
						{posts}.id IN (
							SELECT post_id FROM {postinfo}
							WHERE ( (name = ? AND value LIKE ? ) OR (name = ? AND value LIKE ? ) )
						)
						AND status = ? AND content_type = ?',
						array(
							'url', "%{$match['url']}%",
							'feedurl', "%{$match['feedurl']}%",
							Post::status( 'published' ), Post::type(self::CONTENT_TYPE)
						),
						'Post'
					);
					if ( $posts instanceof Posts && $posts->count() > 0 ) {
						foreach ( $posts as $post ) {
							$post->updated = HabariDateTime::create($update);
							$post->update();
							EventLog::log("Updated {$post->title} last update time from weblogs.com");
						}
					}
				}
				Options::set( 'blogroll__last_update', gmdate( 'D, d M Y G:i:s e' ) );
				return true;
			}
			else {
				EventLog::log( 'Could not connect to weblogs.com' );
				return false;
			}
		}
		return $success;
	}

	public function upgrade_pre_004()
	{
		DB::register_table( 'blogroll' );
		DB::register_table( 'bloginfo' );
		DB::register_table( 'tag2blog' );

		if ( ! in_array( DB::table( 'blogroll' ), DB::list_tables() ) ) {
			Options::set( 'blogroll__api_version', self::API_VERSION );
			return;
		}

		Post::add_new_type(self::CONTENT_TYPE);

		$opml = new SimpleXMLElement( '<opml version="1.1"></opml>' );
		$head = $opml->addChild( 'head' );
		$head->addChild( 'title', Options::get( 'title' ) );
		$head->addChild( 'dateCreated', gmdate( 'D, d M Y G:i:s e' ) );
		$body = $opml->addChild( 'body' );

		$blogs = DB::get_results("SELECT * FROM {blogroll}", array());
		foreach ( $blogs as $blog ) {
			$outline = $body->addChild( 'outline' );
			$outline->addAttribute( 'text', $blog->name );
			$outline->addAttribute( 'htmlUrl', $blog->url );
			$outline->addAttribute( 'xmlUrl', $blog->feed );
			$outline->addAttribute( 'relation', $blog->rel );
			$outline->addAttribute( 'updated', $blog->updated );
			$outline->addAttribute( 'content', $blog->description );
			$outline->addAttribute( 'type', 'link' );
		}
		try {
			$count = $this->import_opml($opml->body);
			DB::query( 'DROP TABLE IF EXISTS {blogroll}' );
			DB::query( 'DROP TABLE IF EXISTS {bloginfo}' );
			DB::query( 'DROP TABLE IF EXISTS {tag2blog}' );
			EventLog::log(
				sprintf(
					_n(
						'Imported %d blog from previous Blogroll version, and removed obsolete tables',
						'Imported %d blogs from previous Blogroll version, and removed obsolete tables',
						$count,
						'blogroll'
					),
					$count
				)
			);
		}
		catch (Exception $e) {
			EventLog::log( _t( 'Could not Import previous data. please import manually and drop tables.', 'blogroll' ) );
		}

		Options::delete( 'blogroll__db_version' );
		Options::set( 'blogroll__api_version', self::API_VERSION );
		Options::set( 'blogroll__sort_by', 'id' );
	}

	/**
	 * Add this to the list of possible block types.
	 **/
	public function filter_block_list( $block_list )
	{
		$block_list[ 'blogroll' ] = _t( 'Blogroll', 'blogroll' );
		return $block_list;
	}

	public function action_block_content_blogroll( $block, $theme )
	{
		// Build the params array to pass it to the get() method
		$order_by = $block->sort_by;
		$direction = $block->direction;

		$params = array(
			'limit' => ( $block->max_links ? $block->max_links : 10 ), // in case it is not yet configured
			'orderby' => $order_by . ' ' . $direction,
			'status' => Post::status( 'published' ),
			'content_type' => Post::type( self::CONTENT_TYPE ),
		);

		$blogs = Posts::get( $params );
		$list = array();

		if ( ! empty( $blogs ) ) {
			foreach( $blogs as $blog ) {
			$list[] = array(
        			"url" => $blog->info->url,
				"content" => $blog->content,
				"relationship" => $blog->info->relationship . " " . $blog->xfn_relationships,
				"title" => $blog->title,
				);
			}
		}

		$block->list = $list;
	}

	public function action_block_form_blogroll( $form, $block )
	{
		$title = $form->append( 'text', 'list_title', $block, _t( 'List title: ', 'blogroll' ) );
		$max = $form->append( 'text', 'max_links', $block, _t( 'Max. displayed links: ', 'blogroll' ) );

		$sort_bys = array_merge( array_combine(
			array_keys( Post::default_fields() ),
			array_map( 'ucwords', array_keys( Post::default_fields() ) )
			),
			array( 'RAND()' => _t( 'Randomly', 'blogroll' ) )
			);
		$sortby = $form->append( 'select', 'sort_by', $block, _t( 'Sort By: ', 'blogroll' ), $sort_bys );

		$orders = array( 'ASC' => _t( 'Ascending' ,'blogroll' ), 'DESC' => _t( 'Descending' ,'blogroll' ) );
		$order = $form->append( 'select', 'direction', $block, _t( 'Order: ', 'blogroll' ), $orders );

		$form->append( 'submit', 'save', 'Save' );
	}
}
?>
