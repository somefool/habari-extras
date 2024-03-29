<?php

/**
 * DownloadPlugin Class
 *
 * This class provides  functionality for downloading and installing
 * plugins directly from a url.
 *
 * @todo Document methods
 * @todo gzip,bzip,tar support
 * @todo better filename and filetype recognition (e.g. from url like http://host/filenamewithourextension)
 **/

class DownloadPlugin extends Plugin
{
	private $downloadplugin_pluginsPath;

	function info()
	{
		return array (
			'name' => 'Download Plugin',
			'url' => 'http://wiki.lulug.org/prog/habari/download-plugin',
			'author' => 'Alessandro Miliucci',
			'authorurl' => 'http://forkwait.net/blog',
			'version' => '0.1.1',
			'description' => 'Download and install plugins',
			'license' => 'Apache License 2.0',
		);
	}

	public function formui_submit( FormUI $form )
	{
		$filename = basename($form->pluginurl);
		//local file path (e.g. habari_installation/system/plugins/plugin.zip)
		$filePath = $this->downloadplugin_pluginsPath . $filename;
		
		// check if the remote file is successfully opened
		if ($fp = fopen($form->pluginurl, 'r')) {
   			$content = '';
   			// keep reading until there's nothing left
		   	while ($line = fread($fp, 1024)) {
		      		$content .= $line;
		   	}

		   	$fp = fopen($filePath, 'w');
			fwrite($fp, $content);
			fclose($fp);
		} else {
		   	Session::notice( _t("Error during file download", 'plugin_locale') );
			break;
		}

			$zip = new ZipArchive;
     			$res = $zip->open( $filePath );
     			if ($res === TRUE) {
         			$zip->extractTo( $this->downloadplugin_pluginsPath );
         			$zip->close();
				//SET 775 Permission ?
				Session::notice( _t('Plugin installed', 'plugin_locale') );
     			} else {
				Session::notice( _t('Error during plugin installation', 'plugin_locale') );
				$form->save();
				unlink($filePath);
				break;
    		 	}
		unlink($filePath);
		$form->pluginurl->value = '';
		$form->save();
		Utils::redirect( URL::get( 'admin', 'page=plugins' ) );
	}



	public function action_plugin_ui( $plugin_id, $action )
	{	
		if ( $this->plugin_id() == $plugin_id ){
			$this->downloadplugin_pluginsPath = HABARI_PATH . '/user/plugins/';
	  		$ui = new FormUI( 'Download Plugin' );
			if(is_writable($this->downloadplugin_pluginsPath)){
				/* in 0.6 texts aren't resizable; search 
				 * "what are you trying to do with formui?" 
				 * in http://drunkenmonkey.org/irc/habari/2010-09-23
				 */
		  		$url = $ui->append( 'textarea', 'pluginurl', 'null:null', _t('Plugin URL:', 'plugin_locale') );
		  		$ui->append('submit', 'Download', _t('Download', 'plugin_locale'));
				$url->add_validator('url_validator', _t('The plugin_url field value must be a valid URL'));
		  		$ui->on_success( array($this, 'formui_submit') );
			}else{
				$ui->append('static','disclaimer', _t( '<p><em><small>Plugins directory is not writable, check permissions and reload this page</small></em></p>') );
			}
			$ui->out();
		}
	}

	public function filter_plugin_config( $actions, $plugin_id )
	{
	  if ( $plugin_id == $this->plugin_id() ) {
	    $actions[] = _t('Install new plugin');
	  }
	  return $actions;
	}

	function action_update_check() 
	{
	  Update::add( 'Download Plugin', '2641A2EE-C7D1-11DF-AB1B-7133DFD72085', $this->info->version ); 
	}

}

?>
