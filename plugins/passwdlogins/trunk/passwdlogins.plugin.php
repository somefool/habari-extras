<?php

class PasswdLogins extends Plugin
{
	private $passwdfile = '/var/www/sites/habariproject/subversion/htpasswd';

	public function info()
	{
		return array(
			'name' => 'passwd Logins',
			'version' => '1.0',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Gets password information for users from a passwd-generated file.',
		);
	}

	public function filter_user_authenticate($user, $username, $password)
	{
		if($username == '' || $password == '' || !file_exists($this->passwdfile)) {
			return false;
		}

		$users = array();

		$lines = preg_split('%[\n\r]+%', file_get_contents($this->passwdfile));
		foreach($lines as $line) {
			if(trim($line) == '') {
				continue;
			}
			$parts = explode(':', $line, 2);
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
