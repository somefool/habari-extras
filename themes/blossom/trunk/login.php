<?php include 'header.php'; ?>
<!-- login -->
	<div id="primary" class="single-post">
		<div class="inside">
			<div class="primary">
				<?php if ( isset( $error ) ): ?>
					<p>That login is incorrect.</p>
				<?php
				endif;
				$logged_in = ( isset( $user ) && is_object( $user ) );
				if ( !$logged_in ):
					Plugins::act( 'theme_loginform_before' ); ?>
					<form method="post" action="<?php URL::out( 'user', array( 'page' => 'login' ) ); ?>" id="loginform">
						<label for="habari_username">Name:</label>
						<input type="text" size="25" name="habari_username" id="habari_username">
						<br class="clear_left" />

						<label for="habari_password">Password:</label>
						<input type="password" size="25" name="habari_password" id="habari_password">
						<br class="clear_left" />

						<?php Plugins::act( 'theme_loginform_controls' ); ?>
						<label for="habari_submit">&nbsp;</label>
						<input type="submit" value="Sign in" id="habari_submit">
						<br class="clear_left" />
					</form>
					<?php
					Plugins::act( 'theme_loginform_after' );
				endif; ?>
				<!-- /loginform -->
				<?php Plugins::act( 'theme_login' ); ?>
			</div>
			<hr class="hide" />
				<?php
				if ( $logged_in ) :
				?>
			<div class="secondary">
				<div>
					<b class="spiffy">
					<b class="spiffy1"><b></b></b>
					<b class="spiffy2"><b></b></b>
					<b class="spiffy3"></b>
					<b class="spiffy4"></b>
					<b class="spiffy5"></b>
					</b>
					<div class="spiffy_content">
						<div class="featured">
							<dl>
								<dt>Logged in: </dt>
								<dd><br>You are logged in as
									<a href="<?php URL::out( 'admin', 'page=user&user=' . $user->username ) ?>" title="Edit Your Profile">
										<?php echo $user->username; ?></a>.<br>
									Want to <a href="<?php Site::out_url( 'habari' ); ?>/user/logout">log out</a>?
								</dd>
							</dl>
						</div>
					</div>
					<b class="spiffy">
					<b class="spiffy5"></b>
					<b class="spiffy4"></b>
					<b class="spiffy3"></b>
					<b class="spiffy2"><b></b></b>
					<b class="spiffy1"><b></b></b>
					</b>
				</div>
			</div>
				<?php endif; ?>
			<div class="clear"></div>
		</div>
	</div>
	<!-- [END] #primary -->
	<hr class="hide" />

<?php include 'sidebar.php'; ?>
<!-- /login -->
<?php include 'footer.php'; ?>
