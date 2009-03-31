<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<!-- header.php -->
<html>
<head>
<title><?php Options::out( 'title' ) ?><?php 
	if ($request->display_plugin && isset($post)) { 
		echo " Plugin: {$post->title_out}"; 
	} else if ($request->display_theme && isset($post)) {
		echo " Theme: {$post->title_out}";
	} else if ($request->display_license && isset($post)) {	
		echo " License Details: {$post->title_out}";
	} 

?></title>
<meta http-equiv="Content-Type" content="text/html">
<meta name="generator" content="Habari">

<link rel="alternate" type="application/atom+xml" title="Atom 1.0" href="<?php $theme->feed_alternate(); ?>">
<link rel="edit" type="application/atom+xml" title="Atom Publishing Protocol" href="<?php URL::out( 'atompub_servicedocument' ); ?>">
<link rel="EditURI" type="application/rsd+xml" title="RSD" href="<?php URL::out( 'rsd' ); ?>">

<?php $theme->header(); ?>

<script type="text/javascript">
	$(document).ready(function() {
		$(".plugin_tabs_container").tabs();
		$("#version_accordion").accordion({
		header:'div.versiontitle'
		});
		});
</script>

</head>

<body>
<div class="container" id="outer">
<div class="container">

	<div class="span-4" id="logotype_white">
		<h1>Habari Addons</h1>
	</div>
	<div class="span-3" id="logotype_black">
<?php        if(($request->display_plugin || $request->display_plugins ) && isset($post)) { 
                echo "<h1>Plugins</h1>";
        }  else if(($request->display_theme || $request->display_themes ) && isset($post)) { 
                echo "<h1>Themes</h1>";
	} ?>
	</div>		

	
	<div class="span-19 last" id="toplinks">
		<ul>
		<li><a href="<?php Site::out_url('habari'); ?>/explore/plugins">Plugins</a></li>
		<li><a href="<?php Site::out_url('habari'); ?>/explore/themes">Themes</a></li>
		<li><a href="http://habariproject.org/">Habari</a></li>
		<li><a href="http://wiki.habariproject.org/">Wiki</a></li>
		</ul>
	</div>
</div>


<div class="container">
<!-- header.php -->
