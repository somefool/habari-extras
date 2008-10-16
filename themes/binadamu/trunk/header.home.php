<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?php $theme->title(); ?></title>
	<link rel="alternate" type="application/atom+xml" title="Atom 1.0" href="<?php $theme->feed_alternate(); ?>" />
	<link rel="edit" type="application/atom+xml" title="Atom Publishing Protocol" href="<?php URL::out('atompub_servicedocument'); ?>" />
	<link rel="EditURI" type="application/rsd+xml" title="RSD" href="<?php URL::out('rsd'); ?>" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?php Site::out_url('theme'); ?>/style.css" />
	<?php $theme->header(); ?>

</head>
<body<?php $theme->body_class(); ?>>
<!-- header -->
<div id="container">
	<div id="header">
		<h1 id="site-name"><a href="<?php Site::out_url('habari'); ?>"><?php Options::out('title'); ?></a></h1>
		<p class="tagline"><?php Options::out('tagline'); ?></p>
		<ul id="nav">
			<li class="nav-home"><a href="<?php Site::out_url('habari'); ?>" title="<?php Options::out('title'); ?>"><?php echo $home_tab; ?></a></li>
<?php foreach ($pages as $tab) { // Menu tabs ?>
			<li class="<?php echo 'nav-' , $tab->slug; ?>"><a href="<?php echo $tab->permalink; ?>" title="<?php echo $tab->title; ?>"><?php echo $tab->title; ?></a></li>
<?php } ?>
		</ul>
	</div>
<!-- /header -->
	<hr />
