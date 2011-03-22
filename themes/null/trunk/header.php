<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title><?php Options::out( 'title' ); ?></title>
	<script src="http://ajax.cdnjs.com/ajax/libs/headjs/0.9/head.min.js"></script>
	<?php $theme->header( ); ?>
</head>
<body class="<?php $theme->body_class( ); ?>">
	<div id="wrapper">
		<header id="nameplate" role="banner">
			<hgroup>
				<h1 class="logo"><a href="<?php Site::out_url( 'habari' ); ?>" rel="home"><?php Options::out( 'title' ); ?></a></h1>
				<h2 class="tagline"><?php Options::out( 'tagline' ); ?></h2>
			</hgroup>
			<nav id="mainmenu" role="navigation">
				<?php $theme->area( 'nav' ); ?>
			</nav>
		</header>