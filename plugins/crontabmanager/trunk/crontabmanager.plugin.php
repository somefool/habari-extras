<?php

class CronTabManager extends Plugin
{
	public function info()
	{
		return array (
			'name' => 'CronTabManager',
			'version' => '0.1',
			'author' => 'Habari Community',
			'license' => 'Apache License 2.0',
		);
	}
	
	public function alias()
	{
		return array(
			'action_admin_theme_get_cronjob' => 'action_admin_theme_post_cronjob'
		);
	}
	
	public function action_init()
	{
		$this->add_template('crontab', dirname($this->get_file()) . '/crontab.php');
		$this->add_template('cronjob', dirname($this->get_file()) . '/cronjob.php');
	}
	
	public function action_admin_theme_get_crontab( AdminHandler $handler, Theme $theme )
	{
		if( isset($handler->handler_vars['action']) ) {
			switch($handler->handler_vars['action']) {
				case 'delete':
					$cron = CronTab::get_cronjob((int) $handler->handler_vars['cron_id']);
					if( $cron instanceof CronJob && $cron->delete() ) {
						Session::notice(_t(
							'Deleted cron job "%s"',
							array($cron->name),
							'crontabmanager'
						));
					}
					else {
						Session::error(_t(
							'Could not delete Cron Job with id "%s"',
							array($handler->handler_vars['cron_id']),
							'crontabmanager'
						));
					}
					break;
			}
		}
		$crons = DB::get_results(
			'SELECT * FROM {crontab}',
			array(),
			'CronJob'
		);
		
		$theme->crons = $crons;
	}
	
	public function action_admin_theme_get_cronjob( AdminHandler $handler, Theme $theme )
	{
		$cron = CronTab::get_cronjob((int) $handler->handler_vars['cron_id']);
		$theme->cron = $cron;
		$form = new FormUI('cronjob');
		
		$cron_id = $form->append(
			'hidden', 'cron_id', 'null:null'
		);
		$cron_id->value = (int) $handler->handler_vars['cron_id'];
		
		$name = $form->append(
			'text', 'cron_name', 'null:null', _t('Name', 'crontabmanager'), 'optionscontrol_text'
		);
		$name->class = 'item clear';
		$name->value = $cron->name;
		$name->helptext = _t('A unique name for this cronjob.', 'crontabmanager');
		
		$callback = $form->append(
			'text', 'callback', 'null:null', _t('Callback', 'crontabmanager'), 'optionscontrol_text'
		);
		$callback->class = 'item clear';
		$callback->value = is_array($cron->callback) ? htmlspecialchars(serialize($cron->callback)) : $cron->callback;
		$callback->helptext = _t('A valid callback OR plugin filter name.', 'crontabmanager');
		
		$increment = $form->append(
			'text', 'increment', 'null:null', _t('Iterval', 'crontabmanager'), 'optionscontrol_text'
		);
		$increment->class = 'item clear';
		$increment->value = $cron->increment;
		$increment->helptext = _t('The interval, in seconds, between executions.', 'crontabmanager');
		
		$next_run = $form->append(
			'text', 'next_run', 'null:null', _t('Next Run', 'crontabmanager'), 'optionscontrol_text'
		);
		$next_run->class = 'item clear';
		$next_run->value = $cron->next_run->get();
		$next_run->helptext = _t('A valid HabariDateTime formatted string.', 'crontabmanager');
		
		$start_time = $form->append(
			'text', 'start_time', 'null:null', _t('Start Time', 'crontabmanager'), 'optionscontrol_text'
		);
		$start_time->class = 'item clear';
		$start_time->value = $cron->start_time->get();
		$start_time->helptext = _t('A valid HabariDateTime formatted string.', 'crontabmanager');
		
		$end_time = $form->append(
			'text', 'end_time', 'null:null', _t('End Time', 'crontabmanager'), 'optionscontrol_text'
		);
		$end_time->class = 'item clear';
		$end_time->value = $cron->end_time ? $cron->end_time->get() : $cron->end_time;
		$end_time->helptext = _t('A valid HabariDateTime formatted string OR empty for "never".', 'crontabmanager');
		
		$description = $form->append(
			'text', 'description', 'null:null', _t('Description', 'crontabmanager'), 'optionscontrol_text'
		);
		$description->class = 'item clear';
		$description->value = $cron->description;
		$description->helptext = _t('A string describing the Cron Job.', 'crontabmanager');
		
		$cron_class = $form->append(
			'select', 'cron_class', 'null:null', _t('Cron Class', 'crontabmanager'), 'optionscontrol_select'
		);
		$cron_class->class = 'item clear';
		$cron_class->value = $cron->cron_class;
		$cron_class->helptext = _t('The type of Cron Job.', 'crontabmanager');
		$cron_class->options = array(
			CronJob::CRON_SYSTEM => _t('System', 'crontabmanager'),
			CronJob::CRON_THEME => _t('Theme', 'crontabmanager'),
			CronJob::CRON_PLUGIN => _t('Plugin', 'crontabmanager'),
			CronJob::CRON_CUSTOM => _t('Custom', 'crontabmanager'),
		);
		
		$form->append( 'submit', 'save', _t('Save', 'crontabmanager') );
		$form->on_success( array($this, 'formui_submit') );
		$theme->form = $form->get();
	}
	
	public function action_admin_theme_post_cronjob( AdminHandler $handler, Theme $theme )
	{
		// saving is handled by FormUI
		$cron = CronTab::get_cronjob((int) $handler->handler_vars['cron_id']);
		$theme->display('cronjob');
		
		// this is stoopid, but exit so adminhandler doesn't complain
		exit;
	}
	
	public function formui_submit( FormUI $form )
	{
		$cron = CronTab::get_cronjob((int) $form->cron_id->value);
		$cron->name =  $form->cron_name->value;
		$cron->callback =
			(strpos($form->callback->value, 'a:') === 0 || strpos($form->callback->value, 'O:') === 0) 
			? unserialize($form->callback->value) 
			: $form->callback->value;
		$cron->increment = $form->increment->value;
		$cron->next_run =  HabariDateTime::date_create($form->next_run->value);
		$cron->start_time =  HabariDateTime::date_create($form->start_time->value);
		$cron->end_time =  $form->end_time->value ? HabariDateTime::date_create($form->end_time->value) : null;
		$cron->description =  $form->description->value;
		$cron->cron_class =  $form->cron_class->value;
		
		if( $cron->update() ) {
			Session::notice( _t('Cron Job saved.', 'crontabmanager') );
		}
		else {
			Session::error( _t('Coul not save Cron Job.', 'crontabmanager') );
		}
	}
	
	public function filter_adminhandler_post_loadplugins_main_menu( array $menu )
	{
		$menu['crontab'] = array( 'url' => URL::get( 'admin', 'page=crontab'), 'title' => _t( 'Manage the crontab' ), 'text' => _t( 'Crontab' ), 'hotkey' => 'J', 'selected' => false);
		// push logout link to bottom.
		uksort($menu, array($this, 'sort_menu'));
		return $menu;
	}
	
	public function sort_menu($a, $b)
	{
		return $b == 'logout' ? -1 : 1;
	}
}

?>