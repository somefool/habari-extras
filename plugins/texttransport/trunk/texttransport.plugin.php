<?php
// komode: le=unix language=php codepage=utf8 tab=4 tabs indent=4
class TextTransport extends Plugin
{
	const ACTION_CONFIGURE = 'Configure';
	const ACTION_EXPORT_ALL = 'Export all posts';
	const ACTION_EXPORT_ONE = 'Export one post';
	
	const FILENAME_HASH = '.hash';
	const FILENAME_CONTENT = 'content.txt';
	
	const FILENAME_PART_COMMENTPREFIX = 'comment';
	const FILENAME_PART_COMMENTSEP = '.';
	const FILENAME_PART_COMMENTSUFFIX = '.txt';
	
	const OPTIONS_LOCALPATH = 'texttransport__localpath';
	
	function action_update_check() 
	{
		Update::add( 'Text Transport', 'c54b7fdc-5738-4ad8-a154-7e555878eaaa', $this->info->version ); 
	}

	public function action_plugin_deactivation( $file )
	{
		Options::delete(self::OPTIONS_LOCALPATH);
	}

	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[] = _t( self::ACTION_CONFIGURE );
			$actions[] = _t( self::ACTION_EXPORT_ALL );
		}
		return $actions;
	}

	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t( self::ACTION_CONFIGURE ):
					$ui = new FormUI( strtolower( __CLASS__ ) );
					$path = $ui->append( 'text', 'exportpath', self::OPTIONS_LOCALPATH, _t('Export Path:') );
					$ui->exportpath->add_validator( array( __CLASS__, 'check_path' ) );
					$ui->append( 'submit', 'save', _t( 'Save' ) );
					$ui->set_option('success_message', _t('TextExport Settings Saved'));
					$ui->out();
					break;

				case _t( self::ACTION_EXPORT_ALL ):
					$this->export_all();
					break;
			}
		}
	}

	public static function check_path( $path )
	{
		if ( is_writable( $path ) ) {
			// path is fine
			return array();
		}
		return array( _t( 'Invalid path (not writable).' ) );
	}
	
	protected function export_all()
	{
		$posts = array();
		foreach ( Posts::get() as $post ) {
			$hash = self::hash_from_post( $post );
			if ($hash !== self::get_disk_post_hash( $post ) || $hash !== self::hash_from_disk_post( $post ) ) {
				// no match, need to export
				echo sprintf( _t( 'Exporting post: %s' ), $post->slug ) . "<br />\n";
				if ( self::export_post( $post ) ) {
					Session::notice( sprintf( _t( 'Exported post: %s' ), $post->slug ) ) . "<br />\n";
				}
			} else {
				echo sprintf( _t( "Post '%s' up to date." ), $post->slug ) . "<br />\n";
			}
		}
	}
	
	protected static function export_post( Post $post )
	{
		$postPath = self::get_post_export_path( $post );
		self::recursive_file_delete( $postPath );
		mkdir( $postPath );
		file_put_contents( $postPath . self::FILENAME_HASH, self::hash_from_post( $post ) );
		file_put_contents( $postPath . self::FILENAME_CONTENT, $post->content );
		self::export_comments( $post );
	}
	
	protected static function export_comments( Post $post )
	{
		foreach ($post->comments as $comment) {
			$commentFilename =
				self::FILENAME_PART_COMMENTPREFIX . self::FILENAME_PART_COMMENTSEP .
				$comment->id . self::FILENAME_PART_COMMENTSEP .
				Comment::status_name($comment->status) . self::FILENAME_PART_COMMENTSUFFIX;
			file_put_contents( $postPath . $commentFilename, $comment->content );
		}
		return true;
	}
	
	protected static function get_post_export_path( Post $post )
	{
		$exportPath = Options::get( 'textexport__exportpath' );
		if (!$exportPath || !is_readable( $exportPath ) || !is_writable( $exportPath ) ) {
			Error::raise( _t( 'Export path is not readable/writable. Be sure to configure this plugin.' ) );
		}
		return $exportPath . DIRECTORY_SEPARATOR . $post->slug . DIRECTORY_SEPARATOR;
	}
	
	public static function hash_from_disk_post( Post $post )
	{
		$postPath = self::get_post_export_path( $post );
		$contentFile = $postPath . self::FILENAME_CONTENT;
		if ( !is_readable( $contentFile ) ) {
			return false;
		}
		$content = file_get_contents( $contentFile );
		$hash = md5( $post->slug . $content );
		$prefixLen = strlen( self::FILENAME_PART_COMMENTPREFIX );

		// gather filenames so they can be sorted
		$filenames = array();
		foreach ( new DirectoryIterator( $postPath ) as $f ) {
			$commentFile = $f->getFileName();
			if ( substr( $commentFile, 0, $prefixLen ) !== self::FILENAME_PART_COMMENTPREFIX ) {
				// not a comment file
				continue;
			}
			$filenames[] = $commentFile;
		}
		natsort( $filenames );
		// now that they're sorted, actually process (hash)
		foreach ( $filenames as $commentFile ) {
			list( , $id, $status ) = explode( self::FILENAME_PART_COMMENTSEP, $postPath.$commentFile );
			$hash = md5( $hash . self::hash_from_disk_comment( $postPath . $commentFile, $id, $status ) );
		}
		return $hash;
	}
	
	protected static function hash_from_disk_comment( $commentFile, $id, $status )
	{
		$content = file_get_contents( $commentFile );
		$hash = md5( $id . $status . $content );
		return $hash;
	}
	
	public static function get_disk_post_hash( Post $post )
	{
		$hashPath = self::get_post_export_path( $post ) . self::FILENAME_HASH;
		if ( !is_readable( $hashPath ) ) {
			// no hash
			return false;
		}
		return file_get_contents( $hashPath );
	}
	
	public static function hash_from_post( Post $post )
	{
		// these hashes are _NOT_ for security purposes
		// the point is to reduce an entire post + comment set into a hash
		// to efficiently check for changes
		
		$hash = md5( $post->slug . $post->content );
		$comments = array();
		foreach ( $post->comments as $comment ) {
			$comments[$comment->id] = $comment;
		}
		ksort($comments);
		foreach ($comments as $comment) {
			$hash = md5( $hash . self::hash_from_comment( $comment ) );
		}
		return $hash;
	}
	
	protected static function hash_from_comment( Comment $comment )
	{
		$hash = md5( $comment->id . Comment::status_name( $comment->status ) . $comment->content );
		return $hash;
	}
	
	protected static function recursive_file_delete( $path )
	{
		if ( !is_writable( $path ) ) {
			return false;
		}
		$outerRdi = new RecursiveDirectoryIterator( $path );
		$rdi = new RecursiveIteratorIterator( $outerRdi, RecursiveIteratorIterator::CHILD_FIRST );
		foreach ($rdi as $name => $file) {
			if ( $file->isDir() ) {
				rmdir( $name );
			} else {
				// file
				unlink( $name );
			}
		}
		rmdir( $path );
		return true;
	}
}

?>