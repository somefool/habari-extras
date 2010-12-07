<?php

class PageMenuPlugin extends Plugin
{

	function action_init()
	{
		$this->add_template( 'pagemenu', dirname(__FILE__) . '/pagemenu.php' );
		$this->add_template( 'block.pagemenu', dirname(__FILE__) . '/block.pagemenu.php' );
	}

	public function configure()
	{
		if($_SERVER['REQUEST_METHOD']=='POST') {
			$this->process_form();
		}
		else {
			$this->show_form();
		}
		
	}
	
	public function show_form()
	{
		$candidates = Posts::get(array('content_type'=>'page', 'status'=>'published', 'nolimit'=>true));
		$candidates = (array) $candidates;
		
		// prepend a fake page for home
		$home = new stdClass();
		$home->id = 0;
		$home->title = 'Home';
		
		array_unshift( $candidates, $home );
		
		usort( $candidates, array($this, 'sort_candidates'));
		$menuids = (array)Options::get('pagemenu__ids');
		
		echo '<form action="" method="post">';
		echo '<ul id="pagemenu_candidates">';
		foreach($candidates as $candidate) {
			$checked = in_array($candidate->id, $menuids) ? ' checked' : '';
			echo '<li><label><input type="checkbox" name="pagemenu[]" value="' . $candidate->id . '" ' . $checked . '> ' . $candidate->title . '</label></li>';
		}
		echo '</ul>';
		echo '<div><label><input id="#pagemenu_show" type="checkbox" checked onclick="pagemenu_toggle(this);"> List pages not currently selected for the menu</label></div>';
		echo '<input type="submit" name="submit" value="Set Menu">';
		echo '</form>';
	}
	
	public function sort_candidates($a, $b)
	{
		static $menuids = null;
		if(empty($menuids)) {
			$menuids = (array)Options::get('pagemenu__ids');
		}
		
		$ia = in_array($a->id, $menuids);
		$ib = in_array($b->id, $menuids);
		
		if(!$ia && !$ib) {
			return $a->title > $b->title ? 1 : -1;
		}
		elseif($ia && !$ib) {
			return -1;
		}
		elseif(!$ia && $ib) {
			return 1;
		}
		else {
			$sa = array_search($a->id, $menuids);
			$sb = array_search($b->id, $menuids);
			
			return $sa > $sb ? 1 : -1;
		}
	}
	
	public function action_admin_header()
	{
		$script = <<< HEADER_JS
$(function(){ 
	$('#pagemenu_candidates').sortable({axis: 'y', distance: 3});
});
function pagemenu_toggle(e){
	if($(e).attr('checked')) {
		$('#pagemenu_candidates li').show();
	} else {
		$('#pagemenu_candidates input[type=checkbox]:not(:checked)').parents('li').hide();
	}
}
HEADER_JS;
		Stack::add( 'admin_header_javascript',  $script, 'pagemenu', array('jquery', 'ui.sortable') );
		
		$styles = "
#pagemenu_candidates {
margin: 10px;
}
#pagemenu_candidates li {
	border: 1px solid #ccc;
	border-width: 1px 2px;
	width: 50%;
	padding: 5px;
	margin-bottom: 3px;

}

#pagemenu_candidates label {
		background: url(" .  Site::get_url('system') . "/admin/images/dashboardhandle.png) no-repeat scroll 320px center;
		display: block;
}

#pagemenu_candidates li:hover {
	background: #f3f3f3;
}

";
		Stack::add( 'admin_stylesheet', array($styles, 'screen'), 'pagemenu');
	}
	
	public function process_form()
	{
		$menus = $_POST['pagemenu'];
		
		Options::set('pagemenu__ids', $menus);
		
		Session::notice(_t('Updated the menu.'));
		$this->show_form();
		//Utils::redirect();  // Should do this instead, wasn't working locally?
	}
	
	public function theme_pagemenu($theme)
	{
		$theme->start_buffer();

		$menuids = (array)Options::get('pagemenu__ids');
		
		if(count($menuids) > 0) {
		
			$orderbys = array();
			foreach(array_reverse($menuids) as $id) {
				$orderbys[] = "id = {$id}";
			}
			$orderby = implode(',', $orderbys);
	
		$menupages = Posts::get(array('content_type'=>'page', 'id'=>$menuids, 'orderby' => $orderby, 'nolimit' => true));
			foreach($menupages as $key => $page) {
				if(isset($theme->post) && $theme->post->slug == $page->slug) {
					$theme->activemenu = $page;
					$menupages[$key]->active = true;
					$menupages[$key]->activeclass = 'active';
				}
				else {
					$menupages[$key]->active = false;
					$menupages[$key]->activeclass = 'inactive';
				}
			}
		}
		else {
			$menupages = new Posts();
		}
		
		$theme->menupages = $menupages;
		$out = $theme->fetch('pagemenu', true);
		
		return $out;
	}

	public function help()
	{
		return <<< END_HELP
<p>To output the menu in your theme, insert this code where you want the menu to appear:</p>
<blockquote><code>&lt;?php \$theme-&gt;pagemenu(); ?&gt;</code></blockquote>
<p>The default theme only displays the &lt;li&gt; and &lt;a&gt; elements for the menu item.  
If you want to alter this, you should copy the <tt>pagemenu.php</tt> template included with 
this plugin to your current theme directory and make changes to it there.</p>
END_HELP;
	}

	public function filter_block_list($block_list)
	{
		$block_list['pagemenu'] = _t('Page Menu');
		return $block_list;
	}
	
	public function action_block_content_pagemenu($block, $theme)
	{
		$menuids = (array)Options::get('pagemenu__ids');
		
		if(count($menuids) > 0) {
		
			$orderbys = array();
			foreach(array_reverse($menuids) as $id) {
				$orderbys[] = "id = {$id}";
			}
			$orderby = implode(',', $orderbys);
	
			$menupages = (array)Posts::get(array('content_type'=>'page', 'id'=>$menuids, 'orderby' => $orderby));
			
			if ( in_array( 0, $menuids ) ) {
				$home = new stdClass();
				$home->id = 0;
				$home->title = _t('Home');
				$home->permalink = Site::get_url('habari');
				array_unshift( $menupages, $home);
			}
			
			foreach($menupages as $key => $page) {
				if(isset($theme->post) && $theme->post->id == $page->id) {
					$block->activemenu = $page;
					$menupages[$key]->active = true;
					$menupages[$key]->activeclass = 'active';
				}
				else {
					$menupages[$key]->active = false;
					$menupages[$key]->activeclass = 'inactive';
				}
			}
		}
		else {
			$menupages = new Posts();
		}
		
		$block->menupages = $menupages;
	}	
	
}


?>
