<?php
class MY_Input extends CI_Input 
{
   function __construct()
	{
		parent::__construct();
   }
	
	function request($index = NULL)
	{
		if ($index === NULL)
		{
			return $_REQUEST;
		}
		
		if (isset($_REQUEST[$index]))
		{
			return $_REQUEST[$index];
		}
		
		return NULL;
	}
	
	public function ip_address()
	{
		$ip_address = get_real_ip_address();
		
		if ($ip_address)
		{
			return $ip_address;
		}
		
		return parent::ip_address();
	}
	
	public function clean_string($str)
	{
		// Clean UTF-8 if supported
		if (UTF8_ENABLED === TRUE)
		{
			$str = $this->uni->clean_string($str);
		}
		
		return $str;
	}
}