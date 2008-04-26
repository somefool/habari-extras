
<div id="search">
	<?php $theme->search_form() ?>
</div>
<div id="feeds">
	<div class="feedlink"><a href="<?php URL::out( 'atom_feed', array( 'index' => '1' ) ); ?>">{blog entries}</a></div>
	<div class="feedlink"><a href="<?php URL::out( 'atom_feed_comments' ); ?>">{comments}</a></div>
</div>
<div id="habari-link">
<?php if ($show_powered) : ?>
	<a href="http://www.habariproject.org" title="Powered by Habari"><img src="<?php Site::out_url('theme'); ?>/images/pwrd_habari.png" alt="Powered by Habari"></a>
<?php  endif; ?>
</div>
<div id="sidebar">
<!-- call your plugins theme methods here-->
</div>
