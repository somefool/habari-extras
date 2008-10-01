<?php

require_once('cpgexif.php');

class CpgDb
{
	const DB_VERSION = 005;

  public static function registerTables()
  {
		DB::register_table( 'cpg_images' );
		DB::register_table( 'cpg_exif' );
		DB::register_table( 'cpg_sets' );
		DB::register_table( 'cpg_setimages' );
  }
  
  public static function install()
  {
    $schema = "CREATE TABLE " . DB::table('cpg_images') . " (
      id int unsigned NOT NULL auto_increment,
      modified int(10) NOT NULL,
      added int(10) NOT NULL,
      path varchar(45) NOT NULL default '',
      name varchar(45) NOT NULL default '',
      PRIMARY KEY (id)
    );";

    $exifDbMap = CpgExif::getDbMap();
    $dbColumns = "";
    foreach ($exifDbMap as $exifColumn)
    {
      $dbColumns .= "\n      $exifColumn text,";
    }
    
    $schema .= "\nCREATE TABLE " . DB::table('cpg_exif') . " (
      image_id int unsigned NOT NULL,$dbColumns
      PRIMARY KEY (image_id)
    );";

    $schema .= "\nCREATE TABLE " . DB::table('cpg_sets') . " (
      id int unsigned NOT NULL auto_increment,
      set_type varchar(45) NOT NULL,
      modified int(10) NOT NULL,
      added int(10) NOT NULL,
      parent_id int unsigned NOT NULL,
      name varchar(45) NOT NULL,
      data text,
      PRIMARY KEY (id)
    );";

    $schema .= "\nCREATE TABLE " . DB::table('cpg_setimages') . " (
      set_id int unsigned NOT NULL default '0',
      image_id int unsigned NOT NULL default '0',
      modified int(10) NOT NULL,
      added int(10) NOT NULL,
      priority int unsigned NOT NULL default '0',
      PRIMARY KEY (set_id, image_id)
    );";
    
    //Utils::debug($schema);
    //exit;

		return DB::dbdelta($schema);
  }
}

?>