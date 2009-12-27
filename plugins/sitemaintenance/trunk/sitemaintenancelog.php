<?php

class SiteMaintenanceLog extends EventLog
{
	const LOG_MODULE = 'sitemaintenance';
	
	public static function report_log( $message, $severity, $type, $data = null )
	{
		parent::log($message, $severity, $type, self::LOG_MODULE, $data);
	}
	
	public static function add_report_type( $type )
	{
		parent::register_type(self::LOG_MODULE, $type);
	}
}
