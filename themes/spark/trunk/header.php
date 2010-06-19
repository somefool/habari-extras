<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en-US">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php if($request->display_entry && isset($post)) { echo "{$post->title} - "; } ?><?php Options::out( 'title' ) ?></title>
<meta name="generator" content="Habari">
<link rel="alternate" type="application/atom+xml" title="Atom 1.0" href="<?php $theme->feed_alternate(); ?>">
<link rel="edit" type="application/atom+xml" title="Atom Publishing Protocol" href="<?php URL::out( 'atompub_servicedocument' ); ?>">
<link rel="EditURI" type="application/rsd+xml" title="RSD" href="<?php URL::out( 'rsd' ); ?>">
<link rel="stylesheet" type="text/css" media="screen" href="<?php Site::out_url( 'theme' ); ?>/css/screen.css">
<link rel="stylesheet" type="text/css" media="print" href="<?php Site::out_url( 'theme' ); ?>/css/print.css">
<script type="text/javascript" src="http://code.jquery.com/jquery-latest.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
<script type="text/javascript" src="http://localhost/habari-dev/user/themes/spark/js/loginbox.js"></script>
<?php $theme->header(); ?>
</head>

<body>
<div id="skiplinks">
<a href="#navigation">Jump to navigation</a>
<a href="#content">Jump to main content</a>
<a href="#searchform">Jump to search form</a>
</div>

<div id="container">



<?php if ( !$loggedin ) { ?>
<div id="login-box">
<div class="login-button"><a href="#">Log in</a></div>
<div class="clear"></div>
<div class="login-form">
<?php Plugins::act( 'theme_loginform_before' ); ?>
<form method="post" action="<?php URL::out( 'auth', array( 'page' => 'login' ) ); ?>" id="loginform">
<div><label for="habari_username"><?php _e('Name:'); ?></label></div>
<div><input type="text" size="25" name="habari_username" id="habari_username"></div>
<div><label for="habari_password"><?php _e('Password:'); ?></label></div>
<div><input type="password" size="25" name="habari_password" id="habari_password"></div>
<?php Plugins::act( 'theme_loginform_controls' ); ?>
<div><button><?php _e('Log in'); ?></button></div>
</form>
<?php Plugins::act( 'theme_loginform_after' ); ?>
</div>
</div>
<?php
}
?>
















<h1><a href="<?php Site::out_url( 'habari' ); ?>"><?php Options::out( 'title' ); ?></a></h1>
<div id="main">
