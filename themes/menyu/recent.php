<div id="recent_updates">
<?php _e('Recently Updated Plugins'); ?>
<ul>
<?php
foreach(Posts::get(array('where'=>'{posts}.id in (select post_id from {dir_plugin_versions})', 
'limit'=>5)) as $recent_plugin) {echo "<li><a 
href=\"{$recent_plugin->permalink}\">{$recent_plugin->title}</a></li>"; }
?>
</ul>
<h1>&nbsp;</h1>
<?php _e('Recently Updated Themes'); ?>
<ul>
<?php
foreach(Posts::get(array('where'=>'{posts}.id in (select post_id from {dir_theme_versions})', 
'limit'=>5)) as $recent_theme) {echo "<li><a 
href=\"{$recent_theme->permalink}\">{$recent_theme->title}</a></li>"; }
?>
</ul>
</div><!-- /recent_updates -->

