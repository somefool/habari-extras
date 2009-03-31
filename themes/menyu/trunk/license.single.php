<?php $theme->display('header'); ?>
<!-- license.single.php -->

<div class="prepend-5 span-19 last">
<h2 class="plugin_title"><a href="<?php echo $post->permalink; 
?>"><?php echo 
$post->title_out; ?></a></h2>
</div>
</div>
<div class="container">

<div class="column span-4">

<?php $theme->display('recent'); ?>

</div>


<div class="span-18 prepend-1 last">

<h3>Quick Summary (not legally binding)</h3>
<?php echo Format::autop( $post->info->simpletext ); ?>

<h3>Link to complete text</h3>
<a href="<?php echo $post->info->link; ?>" title="Read full text of <?php echo $post->title_out; ?>"><?php echo $post->info->link; ?>
</a>

<h3>Plugins using this license</h3>
<ul>
<?php 
// var_dump($post->info->shortname);
// var_dump(Posts::get(array('where'=>'{posts}.id in (select post_id from {dir_plugin_versions} where {dir_plugin_versions}.license = "ASL 2.0") ')));
// where license="{$post->info->shortname}")')));

foreach(Posts::get(array('where'=>'{posts}.id in (select post_id from {dir_plugin_versions} where license="' . $post->info->shortname . '")')) as 
$licensed) { 	echo "<li><a href=\"{$licensed->permalink}\">{$licensed->title}</a></li>";
} ?>
</ul>
</div>

<!-- /license.single.php -->
<?php $theme->display('footer'); ?>



