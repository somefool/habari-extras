<?php
//Getting result of of theme function

if ($pollid == null) { 
$poll = Posts::Get(array('content_type' => Post::type('poll')));
} elseif (is_int($pollid) === true) {
$poll = Posts::get(array('content_type'=>Post::type('poll'), 'id'=>$pollid));
}
//Utils::debug($poll[0]->id);
$form = new FormUi(strtolower( get_class( $this ) ) );
$array = array();
$form->append('radio', 'entry', 'null:null', 'poll this');
//Utils::debug($poll);

if ( $poll[0]->info->entry1 != '') {
	$array['entry1'] = $poll[0]->info->entry1;
}
if ( $poll[0]->info->entry2 != '') {
	$array['entry2'] = $poll[0]->info->entry2;
}
if ( $poll[0]->info->entry3 != '') {
	$array['entry3'] = $poll[0]->info->entry3;
}
if ( $poll[0]->info->entry4 != '') {
	$array['entry4'] = $poll[0]->info->entry4;
}
if ( $poll[0]->info->entry5 != '') {
	$array['entry5'] = $poll[0]->info->entry5;
}

	$form->entry->options = $array;
?> 


<div id="main_poll">
<span id="polltitle"><b> <?php echo $poll[0]->title; ?> </b></span>

<?php if (!Session::get_set('votes', false)) { ?>
	<div id="vote">
<?php
	$form->out();
?>

</div>
<a id="votesubmitt"> Vote </a>
<br />
<?php } ?>
<img style='display: none' id="contentLoading" alt="Loading, please wait" src='<?php URL::get_from_filesystem(__FILE__, TRUE); ?>ajax-loader.gif' />
<div id="results" >

</div>

<?php if (!Session::get_set('votes', false)) {
?>
<a submitt="<?php if (Session::get_set('votes', false)) { echo 'off'; } ?>"> <span id="veiw_results"> Veiw resutls </span> </a>
<?php } ?>
<script type="text/javascript">


$('#veiw_results').click(function() {
	if($('#veiw_results').text() == "go back to poll") {
		$('#votesubmitt').show();
		$('#veiw_results').text("Veiw resutls");
		$('#vote').css({display: "block"});
		$('#results').css({display: "none"});
	} else {
		//results
		$('#votesubmitt').hide();
		$('#veiw_results').text("go back to poll")
		$('#vote').css({display: "none"});
		$('#results').css({display: "block"});
		
		$.get('<?php echo URL::get('ajax', array('context' => 'ajaxpoll')); ?>', {result: null, pollid: <?php echo $poll[0]->id?>}, function(data) {
		
		$('#results').html(data);
		
		})
	}

}) 

$('#votesubmitt').click(function() {
	value = "entry"
	value = $("input[type = 'radio']:checked").val();
	value = value.replace('entry','')
	value = parseFloat(value);

	$.get('<?php echo URL::get('ajax', array('context' => 'ajaxpoll')); ?>', {result: value, pollid: <?php echo $poll[0]->id ?> }, function(data) {
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
	$.get('<?php echo URL::get('ajax', array('context' => 'ajaxpoll')); ?>', {result: null, pollid: <?php echo $poll[0]->id ?>}, function(data) {
		
		$('#results').html(data);
		
		})

}


$().ajaxSend(function(r,s){
$("#contentLoading").show();
});




 $().ajaxStop(function(r,s){  
 $("#contentLoading").fadeOut("fast");  
 });  



</script>
<?php if (Session::get_set('votes', false)) { ?>
<script type="text/javascript">

lockdown()
getresults()

</script>
<?php } ?>

