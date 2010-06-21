			<li id="widget-admin" class="widget">
				<h3><?php _e('Admin', 'demorgan'); ?></h3>
				<ul>
<?php if ($loggedin) { ?>
					<li><?php printf(_t('You are logged in as %s.', 'demorgan'), '<a href="' . URL::get('admin', 'page=user&user=' . $user->username) . '" title="' . _t('Edit Your Profile', 'demorgan') . '">' . $user->username . '</a>'); ?></li>
					<li><a href="<?php Site::out_url('admin'); ?>"><?php _e('Admin', 'demorgan'); ?></a></li>
					<li><a href="<?php URL::out('user', array('page' => 'logout')); ?>"><?php _e('Logout', 'demorgan'); ?></a></li>
<?php } else { ?>
					<li><a href="<?php URL::out('user', array('page' => 'login')); ?>"><?php _e('Login', 'demorgan'); ?></a></li>
<?php } ?>
				</ul>
			</li>
