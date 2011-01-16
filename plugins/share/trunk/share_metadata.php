<meta property="og:title" content="<?php echo $post->title; ?>"/>
<meta property="og:type" content="article"/>
<meta property="og:url" content="<?php echo $post->permalink; ?>"/>
<meta property="og:site_name" content="<?php Options::out('title'); ?>"/>
<meta property="fb:admins" content="<?php Options::out('share__fb_admins'); ?>"/>
<meta property="og:description" content="<?php $theme->get_post_description($post); ?>"/>

