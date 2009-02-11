<?php $theme->display('header');?>

<div class="create">
	
	<?php echo $form; ?>

</div>

<div id="crontab" class="container settings">
	<div style="float:right">Current Time: <?php HabariDateTime::date_create('now')->out(); ?></div>
	<h2><?php _e('Current Crontab Entries', 'crontabmanager'); ?></h2>
	

	<?php foreach($crons as $cron): ?>

	<div class="item plugin clear" id="title">
		<div class="head">
			<b><?php echo $cron->name; ?></b> <span class="dim"><?php _e('calls', 'crontabmanager'); ?></span>
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
				<li><a href="<?php URL::out('admin', array('page'=>'cronjob', 'cron_id'=>$cron->cron_id)); ?>"><?php _e('Edit', 'crontabmanager'); ?></a></li>
				<li><a href="<?php URL::out('admin', array('page'=>'crontab', 'action'=>'run', 'cron_id'=>$cron->cron_id)); ?>"><?php _e('Run Now', 'crontabmanager'); ?></a></li>
				<li><a href="<?php URL::out('admin', array('page'=>'crontab', 'action'=>'delete', 'cron_id'=>$cron->cron_id)); ?>"><?php _e('Delete', 'crontabmanager'); ?></a></li>
			</ul>

		</div>
		
		<div class="description pct50">
			<p><?php _e('Result:', 'crontabmanager'); ?> <b><?php echo $cron->result ? ucfirst($cron->result) : _t('Not Run', 'crontabmanager'); ?></b></p>
			<?php echo $cron->description; ?>
		</div>
		<?php
			if($i = $cron->increment % 86400 == 0){
				$inc = sprintf(_n('Day', '%s Days', $i, 'crontabmanager'), $i);
			}
			else {
				$inc = _t('%s Seconds', array($cron->increment), 'crontabmanager');
			}
			?>
		<ul class="description pct50">
			<li><?php _e('Runs Every', 'crontabmanager'); ?> <?php echo $inc; ?></li>
			<li><?php _e('Last Run:', 'crontabmanager'); ?> <?php echo $cron->last_run ? $cron->last_run->get() : 'Not Run'; ?></li>
			<li><?php _e('Next Run:', 'crontabmanager'); ?> <?php $cron->next_run->out(); ?></li>
			<li><?php _e('Starts On:', 'crontabmanager'); ?> <?php  $cron->start_time->out(); ?></li>
			<li><?php _e('Ends On:', 'crontabmanager'); ?> <?php echo $cron->end_time ? $cron->end_time->get() : 'Never'; ?></li>
		</ul>
	</div>

	<?php endforeach; ?>
</div>

<?php $theme->display('footer');?>
