	<hr class="hide" />
	<!-- ancillary.php -->
	<div id="ancillary">
		<div class="inside">
			<div class="block first">
				 <?php if (Plugins::is_loaded('Colophon')) { ?>
    			 <h2><?php echo $colophon_title; ?></h2>
    			 <?php echo $colophon; ?>
    			 <?php } ?>
			</div>
			
			<div class="block">

				<?php if (Plugins::is_loaded('Twitter') && $theme->twitter_in=='sidebar') { ?>
					<h2><a href="http://twitter.com/<?php echo urlencode( Options::get( 'twitter:username' )); ?>">Twitter Status</a></h2>
					<p><?php $theme->twitter (); ?></p>
					<br>
				<?php } ?>
				
				<h2>Recently</h2>
				<ul class="dates">
				<?php foreach($recent_posts as $recent_post): ?>
					<li><a href="<?php echo $recent_post->permalink ?>"><span class="date"><?php echo $recent_post->pubdate_out ?></span> <?php echo $recent_post->title_out ?> </a></li>
				<?php endforeach; ?>
				</ul>

				<?php if (Plugins::is_loaded('RN Monthly Archives')) { ?>
				<h2>Monthly Archives</h2>
        			<?php echo $monthly_archives ?>
				<?php } ?>
        
			</div>
			
			<div class="block">
				<?php if (count($nav_pages) > 0) { ?>
				<h2>Pages</h2>
				<ul class="pages">
					<?php foreach ( $nav_pages as $tab ) {
    				$link = ucfirst($tab->slug);
    				echo "<li><a href=\"{$tab->permalink}\" title=\"{$tab->title}\">{$link}</a></li>";
					} ?>
				</ul>
				<?php } ?>
			
		      	<?php if (Plugins::is_loaded('RN Tag Cloud')) { ?>
				<h2>Tags</h2>
        		<?php echo $tag_cloud ?>
        		<?php } ?>
			</div>
			
			<div class="clear"></div>
		</div>
	</div>
	<!-- end ancillary.php -->   