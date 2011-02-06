<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
	"http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="generator" content="Habari">

	<title>{hi:?request.display_entry = true && posts !== false && count(posts)}{hi:post.title} - {/hi:?}
	{hi:?request.display_entries_by_tag = true && posts !== false}{hi:tag} - {/hi:?}
	{hi:option:title}
	</title>

	<link rel="stylesheet" type="text/css" media="screen" href="{hi:siteurl:theme}/style.css">
	<link rel="stylesheet" type="text/css" media="print" href="{hi:siteurl:theme}/print.css">
	<link rel="Shortcut Icon" href="/favicon.ico">
	
	{hi:@header}
	<script type="text/javascript">
	$(document).ready(function() {
	$('#content *').tooltip();
	});
	</script>
</head>
<body>
	<div id="wrapper">
		<div id="masthead">
			<h1 id="title"><a href="{hi:siteurl:habari}">{hi:option:title}</a></h1>
		</div>