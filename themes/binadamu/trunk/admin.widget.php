			<li id="widget-admin" class="widget">
				<h3><?php _e('Admin', 'binadamu'); ?></h3>
				<ul>
<?php if ($user) { ?>
					<li><?php printf(_t('You are logged in as %s.', 'binadamu'), '<a href="' . URL::get('admin', 'page=user&user=' . $user->username) . '" title="' . _t('Edit Your Profile', 'binadamu') . '">' . $user->username . '</a>'); ?></li>
					<li><a href="<?php Site::out_url('admin'); ?>"><?php _e('Admin', 'binadamu'); ?></a></li>
					<li><a href="<?php URL::out('user', array('page' => 'logout')); ?>"><?php _e('Logout', 'binadamu'); ?></a></li>
<?php } else { ?>
					<li><a href="<?php URL::out('user', array('page' => 'login')); ?>"><?php _e('Login', 'binadamu'); ?></a></li>
<?php } ?>
				</ul>
			</li>
