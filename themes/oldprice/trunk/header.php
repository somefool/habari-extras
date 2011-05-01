<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title><?php $theme->title(); ?></title>
<meta name="generator" content="Habari" />
<?php $theme->header(); ?>
<link rel="alternate" type="application/atom+xml" title="Atom 1.0" href="<?php $theme->feed_alternate(); ?>" />
<link rel="edit" type="application/atom+xml" title="Atom Publishing Protocol" href="<?php URL::out( 'atompub_servicedocument' ); ?>" />
<link rel="EditURI" type="application/rsd+xml" title="RSD" href="<?php URL::out( 'rsd' ); ?>" />
<link rel="Shortcut Icon" href="/favicon.ico" />
<link rel="stylesheet" type="text/css" href="<?php Site::out_url( 'theme' ); ?>/style.css" />
<!--[if lte IE 7]>
	<link href="<?php Site::out_url( 'theme' ); ?>/ie6.css" type="text/css" rel="stylesheet" media="screen" />
<![endif]-->
<script src="/mint/?js" type="text/javascript"></script>
<!-- Special thanks to Diagona Icons by Yusuke,they are really nice. -->
</head>

<body<?php $theme->body_class(); ?>>
	<div id="header">
		<h1 id="blog-title"><span><a href="<?php Site::out_url( 'habari' ); ?>"><?php Options::out( 'title' ); ?></a></span></h1>
		<div id="blog-description"><?php Options::out( 'tagline' ); ?></div>
		<div id="menu">
			<ul>
				<li class="page_item<?php if ($post_id == 0) echo " current_page_item"; ?>"><a href="<?php Site::out_url( 'habari' ); ?>" title="<?php Options::out( 'title' ); ?>"><?php echo $home_tab; ?></a></li><?php
			foreach ( $pages as $tab ) {
			?>
				<li id="page_item_<?php echo $tab->id; ?>" class="page_item<?php if($tab->id == $post_id) echo " current_page_item"; ?>"><a href="<?php echo $tab->permalink; ?>" title="<?php echo $tab->title; ?>"><?php echo $tab->title; ?></a></li>
			<?php } ?>
			</ul>
		</div>
		<div id="search">
			<?php $theme->display ('searchform' ); ?>
		</div>
	</div>
	<div id="wrapper_top">top</div>
	<div id="wrapper" class="hfeed">
