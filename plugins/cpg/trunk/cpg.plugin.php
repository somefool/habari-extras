<?php

require_once 'cpgdb.php';
require_once 'cpgoptions.php';

class Cpg extends Plugin
{
  const VERSION = '0.1';

  function info()
  {
    return array(
      'name' => 'Copperleaf Photo Gallery Plugin',
      'version' => self::VERSION,
      'url' => 'http://wiki.habariproject.org/en/CPG',
      'author' => 'Bill Smith',
      'authorurl' => 'http://www.copperleaf.org/',
      'license' => 'Apache License 2.0',
      'description' => 'A plugin for managing and displaying photos.'
    );
  }

  // *** Uncomment when ready for release ***
  //function action_update_check()
  //{
  //  Update::add('Copperleaf Photo Gallery Plugin', '397B7ABC-7E7A-11DD-A57A-408D55D89593', self::VERSION);
  //}
  
	public function action_plugin_activation($file)
	{
		if ($file == str_replace( '\\','/', $this->get_file())) 
		{
      CpgDb::registerTables();

			//Options::set( 'cpg__db_version', CpgDb::DB_VERSION );
			CpgOptions::setDbVersion(CpgDb::DB_VERSION);

			if (CpgDb::install())
			{
				Session::notice(_t( 'Created the CPG database tables.', 'cpg'));
			}
			else 
			{
				Session::error(_t( 'Could not install CPG database tables.', 'cpg'));
			}
		}
	}
  
	public function action_init() 
	{		
		//$this->add_template('event.single', dirname(__FILE__) . '/event.single.php');
		
		Post::add_new_type('imageset', false);
		Post::add_new_type('image', false);
		Post::add_new_type('gallery', false);

    CpgDb::registerTables();
    //Utils::debug('tables registered!');

		if (CpgDb::DB_VERSION > CpgOptions::getDbVersion()) 
		{
			CpgDb::install();
			EventLog::log( 'Updated CPG.' );
			CpgOptions::setDbVersion(CpgDb::DB_VERSION);
		}
	}

	public function filter_adminhandler_post_loadplugins_main_menu($menus) 
	{
		unset($menus['create_' . Post::type('imageset')]);
		unset($menus['manage_' . Post::type('imageset')]);
		unset($menus['create_' . Post::type('image')]);
		unset($menus['manage_' . Post::type('image')]);
		unset($menus['create_' . Post::type('gallery')]);

		return $menus;
	}
  public function filter_plugin_config($actions, $plugin_id)
  {
    if ($plugin_id == $this->plugin_id()) 
    {
      $actions[]= _t('Configure');
    }
    return $actions;
  }  

  public function action_plugin_ui($plugin_id, $action)
  {
    if ( $plugin_id == $this->plugin_id() ) 
    {
      switch ( $action ) 
      {
      case _t('Configure') :
        $ui = new FormUI( strtolower( get_class( $this ) ) );
        $customvalue= $ui->append( 'text', 'customvalue', 'cpg__customvalue', _t('Your custom value:') );
        $ui->out();
        break;
      }
    }
  }
  
  function action_plugins_loaded()  
  {
  }
}

?>
