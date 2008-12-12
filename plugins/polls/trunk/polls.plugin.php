<?php

class Polls extends Plugin {
	function info() {
		return array(
			'name' => 'Poll',
			'version' => '0.51',
			'url' => 'http://www.thebigsqueak.com',
			'author' => 'Bigsqueak Studios',
			'authorurl' => 'http://www.thebigsqueak.com',
			'licence' => 'Creative Commons Attribution-Share Alike 3.0',
			'description' => 'A flexible polling plugin for habari'
		);
	}
	
	//initilization
	public function action_init() {
		Post::add_new_type('poll');
		$this->add_template('widget', dirname(__FILE__) . '/widget.php');
		$this->add_template('poll.single', dirname(__FILE__) . '/poll.single.php');
		Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery' );
		Stack::add( 'template_stylesheet', array(URL::get_from_filesystem(__FILE__) . '/widget.css', 'screen'), 'pollwigitcss');
		
	} 	
	
	//deactivate
	public function remove_template() {
		Post::deactivate_post_type('poll');
		$this->remove_template('widget', dirname(__FILE__) . '/widget.php');
		Stack::remove( 'template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery' );
		Stack::remove( 'template_stylesheet', array(URL::get_from_filesystem(__FILE__) . '/widget.css', 'screen'), 'pollwigitcss');
		$this->remove_template('poll.single', dirname(__FILE__) . '/poll.single.php');
	}
	
	public	function action_ajax_ajaxpoll() {
		$pollid = $_GET['pollid'];
		$vote = $_GET['result'];
		$post = Post::get(array('content_type'=>Post::type('poll'), 'id'=>$pollid));
		
		if ($vote != 'null') {
			$array = $post->info->r;
			$temp = $post->info->r;
			$temp[$vote]++;
			$post->info->r = $temp;
			
			Session::add_to_set('votes', $post->id);
		}
		$post->update();
		
		?>
	<ul id="poll_results">
		<?php
		$length = 200;
		$post->info->r;
		$max = max($post->info->r);
		
		for($n=1; $n<sizeof($post->info->r); $n++) { ?>
			<label > <?php echo $post->info->entry[$n] ."(". $post->info->r[$n]. ")"; ?> <li style='width: <?php echo $length*($post->info->r[$n]/$max); ?>px'>  </li> </label>
		
		<?php }; ?>

	</ul>
		<?php
			
	}
	
	//displaying the poll by this
	//WIGET
	public function theme_poll($theme, $pollid = null) {
		
		include 'widget.php';
	}
	
			
	public function action_form_publish($form, $post) {
	if($post->content_type == Post::type('poll')) {
		if ($form->silos) 
		$form-> silos->remove();
		if ($form->clearbutton)
		$form-> clearbutton->remove();
		$form->title->caption = "Poll Name";
		$form->append('text','entry1', 'null:null', 'entry 1','admincontrol_text');
		$form->move_after($form->entry1, $form->title);
		$form->entry1->value = $post->info->entry[1];
		$form->append('text','entry2', 'null:null', 'entry 2','admincontrol_text');
		$form->move_after($form->entry2, $form->entry1);
		$form->entry2->value = $post->info->entry[2];
		$form->append('text','entry3', 'null:null', 'entry 3','admincontrol_text');
		$form->move_after($form->entry3, $form->entry2);
		$form->entry3->value = $post->info->entry[3];
		$form->append('text','entry4', 'null:null', 'entry 4','admincontrol_text');
		$form->move_after($form->entry4, $form->entry3);
		$form->entry4->value = $post->info->entry[4];
		$form->append('text','entry5', 'null:null', 'entry 5','admincontrol_text');
		$form->move_after($form->entry5, $form->entry4);
		$form->entry5->value = $post->info->entry[5];
	   }
	}
	
	public function action_publish_post($post, $form) {
		if ($post->content_type == Post::type('poll')) {
			$this->action_form_publish($form, $post);
			$entry= array();
			$entry[1] = $form->entry1->value;
			$entry[2] = $form->entry2->value;
			$entry[3] = $form->entry3->value;
			$entry[4] = $form->entry4->value;
			$entry[5] = $form->entry5->value;
			$post->info->entry= $entry;
			$n = 1;
			$r = array();
			if (!$post->info->r) {
				foreach ( $post->info->entry as $result):
					$r[$n] = 0;
					$n++;
					endforeach;
					$post->info->r = $r;
			}			
		}
	}
	

}
?>
