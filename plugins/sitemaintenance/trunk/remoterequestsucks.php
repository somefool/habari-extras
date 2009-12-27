<?php

class RemoteRequestSucks
{
	public static function head( $url )
	{
		$headers = get_headers($url, 1);
		return self::get_status($headers);
	}
	
	/**
	 * get original status
	 */
	protected static function get_status( $headers )
	{
		if ( preg_match('|^HTTP/1\.[01] ([1-5][0-9][0-9]) ?(.*)|', $headers[0], $m) ) {
			$headers['status'] = $m[1];
			$headers['status_name'] = $m[2];
		}
		return $headers;
	}
}
