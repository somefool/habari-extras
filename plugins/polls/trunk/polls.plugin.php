<?php

class Polls extends Plugin {
	function info() {
		return array(
			'name' => 'Poll',
			'version' => '0.1',
			'url' => 'www.bigsqueak.info',
			'author' => 'luke',
			'authorurl' => 'www.bigsqueak.info',
			'licence' => 'umm....',
			'description' => 'A flexible polling plugin for habari'
		);
	}
	
	//initilization
	public function action_init() {
	Post::add_new_type('poll');
	$this->add_template('wiget', dirname(__FILE__) . '/wiget.php');
	Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery' );
	Stack::add( 'template_stylesheet', array(URL::get_from_filesystem(__FILE__) . '/wiget.css', 'screen'), 'pollwigitcss');

	}
	
	//deactivate
	public function action_plugin_deactivation() {
		Post::deactivate_post_type('poll');
		Stack::remove( 'template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery' );
		Stack::remove( 'template_stylesheet', array(URL::get_from_filesystem(__FILE__) . '/wiget.css', 'screen'), 'pollwigitcss');
	}
	
	public	function action_ajax_ajaxpoll() {
		$pollid = $_GET['pollid'];
		$post = Post::get(array('content_type'=>Post::type('poll'), 'id'=>$pollid));
		$vote = $_GET['result'];
		
	
		
		if ($vote == 1) {
		$post->info->r1 ++;
		}
		if ($vote == 2) {
		$post->info->r2 ++;
		}
		if ($vote == 3) {
		$post->info->r3 ++;
		}
		if ($vote == 4) {
		$post->info->r4 ++;
		}
		if ($vote == 5) {
		$post->info->r5s ++;
		}
		if ($vote != 'null') {
		//no vote, just veiw.
		Session::add_to_set('votes', $post->id);
		}
		$post->update();
		?>
		
		<ul id="poll_results">
		<?php
		
		$votearray = array(1=>$post->info->r1, 2=>$post->info->r2, 3=>$post->info->r3, 4=>$post->info->r4, 5=>$post->info->r5);
		$max = max($votearray);
	
		if ( $post->info->entry1 != '') { ?>
		<label > <?php echo $post->info->entry1 ."(". $post->info->r1. ")"; ?> <li style='width: <?php echo 175*($post->info->r1/$max); ?>px'>  </li> </label>
		<?php
		}
		if ( $post->info->entry2 != '') { ?>
			<label > <?php echo $post->info->entry2 ."(". $post->info->r2. ")"; ?> <li style='width: <?php echo 175*($post->info->r2/$max); ?>px'>  </li> </label>
		<?php
		}
		if ( $post->info->entry3 != '') { ?>
			<label > <?php echo $post->info->entry3 ."(". $post->info->r3. ")"; ?> <li style='width: <?php echo 175*($post->info->r3/$max); ?>px'>  </li> </label>
		<?php
		}
		if ( $post->info->entry4 != '') { ?>
			<label > <?php echo $post->info->entry4 ."(". $post->info->r4. ")"; ?> <li style='width: <?php echo 175*($post->info->r4/$max); ?>px'>  </li> </label>

		<?php
		}
		if ( $post->info->entry5 != '') { ?>
			<label > <?php echo $post->info->entry5 ."(". $post->info->r5. ")"; ?> <li style='width: <?php echo 175*($post->info->r5/$max); ?>px'>  </li> </label>
		<?php
		}
		
	
		?>
		</ul>
		<?php
			
	}
	
	//displaying the poll by this
	//WIGET
	public function theme_poll($theme, $pollid = null) {
		
		include 'wiget.php';
	}
	
			
	public function action_form_publish($form, $post) {
	if($post->content_type == Post::type('poll')) {
		
		$form->content->remove();
		$form-> silos->remove();
		$form-> clearbutton->remove();
		$form->title->caption = "Poll Name";
		
		$form->append('text','entry1', 'null:null', 'entry 1','admincontrol_text');
		$form->move_after($form->entry1, $form->title);
		$form->append('text','entry2', 'null:null', 'entry 2','admincontrol_text');
		$form->move_after($form->entry2, $form->entry1);
		$form->append('text','entry3', 'null:null', 'entry 3','admincontrol_text');
		$form->move_after($form->entry3, $form->entry2);
		$form->append('text','entry4', 'null:null', 'entry 4','admincontrol_text');
		$form->move_after($form->entry4, $form->entry3);
		$form->append('text','entry5', 'null:null', 'entry 5','admincontrol_text');
		$form->move_after($form->entry5, $form->entry4);

	   }
	}
	
	public function action_publish_post($post, $form) {
		if ($post->content_type == Post::type('poll')) {
		$this->action_form_publish($form, $post);
		
		$post->info->entry1 = $form->entry1->value;
		$post->info->entry2 = $form->entry2->value;
		$post->info->entry3 = $form->entry3->value;
		$post->info->entry4 = $form->entry4->value;
		$post->info->entry5 = $form->entry5->value;
		
		$post->info->r1 = 0; 
		$post->info->r2 = 0; 
		$post->info->r3 = 0; 
		$post->info->r4 = 0; 
		$post->info->r5 = 0;
						
		}
	}
	
	public function action_ajax_novote() {
	
		Session::add_to_set('votes', $pollname, $key);
	
		}
}
?>
