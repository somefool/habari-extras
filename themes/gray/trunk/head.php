<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title><?php echo $page_title; ?></title>
	<?php $theme->header(); ?>
</head>
<body class="<?php $theme->body_class(); ?>">
<div id="<?php echo $yui_id; ?>" class="<?php echo $yui_class; ?>">
	<div id="hd">
		<h1><a href="/"><?php echo $site_title; ?></a></h1>
		<?php $theme->area('nav'); ?>
	</div>

