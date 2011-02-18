<?php $theme->display('header');?>

<?php

	if (isset( $_GET['action'] ) and User::identify()->can( 'manage_shelves' ) ) {
		switch( $_GET[ 'action' ] ) {
			case "delete":
				Shelves::delete_shelf( $_GET[ 'shelf' ] );
				break;
			case "edit":
				// actually, probably don't need to even do this.
				break;
			default:
				// shouldn't come to this.
		}
		// ok, token checked, but is it overkill?
// 		Utils::debug( $_GET );
	}	
?>
	<div class="container">

<?php	echo $form; ?>

	</div>

	<div class="container plugins activeplugins">
<?php
	// this should be in the plugin, not on this page.

	$all_shelves = array();
	$all_shelves = Vocabulary::get( 'shelves' )->get_tree();
//	Utils::debug( $all_shelves );
	if ( count( $all_shelves) > 0 ) {
		$right = array();
		foreach ( $all_shelves as $shelf ) {
			while ( count($right) > 0 && $right[count($right) - 1] < $shelf->mptt_right ) {
				array_pop($right);
			}
			$pad = count($right)*30;
			$titlelink = sprintf(
				'<a href="%s" title="%s">%s</a>',
				URL::get( 'admin', array( 'page' => 'posts', 'search' => Options::get( 'shelves__single', _t( 'shelf', 'shelves' ) ) . ':' . $shelf->term ) ),
				_t( "Manage content categorized '%s'", array($shelf->term_display), 'shelves' ),
				$shelf->term_display
			);

			$dogs_eat_cats = _t('Contains %d posts.', array( Posts::get(array ('vocabulary'=> array( 'shelves:term' => $shelf->term ), 'count' => 'term' ) ) ), 'shelves' );

			// debugging
			$titlelink .= "<h4>{$shelf->mptt_left} :: {$shelf->mptt_right}</h4>";
			$dropbutton = '<ul class="dropbutton"><li><a href="'. URL::get( 'admin', array( 'page' => 'shelves', 'action' => 'edit', 'shelf' => $shelf->term )  ) . '" title="' . _t( "Rename or move '{$shelf->term_display}'" ) . '">' .
					_t( "Edit" ) . '</a></li><li><a href="' . URL::get( 'admin', array( 'page' => 'shelves', 'action' => 'delete', 'shelf' => $shelf->term ) ) . '" title="' . _t( "Delete '{$shelf->term_display}'" ) . '">' . _t( "Delete" ) . '</a></li></ul>';
			echo "\n<div class='item plugin clear' style='border-left: {$pad}px solid #e9e9e9; border-color:#e9e9e9;'><div class='head'>";
			echo "\n$titlelink $dropbutton\n</div><p>$dogs_eat_cats</p></div>";

			$right[] = $shelf->mptt_right;
		}
	}
	else {
		_e( "<h2>No %s have been created yet</h2>", array( Options::get( 'shelves__plural', _t( 'shelves', 'shelves' ) ) ) );
	}

?></div>

<?php $theme->display('footer'); ?>
