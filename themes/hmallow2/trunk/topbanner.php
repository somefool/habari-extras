<div id="topimage">

		<div id="supernavcontainer">
			  <ul id="supernav">
			    <li <?php if($request->display_home) { ?>
				class="current_page_item"<?php } ?>><a href="<?php Site::out_url( 'habari' ); ?>" title="<?php Options::out( 'title' ); ?>"><?php echo $home_tab; ?></a></li>
			<?php
			// Menu tabs
			foreach ( $pages as $tab ) {
			?>
			    <li<?php if(isset($post) && $post->slug == $tab->slug) { ?>
				class="current_page_item"<?php } ?>><a href="<?php echo $tab->permalink; ?>" title="<?php echo $tab->title; ?>"><?php echo $tab->title; ?></a></li>
			<?php
			}
				if ( User::identify()->loggedin ) { ?>
			    <li class="admintab"><a href="<?php Site::out_url( 'admin' ); ?>" title="<?php _e('Admin area'); ?>"><?php _e('Admin'); ?></a></li>
			<?php } ?>
			   </ul>

	</div>

	</div>

	<div id="blogtitle">

	  <h1 style="float:left;"><a href="<?php Site::out_url( 'habari' ); ?>" title="<?php Options::out( 'title' ); ?>"><?php Options::out( 'title' ); ?></a> <span><?php Options::out( 'tagline' ); ?></span></h1>



			<form  id="titlesearch" name="searchform" method="get" action="<?php URL::out('display_search'); ?>" autocomplete="off">
						<input  type="search" results="5" autosave="<?php Options::out( 'title' ); ?>search" id="searchsubmit" name="s" value="Search..." placeholder="Search..." size="18"  onblur=" if (this.value == '') {this.value = 'Search...';}"  onfocus="if (this.value == 'Search...') {this.value = '';}" class="inputboxes" />
						<input type="submit" id="searchsubmitb" value="Search" />

				</form>


	</div>
<!-- /header -->
