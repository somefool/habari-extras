<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>
		<?php 
				
			if($request->display_entry && isset($post)) { echo "{$post->title} | "; } elseif ($request->display_entries_by_tag) { echo "Tag Archives for '".str_replace("-","&nbsp;","$tag")."' | "; } elseif ($request->display_search) { echo "Search 
for '".$_GET['criteria']."' | "; } elseif ($request->display_entries_by_date) { echo "Archives for " . date('F', mktime(0,0,0,$month,1)) . " $year | "; }

			Options::out( 'title' );

			if($request->display_home) { echo " | "; Options::out( 'tagline' ); }

		?>
		</title>
 		<meta http-equiv="Content-Type" content="text/html">
 		<meta name="generator" content="Habari">
	 	<link rel="alternate" type="application/atom+xml" title="Atom 1.0" href="<?php $theme->feed_alternate(); ?>">
 		<link rel="edit" type="application/atom+xml" title="Atom Publishing Protocol" href="<?php URL::out( 'atompub_servicedocument' ); ?>">
 		<link rel="EditURI" type="application/rsd+xml" title="RSD" href="<?php URL::out( 'rsd' ); ?>">
		<link rel="stylesheet" type="text/css" media="screen" href="<?php Site::out_url( 'theme' ); ?>/style.css">
		<!--[if IE]>
		<link rel="stylesheet" type="text/css" media="screen" href="<?php Site::out_url( 'theme' ); ?>/style-ie.css">
		<![endif]-->
		<?php $theme->header(); ?>
	</head>
	<body id="home">
 		<div id="wrap">
  			<div id="header">
				<h1><a href="<?php Site::out_url( 'habari' ); ?>"><?php Options::out( 'title' ); ?></a></h1>
   				<p id="tagline"><?php Options::out( 'tagline' ); ?></p>
   				<ul id="nav">
    				<li><a href="<?php Site::out_url( 'habari' ); ?>" title="<?php Options::out( 'title' ); ?>" <?php if($request->display_home) { ?> class="selected" <?php } ?>>Home</a></li>
					<?php
							foreach ($pages as $tab) {
					?>
    				<li><a href="<?php Site::out_url( 'habari' ); ?>/<?php echo $tab->slug; ?>" title="<?php echo $tab->title; ?>" <?php if($request->display_404 || $request->display_search) { } elseif($tab->slug == $post->slug) { ?> class="selected" <?php } ?>><?php echo $tab->title; ?></a></li>
					<?php } ?>
					<li id="rss"><a href="<?php $theme->feed_alternate(); ?>">Atom</a></li>
					<li class="clear"></li>
   				</ul>
  			</div>
			<div id="content">
<!-- /header -->
