<?php
/**
 * Blogger Importer
 *
 * @package bloggerimport
 * @version $Id$
 * @author ayunyan <ayu@commun.jp>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link http://ayu.commun.jp/habari-bloggerimport
 */
class BloggerImport extends Plugin implements Importer
{
	private $supported_importers;
	private $import_batch = 100;

	/**
	 * plugin information
	 *
	 * @access public
	 * @retrun void
	 */
	public function info()
	{
		return array(
			'name' => 'Blogger Importer',
			'version' => '0.01-alpha',
			'url' => 'http://ayu.commun.jp/habari-bloggerimport',
			'author' => 'ayunyan',
			'authorurl' => 'http://ayu.commun.jp/',
			'license' => 'Apache License 2.0',
			'description' => 'Import Blogger Export File',
			'guid' => '6e805c3a-c775-11dd-aff6-001b210f913f'
			);
	}

	/**
	 * action: init
	 *
	 * @access public
	 * @return void
	 */
	public function action_init()
	{
		$this->load_text_domain('bloggerimport');

		$this->supported_importers = array();
		$this->supported_importers['blogger'] = _t('Blogger Export File', 'bloggerimport');
	}

	/**
	 * action: update_check
	 *
	 * @access public
	 * @return void
	 */
	public function action_update_check()
	{
		Update::add($this->info->name, $this->info->guid, $this->info->version);
	}

	/**
	 * filter: import_names
	 *
	 * @access public
	 * @param array $import_names
	 * @return array
	 */
	public function filter_import_names($import_names)
	{
		return array_merge($import_names, $this->supported_importers);
	}

	/**
	 * filter: import_stage
	 *
	 * @access public
	 * @param string $stageoutput
	 * @param string $import_name
	 * @param integer $stage
	 * @param integer $step
	 */
	public function filter_import_stage($stageoutput, $import_name, $stage, $step)
	{
		if (($importer = array_search($import_name, $this->supported_importers)) === false) {
			return $stageoutput;
		}


		if (empty($stage)) $stage = 1;

		$stage_method = $importer . '_stage_' . $stage;
		if (!method_exists($this, $stage_method)) {
			return sprintf(_t('Unknown Stage: %s', 'bloggerimport'), $stage);
		}

		$output = $this->$stage_method(array());

		return $output;
	}

	/**
	 * filter: import_form_enctype
	 *
	 * @access public
	 * @param string $enctype
	 * @param string $import_name
	 * @param string $stage
	 * @return string
	 */
	public function filter_import_form_enctype($enctype, $import_name, $stage)
	{
		if (($importer = array_search($import_name, $this->supported_importers)) === false) {
			return $enctype;
		}

		if ($importer == 'blogger') {
			return 'multipart/form-data';
		}

		return $enctype;
	}

	/**
	 * first stage of Blogger Export File import process
	 *
	 * @access private
	 * @return string The UI for the first stage of the import process
	 */
	private function blogger_stage_1($inputs)
	{
		$default_values = array(
			'warning' => ''
		 );
		$inputs = array_merge( $default_values, $inputs );
		extract( $inputs );

		ob_start();
?>
<p><?php echo _t('Habari will attempt to import from a Blogger Export File.', 'bloggerimport'); ?></p>
<?php if (!empty($warning)): ?>
<p class="warning"><?php echo htmlspecialchars($warning); ?></p>
<?php endif; ?>
<table>
	<tr><td><?php echo _t('Blogger Export File', 'bloggerimport'); ?></td><td><input type="file" name="file" /></td></tr>
</table>
<input type="hidden" name="stage" value="2">
<p class="submit"><input type="submit" name="import" value="<?php echo _t('Next', 'bloggerimport'); ?>" /></p>
<?php
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	/**
	 * second stage of Blogger Export File import process
	 *
	 * @access private
	 * @return string The UI for the first stage of the import process
	 */
	private function blogger_stage_2($inputs)
	{
		$default_values = array(
			'warning' => ''
		 );
		$inputs = array_merge($default_values, $inputs);
		extract($inputs);

		if (empty($_FILES['file'])) {
			$inputs['warning'] = _t('Please specify Blogger Export File.', 'bloggerimport');
			return $this->blogger_stage_1($inputs);
		}

		switch ($_FILES['file']['error']) {
		case UPLOAD_ERR_OK:
			break;
		default:
			$inputs['warning'] = _t('Upload failed.', 'bloggerimport');
			return $this->blogger_stage_1($inputs);
		}

		$atom_file = tempnam(null, 'habari_');
		
		if (!move_uploaded_file($_FILES['file']['tmp_name'], $atom_file)) {
			$inputs['warning'] = _t('Possible file upload attack!', 'bloggerimport');
			return $this->blogger_stage_1($inputs);
		}
		$_SESSION['bloggerimport_file'] = $atom_file;

		$ajax_url = URL::get('auth_ajax', array('context' => 'blogger_import_all'));
		EventLog::log(sprintf(_t('Starting import from "%s"'), 'mtfile'));
		Options::set('import_errors', array());

		ob_start();
?>
<p>Import In Progress</p>
<div id="import_progress">Starting Import...</div>
<script type="text/javascript">
// A lot of ajax stuff goes here.
$(document).ready(function(){
	$('#import_progress').load(
		"<?php echo $ajax_url; ?>",
		{
		postindex: 0
		}
	 );
});
</script>
<?php
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	/**
	 * action: auth_ajax_blogger_import_all
	 *
	 * @access public
	 * @param array $handler
	 */
	public function action_auth_ajax_blogger_import_all($handler)
	{
		$feed = simplexml_load_file($_SESSION['bloggerimport_file']);
		if (!$feed) {
			echo '<p>' . _t('Failed parsing File', 'bloggerimport') . '</p>';
			return;
		}

		$post_id_map = array();

		$entry_count = count($feed->entry);
		for ($i = 0; $i < $entry_count; $i++) {
			$entry = $feed->entry[$i];

			switch ((string)$entry->category[0]['term']) {
			// post
			case 'http://schemas.google.com/blogger/2008/kind#post':
				$t_post = array();

				$t_post['title'] = MultiByte::convert_encoding((string)$entry->title);
				$t_post['content'] = MultiByte::convert_encoding((string)$entry->content);
				$t_post['user_id'] = User::identify()->id; // TODO: import Blogger author
				$t_post['pubdate'] = HabariDateTime::date_create((string)$entry->published);
				$t_post['content_type'] = Post::type('entry');

				$entry->registerXPathNamespace('app', 'http://purl.org/atom/app#');
				$result = $entry->xpath('//app:draft');
				if (!empty($result) && (string)$result[0] == 'yes') {
					$t_post['status'] = Post::status('draft');
				} else {
					$t_post['status'] = Post::status('published');
				}

				$tags = array();
				$category_count = count($entry->category);
				for ($j = 0; $j < count($category_count); $j++) {
					$tags[] = (string)$entry->category[$i]['term'];
				}

				$post = new Post($t_post);
				$post->tags = array_unique($tags);
				try {
					$post->insert();
				} catch (Exception $e) {
					EventLog::log($e->getMessage(), 'err', null, null, print_r(array($p, $e), 1));
					Session::error($e->getMessage());
					$errors = Options::get('import_errors');
					$errors[] = $p->title . ' : ' . $e->getMessage();
					Options::set('import_errors', $errors);
				}

				$post_id_map[(string)$entry->id] = $post->id;
				break;
			// comment
			case 'http://schemas.google.com/blogger/2008/kind#comment':
				$result = $entry->xpath('//thr:in-reply-to');
				if (empty($result) || !isset($post_id_map[(string)$result[0]->ref])) break;

				$t_comment = array();

				$t_comment['post_id'] = $post_id_map[(string)$result[0]->ref];
				$t_comment['name'] = MultiByte::convert_encoding((string)$entry->author->name);
				if (isset($entry->author->email)) {
					$t_comment['email'] = (string)$entry->author->email;
				}
				if (isset($entry->author->uri)) {
					$t_comment['url'] = (string)$entry->author->uri;
				}
				$t_comment['content'] = MultiByte::convert_encoding((string)$entry->content);
				$t_comment['status'] = Comment::STATUS_APPROVED;
				$t_comment['date'] = HabariDateTime::date_create((string)$entry->published);
				$t_comment['type'] = Comment::COMMENT;

				$comment = new Comment($t_comment);
				try {
					$comment->insert();
				} catch (Exception $e) {
					EventLog::log($e->getMessage(), 'err', null, null, print_r(array($c, $e), 1));
					Session::error($e->getMessage());
					$errors = Options::get('import_errors');
					$errors[] = $e->getMessage();
					Options::set('import_errors', $errors);
				}

				break;
			default:
				break;
			}
		}

		EventLog::log(_t('Import complete from Blogger Export File', 'bloggerimport'));
		echo '<p>' . _t('Import is complete.') . '</p>';

		$errors = Options::get('import_errors');
		if(count($errors) > 0 ) {
			echo '<p>' . _t('There were errors during import:') . '</p>';

			echo '<ul>';
			foreach ($errors as $error) {
				echo '<li>' . $error . '</li>';
			}
			echo '</ul>';
		}
	}
}
?>