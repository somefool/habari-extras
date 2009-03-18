<?php
require_once( dirname(__FILE__) . '../../../../wp-config.php');
require_once( dirname(__FILE__) . '/functions.php');
header("Content-type: text/css");

global $options;

foreach ($options as $value) {
        if (get_settings( $value['id'] ) === FALSE) { $$value['id'] = $value['std']; } else { $$value['id'] = get_settings( $value['id'] ); } }
?>

html, body {
	font-family: arial;
	font-size: 12px;
	border: 0;
	color: #000;
}

body, img, p, h1, h2, h3, h4, h5, ul, ol, li, form, blockquote {
	margin: 0;
	padding: 0;
}

body {
	background: url(img/body_bg.jpg);
	padding-bottom: 10px;
}

p {
	line-height: 19px;
	padding: 10px 0;
}

h1, h2, h3, h4, h5 {
	padding: 10px 0;
}

ul, ol {
	list-style: none;
	padding: 10px 0;
}

small {
	font-size: 11px;
}

code {
	background: #FFFFC1;
}

a {
	color: #C10000;
	text-decoration: underline;
}

a:hover {
	text-decoration: none;
}

a img {
	border: none;
}

blockquote {
	font-size: 12px;
	width: 80%;
	padding: 0 10%;
	margin: 10px auto;
	background: url(img/quote.gif) no-repeat 20px 7px;
	color: #717171;
	line-height: 19px;
	font-style: italic;
}

/* Wrap */

#wrap {
	width: 869px;
	margin: 0 auto;
}

/* Top */

#top {
	width: 100%;
	padding-top: 25px;
	padding-bottom: 20px;
}

/* Top (Title) */

#title {
	width: 570px;
}

#top h1 {
	font-family: "Trebuchet MS";
	font-size: 25px;
	padding: 0 0 0 40px;
	display: block;
	background: url(img/logo.gif) no-repeat 0 5px;
}

#top h1 a {
	color: #fff;
	text-decoration: none;
	font-weight: normal;
}

#top h1 a:hover {
	color: #F4DD2F;
}

#top p {
	font-size: 12px;
	font-family: "Trebuchet MS";
	color: #BBBBBB;
	padding: 0 0 0 40px;
}

/* Top (Menu) */

#menu {
	width: 100%;
}

#menu ul {
	font-weight: bold;
	font-size: 13px;
	float: right;
}

#menu ul li {
	display: inline;
}

#menu ul li a {
	float: left;
	text-decoration: none;
	padding: 0 0 0px 4px;
	line-height: 24px;
	margin-left: 12px;
	color: #9B9B9B;
}

#menu ul li a span {
	display: block;
	padding-right: 9px;
	padding-left: 6px;
	padding-bottom: 2px;
}

#menu ul li.current_page_item a {
	background: url(img/link_left.gif) no-repeat left top;
	color: #fff;
}

#menu ul li.current_page_item a span {
	background: url(img/link_right.gif) no-repeat right top;
}

#menu ul li a:hover {
	background: url(img/link_left.gif) no-repeat left top;
	color: #F4DD2F;
}

#menu ul li a:hover span {
	background: url(img/link_right.gif) no-repeat right top;
}

/* Header */

#header {
	width: 580px;
	height: 140px;
	background: url(img/content_top.gif);
	padding: 9px 0 0 9px;
}

#header #text {
	width: 570px;
	height: 140px;
	background: url(img/header_<?php echo $C9_header_bg; ?>.jpg);
	display: table;
	position: relative;
	overflow: hidden;
}

#header #text_in {
	display: table-cell;
	vertical-align: middle;
}

#header #inside {
	position: relative;
	top: -50%;
	width: 430px;
	font-family: Georgia;
	font-size: 18px;
	color: #fff;
	margin: 0 auto;
	text-align: center;
}

#header #inside p {
	line-height: 25px;
}

/* Content Wrap */

#content_wrap {
	width: 100%;
	background: url(img/content_wrap_bg.gif);
}

/* Content */

#content {
	width: 522px;
	float: left;
	padding: 25px 40px 0 27px;
	color: #4A4A4A;
}

#content h2 {
	font-family: Georgia;
	font-size: 17px;
	font-weight: normal;
	padding: 10px 0;
}

#content h2 a {
	color: #000;
	text-decoration: none;
}

#content h2 a:hover {
	text-decoration: underline;
}

#content img {
	border: #BBBBBB 1px solid;
	padding: 2px;
}

#content h1, #content h3, #content h4 {
	font-family: Georgia;
}

#content h1 {
	font-size: 20px;
}

#content h3 {
	font-size: 15px;
}

#content h4 {
	font-size: 12px;
}

/* Content (lists) */

#content ul, #content ol {
	padding-left: 50px;
	line-height: 22px;
}

#content ul li {
	background: url(img/square.gif) no-repeat 0 9px;
	padding-left: 15px;
}

#content ol {
	list-style: decimal;
	padding-left: 70px;
}

/* Content (img align) */

#content img.alignleft {
	margin: 10px 10px 8px 0;
}

#content img.alignright {
	margin: 10px 0px 8px 10px;
}

#content img.centered {
	margin: 10px auto 20px auto;
	display: block;
}

/* Post Wrap */

.post_wrap {
	width: 100%;
	padding-bottom: 15px;
}

p.post_details {
	background: url(img/icon_folder.gif) no-repeat left 5px;
	padding: 0 0 0 16px;
	color: #858585;
	margin-top: -9px;
	font-size: 11px;
	line-height: 20px;
}

p.post_details a {
	color: #000;
	text-decoration: none;
}

p.post_details a:hover {
	text-decoration: underline;
}

/* More Entries */

#more_entries {
	width: 100%;
	padding-top: 10px;
}

/* Comments (single.php and comments.php) */

.comments_wrap {
	width: 470px;
	margin: 20px 0 0px 10px;
	clear: both;
	padding-bottom: 5px;
}

.comments_wrap .left {
	width: 46px;
	padding-top: 2px;
	float: left;
}

.comments_wrap .right {
	width: 380px;
	float: left;
	padding-left: 15px;
}

.comments_wrap .right h4 {
	font-size: 11px !important;
	text-transform: none;
	padding: 0;
	font-weight: normal;
	font-family: Arial, Helvetica, sans-serif !important;
}

.comments_wrap .right h4 b {
	font-size: 12px !important;
}

.comments_wrap .right h4 a {
	text-decoration: none;
}

.comments_wrap .right h4 a:hover {
	text-decoration: underline;
}

/* Comments (form) */

#content form {
	margin: 20px 0 30px 10px;
}

#content form label {
	display: block;
	margin: 10px 0;
	font-size: 12px;
}

#content form label input {
	padding: 3px;
	width: 180px;
	font-size: 12px;
}

#content textarea {
	margin-bottom: 10px;
	display: block;
	padding: 3px;
	font-size: 12px;
}

#content form input {
	margin-top: 5px;
}

.lc_logged {
	padding: 0;
	margin-top: -10px;
}

/* Sidebar */

#sidebar {
	width: 280px;
	float: right;
	margin-top: -139px;
	position: relative;
	color: #93A7A9;
}

#sidebar p {
	color: #93A7A9;
	line-height: 18px;
	padding: 8px 0;
}

#sidebar a {
	color: #F4DD2F;
	text-decoration: none;
}

#sidebar a:hover {
	text-decoration: underline;
}

#sidebar h1, #sidebar h2, #sidebar h3, #sidebar h4, #sidebar h5 {
	color: #fff;
}

#sidebar h2 {
	font-family: Georgia;
	font-size: 17px;
	font-weight: normal;
	padding: 10px 0 5px 0;
}

#sidebar ul {
	width: 100%;
	position: relative;
	padding: 10px 0 15px 0;
}

#sidebar ul ul {
	padding: 2px 0 0px 0;
}

#sidebar li {
	line-height: 16px;
	padding: 2px 0 2px 18px;
	background: url(img/star.gif) no-repeat 0 7px;
}

#sidebar li a {
	color: #93A7A9;
}

/* Sidebar (RSS) */

#sidebar #rss {
	width: 100%;
	height: 30px;
	padding-top: 10px;
	padding-bottom: 16px;
	background: url(img/sidebar_top.gif) no-repeat bottom;
}

#sidebar #rss a {
	width: 102px;
	height: 23px;
	background: url(img/rss.gif) 0 23px;
	display: block;
	margin-left: 14px;
}

#sidebar #rss a:hover {
	background-position: 0 0px;
}

#sidebar #rss a span {
	display: none;
}

/* Sidebar (Main Block) */

#sidebar_main {
	width: 237px;
	background: #020E11;
	padding-left: 20px;
	padding-right: 23px;
	position: relative;
	padding-bottom: 30px;
}

/* Sidebar (About Block) */

.about {
	width: 100%;
	padding-bottom: 7px;
}

/* Sidebar (Search) */

#sidebar #search_block {
	width: 181px;
	height: 46px;
	background: url(img/search_bg.gif) no-repeat;
	overflow: hidden;
	position: relative;
	margin: 15px 0 3px 0;
}

#sidebar #search_block input.field {
	width: 135px;
	position: absolute;
	left: 10px;
	top: 6px;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	color: #D7D6CC;
	background: transparent;
	border: none;
}

#sidebar #search_block input.submit {
	position: absolute;
	left: 150px;
	top: 3px;
}

/* Widget (calendar) */

#wp-calendar {
	width: 90%;
	padding: 0 0 0px 0;
	margin-bottom: 15px;
}

#wp-calendar caption {
	padding: 10px;
}

#wp-calendar th, #wp-calendar td {
	padding: 5px;
	text-align: center;
	background: #072C37;
}

#wp-calendar td {
	background: transparent;
}

#wp-calendar td, table#wp-calendar th {
	padding: 3px 0;
}

#wp-calendar a {
	text-decoration: underline;
}

#wp-calendar a:hover {
	text-decoration: none;
}

/* Widget (tag cloud) */

#tag_cloud {
	padding-bottom: 20px;
}

#tag_cloud h2 {
	margin-bottom: 15px;
}

/* Sidebar (Bottom) */

#sidebar_bottom {
	width: 100%;
	height: 16px;
	background: url(img/sidebar_bottom.gif) no-repeat top;
}

/* Footer */

#footer {
	width: 100%;
	padding-top: 40px;
	background: url(img/footer.gif) no-repeat 0 0;
}

#footer p {
	color: #818181;
	font-size: 11px;
	font-style: italic;
	width: 400px;
	padding-left: 100px;
}

#footer a {
	text-decoration: none;
	color: #AEAEAE;
}

#footer a:hover {
	text-decoration: underline;
}

/* Fix */

#content_wrap:after,
#menu:after,
.comments_wrap:after {
    content: "."; 
    display: block;
	 height: 0;
    clear: both; 
    visibility: hidden;
}
