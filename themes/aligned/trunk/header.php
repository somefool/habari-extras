<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?php if($request->display_entry && isset($post)) { echo "{$post->title} - "; } ?><?php Options::out( 'title' ) ?></title>
<meta http-equiv="Content-Type" content="text/html">
<meta name="generator" content="Habari">

<link rel="alternate" type="application/atom+xml" title="Atom 1.0" href="<?php $theme->feed_alternate(); ?>">
<link rel="edit" type="application/atom+xml" title="Atom Publishing Protocol" href="<?php URL::out( 'atompub_servicedocument' ); ?>">
<link rel="EditURI" type="application/rsd+xml" title="RSD" href="<?php URL::out( 'rsd' ); ?>">

<link rel="stylesheet" type="text/css" media="screen" href="<?php Site::out_url( 'theme' ); ?>/style.css">

<?php $theme->header(); ?>
</head>

<body>
<div id="wrapper">
	<div id="header" class="clearfix">
		<div id="header_left" class="left_content">
			<h1><a href="<?php Site::out_url( 'habari' ); ?>"><?php Options::out( 'title' ); ?> <span>| <?php Options::out( 'tagline' ); ?></span></a></h1>
			<div id="header_image">
				<a href="<?php Site::out_url( 'habari' ); ?>"><img src="<?php Site::out_url( 'theme' ); ?>/headers/<?php $theme->header_image(); ?>" alt="<?php Options::out( 'title' ); ?>"></a>
			</div>
		</div>
		<div id="header_right" class="right_content">
			<?php include 'searchform.php'; ?>
			<div id="preface">
				<div id="preface_inner">
					<p><?php if (Plugins::is_loaded('Colophon')) { $theme->colophon(); } else { ?>You need to load the <a href="http://habariproject.org/dist/plugins/colophon.zip">colophon plugin</a><?php } ?></p>
				</div>
			</div>
		</div>
	</div>
	<div id="content_wrapper" class="clearfix">
		<div class="left_content" id="content_left">
			<ul id="menu" class="clearfix">
				<li><a href="<?php Site::out_url( 'habari' ); ?>" title="<?php Options::out( 'title' ); ?>">Home</a></li>
				<?php
				foreach ( $pages as $tab ) {
				?>
				    <li><a href="<?php echo $tab->permalink; ?>" title="<?php echo $tab->title; ?>"><?php echo $tab->title; ?></a></li>
				<?php
				}
				if ( $loggedin ) { ?>
				    <li><a href="<?php Site::out_url( 'admin' ); ?>" title="Admin area">Admin</a></li>
				<?php } ?>
			</ul>
	
