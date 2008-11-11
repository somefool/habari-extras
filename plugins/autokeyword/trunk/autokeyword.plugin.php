<?php

/**
 * Auto Keyword Generator
 * Version: 1.0
 * Author: Benjamin Hutchins <http://www.xvolter.com>
 * Copyright: Copyright (C) 2008, Benjamin Hutchins
 * License: MIT License
 */

class AutoKeyword extends Plugin
{

	/**
	 * Return plugin information
	 */
	public function info()
	{
		return array(
			'name' => 'AutoKeyword',
			'version' => '1.0',
			'url' => 'http://www.xvolter.com/project/autokeyword',
			'author' =>	'Benjamin Hutchins',
			'authorurl' => 'http://www.xvolter.com/',
			'license' => 'MIT',
			'description' => 'Auto generate keywords for posts and pages.'
		 );
	}

	/**
	 * Enable the ability for 
	 */
	function action_update_check() 
	{
		Update::add( 'AutoKeyword', '210D3BF6-AF6B-11DD-97B3-B85A56D89593', $this->info->version ); 
	}

	/**
	 * Add default options when plugin is activated
	 */
	public function action_plugin_activation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {

			// language
			Options::set( 'autokeyword__lang', 'en' );

			// single words
			Options::set( 'autokeyword__min_1word_length', '5' );
			Options::set( 'autokeyword__min_1word_occur', '2' );

			// two word phrases
			Options::set( 'autokeyword__min_2word_length', '3' );
			Options::set( 'autokeyword__min_2phrase_length', '6' );
			Options::set( 'autokeyword__min_2phrase_occur', '2' );

			// three word phrases
			Options::set( 'autokeyword__min_3word_word_length', '3' );
			Options::set( 'autokeyword__min_3phrase_length', '9' );
			Options::set( 'autokeyword__min_3phrase_occur', '2' );
		}
	}

	/**
	 * Add configure tab to action list
	 */
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() )
			$actions[]= _t( 'Configure' );

		return $actions;
	}

	/**
	 * Create the configuration FromUI
	 */
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() )
		{
			switch ( $action ) {
				case _t( 'Configure' ):
					$ui= new FormUI( strtolower( get_class( $this ) ) );

					// add language selector
					$ui->append( 'select', 'lang', 'option:autokeyword__lang', _t( 'Language' ),
						array(
							// TODO: Get some more languages,
							// Please help me here thee who speakith other
							// languages :)
							'en' => 'English'
						)
					);

					// add single word entries
					$ui->append( 'text', 'min_1word_length', 'option:autokeyword__min_1word_length',
						_t('Minimum length for one word keywords'));
					$ui->append( 'text', 'min_1word_occur', 'option:autokeyword__min_1word_occur',
						_t('Minimum occurance for one word keywords'));

					// add two word phrase entries
					$ui->append( 'text', 'min_2word_length', 'option:autokeyword__min_2word_length',
						_t('Minimum length for single words in two word phrases'));
					$ui->append( 'text', 'min_2phrase_length', 'option:autokeyword__min_2phrase_length',
						_t('Minimum length for entire two word phrase'));
					$ui->append( 'text', 'min_2phrase_occur', 'option:autokeyword__min_2phrase_occur',
						_t('Minimum occurance for entire two word phrase'));

					// add three word phrase entries
					$ui->append( 'text', 'min_3word_length', 'option:autokeyword__min_3word_length',
						_t('Minimum length for single words in three word phrases'));
					$ui->append( 'text', 'min_3phrase_length', 'option:autokeyword__min_3phrase_length',
						_t('Minimum length for entire three word phrase'));
					$ui->append( 'text', 'min_3phrase_occur', 'option:autokeyword__min_3phrase_occur',
						_t('Minimum occurance for entire three word phrase'));

					// misc
					$ui->append( 'submit', 'save', 'Save' );
					$ui->on_success( array( $this, 'updated_config' ) );
					$ui->out();
				break;
			}
		}
	}

	/**
	 * Save options
	 */
	public function updated_config( $ui )
	{
		$ui->save();
		return false;
	}

	/**
	 * Change how we save project, client, and tasks
	 * Get rid of unneeded items and add to info
	 */
	public function action_publish_post( &$post, &$form )
	{
		// save post keywords
		$post->info->keywords = self::get_keywords( $post->content );
	}

	/**
	 * Get the keywords for a group of text
	 * @param String content to process
	 * @return String of keywords separated by commas (,)
	 */
	public static function get_keywords($content)
	{
		// pre process content
		$segments = self::pre_proccess( $content );

		// return keywords in a more usable variable
		return implode(", ",
			// merge all keywords into one array
			array_merge(
				self::parse_words($segments),
				self::parse_2words($segments),
				self::parse_3words($segments)
			)
		);
	}

	/**
	 * Turn a mass of words into a usable variable.
	 */
	private static function pre_proccess($content)
	{
		// remove HTML .. TODO: Process bolded words and headers
		$content = strip_tags( strtolower($content) );

		// remove punctuation
		$punctuations = array(
		',', ')', '(', '.', "'", '"',
		'<', '>', ';', '!', '?', '/', '-',
		'_', '[', ']', ':', '+', '=', '#',
		'$', '&quot;', '&copy;', '&gt;', '&lt;',
		chr(10), chr(13), chr(9));
		$content = str_replace($punctuations, " ", $content);

		// replace wide gaps with smaller ones
		$content = preg_replace('/\s{2,}/si', " ", $content);

		return explode(" ", $content);
	}

	/**
	 * Find commonly used words
	 */
	public static function parse_words( $segments )
	{
		// load common words from language file
		$file = dirname(__FILE__) . "/words/" . Options::get('autokeyword__lang') . ".txt";
		$common = file_exists($file) ? file( $file ) : array();

		$keywords = array();
		$min_word_length = Options::get('autokeyword__min_1word_length');

		foreach( $segments as $word ) {
			$word = trim($word);
			if (	strlen($word) >= $min_word_length &&
				!in_array($word, $common) && // skip common words completely
				!is_numeric($word) // dont process numbers as a keyword
			)
				$keywords[] = $word;
		}

		return self::filter($keywords, Options::get('autokeyword__min_1word_occur'));
	}

	/**
	 * Finds two word phrases 
	 */
	public static function parse_2words( $segments )
	{
		$keywords = array();
		$min_word_length = Options::get('autokeyword__min_2word_length');
		$min_phrase_length = Options::get('autokeyword__min_2phrase_length');

		for ($i=0; $i < count( $segments ) - 1; $i++) {
			$var1 = trim( $segments[ $i ] );
			$var2 = trim( $segments[ $i+1 ] );
			$var3 = "$var1 $var2";

			if (	strlen($var1) >= $min_word_length &&
				strlen($var2) >= $min_word_length &&
				strlen($var3) >= $min_phrase_length
			)
				$keywords[] = $var3;
		}

		return self::filter($keywords, Options::get('autokeyword__min_2phrase_occur'));
	}

	/**
	 * Find three word phrases
	 */
	public static function parse_3words( $segments )
	{
		$keywords = array();
		$min_word_length = Options::get('autokeyword__min_3word_word_length');
		$min_phrase_length = Options::get('autokeyword__min_3phrase_length');

		for ($i=0; $i < count( $segments ) - 2; $i++) {
			$var1 = trim( $segments[ $i ] );
			$var2 = trim( $segments[ $i+1 ] );
			$var3 = trim( $segments[ $i+2 ] );
			$var4 = "$var1 $var2 $var3";

			if (	strlen($var1) >= $min_word_length &&
				strlen($var2) >= $min_word_length &&
				strlen($var3) >= $min_word_length &&
				strlen($var4) >= $min_phrase_length
			)
				$keywords[] = $var4;
		}

		return self::filter( $keywords, Options::get('autokeyword__min_3phrase_occur') );
	}

	/**
	 * Process arrays of words with counts and
	 * returns the words that match the occurance
	 * requirement, then sort the words.
	 */
	private static function filter($array, $min)
	{
		$array = array_count_values($array);
		$filtered = array();

		foreach ($array as $word => $occured)
			if ($occured >= $min)
				$filtered[] = $word;

		arsort( $filtered );
		return $filtered;
	}

}
