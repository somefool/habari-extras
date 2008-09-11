<style>
.prof_container {
  margin: 10 100;
  text-align: center;
  border: solid 1px #999;
  background-color: #f7f7f7;
  padding: 15;
}
.prof_header {
  color: #000;
  text-align: left;
  margin: 0 0 6 0;
  padding: 0;
}
.prof_sql {
  font-weight: bold;
  text-align: left;
  padding: 4 4 4 30;
  margin: 4 100;
  display: block;
}
.prof_time {
  color: red;
  text-align: left;
}
</style>
<div class="prof_container">
	<h1 class="prof_header"><?php _e('DB Profiling', 'binadamu'); ?></h1>
	<?php
	$profiles = DB::get_profiles();
	$total_time_querying = 0;
	foreach ($profiles as $profile) {
	?>
	<div>
		<code class="prof_sql">
			<?php echo $profile->query_text; ?>
		</code>
		<div class="prof_time">
			<p><?php _e('Time to Execute:', 'binadamu'); ?> <strong><?php echo $profile->total_time; ?></strong></p>
		</div>
		<?php if (!empty($profile->backtrace)) { ?>
		<pre style="text-align: left;">
			<strong>BACKTRACE:</strong><br/>
			<?php print_r($profile->backtrace); ?>
		</pre>
		<?php } ?>
	</div>
	<?php  
			$total_time_querying += $profile->total_time;
	}
	?>
	<div class="prof_time_total">
		<p><?php _e('Total Number of Queries:', 'binadamu'); ?> <?php echo count($profiles); ?></p>
		<p><?php _e('Total Time Executing Queries:', 'binadamu'); ?> <?php echo $total_time_querying; ?></p>
	</div>
</div>
