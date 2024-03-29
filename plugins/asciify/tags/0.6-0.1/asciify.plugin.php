<?php
/**
 * Asciify will make your titles and tags nicer for urls.
 * Doesn't handles tag-slugs since Tag isn't correctly implemented yet.
 *
 * Usage:
 * Enable and enjoy
 *
 */

class asciify extends Plugin
{
	/**
	 * Required plugin information
	 * @return array The array of information
	 **/
	public function info()
	{
		return array(
			'name' => 'Asciify',
			'version' => '0.1',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Make urls more friendly ',
			'copyright' => '2008'
		);
	}

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'asciify', 'CBEC596E-9BBE-11DD-8801-147256D89593', $this->info->version );
	}

	/**
	 * Thanks to bcse for the from and to.
	 **/
	private function _asciize( $string )
	{
  		static $from = array( 'Á', 'À', 'Â', 'Ä', 'Ǎ', 'Ă', 'Ā', 'Ã', 'Å', 'Ǻ', 'Ą', 'Ɓ', 'Ć', 'Ċ', 'Ĉ', 'Č', 'Ç', 'Ď', 'Ḍ', 'Ɗ', 'É', 'È', 'Ė', 'Ê', 'Ë', 'Ě', 'Ĕ', 'Ē', 'Ę', 'Ẹ', 'Ǝ', 'Ə', 'Ɛ', 'Ġ', 'Ĝ', 'Ǧ', 'Ğ', 'Ģ', 'Ɣ', 'Ĥ', 'Ḥ', 'Ħ', 'I', 'Í', 'Ì', 'İ', 'Î', 'Ï', 'Ǐ', 'Ĭ', 'Ī', 'Ĩ', 'Į', 'Ị', 'Ĵ', 'Ķ', 'Ƙ', 'Ĺ', 'Ļ', 'Ł', 'Ľ', 'Ŀ', 'Ń', 'Ň', 'Ñ', 'Ņ', 'Ó', 'Ò', 'Ô', 'Ö', 'Ǒ', 'Ŏ', 'Ō', 'Õ', 'Ő', 'Ọ', 'Ø', 'Ǿ', 'Ơ', 'Ŕ', 'Ř', 'Ŗ', 'Ś', 'Ŝ', 'Š', 'Ş', 'Ș', 'Ṣ', 'Ť', 'Ţ', 'Ṭ', 'Ú', 'Ù', 'Û', 'Ü', 'Ǔ', 'Ŭ', 'Ū', 'Ũ', 'Ű', 'Ů', 'Ų', 'Ụ', 'Ư', 'Ẃ', 'Ẁ', 'Ŵ', 'Ẅ', 'Ƿ', 'Ý', 'Ỳ', 'Ŷ', 'Ÿ', 'Ȳ', 'Ỹ', 'Ƴ', 'Ź', 'Ż', 'Ž', 'Ẓ', 'á', 'à', 'â', 'ä', 'ǎ', 'ă', 'ā', 'ã', 'å', 'ǻ', 'ą', 'ɓ', 'ć', 'ċ', 'ĉ', 'č', 'ç', 'ď', 'ḍ', 'ɗ', 'é', 'è', 'ė', 'ê', 'ë', 'ě', 'ĕ', 'ē', 'ę', 'ẹ', 'ǝ', 'ə', 'ɛ', 'ġ', 'ĝ', 'ǧ', 'ğ', 'ģ', 'ɣ', 'ĥ', 'ḥ', 'ħ', 'ı', 'í', 'ì', 'i', 'î', 'ï', 'ǐ', 'ĭ', 'ī', 'ĩ', 'į', 'ị', 'ĵ', 'ķ', 'ƙ', 'ĸ', 'ĺ', 'ļ', 'ł', 'ľ', 'ŀ', 'ŉ', 'ń', 'ň', 'ñ', 'ņ', 'ó', 'ò', 'ô', 'ö', 'ǒ', 'ŏ', 'ō', 'õ', 'ő', 'ọ', 'ø', 'ǿ', 'ơ', 'ŕ', 'ř', 'ŗ', 'ś', 'ŝ', 'š', 'ş', 'ș', 'ṣ', 'ſ', 'ť', 'ţ', 'ṭ', 'ú', 'ù', 'û', 'ü', 'ǔ', 'ŭ', 'ū', 'ũ', 'ű', 'ů', 'ų', 'ụ', 'ư', 'ẃ', 'ẁ', 'ŵ', 'ẅ', 'ƿ', 'ý', 'ỳ', 'ŷ', 'ÿ', 'ȳ', 'ỹ', 'ƴ', 'ź', 'ż', 'ž', 'ẓ', 'Α', 'Ά', 'Β', 'Γ', 'Δ', 'Ε', 'Έ', 'Ζ', 'Η', 'Ή', 'Θ', 'Ι', 'Ί', 'Ϊ', 'Κ', 'Λ', 'Μ', 'Ν', 'Ξ', 'Ο', 'Ό', 'Π', 'Ρ', 'Σ', 'Τ', 'Υ', 'Ύ', 'Ϋ', 'Φ', 'Χ', 'Ψ', 'Ω', 'Ώ', 'α', 'ά', 'β', 'γ', 'δ', 'ε', 'έ', 'ζ', 'η', 'ή', 'θ', 'ι', 'ί', 'ϊ', 'ΐ', 'κ', 'λ', 'μ', 'ν', 'ξ', 'ο', 'ό', 'π', 'ρ', 'σ', 'ς', 'τ', 'υ', 'ύ', 'ϋ', 'ΰ', 'φ', 'χ', 'ψ', 'ω', 'ώ', 'Æ', 'Ǽ', 'Ǣ', 'Ð', 'Đ', 'Ĳ', 'Ŋ', 'Œ', 'Þ', 'Ŧ', 'æ', 'ǽ', 'ǣ', 'ð', 'đ', 'ĳ', 'ŋ', 'œ', 'ß', 'þ', 'ŧ' );
  		static $to = array( 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'B', 'C', 'C', 'C', 'C', 'C', 'D', 'D', 'D', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'G', 'G', 'G', 'G', 'G', 'G', 'H', 'H', 'H', 'I', 'I', 'I', 'I', 'I', 'I', 'I', 'I', 'I', 'I', 'I', 'I', 'J', 'K', 'K', 'L', 'L', 'L', 'L', 'L', 'N', 'N', 'N', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'R', 'R', 'R', 'S', 'S', 'S', 'S', 'S', 'S', 'T', 'T', 'T', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'W', 'W', 'W', 'W', 'W', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Z', 'Z', 'Z', 'Z', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'b', 'c', 'c', 'c', 'c', 'c', 'd', 'd', 'd', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'g', 'g', 'g', 'g', 'g', 'g', 'h', 'h', 'h', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'j', 'k', 'k', 'q', 'l', 'l', 'l', 'l', 'l', 'n', 'n', 'n', 'n', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'r', 'r', 'r', 's', 's', 's', 's', 's', 's', 's', 't', 't', 't', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'w', 'w', 'w', 'w', 'w', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'z', 'z', 'z', 'z', 'A', 'A', 'V', 'G', 'D', 'E', 'E', 'Z', 'I', 'I', 'Th', 'I', 'I', 'I', 'K', 'L', 'M', 'N', 'X', 'O', 'O', 'P', 'R', 'S', 'T', 'Y', 'Y', 'Y', 'F', 'Ch', 'Ps', 'O', 'O', 'a', 'a', 'v', 'g', 'd', 'e', 'e', 'z', 'i', 'i', 'th', 'i', 'i', 'i', 'i', 'k', 'l', 'm', 'n', 'x', 'o', 'o', 'p', 'r', 's', 's', 't', 'y', 'y', 'y', 'y', 'f', 'ch', 'ps', 'o', 'o', 'Ae', 'Ae', 'Ae', 'Dh', 'Dj', 'Ij', 'Ng', 'Oe', 'Th', 'Th', 'ae', 'ae', 'ae', 'dh', 'dj', 'ij', 'ng', 'oe', 'ss', 'th', 'th' );
 
  		return str_replace( $from, $to, $string );
	}

	/**
	 * Filter that does all the magic
	 */
	public function filter_post_setslug($slug)
	{
		return $this->_asciize($slug);
	}

	/**
	 * Filter that does all the magic
	 */
	public function filter_tag_setslug($tag)
	{
		return $this->_asciize($tag);	
	}
}

?>