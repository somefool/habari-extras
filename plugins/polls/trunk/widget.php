<?php
//Getting result of of theme function
if ($pollid == null) { 
$poll = Post::get(array('content_type' => Post::type('Poll')));
} elseif (is_int($pollid) === true) {
$poll = Post::get(array('content_type'=>Post::type('Poll'), 'id'=>$pollid));
}

$form = new FormUi(strtolower( get_class( $this ) ) );
$array = array();
$form->append('radio', 'entry', 'null:null', 'poll this');


	$poll->info->entry;
	$array = $poll->info->entry;
	$form->entry->options = $array;
?> 


<div id="main_poll">
	<span id="polltitle"> <a href="<?php echo $poll->permalink ?>"> <b> <?php echo $poll->title; ?> </b> </a></span>

	<?php if (!Session::get_set('votes', false)) { ?>
		<div id="vote">
		<?php
		$form->out();
		?>

		</div>
		<a id="votesubmitt"> Vote </a>
	<?php } ?>
	<div id="contentLoading" style=" display: none;"alt="Loading, please wait">
		Loading...
	</div>
	<div id="results" >

	</div>

	<?php if (!Session::get_set('votes', false)) { ?>
		<a submitt="<?php if (Session::get_set('votes', false)) { echo 'off'; } ?>"> <span id="view_results"> View results </span> </a>
	<?php } ?>

<script type="text/javascript">


$('#view_results').click(function() {
	if($('#view_results').text() == "go back to poll") {
		$('#votesubmitt').show();
		$('#view_results').text("View results");
		$('#vote').css({display: "block"});
		$('#results').css({display: "none"});
	} else {
		//results
		$('#votesubmitt').hide();
		$('#view_results').text("go back to poll")
		$('#vote').css({display: "none"});
		$('#results').css({display: "block"});
		
		$.get('<?php echo URL::get('ajax', array('context' => 'ajaxpoll')); ?>', {result: null, pollid: <?php echo $poll->id?>}, function(data) {
		
		$('#results').html(data);
		
		})
	}

}) 

$('#votesubmitt').click(function() {
	value = "entry"
	value = $("input[type = 'radio']:checked").val();
	value = value.replace('entry','')
	value = parseFloat(value);

	$.get('<?php echo URL::get('ajax', array('context' => 'ajaxpoll')); ?>', {result: value, pollid: <?php echo $poll->id ?> }, function(data) {
		$('#results').html(data);
		$('#results').show()
		$('#vote').css({display: "none"});
		lockdown()
		})

})
	


function lockdown() {
$('#view_results').hide();
$('#votesubmitt').hide();
$('#vote').hide();
}

function getresults() {
	$.get('<?php echo URL::get('ajax', array('context' => 'ajaxpoll')); ?>', {result: null, pollid: <?php echo $poll->id ?>}, function(data) {
		
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

</div>
