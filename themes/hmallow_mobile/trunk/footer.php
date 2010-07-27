
<!-- bottom meta -->
<div id="bottommeta">
Choose from <a href="<?php URL::out( 'atom_feed', array( 'index' => '1' ) ); ?>" title="<?php _e('Syndicate this sitw'); ?>">full feed</a> or <a href="<?php URL::out( 'atom_feed_comments' ); ?>" title="<?php _e('The latest comments to all posts'); ?>">comments feed</a>. 
<br />
<a href="<?php Site::out_url( 'habari' ); ?>" title="<?php Options::out('title'); ?>"><?php Options::out('title'); ?></a> is powered by <a href="http://www.habariproject.org/" title="Powered by Habari">Habari <?php
echo Version::get_habariversion();
if(Version::is_devel()) {
	echo ' r' . Version::get_svn_revision();
}
?></a>.
<br />
Theme: <a href="http://www.somefoolwitha.com/hmallow2" title="hMallow Mobile">hMallow Mobile</a>. Administrator <a href="<?php Site::out_url( 'habari' ); ?>/admin"><?php _e('admin'); ?></a>.

</div>


<!-- http://www.somefoolwitha.com/hmallow Design by Matthew 
& please dont delete this comment .... credit where credits due -->

<div id="footer_area">
<?php $theme->area('footer'); ?>
</div>

<?php $theme->footer(); ?>


