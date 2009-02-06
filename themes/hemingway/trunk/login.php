<?php include 'header.php'; ?>

	<div id="primary" class="single-post">
	<div class="inside">
		<div class="primary">

		<h1>Please sign in</h1>
		
 		<ul class="login"><li>

      <!-- loginform -->
      <?php if ( isset( $error ) ): ?>
        <p>That login is incorrect.</p>
      <?php
      endif;
      if ( User::identify()->loggedin ):
      ?>
        <p>You are logged in as <a href="<?php URL::out( 'admin', 'page=user&user=' . $user->username ) ?>" title="Edit Your Profile"><?php echo $user->username; ?></a>.</p>
        <p>Want to <a href="<?php Site::out_url( 'habari' ); ?>/user/logout">log out</a>?</p>
      <?php
      else:
        Plugins::act( 'theme_loginform_before' ); ?>
        <form method="post" action="<?php URL::out( 'user', array( 'page' => 'login' ) ); ?>" id="loginform">
          <label for="habari_username">Name:</label>
          <input type="text" size="25" name="habari_username" id="habari_username"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <label for="habari_password">Password:</label>
          <input type="password" size="25" name="habari_password" id="habari_password"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <?php Plugins::act( 'theme_loginform_controls' ); ?>
          <input type="submit" value="Sign in"/>
        </form>
        <?php
        Plugins::act( 'theme_loginform_after' ); 
      endif; ?>
      <!-- /loginform -->
      <?php Plugins::act( 'theme_login' ); ?>
      
		</li></ul>
    
	</div>
	
	<div class="secondary">
		<h2>Welcome</h2>
		<p>You are not currently logged in. Please enter your login and password to sign in.</p>
	</div>
	<div class="clear"></div>
	</div>
	</div>
  <!-- end primary -->

<?php include 'sidebar.php'; ?>

<?php include 'footer.php'; ?>
<!-- end home -->