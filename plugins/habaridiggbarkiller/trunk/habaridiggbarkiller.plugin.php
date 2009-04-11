<?php
/**
 * habaridiggbarkiller Plugin
 *
 * A plugin for Habari that removes the diggbar frame
 * adapted from http://farukat.es/journal/2009/04/225-javascript-diggbar-killer-not-blocker
 * 
 * @package habaridiggbarkiller
 */


class habaridiggbarkiller extends Plugin
{
	const VERSION= '1.0.0';
	
	/**
	 * function info
	 *
	 * Returns information about this plugin
	 * @return array Plugin info array
	 */
	public function info()
	{
		return array (
			'name' => 'habaridiggbarkiller',
			'url' => 'http://www.somefoolwitha.com',
			'author' => 'MatthewM',
			'authorurl' => 'http://somefoolwitha.com',
			'version' => self::VERSION,
			'description' => 'Removes the Diggbar frame.',
			'license' => 'Apache License 2.0',
		);
	}

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'habaridiggbarkiller', '506FC1DE-263A-11DE-8510-152456D89593', $this->info->version );
	}
	

	public function action_template_header()
	{
		?>
		
			
<!-- habaridiggbarkiller -->
<script type="text/javascript">
if (top !== self && document.referrer.match(/digg\.com\/\w{1,8}/)) {
		  top.location.href = self.location.href;
		}
</script>
<!-- habaridiggbarkiller END -->

		
		<?php 	}	
	
}

?>
