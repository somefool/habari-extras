<?php

	class Code_Escape extends Plugin {
		
		const VERSION = '0.1';
		
		public function info ( ) {
			
			return array (
					'name' => 'Code Escape',
					'url' => 'http://habariproject.org',
					'author' => 'Habari Community',
					'authorurl' => 'http://habariproject.org',
					'version' => self::VERSION,
					'description' => 'Runs htmlspecialchars() on any &lt;code&gt; blocks in your post.',
					'license' => 'Apache License 2.0'
			);
			
		}
		
		public function filter_post_content_out ( $content, $post ) {
			
			$content = preg_replace_callback('/<code>(.*?)<\/code>/s', array( 'self', 'escape_code' ), $content);
			
			return $content;
			
		}
		
		public static function escape_code ( $matches ) {
			
			$string = $matches[1];
			
			$string = htmlspecialchars( $string );
			
			$string = '<code>' . $string . '</code>';
			
			return $string;
			
		}
		
	}
	
?>