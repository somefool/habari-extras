<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title><?php echo $page_title; ?></title>
	<?php $theme->header(); ?>
</head>
<body class="<?php $theme->body_class('test'); ?>">
<div id="doc2" class="yui-t7">
	<div id="hd">
		<h1><a href="/"><?php echo $site_title; ?></a></h1>
		<ul id="nav">
		<?php $theme->area('nav'); ?>
		</ul>
	</div>

