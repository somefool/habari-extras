<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
<title><?php 
	if( ( $request->display_entry || $request->display_page ) && isset( $post ) ) { 
		echo "{$post->title} - "; 
	} elseif( $request->display_entries_by_tag && isset( $posts ) ) {
		echo Controller::get_var('tag') . ' - ';
	}
	?> 
	<?php Options::out( 'title' ) ?>
</title>

<meta http-equiv="Content-Type" content="text/html">
<meta name="generator" content="Habari">
<link rel="edit" type="application/atom+xml" title="<?php Options::out( 'title' ); ?>" href="<?php URL::out( 'atompub_servicedocument' ); ?>">
<link rel="alternate" type="application/atom+xml" title="Atom 1.0" href="<?php URL::out( 'atom_feed', array( 'index' => '1' ) ); ?>">
<link rel="EditURI" type="application/rsd+xml" title="RSD" href="<?php URL::out( 'rsd' ); ?>">
<link rel="stylesheet" type="text/css" media="screen" href="<?php Site::out_url( 'theme' ); ?>/style.css">
<?php if ( !empty($theme->css_color) && $theme->css_color != 'black' ) { ?>
	<link rel="stylesheet" type="text/css" media="screen" href="<?php Site::out_url( 'theme' ); ?>/<?php echo $theme->css_color; ?>.css">
<?php } ?>
<?php $theme->header() ?>
</head>

<body>
	<div id="header">
		<div class="inside">
			<div id="search">
				<form id="searchform" method="get" action="<?php URL::out( 'display_search' ); ?>">
 					<div class="searchimg"></div>
					<div><input type="text" id="f" value="" name="criteria" size="15"></div>
				</form>
			</div>

			<h2><a href="<?php Site::out_url( 'habari' ); ?>/"><?php Options::out( 'title' ); ?></a></h2>	
			<table><tr><td>
			<?php if (Plugins::is_loaded('Twitter') && $theme->twitter_in=='header') { ?>
				<p><?php $theme->twitter (); ?> <a href="http://twitter.com/<?php echo urlencode( Options::get( 'twitter:username' )); ?>">via Twitter</a></p>
			<?php } else { ?>
				<?php if (Options::out( 'tagline' ) != '') { ?><p><?php Options::out( 'tagline' ); ?></p><?php } ?>
			<?php } ?>
			</td></tr></table>
		</div>
	</div>


<!-- end header.php -->
