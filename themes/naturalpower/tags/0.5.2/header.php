<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta name="description" content="<?php Options::out( 'title' );?> - <?php Options::out( 'tagline' ); ?>" />
<meta name="keywords" content="" />
<meta http-equiv="Content-Type" content="text/html">
<meta name="generator" content="Habari">
<link rel="alternate" type="application/atom+xml" title="Atom 1.0" href="<?php $theme->feed_alternate(); ?>">
<link rel="edit" type="application/atom+xml" title="Atom Publishing Protocol" href="<?php URL::out( 'atompub_servicedocument' ); ?>">
<link rel="EditURI" type="application/rsd+xml" title="RSD" href="<?php URL::out( 'rsd' ); ?>">
<link rel="stylesheet" type="text/css" media="screen" href="<?php Site::out_url( 'theme' ); ?>/style.css">

<title><?php Options::out( 'title' ) ?><?php if($request->display_entry && isset($post)) { echo " :: {$post->title}"; } ?></title>

<!--[if gt IE 5]>
<style>
#header #text_in {position: absolute; top: 50%;}
</style>
<![endif]-->

<?php /*
global $options;
foreach ($options as $value) {
if (get_settings( $value['id'] ) === FALSE) { $$value['id'] = $value['std']; } else { $$value['id'] = get_settings( $value['id'] ); } }
*/ ?>

<?php //wp_head(); ?>
</head>

<body>

<div id="wrap">

	<div id="top">
	
		<div id="menu">
		<ul>
		
		<li class="page_item<?php if($request->display_home) { ?>
		 current_page_item<?php } ?>"><a href="<?php Site::out_url( 'habari' ); ?>"><span>Home</span></a></li>
	
		<?php foreach ( $pages as $tab ) { ?>
		<li class="page_item<?php if(isset($post) && $post->slug == $tab->slug) { ?>
		 current_page_item<?php } ?>"><a href="<?php echo $tab->permalink; ?>" title="<?php echo $tab->title; ?>"><span><?php echo $tab->title; ?></span></a></li>
		<?php } ?>
		
		<?php
		//$pages = wp_list_pages('sort_column=menu_order&depth=1&title_li=&echo=0');
		//$pages = preg_replace('%<a ([^>]+)>%U','<a $1><span>', $pages);
		//$pages = str_replace('</a>','</span></a>', $pages);
		//echo $pages;
		?>
		</ul>
		</div>
	
		<div id="title">
		<h1><a href="<?php Site::out_url( 'habari' ); ?>"><?php Options::out( 'title' ); ?></a></h1>
		<p><?php Options::out( 'tagline' ); ?></p>
		</div>
	
	</div>

	<div id="header">
	
		<div id="text">
		<div id="text_in">
		<div id="inside">
		<p><?php echo $header_text; ?></p>
		</div>
		</div>
		</div>

	</div>