<?php
class MY_User_agent extends CI_User_agent 
{
	private static $detect;
	function __construct()
	{
		parent::__construct();		
	}
	
	static function init()
	{
  		$CI =& get_instance();
  		$CI->load->library('Mobile_Detect');
	    self::$detect = new Mobile_Detect();
	}
	/**
	 * Is Mobile
	 *
	 * @param	string	$key
	 * @return	bool
	 */
	public function is_mobile($key = NULL)
	{
		static $cache;
		
		if ($cache !== NULL)
		{
			return $cache;
		}
		
		if (!self::$detect->isMobile())
		{
			$cache = FALSE;
			return $cache;
		}

		// No need to be specific, it's a mobile
		if ($key === NULL)
		{
			$cache = TRUE;
			return $cache;
		}

		// Check for a specific robot
		$cache = (isset($this->mobiles[$key]) && $this->mobile === $this->mobiles[$key]);
		return $cache;
	}
	
	public function is_tablet()
	{
		static $cache;
		
		if ($cache !== NULL)
		{
			return $cache;
		}
		
		$cache = self::$detect->isTablet();
		return $cache;
	}
	
	public function is_android_less_than_4_4()
	{
		static $cache;
		
		if ($cache !== NULL)
		{
			return $cache;
		}
		
		if (self::$detect->isAndroidOS())
		{
			$cache = self::$detect->version('Android') < 4.4;
			return $cache;
		}
		
		$cache = FALSE;
		return $cache;
	}
}

MY_User_agent::init();