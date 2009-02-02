<!-- loginform -->
<?php
if ( isset( $error ) && ( $error == 'Bad credentials' ) ) {
?>
     <li><?php _e('That login is incorrect.'); ?></li>

<?php
}
if ( $loggedin ) {
?>
     <li><?php _e('You are logged in as'); ?> <a href="<?php URL::out( 'admin', 'page=user&user=' . $user->username ) ?>" title="<?php _e('Edit Your Profile'); ?>"><?php echo $user->username; ?></a>.</li>
     <li><?php _e('Want to'); ?> <a href="<?php Site::out_url( 'habari' ); ?>/user/logout"><?php _e('log out'); ?></a>?</li>
<?php
}
else {
?>
	<?php Plugins::act( 'theme_loginform_before' ); ?>
     <form method="post" action="<?php URL::out( 'user', array( 'page' => 'login' ) ); ?>" id="loginform">
      <li>
       <label for="habari_username"><?php _e('Name:'); ?></label>
       <input type="text" size="25" name="habari_username" id="habari_username">
      </li>
      <li>
       <label for="habari_password"><?php _e('Password:'); ?></label>
       <input type="password" size="25" name="habari_password" id="habari_password">
      </li>
      <span id="remme"><?php Plugins::act( 'theme_loginform_controls' ); ?></span>
      <li>
       <input type="submit" value="<?php _e('Sign in'); ?>">
      </li>
     </form>
     <?php Plugins::act( 'theme_loginform_after' ); ?>
<?php
}
?>
<!-- /loginform -->