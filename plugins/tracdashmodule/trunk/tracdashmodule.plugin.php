<?php

    class TracDashModule extends Plugin {
    
        private $theme;
    
        /**
         * action_plugin_activation
         * Registers the core modules with the Modules class. 
         * @param string $file plugin file
         */
        function action_plugin_activation( $file ) {
        
            if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
            
                Modules::add( 'My Tickets' );
                
                if ( Options::get( 'tracdashmodule__trac_query' ) == null ) {
                
                    Options::set( 'tracdashmodule__trac_query', 'enter query' );
                
                }
            }
            
        }
        
        /**
         * Add the Configure option for the plugin
         *
         * @access public
         * @param array $actions
         * @return array
         */
        public function filter_plugin_config( $actions ) {
            
            $actions[] = _t( 'Configure' );
            
            return $actions;
            
        }
    
        /**
         * action_plugin_deactivation
         * Unregisters the module.
         * @param string $file plugin file
         */
        function action_plugin_deactivation( $file ) {
            
            Modules::remove_by_name( 'My Tickets' );
        
        }
        
        /**
         * filter_dash_modules
         * Registers the core modules with the Modules class. 
         */
        function filter_dash_modules( $modules ) {
            
            $modules[] = 'My Tickets';
            
            //currently not using this
            //$this->add_template( 'dash_latesttickets', dirname( __FILE__ ) . '/dash_latesttickets.php' );
    
            return $modules;
        }
        
        /**
         * Plugin UI - Displays the various config options depending on the "option"
         * chosen.
         *
         * @access public
         * @param string $plugin_id
         * @param string $action
         * @return void
         */
        public function action_plugin_ui( $plugin_id, $action ) {
            
            $ui = new FormUI( strtolower( __CLASS__ ) );
    
            switch ( $action ){
                
                case _t( 'Configure' ) :
                
                    $ui = new FormUI( strtolower( __CLASS__ ) );
                    $post_fieldset = $ui->append( 'fieldset', 'post_settings', _t( 'Fetch trac tickets using custom query', 'tracdashmodule' ) );
                    $trac_query = $post_fieldset->append( 'textmulti', 'trac_query', 'tracdashmodule__trac_query', _t( 'Enter custom query:', 'tracdashmodule' ) );
                   
                    $ui->on_success( array( $this, 'updated_config' ) );
                    $ui->append( 'submit', 'save', _t( 'Save', 'tracdashmodule' ) );
                    $ui->out();
                    break;
            }
        }
    
        /**
         * Give the user a session message to confirm options were saved.
         **/
        public function updated_config( FormUI $ui ) {
        
            Session::notice( _t( 'Trac options saved.', 'tracdashmodule' ) );
        
            $ui->save();
        
        }	
        
        /**
         * filter_dash_module_trac_tickets
         * Gets the latest entries module
         * @param string $module
         * @return string The contents of the module
         */
        public function filter_dash_module_my_tickets( $module ) {
            
            //add assets when module is on page
            Stack::add( 'admin_footer_javascript', 'http://yui.yahooapis.com/3.2.0/build/yui/yui.js');
            Stack::add( 'admin_footer_javascript', Site::get_url('user') . '/plugins/tracdashmodule/tracdashmodule.js');
            
            $items = false;
            $cache = false;
            
            //get rss feeds from admin configuration
            $trac_query =  Options::get( 'tracdashmodule__trac_query' );
            
            //if multiple then join
            $trac_query = ( count($trac_query) > 1 ) ? implode('","',$trac_query) : $trac_query[0];
            
            //yql query to extract what we want from feeds & return json
            $query = "http://query.yahooapis.com/v1/public/yql?q=select%20link%2Ctitle%20from%20rss%20where%20url%20in(%22".urlencode($trac_query)."%22)&format=json&diagnostics=false";
    
            try {
            
                $r = new RemoteRequest( $query );
                $r->set_timeout( 10 );
                $r->execute();
                $response = $r->get_response_body();
                $json = json_decode($response,1);
                $items = $json['query']['results']['item'];
                
            } catch ( Exception $e ) {
                //if request failed, then check cache to show last group of tickets
                if ( Cache::has( 'my_tickets' ) ) {
                    $items = Cache::get( 'my_tickets' );
                }
                
                //log error
                EventLog::log( _t( 'TracDashModule error: %1$s', array( $e->getMessage() ), 'tracdashmodule' ), 'err', 'plugin', 'tracdashmodule' );
                $item = (object) array (
                    'text' => 'Unable to fetch Trac Tickets.', 
                    'time' => '', 
                    'image_url' => ''
                );
                    
                if ( ! $items ) {
                    $items[] = $item;
                }
            } //end catch
            
            // Cache (even errors) to avoid hitting rate limit.  We only use the cache as fallback when api fails
            Cache::set( 'my_tickets', $items, ( $cache !== false ? $cache : 9000 ) ); // , true );
            
            $html = '<ul class="trac">';
            foreach( $items as $k => $v ) {
                
                $html .= '<li class="item"><a class="minor" href="'.$v['link'].'">'.$v['title'].'</a></li>';
            
            }
            
            $html .= '</ul>';
    
            $module[ 'title' ] == 'Trac Module';
            $module[ 'content' ] = $html;
            
            return $module;
        }
    }
?>