<?php
/**
 * Plugin class that extends post editing functionality to allow selection of styles from a given directory. Any style
 * located in this directory will be checkbox selectable allowing multiple style selection. The plugin will also read
 * any sub-directories and make them drop-down list selectable.  
 * 
 *
 */
class DynamicStyleSelecor extends Plugin
{
	//TODO: Change these to an configurable option in the admin screen
	const CSS_PATH = 'design/css/alts/';
	const PARENT_CSS_DIRECTORY = 'alts';
	const OPTION_PREFIX = 'css_option_';
	const STYLE_EXTENSION = 'css';
	const DEFAULT_STYLE_SELECTION = '--Select a style--';
	
	/**
	 * Add update beacon support
	 */
	public function action_update_check()
	{
		Update::add( $this->info->name, '2A105C98-A645-11DE-8977-425B55D89593', $this->info->version );
	}
	
	/**
	 * This function creates the style selection form, and appends it to the intercepted FormUI object. During the
	 * construction of the form it will retrieve any existing selections for the post.
	 * 
	 * The parent style directory defined with @param PARENT_CSS_DIRECTORY will have a checkbox form created, while
	 * any sub-directory will have a drop-down selection form created for the style files included.
	 * 
	 * @param $form The intercepted FormUI object to attach the extra form when editting posts.
	 * @param $post The intercepted Post object which may contain information about already selected styles.
	 * @param $context Not explicitly used in this function.
	 * @return unknown_type
	 */
	public function action_form_publish($form, $post, $context)
	{
		//To satisfy reference constraint for some functions
		$default_css_selection = self::DEFAULT_STYLE_SELECTION;
		$parent_css_dir = self::PARENT_CSS_DIRECTORY;
		
		//Grab the 2D array of arrays. 
		$css_styles = self::getCssStyleFileNames(Site::get_dir('theme', TRUE) . self::CSS_PATH);

		$keys = array_keys($css_styles);
		
		if (count($css_styles) == 0)
		{
			return; /* no fields to make */
		}

		//Create the object that will be the style selection form
		$css_styles_fieldset = $form->insert('settings', 'fieldset', 'css_styles_fieldset', _t('Select style') );
		$css_styles_fieldset->class = 'container';
		
		$already_selected_css_styles = array();
		
		//Retrieve already existing style selections saved in the database
		foreach($keys as $key)
		{
			$already_selected_css_styles[$key] = explode(',', $post->info->dynamicStyles[$key]);
		}

		$css_style_option_list = array();

		//Iterate through all existing styles and create the relevant form controls (checkbox or selection box)
		while($styles_in_directory = current($css_styles))
		{
			$current_dir = key($css_styles);
			
			//If we are at the parent directory, create the checkbox form
			if($current_dir == $parent_css_dir) {
				foreach($css_styles[$parent_css_dir] as $css_style)
				{
					/* skip if it's the none options */
					if ($css_style == self::DEFAULT_STYLE_SELECTION) continue;
					
					//Since checkbox requires one id per box to identify which has been selected or not, need individual HTML name
					$css_style_html_name = self::OPTION_PREFIX . preg_replace("/\s+/", "", $css_style);
					$postprop = $css_styles_fieldset->append('checkbox', $css_style_html_name, 'null:null', _t('Page style: ' . ucwords($css_style)), 'tabcontrol_checkbox');
					
					//If there are any existing selections, portray them as selected checkboxes
					if(in_array($css_style, $already_selected_css_styles[$parent_css_dir])) {
						$postprop->value = true;
					} else {
						$postprop->value = false;
					}
					$postprop->class = 'container';
				}
			}
			//If we have a sub-directory, then create the drop-down selection form 
			else {
				//One HTML name for each select-one selections
				$css_style_html_name = self::OPTION_PREFIX . preg_replace("/\s+/", "", $current_dir);

				//Since we need to add the option for the default value
				//array_unshift($default_css_selection, $styles_in_directory);
				$css_styles_fieldset->append('select', $css_style_html_name, 'null:null', _t('Style set: ' . ucwords($current_dir)), $styles_in_directory, 'tabcontrol_select');
				
				//If there are any existing selections, have the selection pre-selected
				if($already_selected_css_styles[$current_dir] != self::DEFAULT_STYLE_SELECTION) {
					//Position 0 since we expect that there is only one selected for the drop-down selection
					$already_selected = array_search($already_selected_css_styles[$current_dir][0], $styles_in_directory);
					$css_styles_fieldset->{$css_style_html_name}->value = $already_selected;
				}
			}
			next($css_styles);
		}

	}
	
	/**
	 * This function saves the style selection for the post to the database. The funciton will save any selections from the
	 * parent style directory as a comma-separated string. Any sub-directories will save one style selection.
	 * 
	 * @param $post Intercepted post object to be modified with relative selections made on the form to then be saved in the
	 * database.
	 * @param $form Intercepted form object to be used to retrieve form selections to be put into the post object before it is
	 * committed into the database.
	 * @return unknown_type
	 */
	public function action_publish_post( $post, $form )
	{
		//To satisfy reference constraint for some functions
		$default_css_selection = self::DEFAULT_STYLE_SELECTION;
		$parent_css_dir = self::PARENT_CSS_DIRECTORY;
		
		//Grab all available styles
		$css_styles = array();
		$css_styles = self::getCssStyleFileNames(Site::get_dir('theme', TRUE) . self::CSS_PATH, $css_styles);
		
		$keys = array_keys($css_styles);

		$dynamicStyles = array();
		
		foreach($keys as $key)
		{
			$dynamicStyles[$key] = '';
			
			//For the parent style directory, retrieve individual checkbox selections via the regenerated html ids and save them
			//in the database as a comma-sepearated string 
			if($key == $parent_css_dir) {
				foreach($css_styles[$parent_css_dir] as $css_style)
				{
					/* skip if it's the none options */
					if ($css_style == self::DEFAULT_STYLE_SELECTION) continue;
					
					if($form->css_styles_fieldset->{self::OPTION_PREFIX . $css_style}->value == true) {
						$dynamicStyles[$parent_css_dir] .= $css_style . ',';
					}
				}
			}
			//For sub-directories, we only expect one selection. Save the selection with its respective data storage name
			else {
				$drop_down_id = $form->css_styles_fieldset->{self::OPTION_PREFIX . $key}->value;
				$dynamicStyles[$key] = $css_styles[$key][$drop_down_id];
			}
		}
		
		/* finally, set the data */
		$post->info->dynamicStyles = $dynamicStyles;
	}
	
	/**
	 * This is a function which retrieves all style files under a given directory path. The function will recurse through
	 * any sub-directories. The function will ignore any file/directory name which starts with a '.'.
	 * 
	 * @param $dir_path The given path to retrieve all style files under.
	 * @param $dir_file_map 2D array to fill the contents with a list of style file names, and return. See @return for full description.
	 * @return 2D array with the top level array having keys corresponding to a directory name. Each key will reference another
	 * list of strings. Each string will represent the name of a style file without the file extension.
	 */
	private function getCssStyleFileNames($dir_path, $dir_file_map = array())
	{
		//Alter this to include any specific exlusion other than files that start with '.'
		$exclude_array = array();
		$file_list = array();

		if(is_dir($dir_path)) {
			
			//In order to get the last element in the array as the directory name
			$dir_path = rtrim($dir_path, '/');
			$dir_path_array = explode('/', $dir_path);
			//In order for the directory path manipulation to work in the following code
			$dir_path .= '/';
			
			//Grab the last directory in the path in order to use as a key to include in the $dir_file_map
			$map_key = end($dir_path_array);
			
			$dir_handle = opendir($dir_path);
			while(($filename = readdir($dir_handle)) != false)
			{
				//To ignore any direcotries or files starting with '.' such as '.svn'
				if(strpos($filename, '.')  != 0 || strpos($filename, '.') === false) {
					$filetype = filetype($dir_path . $filename);
					
					//If we detect a directory
					if(!in_array($filename, $exclude_array)) {
						if($filetype == 'dir') {
							//Recurse into the directory
							$dir_file_map = self::getCssStyleFileNames($dir_path . $filename, $dir_file_map);
						} elseif ($filetype == 'file') {
							$filename_array = explode('.', $filename);
							if(end($filename_array) == self::STYLE_EXTENSION) {
								$file_list[] = substr($filename, 0, - (strlen(self::STYLE_EXTENSION) + 1 /* for the dot */ ));
							}
						}
					}
				}
			}
		}
		
		/* add a default 'none' option if there are options', and add this set to list */
		if (count($file_list) > 0)
		{
			/* add a default 'none' style */
			array_unshift($file_list, self::DEFAULT_STYLE_SELECTION);
			$dir_file_map[$map_key] = $file_list;
		}
		
		return $dir_file_map;
	}
	
	function action_template_header( $theme )
	{
		if (isset($theme->post->info->dynamicStyles) ) {
			foreach ( $theme->post->info->dynamicStyles as $styleGroup => $style ) {
				if ($styleGroup == self::PARENT_CSS_DIRECTORY)
				{
					/* mannny options */
					foreach(explode(',', $style) as $singleStyle) {
						if ($singleStyle != '') {
							Stack::add( 'template_stylesheet', array( Site::get_url( 'theme' ) .'/' . self::CSS_PATH . $singleStyle . '.' . self::STYLE_EXTENSION, 'screen' ) );
						}
					}
				} else {
					/* we are in a directory */
					if ($style != self::DEFAULT_STYLE_SELECTION) {
						Stack::add( 'template_stylesheet', array( Site::get_url( 'theme' ) .'/' . self::CSS_PATH . $styleGroup . '/' . $style . '.' . self::STYLE_EXTENSION, 'screen' ) );
					}
				}
				
			}
		}
	}
}

?>