<?php
/**
 * MovableType Export File Parser
 *
 * @version $Id$
 * @author ayunyan <ayu@commun.jp>
 * @license http://www.opensource.org/licenses/bsd-license.php Modified BSD License
 * @link http://ayu.commun.jp/
 */
class MTFileParser
{
	private $result = array();

	/**
	 * constructer
	 *
	 * @access public
	 * @param string $text;
	 */
	public function __construct($text)
	{
		if (!preg_match("/^[A-Z\s]+: /", $text)) throw new Exception('Invalid Format');
		$text = str_replace("\r\n", "\n", $text);

		$t_posts = explode("--------\n", $text);

		@reset($t_posts);
		while (list(, $t_post) = @each($t_posts)) {
			$t_post = ltrim($t_post);
			if (empty($t_post)) continue;
			$t_sections = explode("-----\n", $t_post);
			$post = array();

			@reset($t_sections);
			while (list(, $t_section) = @each($t_sections)) {
				$t_section = ltrim($t_section);
				if (empty($t_section)) continue;

				$section = $this->parseSection($t_section);
				if (empty($section['_NAME'])) {
					$post['_META'] = $section;
				} elseif ($section['_NAME'] == 'COMMENT' || $section['_NAME'] == 'PING') {
					if (!isset($post[$section['_NAME']])) $post[$section['_NAME']] = array();
					$post[$section['_NAME']][] = $section;
				} else {
					$post[$section['_NAME']] = $section;
				}
			}
			$this->result[] = $post;
		}
	}

	/**
	 * get results
	 *
	 * @access public
	 * @return array
	 */
	public function getResult()
	{
		return $this->result;
	}

	/**
	 * parse section
         *
         * @access private
         * @param string $section_text
	 * @return array
         */
	private function parseSection($section_text)
	{
		$section = array();
		$section['_NAME'] = '';
		$section['_BODY'] = '';

		$lines = explode("\n", $section_text);
		$line_count = count($lines);
		$meta_flag = true;
		for ($i = 0; $i < $line_count; $i++) {
			if ($meta_flag && preg_match("/^([A-Z\s]+):\s?(.*?)$/", $lines[$i], $match)) {
				if ($i == 0 && empty($match[2])) {
					$section['_NAME'] = $match[1];
				} else {
					if ($match[1] == 'CATEGORY') {
						if (!isset($section[$match[1]])) $section[$match[1]] = array();
						$section[$match[1]][] = $match[2];
					} else {
						$section[$match[1]] = $match[2];
					}
				}
			} else {
				$meta_flag = false;
				$section['_BODY'] .= $lines[$i] . "\n";
			}
		}
		return $section;
	}
}
?>