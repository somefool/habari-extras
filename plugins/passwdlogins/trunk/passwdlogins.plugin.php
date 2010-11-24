<?php

class PasswdLogins extends Plugin
{

	public function filter_plugin_config ( $actions, $plugin_id ) {
			
		$actions['configure'] = _t('Configure', 'passwdlogins');
		
		return $actions;
		
	}
	
	public function action_plugin_ui_configure ( ) {
		
		// get the groups list for the drop-down
		$ugs = UserGroups::get_all();
		
		$groups = array();
		foreach ( $ugs as $group ) {
			
			$groups[ $group->name ] = $group->name;
			
		}
		
		// remove anonymous - that would be pointless
		unset( $groups['anonymous'] );
		
		$ui = new FormUI('plugin_directory');
			
		$ui->append( 'text', 'passwdfile', 'passwdlogins__file', _t( 'Passwd File', 'passwdlogins' ) );
		$ui->append( 'checkbox', 'createusers', 'passwdlogins__create', _t( 'Create users on successful login', 'passwdlogins' ) );
		$select = $ui->append( 'select', 'defaultgroup', 'passwdlogins__group', _t( 'Group to create new users in', 'passwdlogins' ) );
		$select->default = 'authenticated';
		// emulate $default until it actually works
		if ( $select->value == null ) {
			$select->value = $select->default;
		}
		$select->options = $groups;
		
		
		$ui->append( 'submit', 'save', _t( 'Save' ) );
		
		$ui->out();
		
	}
	
	public function filter_user_authenticate($user, $username, $password)
	{
		
		$passwdfile = Options::get('passwdlogins__file');
		
		if ( !$passwdfile ) {
			EventLog::log( _t('No passwd file configured!'), 'err', 'passwdlogins', 'passwdlogins' );
			return false;
		}
		
		if ( !file_exists( $passwdfile ) ) {
			EventLog::log( _t('Passwd file does not exist: %1$s', array( $passwdfile ) ), 'err', 'passwdlogins', 'passwdlogins' );
			return false;
		}
		
		// go ahead and trim the user and password
		$username = trim( $username );
		$password = trim( $password );
		
		// blank usernames and passwords are not allowed
		if ( $username == '' || $password == '' ) {
			return false;
		}
		
		$users = $this->parse_htpasswd( $passwdfile );
		
		if ( isset( $users[ $username ] ) ) {
			
			$crypt_pass = $users[ $username ];
			
			if ( $crypt_pass{0} == '{' ) {
				
				// figure out the algorithm used for this password
				$algo = MultiByte::strtolower( MultiByte::substr( $crypt_pass, 1, MultiByte::strpos( $crypt_pass, '}', 1 ) - 1 ) );
				
				$passok = false;
				
				switch ( $algo ) {
					
					case 'ssha':
						$hash = base64_decode( MultiByte::substr( $crypt_pass, 6 ) );
						$passok = MultiByte::substr( $hash, 0, 20 ) == pack( "H*", sha1( $password . MultiByte::substr( $hash, 20 ) ) );
						
						break;
						
					case 'sha':
						$passok = '{SHA}' . base64_encode( pack( "H*", sha1( $password ) ) ) == $crypt_pass;
						
						break;
					
				}
				
			}
			else {
				
				// it's plain crypt
				$passok = crypt( $password, MultiByte::substr( $crypt_pass, 0, CRYPT_SALT_LENGTH ) ) == $crypt_pass;
				
			}
			
			if ( $passok == true ) {
				return $this->get_user( $username );
			}
			
		}
		
		// returning $user would continue the login check through other plugins and core - we want to force passwd logins
		return false;
		
		
	}
	
	public function parse_htpasswd ( $passwdfile ) {
		
		$lines = file( $passwdfile );
		
		$users = array();
		
		$i = 0;
		foreach ( $lines as $line ) {
			
			// if there is no :, assume this is a username with a newline
			if ( MultiByte::strpos( $line, ':' ) === false ) {
				// append the next line to it
				$line = $line . $lines[ $i + 1 ];
				
				// unset the next line
				unset( $lines[ $i + 1 ] );
			}
			
			list( $username, $password ) = explode( ':', $line );
			
			// trim the username and password
			$username = trim( $username );
			$password = trim( $password );
			
			if ( $username == '' || $password == '' ) {
				continue;
			}
			
			$users[ $username ] = $password;
			
			$i++;
			
		}
		
		return $users;
		
	}
	
	private function get_user ( $username ) {
		
		// should we create users if they don't exist? default to false
		$create = Options::get( 'passwdlogins__create', false );
		$group = Options::get( 'passwdlogins__group' );
		
		$user = User::get_by_name( $username );
		
		// if the user doesn't exist
		if ( $user == false ) {
			
			// do we want to create it?
			if ( $create == true ) {
				
				$user = User::create( array(
					'username' => $username,
					'password' => Utils::random_password( 200 ),
				) );
				
				// add them to the default group, if one has been selected - otherwise it'll just default to authenticated anyway
				if ( $group ) {
					$user->add_to_group( $group );
				}
				
				return $user;
				
			}
			else {
				// don't create it, it doesn't exist, so fail
				return false;
			}
			
		}
		else {
			
			// the user already exists, return it
			return $user;
			
		}
		
	}

}

?>
