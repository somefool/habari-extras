<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
 <title><?php  
 
 if (($this->request->display_page) | ($this->request->display_entry)) { echo "{$post->title} - "; } 
 
 if ($this->request->display_search) {  echo "Search for '".htmlspecialchars( $criteria )."' - ";  }
 
 ?><?php Options::out( 'title' ) ?></title>
 <meta http-equiv="Content-Type" content="text/html">
 <meta name="generator" content="Habari">

 <link rel="alternate" type="application/atom+xml" title="Atom 1.0" href="<?php $theme->feed_alternate(); ?>">
 <link rel="edit" type="application/atom+xml" title="Atom Publishing Protocol" href="<?php URL::out( 'atompub_servicedocument' ); ?>">
 <link rel="EditURI" type="application/rsd+xml" title="RSD" href="<?php URL::out( 'rsd' ); ?>">

 <link rel="stylesheet" type="text/css" media="screen" href="<?php Site::out_url( 'theme' ); ?>/style.css">
 
 <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no' /> 
 <meta name='apple-mobile-web-app-status-bar-style' content='black' /> 
 <meta name='apple-mobile-web-app-capable' content='yes' /> 
 
 
<script type="text/javascript" src="http://code.jquery.com/jquery-1.4.2.min.js"></script>

<script type="text/javascript" >
$(document).ready(function() {
$('#header a#menu').click( function() {
    $('#supernavcontainer').toggle();
   });
});
</script>

<?php $theme->header(); ?>

</head>



