<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en"><head><meta http-equiv="Content-Type" content="text/html;charset=utf-8" /><?php $theme->header();?>	<!--[if IE]><link rel="stylesheet" href="<?php Site::out_url('3rdparty'); ?>/blueprint/ie.css" type="text/css" media="screen, projection" /><![endif]-->	<link rel="stylesheet" href="<?php Site::out_url( 'theme' ); ?>/style.css" type="text/css" media="screen" />	<title><?php if($request->display_entry && isset($post)) { echo "{$post->title} - "; } ?><?php Options::out( 'title' ) ?></title>		<link rel="alternate" type="application/atom+xml" title="Atom 1.0" href="<?php $theme->feed_alternate(); ?>" />	<link rel="edit" type="application/atom+xml" title="Atom Publishing Protocol" href="<?php URL::out( 'atompub_servicedocument' ); ?>" />	<link rel="EditURI" type="application/rsd+xml" title="RSD" href="<?php URL::out( 'rsd' ); ?>" /><script type="text/javascript">	$(function(){	$('#main').corner({			  tl: { radius: 3 },			  tr: { radius: 3 },			  bl: { radius: 3 },			  br: { radius: 3 },			  antiAlias: true,			  autoPad: true,			  validTags: ["div"] });	})</script></head><body>	<div class="container">		<div id="header" class="push-4 span-16">			<h1><a href="<?php Site::out_url( 'habari' ); ?>"><?php Options::out( 'title' ); ?></a></h1>		</div>				<div id="searchbox" class="push-4 span-16">			<?php include 'searchform.php'; ?>		</div>