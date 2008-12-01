<?php $theme->display('header');?>


<div id="crontab" class="container settings">
	<h2>Current Crontab Entries</h2>
	

	<?php foreach($crons as $cron): ?>

	<div class="item plugin clear" id="title">
		<div class="head">
			<b><?php echo $cron->name; ?></b> <span class="dim">calls</span>
			<?php
			echo is_callable($cron->callback)
				? (
					is_array($cron->callback) 
					? "{$cron->callback[0]}::{$cron->callback[1]}()"
					: "{$cron->callback}()"
				)
				: "Plugins::filter_{$cron->callback}";
			?>
			
			<ul class="dropbutton">
				<li><a href="<?php URL::out('admin', array('page'=>'cronjob', 'cron_id'=>$cron->cron_id)); ?>">Edit</a></li>
				<li><a href="<?php URL::out('admin', array('page'=>'crontab', 'action'=>'delete', 'cron_id'=>$cron->cron_id)); ?>">Delete</a></li>
			</ul>

		</div>
		
		<div class="description pct50">
			<p>Result: <b><?php echo $cron->result ? ucfirst($cron->result) : 'Not Run'; ?></b></p>
			<?php echo $cron->description; ?>
		</div>
		
		<ul class="description pct50">
			<li>Runs Every <?php echo $cron->increment; ?> seconds.</li>
			<li>Last Run: <?php $cron->last_run->out(); ?></li>
			<li>Next Run: <?php $cron->next_run->out(); ?></li>
			<li>Starts On: <?php  $cron->start_time->out(); ?></li>
			<li>Ends On: <?php echo $cron->end_time ? $cron->end_time->get() : 'Never'; ?></li>
		</ul>
	</div>

<?php endforeach; ?>
</div>

<?php $theme->display('footer');?>
