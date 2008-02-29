<?php
$active_theme= Options::get( 'theme_dir' );
$all_themes= Themes::get_all_data();
?>
	<div class="sb-switcher">
		<h2>Theme Switcher</h2>
		<form action="" method="post" name="themeswitcher">
			<select name="theme_dir">
				<?php foreach( $all_themes as $theme ) : ?>
					<option value="<?php echo $theme['dir']; ?>"<?php echo ($theme['dir'] == $active_theme) ? 'selected' : ''; ?>><?php echo $theme['info']->name; ?> <?php echo $theme['info']->version; ?></option>
				<?php endforeach; ?>
			</select>
			<input type="submit" name="s" value="Switch">
		</form>
	</div>