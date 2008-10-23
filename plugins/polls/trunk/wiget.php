<?php
$id = 16;
Utils::debug(Session::get_set('votes', false));

$post = Post::get(array('content_type'=>Post::type('poll'), 'id'=>$pollid));
$form = new FormUi(strtolower( get_class( $this ) ) );
$array = array();
$form->append('radio', 'entry', 'null:null', 'poll this');
?>
<?php

if ( $post->info->entry1 != '') {
	$array['entry1'] = $post->info->entry1;
}
if ( $post->info->entry2 != '') {
	$array['entry2'] = $post->info->entry2;
}
if ( $post->info->entry3 != '') {
	$array['entry3'] = $post->info->entry3;
}
if ( $post->info->entry4 != '') {
	$array['entry4'] = $post->info->entry4;
}
if ( $post->info->entry5 != '') {
	$array['entry5'] = $post->info->entry5;
}

?>
<?php 
$form->entry->options = $array;

?> 


<div id="main_poll">
<?php if (!Session::get_set('votes', false)) {
?>
<div id="vote">
<?php

$form->out();
?>

</div>
 <a id="votesubmitt"> Vote </a>
<?php } ?>
<img id="contentLoading" alt="Loading, please wait" src='<?php URL::get_from_filesystem(__FILE__, TRUE); ?>ajax-loader.gif' />
<div id="results" >

</div>

<?php if (!Session::get_set('votes', false)) {
?>
<a submitt="<?php if (Session::get_set('votes', false)) { echo 'off'; } ?>"> <span id="veiw_results"> Veiw resutls </span> </a>
<?php } ?>
<script type="text/javascript">


$('#veiw_results').click(function() {
	if($('#veiw_results').text() == "go back to poll") {

		$('#veiw_results').text("Veiw resutls");
		$('#vote').css({display: "block"});
		$('#results').css({display: "none"});
	} else {
		//results
		$('#veiw_results').text("go back to poll")
		$('#vote').css({display: "none"});
		$('#results').css({display: "block"});
		
		$.get('<?php echo URL::get('ajax', array('context' => 'ajaxpoll')); ?>', {result: null}, function(data) {
		
		$('#results').html(data);
		
		})
	}

}) 

$('#votesubmitt').click(function() {
	value = "entry"
	value = $("input[type = 'radio']:checked").val();
	value = value.replace('entry','')
	value = parseFloat(value);

	$.get('<?php echo URL::get('ajax', array('context' => 'ajaxpoll')); ?>', {result: value }, function(data) {
		$('#results').html(data);
		$('#results').show()
		$('#vote').css({display: "none"});
		lockdown()
		})

})
	


function lockdown() {
$('#veiw_results').hide();
$('#votesubmitt').hide();
$('#vote').hide();
}

function getresults() {
	$.get('<?php echo URL::get('ajax', array('context' => 'ajaxpoll')); ?>', {result: null}, function(data) {
		
		$('#results').html(data);
		
		})

}


$().ajaxSend(function(r,s){
$("#contentLoading").show();
});





</script>
<?php if (!Session::get_set('votes', false)) { ?>
<script type="text/javascript">
lockdown()
getresults()

</script>
<?php } ?>
<?php

?>
