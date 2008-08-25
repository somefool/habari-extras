<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" 
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>
	<?php Options::out( 'tagline' ); ?>
</title>
<meta http-equiv="Content-Type" content="text/html">
<meta name="generator" content="Habari">

<link rel="alternate" type="application/atom+xml" title="Atom" href="http://www.chrisjdavis.org/atom/1">
<link rel="edit" type="application/atom+xml" title="Atom Publishing Protocol" href="<?php URL::out( 'atompub_servicedocument' ); ?>">
<link rel="EditURI" type="application/rsd+xml" title="RSD" href="<?php URL::out( 'rsd' ); ?>">

<link rel="stylesheet" type="text/css" media="screen" href="<?php Site::out_url( 'theme' ); ?>/style.css">
<?php $theme->header(); ?>
<?php Stack::out('template_header_javascript', '<script type="text/javascript" src="%s"></script>'); ?>
</head>
<body>
<div class="container">
	<div class="header column span-13">
		<ul class="navigation">
			<li class="first-item"><a href="/" title="Go Home">Home</a></li>
		</ul>
	</div>
	<div class="searchfield">
		<?php Plugins::act( 'theme_searchform_before' ); ?>
		     <form method="get" id="searchform" action="<?php URL::out('display_search'); ?>">
		      	<input type="text" id="s" name="criteria" class="search" value="search this site"> 
		     </form>
		<?php Plugins::act( 'theme_searchform_after' ); ?>
	</div>
