<?php

  //define('CPG_URL', get_settings('siteurl') . '/wp-content/plugins/cpg');
  //define('CPG_PHOTO_DIRECTORY', 'cpgphotos');
  //define('CPG_SERVER_ADD_DIRECTORY', '');

class CpgOptions extends Singleton
{    
  const DEBUG = false;
  const QUALITY = 95;
  
  private $options = null;

	protected static function instance()
	{
	  return self::getInstanceOf(get_class());
	}
	
  public static function setDebug($val = self::DEBUG)
  {
    self::instance()->set('debug', $val);
  }
  
  public static function getDebug()
  {
    return self::instance()->get('debug', self::CPG_DEBUG);
  }
  
  public static function setQuality($val = self::QUALITY)
  {
    self::instance()->set('quality', $val);
  }
  
  public static function getQuality()
  {
    return self::instance()->get('quality', self::QUALITY);
  }
  
  public static public function setDbVersion($val)
  {
    self::instance()->set('dbVersion', $val);
  }

  public static function getDbVersion()
  {
    return self::instance()->get('dbVersion', 0);
  }

  //////////////////////////////////////////////////////////
  // private methods
  //////////////////////////////////////////////////////////

  private function get($key, $defaultVal = null)
  {
    if (!isset($this->options))
    {
      $this->options = Options::get('cpg_options');
      if ($this->options == null)
        $this->options = array();      
    }
    
    if (isset($this->options[$key]))
      return $this->options[$key];
    else
      return $defaultVal;
  }    
  
  private function set($key, $value)
  {
    if (!isset($this->options[$key]) || $this->options[$key] != $value)
    {
      $this->options[$key] = $value;
      Options::set('cpg_options', $this->options);
    }
  }
}  

?>