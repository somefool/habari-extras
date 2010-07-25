<?php
class FileMan extends Plugin { 
 public function filter_admin_access_tokens(array $require_any, $page){
  /* Set access tokens */
  switch ($page) {
   case 'fileman':
    $require_any = array('fileman' => true);
    break;
   }
  return $require_any;
 }

 public function action_init() {
  /* Give FileMan its own page */
  $this->add_template('fileman', dirname($this->get_file()) . '/fileman.php');
 }
 public function filter_adminhandler_post_loadplugins_main_menu(array $menu){
  /* Add FileMan to the main menu */
  /* Set menu variables */
  $item_menu = array('pagename' => array(
   'url' => URL::get('admin','page=fileman'),
   'title' => _t('File Manager'),
   'text' => _t('FileMan'),
   'hotkey' => 'F',
   'selected' => false
  ) );
  /* Do stuff */
  $slice_point = array_search('options', array_keys($menu));
  array_splice($menu, $slice_point, 0, $item_menu);
  return $menu;
 }
}
?>
