		<div id="sidebar">
		
			<div id="rss">
			<a href="<?php URL::out( 'atom_feed', array( 'index' => '1' ) ); ?>"><span>Subscribe</span></a>
			</div>
			
			<div id="sidebar_main">

			<h2>Search</h2>
			
			<div id="search_block">
			<form method="get" id="searchform" action="<?php URL::out('display_search'); ?>/">
			<div>
			<input type="text" name="criteria" id="s" class="field" value="Searching for ?" onfocus="if (this.value == 'Searching for ?') {this.value = '';}" onblur="if (this.value == '') {this.value = 'Searching for ?';}" />
			<input type="image" src="<?php Site::out_url( 'theme' ); ?>/img/search.gif" id="searchsibmit" class="submit" name="submit" />
			</div>
			</form>
			</div>
			
			<?php include "sidebar-text.php"; ?>
		
			<h2>Recent Entries</h2>
			<ul>
			<?php foreach($more_posts as $post): ?>
			<?php
			echo '<li>';
			echo '<a href="' . $post->permalink .'">' . $post->title_out . '</a>';
			echo '</li>';
			?>
			<?php endforeach; ?>
			</ul>
			
			<h2>Categories</h2>
			<ul>
			<?php if( count( $all_tags ) > 0 ) { ?>
			  <ul class="tags">
			    <?php foreach($all_tags as $tag) {  ?>
			      <li>
			        <a href="<?php Site::out_url( 'habari' ); ?>/tag/<?php echo $tag->slug; ?>/" rel="tag" title="See posts with <?php echo $tag->tag; ?> tag">
			          <?php echo $tag->tag; ?>
			        </a>
			      </li>
			    <?php } ?>
			  </ul>
			<?php } ?>			
			</ul>

			<?php if(class_exists('HabariDateTime')) { ?>
			<h2>Archives</h2>
			<ul>
			<?php $theme->monthly_archives_links_list();  ?>
			</ul>
			<?php } ?>

			</div>
			
			<div id="sidebar_bottom">
			</div>

		</div>