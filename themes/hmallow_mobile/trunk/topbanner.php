 <div id="header">
 <h1><a href="<?php Site::out_url( 'habari' ); ?>" title="<?php Options::out( 'title' ); ?>"><?php Options::out( 'title' ); ?></a> <span><?php Options::out( 'tagline' ); ?></span><a id="menu" a href="#">MENU</a></h1>
 </div>


<div id="topimage">

		<div id="supernavcontainer">
			  <ul id="supernav">
			  <li id="search">
			  			<form  id="titlesearch" name="searchform" method="get" action="<?php URL::out('display_search'); ?>" autocomplete="off">
			  					<input type="search" results="5" autosave="<?php Options::out( 'title' ); ?>search" id="s" name="criteria" value="<?php if ( isset( $criteria ) ) { echo htmlentities($criteria, ENT_COMPAT, 'UTF-8'); }  else { echo ("Search..."); } ?>"  onblur=" if (this.value == '') {this.value = 'Search...';}"  onfocus="if (this.value == 'Search...') {this.value = '';}" > 
			  
			  				</form>
			  </li>
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

	

				
			
	</div>
<!-- /header -->
