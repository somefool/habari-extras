<?php

class PasswdLogins extends Plugin
{
	private $passwdfile = '/home/habari/public_html/svn.habariproject.org/private/repos/htpasswd';

	public function filter_user_authenticate($user, $username, $password)
	{
		if($username == '' || $password == '' || !file_exists($this->passwdfile)) {
			return false;
		}

		$users = array();

		// @todo improve this so it handles line breaks at the end of the username (see comment below) - meller
		$lines = preg_split('%[\n\r]+%', file_get_contents($this->passwdfile));
		foreach($lines as $line) {
			if(trim($line) == '') {
				continue;
			}
			$parts = explode(':', $line, 2);
			// this gets Notice: Undefined offset:  1 in user/plugins/passwdlogins/passwdlogins.plugin.php line 22 when user types the wrong password
			// actually, that has nothing to do with passwords, it means a spammer has registered at Trac with a line break at the end of their username. i deleted one just now and the error went away. you only ever saw the error if login failed, so it was a natural conclusion - meller
			$users[$parts[0]] = $parts[1];
		}

	  if ( isset( $users[$username] ) ) {
	  	$crypt_pass = $users[$username];
			if ( $crypt_pass{0} == '{' ) {
				$algo = strtolower( substr( $hash, 1, strpos( $hash, '}', 1 ) - 1 ) );
				$passok = false;
				switch ( $algo ) {
					case 'ssha':
				    $hash = base64_decode(substr($crypt_pass, 6));
				    $passok = substr($hash, 0, 20) == pack("H*", sha1($password . substr($hash, 20)));
				    break;

					case 'sha':
						$passok = "{SHA}" . base64_encode(pack("H*", sha1($password))) == $crypt_pass;
						break;
				}
			}
			else {
		    $passok = crypt( $password, substr($crypt_pass, 0, CRYPT_SALT_LENGTH) ) == $crypt_pass;
			}

	  	if ( $passok ) {
			if ( $tuser = User::get_by_name( $username ) ) {
	  			return $tuser;
				}
			}
	  }
  	return false; // Returning $user would allow other plugins and the core to process the login, too.
	}

}

?>
