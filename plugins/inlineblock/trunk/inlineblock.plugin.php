<?php

/**
 * Create a block with arbitrary content.
 *
 */
class InlineBlock extends Plugin
{
	/**
	 * Output the content of the block, and nothing else.
	 **/
	public function action_block_content($block, $theme)
	{
		if(User::identify()->loggedin) {
			$block_id = 'inline_block_' . $block->id;
			$href = URL::get('admin', array('page' => 'configure_block', 'blockid' => $block->id, 'inline' => 1, 'iframe' => 'true', 'width' => 600, 'height' => 400, 'block' => $block_id) );
			if($_GET['inline'] == 1) {
				$block->content = '<a class="editable-block-link" href="' . $href . '" onclick="$.prettyPhoto.open($(this).attr(\'href\'),\'Edit Content\',\'Hello!\');return false;">Edit</a>' . $block->content;
			}
			else {
				$block->content = '<div class="editable-inline-block" id="' . $block_id . '"><a class="editable-block-link" href="' . $href . '" onclick="$.prettyPhoto.open($(this).attr(\'href\'),\'Edit Content\',\'Edit the content, then click Save.  Reload the page to see the changes.\');return false;">Edit</a>' . $block->content . '</div>';
			}
		}
		return $block;
	}

	public function action_template_header($theme)
	{
		Stack::add('template_stylesheet', array($this->get_url() . '/inlineblock.css', 'screen'));
	}
	
	public function action_admin_header($theme)
	{
		if ( $theme->page == 'configure_block' && $_GET['inline'] == 1) {
			Plugins::act('add_jwysiwyg_admin');
			
			Stack::add('admin_stylesheet', array('#block_admin { display: none; } textarea { height: 250px; width: 540px; }', 'screen'));
		}
	}
	
	public function action_admin_footer($theme)
	{
		if ( $theme->page == 'configure_block' && $_GET['inline'] == 1 ) {
			echo <<<JWYSIWYG
			<script type="text/javascript">
			$(function() {
				$('#content textarea').wysiwyg({
				    resizeOptions: {},
				    controls : {html : {visible: true}}
				});
			});
			</script>
JWYSIWYG;
		}
	}
}

?>
