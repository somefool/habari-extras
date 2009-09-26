<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="generator" content="Habari">

	<title>{hi:?request.display_entry = true && posts !== false && count(posts)}{hi:post.title} - {/hi:?}
	{hi:?request.display_entries_by_tag = true && posts !== false}{hi:tag} - {/hi:?}
	{hi:option:title}</title>

	<link rel="alternate" type="application/atom+xml" title="Atom 1.0" href="{hi:@feed_site}">
	<link rel="edit" type="application/atom+xml" title="Atom Publishing Protocol" href="{hi:url:atompub_servicedocument}">
	<link rel="EditURI" type="application/rsd+xml" title="RSD" href="{hi:url:rsd}">
	<link rel="stylesheet" type="text/css" media="screen" href="{hi:siteurl:theme}/custom.css">
	<link rel="stylesheet" type="text/css" media="screen" href="{hi:siteurl:theme}/style.css">
	<link rel="stylesheet" type="text/css" media="print" href="{hi:siteurl:theme}/print.css">
	<link rel="Shortcut Icon" href="/favicon.ico">

	<script type="text/javascript">
		window.google_analytics_uacct = "UA-303066-5";
	</script>
	{hi:@header}
</head>
<body>
	<!--begin masthead-->
	<div id="masthead">
		<div id="branding">
			<div id="siteTitle"><a href="{hi:siteurl:habari}">{hi:option:title}</a></div>
			<div id="siteTagline">{hi:option:tagline}</div>
		</div>

	<ul class="sitemenu">
		<li><a href="{hi:siteurl:habari}">{hi:"Home"}</a></li>
		{hi:pages}
			<li><a href="{hi:permalink}" title="{hi:title}" >{hi:title}</a></li>
		{/hi:pages}
		<li class="search">{hi:display:searchform}</li>
	</ul>
	</div>
	<!--end masthead-->

	<!--begin wrapper-->
	<div id="wrapper">
