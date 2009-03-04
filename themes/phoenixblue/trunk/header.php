<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
 <title><?php Options::out( 'title' ); ?></title>
    <meta http-equiv="Content-Type" content="text/html">
    <meta name="generator" content="Habari">

    <link rel="edit" type="application/atom+xml" title="<?php Options::out( 'title' ); ?>" href="<?php URL::out( 'atompub_servicedocument' ); ?>">
    <link rel="alternate" type="application/atom+xml" title="Atom 1.0" href="<?php echo $feed_alternate; ?>">
    <link rel="EditURI" type="application/rsd+xml" title="RSD" href="<?php URL::out( 'rsd' ); ?>">

    <link rel="stylesheet" type="text/css" media="screen" href="<?php Site::out_url( 'theme' ); ?>/style.css">

    <?php $theme->header() ?>
</head>

<body>

<div id="wrapper">

<div class="pusher"></div>
<div id="header">
	<div class="nav_title">
		<h1><a href="<?php Site::out_url( 'habari' ); ?>"><?php Options::out( 'title' ); ?></a></h1>
        
	</div>
	<div class="navigation">
		<ul id="miniflex">
		
          <?php
          // Menu tabs
          foreach ( $nav_pages as $tab ) {
            $link = ucfirst($tab->slug);
            echo "<li><a href=\"{$tab->permalink}\" title=\"{$tab->title}\">  {$link}</a></li>";
          }
          if ( $loggedin ): ?>
            <li class="admintab"><a href="<?php Site::out_url( 'admin' ); ?>" title="Admin area">  Site Admin</a></li>
          <?php endif; ?>
		</ul>
	</div>
</div>

<div id="content">
