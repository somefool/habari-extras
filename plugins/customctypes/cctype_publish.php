<?php $theme->display('header'); ?>
<form name="create-content" id="create-content" method="post" action="<?php URL::out( 'admin', 'page=publish' ); ?>">

<div class="publish">
	<div class="container">
	<?php if(Session::has_messages()) {Session::messages_out();} ?>
	</div>

	<div class="container">

		<?php if(!$newpost): ?>
		<a href="<?php echo $post->permalink; ?>" class="viewpost">View Post</a>
		<?php endif; ?>

		<p><label for="title" class="incontent"><?php _e('Title'); ?></label><input type="text" name="title" id="title" class="styledformelement" size="100%" value="<?php if ( !empty($post->title) ) { echo $post->title; } ?>" tabindex='1'></p>
	</div>

	<?php if (isset($silos) && count($silos)) : ?>
	<div class="container pagesplitter">
		<ul id="mediatabs" class="tabs">
			<?php
			$first = 'first';
			$ct = 0;
			$last = '';
			foreach($silos as $silodir):
				$ct++;
				if($ct == count($silos)) {
					$last = 'last';
				}
				$class = "{$first} {$last}";
				$first = '';
			?><li class="<?php echo $class; ?>"><a href="#silo_<?php echo $ct; ?>"><?php echo $silodir->path; ?></a></li><?php endforeach; ?>
		</ul>

		<?php
		$ct = 0;
		foreach($silos as $silodir):
			$ct++;
		?>
			<div id="silo_<?php echo $ct; ?>" class="splitter mediasplitter">
				<div class="toload pathstore" style="display:none;"><?php echo $silodir->path; ?></div>
				<div class="splitterinside">
					<div class="media_controls"><ul><li><a href="#" onclick="habari.media.showdir('<?php echo $silodir->path; ?>');return false;">Root</a></li></ul></div>
					<div style="white-space:nowrap;overflow-x:scroll;overflow-y:hidden;" class="media_browser">
						<div class="mediadir"></div>
						<div class="mediaphotos"></div>
					</div>
					<div class="media_panel"></div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<div class="container">
		<p><label for="content" class="incontent"><?php _e('Content'); ?></label><textarea name="content" id="content" class="styledformelement resizable" rows="20" cols="114" tabindex='2'><?php if ( !empty($post->content) ) { echo htmlspecialchars($post->content); } ?></textarea></p>

		<p><label for="tags" class="incontent"><?php _e('Tags, separated by, commas')?></label><input type="text" name="tags" id="tags" class="styledformelement" value="<?php if ( !empty( $tags ) ) { echo $tags; } ?>" tabindex='3'></p>
	</div>


	<div class="container pagesplitter">
		<ul class="tabcontrol tabs">
			<?php
			$first = 'first';
			$ct = 0;
			$last = '';
			foreach($controls as $controlsetname => $controlset) :
				$ct++;
				if($ct == count($controls)) {
					$last = 'last';
				}
				$class = "{$first} {$last}";
				$first = '';
				$cname = preg_replace('%[^a-z]%', '', strtolower($controlsetname)) . '_settings';
				echo <<< EO_CONTROLS
<li class="{$cname} {$class}"><a href="#{$cname}">{$controlsetname}</a></li>
EO_CONTROLS;
			endforeach;
			?>
		</ul>

		<?php
		foreach($controls as $controlsetname => $controlset):
			$cname = preg_replace('%[^a-z]%', '', strtolower($controlsetname)) . '_settings';
		?>
		<div id="<?php echo $cname; ?>" class="splitter">
			<div class="splitterinside">
			<?php echo $controlset; ?>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	<div style="display:none;" id="hiddenfields">
	<input type="hidden" name="content_type" value="<?php echo $content_type; ?>">
	<?php if ( $post->slug != '' ) { ?>
	<input type="hidden" name="slug" id="slug" value="<?php echo $post->slug; ?>">
	<?php } ?>
	</div>


	<div id="formbuttons" class="container">
		<p class="column span-13" id="left_control_set">
			<input type="submit" name="submit" id="save" class="save" value="<?php _e('Save'); ?>">
		</p>
		<p class="column span-3 last" id="right_control_set"></p>
	</div>


</div>

</form>

<script type="text/javascript">
$(document).ready(function(){
	<?php if(isset($statuses['published']) && $post->status != $statuses['published']) : ?>
	$('#left_control_set').append($('<input type="submit" name="submit" id="publish" class="publish" value="<?php _e('Publish'); ?>">'));
	$('#publish').click(function(){
		$('#status').val(<?php echo $statuses['published']; ?>);
	});
	<?php endif; ?>
	<?php if(isset($post->slug) && ($post->slug != '')) : ?>
	$('#right_control_set').append($('<input type="submit" name="submit" id="delete" class="delete" value="<?php _e('Delete'); ?>">'));
	$('#delete').click(function(){
		$('#create-content')
			.append($('<input type="hidden" name="nonce" value="<?php echo $wsse['nonce']; ?>"><input type="hidden" name="timestamp" value="<?php echo $wsse['timestamp']; ?>"><input type="hidden" name="digest" value="<?php echo $wsse['digest']; ?>">'))
			.attr('action', '<?php URL::out( 'admin', array('page' => 'delete_post', 'slug' => $post->slug )); ?>');
	});
	<?php endif; ?>
});
</script>

<?php $theme->display('footer'); ?>
