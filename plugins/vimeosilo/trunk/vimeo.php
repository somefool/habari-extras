<?php
/**
* class Vimeo
*
* A wrapper for Vimeo's Simple API ( http://www.vimeo.com/api/docs/simple-api ).
* Licensed under the terms of Apache Software license v2.0.
*/

class Vimeo {

	const API_URL = 'http://vimeo.com/api/';
	public $username;

	function __construct( $user_name )
	{
		$this->username = $user_name;
	}

	public function get_user_info()
	{
		if ( $info = $this->fetch( $this->username . '/info' ) ){
			return $info;
		}
		return array();
	}

	public function get_user_clips()
	{
		if ( $clips = $this->fetch( $this->username . '/clips' ) ){
			return $clips;
		}
		return array();
	}

	public function get_user_likes()
	{
		if ( $clips = $this->fetch( $this->username . '/likes' ) ){
			return $clips;
		}
		return array();
	}

	public function get_user_albums()
	{
		if ( $albums = $this->fetch( $this->username . '/albums' ) ){
			return $albums;
		}
		return array();
	}

	public function get_user_subscriptions()
	{
		if ( $subs = $this->fetch( $this->username . '/subscriptions' ) ){
			return $subs;
		}
		return array();
	}

	public function get_user_channels()
	{
		if ( $channels = $this->fetch( $this->username . '/channels' ) ){
			return $channels;
		}
		return array();
	}

	public function get_user_groups()
	{
		if ( $groups = $this->fetch( $this->username . '/groups' ) ){
			return $groups;
		}
		return array();
	}

	public function get_all_user_clips()
	{
		if ( $clips = $this->fetch( $this->username . '/all_clips' ) ){
			return $clips;
		}
		return array();
	}

		public function get_user_appears_in()
	{
		if ( $clips = $this->fetch( $this->username . '/appears_in' ) ){
			return $clips;
		}
		return array();
	}

	public function get_contacts_clips()
	{
		if ( $clips = $this->fetch( $this->username . '/contacts_clips' ) ){
			return $clips;
		}
		return array();
	}

	public function get_contacts_likes()
	{
		if ( $clips = $this->fetch( $this->username . '/contacts_like' ) ){
			return $clips;
		}
		return array();
	}

	public function get_group_members( $group )
	{
	}

	public function get_group_clips( $group )
	{
		if ( $clips = $this->fetch( 'group/' . $group . '/clips' ) ){
			return $clips;
		}
		return array();
	}

	public function get_group_info( $group )
	{
		if ( $info = $this->fetch( 'group/' . $group . '/info' ) ){
			return $info;
		}
		return array();
	}

	public function get_channel_clips( $channel )
	{
		if ( $clips = $this->fetch( 'channel/' . $channel . '/clips' ) ){
			return $clips;
		}
		return array('title' => 'error');
	}

	public function get_channel_info( $channel )
	{
		if ( $clips = $this->fetch( 'channel/' . $channel . '/info' ) ){
			return $clips;
		}
		return array();
	}

	public function get_album_clips( $album_id )
	{
		if ( $clips = $this->fetch( 'album/' . $album_id . '/clips' ) ){
			return $clips;
		}
		return array();
	}

	public function get_album_info( $album_id )
	{
		if ( $clips = $this->fetch( 'album/' . $album_id . '/info' ) ){
			return $clips;
		}
		return array();
	}

	private function fetch( $relative_url, $response_format = 'php' )
	{
		$request = new RemoteRequest( self::API_URL . $relative_url . '.' . $response_format , 'GET', 10 );

		$result = $request->execute();
		if (Error::is_error($result)){
			throw $result;
		}

		$response = $request->get_response_body();

		switch ( $response_format ){
			case 'php':
				try
				{
					if ( $response_array = unserialize( $response ) ){
						return $response_array;
					}
				}
				catch( Exception $e )
				{
					Session::error('Problem with response from Vimeo.', 'Vimeo');
					return false;
				}
				break;
			default:
				//TODO: Implement the other formats (xml, json).
				Session::error('Output format is not supported.', 'Vimeo');
				return false;
				break;
		}
	}
}
?>