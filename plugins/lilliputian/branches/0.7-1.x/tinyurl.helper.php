<?php
// TinyURL helper module for the Habari Lilliputian plugin

{
	function shrink( $url )
	{
		$service= 'http://tinyurl.com/api-create.php?url=';
		$request = new RemoteRequest( $service . urlencode($url), 'GET' );
		$result = $request->execute();
		if ( Error::is_error( $result ) ) {
			throw $result;
		}
		$data= $request->get_response_body();
		if ( Error::is_error( $data ) ) {
			throw $data;
		}
		return $data;
	}
}
