<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title><?php Options::out( 'title' ) ?><?php if($request->display_entry && isset($post)) { echo " :: {$post->title}"; } ?></title>
	<meta http-equiv="Content-Type" content="text/html">
	<meta name="generator" content="Habari">
	<link rel="alternate" type="application/atom+xml" title="Atom 1.0" href="<?php $theme->feed_alternate(); ?>">
	<link rel="edit" type="application/atom+xml" title="Atom Publishing Protocol" href="<?php URL::out( 'atompub_servicedocument' ); ?>">
	<link rel="EditURI" type="application/rsd+xml" title="RSD" href="<?php URL::out( 'rsd' ); ?>">
	<link rel="stylesheet" type="text/css" media="screen" href="<?php Site::out_url( 'theme' ); ?>/style.css">
	<!--[if lte IE 6]>
	<link rel="stylesheet" href="<?php Site::out_url( 'theme' ); ?>/ie.css" type="text/css" media="screen" />
	<![endif]-->
	<?php $theme->header(); ?>
	<?php Stack::out('template_header_javascript', '<script type="text/javascript" src="%s"></script>'); ?>
</head>
<body>
<div id="page-top">
	<div id="wrapper-top">
		<div id="top-primary">
			<div id="header">
				<div id="title">
					<?php if ( $show_title_image ) : ?>
						<img src="<?php Site::out_url( 'theme' ); ?>/images/title.png" rel="<?php Options::out( 'title' ); ?>" /> <span class="hidden"><h1><a href="<?php Site::out_url( 'habari' ); ?>"><?php Options::out( 'title' ); ?></a></h1></span>
					<?php else : ?>
						<h1><a href="<?php Site::out_url( 'habari' ); ?>"><?php Options::out( 'title' ); ?></a></h1>
					<?php endif; ?>
						<p class="tagline"><?php Options::out( 'tagline' ); ?></p>
				</div>


				<div id="navbar">
					<div id="nav">
						<ul>
							<li<?php if ($post_id == 0) echo ' class="current-page"'; ?>>
								<a href="<?php Site::out_url( 'habari' ); ?>"><?php echo $home_label; ?></a>
							</li>
							<?php foreach ( $pages as $pagelink ) : ?>
							<li<?php if ($pagelink->id == $post_id) echo ' class="current-page"'; ?>>
								<a href="<?php echo $pagelink->permalink; ?>" title="<?php echo $pagelink->title; ?>"><?php echo $pagelink->title; ?></a>
							</li>
							<?php endforeach; ?>
						</ul>
					</div>
					<div id="utils">
						<ul>
							<?php if ( isset( $user ) && is_object( $user ) ) : ?>
							<li class="login"><a href="<?php Site::out_url( 'habari' ); ?>/user/logout">Logout</a></li>
							<?php else : ?>
							<li class="login"><a href="<?php Site::out_url( 'habari' ); ?>/user/login">Login</a></li>
							<?php endif; ?>
						</ul>
					</div>
					<div class="clear"></div>
				</div>
			</div>