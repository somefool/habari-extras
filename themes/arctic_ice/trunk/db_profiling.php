<style>
.prof_container {
  margin: 10 100;
  text-align: center;
  border: solid 1px #999;
  background-color: #f7f7f7;
  padding: 15;
}
.prof_header {
  font: 320% Arial, Helvetica;
  font: 200% Arial, Helvetica;
  color: #000;
  text-align: left;
  margin: 0 0 6 0;
  padding: 0;
}
.prof_sql {
  font: 240% monospace, Courier;
  font: 150% monospace, Courier;
  font-weight: bold;
  text-align: left;
  padding: 4 4 4 30;
  margin: 4 100;
  display: block;
}
.prof_time {
  font: 180%/2.0 Verdana, Tahoma, sans;
  color: red;
  text-align: left;
}
</style>
<div class="prof_container">
	<h1 class="prof_header">{hi:"DB Profiling"}</h1>
	<?php
	$profiles= DB::get_profiles();
	$total_time_querying= 0;
	?>
	{hi:profiles}
	<div class="prof">
		<code class="prof_sql">
			{hi:query_text}
		</code>
		<div class="prof_time">
			<p>{hi:"Time to Execute:"} <strong>{hi:total_time}</strong></p>
		</div>
		<?php if ( !empty( $profiles_1->backtrace ) ) { ?>
		<pre style="text-align: left;">
			<strong>{hi:"BACKTRACE:"}</strong><br/>
			<?php print_r($profiles_1->backtrace); ?>
		</pre>
		<?php } ?>
	</div>
	<?php $total_time_querying += $profiles_1->total_time; ?>
	{/hi:profiles}
	<div class="prof_time_total">
		<p>{hi:"Total Number of Queries Executed:"} <?php echo count( $profiles ); ?></p>
		<p>{hi:"Total Time Executing Queries:"} {hi:total_time_querying}</p>
	</div>
</div>
