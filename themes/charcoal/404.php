<?php include 'header.php'; ?>
<div id="main-posts">
<div class="post-alt">
<div class="post-entry">
<h2>Woops! 404</h2>
The page you were trying to access is not really there. Please try again.
</div>
</div>
</div>
</div>
<div id="top-secondary"><?php include'sidebar.php' ?></div>
<div class="clear"></div>
</div>
</div>
<div id="page-bottom">
<div id="wrapper-bottom">
<div id="bottom-primary">

<div id="archives"><?php if (Plugins::is_loaded("extendedmonthlyarchives")) echo $extended_monthly_archives;?></div>
<?php include 'footer.php'; ?></div>
<div id="bottom-secondary">
<div id="tags"><?php if (Plugins::is_loaded('tagcloud')) echo $tag_cloud; ?></div>
</div>
<div class="clear"></div>
</div>
</div>
</body>
</html>
