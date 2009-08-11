<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	
	<title><?php echo sprintf( _t( 'Register on %s' ), Options::get('title') ); ?></title>
	
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="generator" content="Habari">

	<?php $theme->header(); ?>
	
</head>
<body class="success">

	<div id="page">

		<h1><a href="<?php Site::out_url('habari'); ?>" title="<?php _e('Go to Site'); ?>"><?php Options::out('title'); ?></a></h1>

		<div class="container">
			<p>Congratulations, <strong><?php echo $user->displayname; ?></strong>, you have successfully registered. Feel free to <a href="<?php Site::out_url('habari'); ?>" title="<?php _e('Go to Site'); ?>">return to the site</a>.</p>
		</div>

	</div>
	
</body>
</html>