<?php
function get_giftcard_processor()
{
	$CI = &get_instance();
	
	if (!$CI->Location->get_info_for_key('enable_credit_card_processing'))
	{
		return false;
	}
	if ($CI->Location->get_info_for_key('credit_card_processor') == 'mercury' || !$CI->Location->get_info_for_key('credit_card_processor'))
	{
		$registers = $CI->Register->get_all();
		$register = $registers->row_array();

		if (!$CI->Employee->get_logged_in_employee_current_register_id() && isset($register['register_id']))
		{
			$CI->Employee->set_employee_current_register_id($register['register_id']);
		}
	
		$current_register_id = $CI->Employee->get_logged_in_employee_current_register_id();
		$register_info = $CI->Register->get_info($current_register_id);
	
		//IP Tran; supports all platforms
		if($register_info->iptran_device_id)
		{
			require_once(APPPATH.'libraries/Mercuryemvtranscloudprocessor.php');
			$credit_card_processor = new Mercuryemvtranscloudprocessor($CI);
			return $credit_card_processor;
		}

		//EMV
		if ($CI->Location->get_info_for_key('emv_merchant_id') && $CI->Location->get_info_for_key('com_port') && $CI->Location->get_info_for_key('listener_port'))
		{
			require_once (APPPATH.'libraries/Mercuryemvusbprocessor.php');
			$credit_card_processor = new Mercuryemvusbprocessor($CI);
			return $credit_card_processor;
		}
	}
	elseif ($CI->Location->get_info_for_key('credit_card_processor') == 'heartland')
	{
		$registers = $CI->Register->get_all();
		$register = $registers->row_array();

		if (!$CI->Employee->get_logged_in_employee_current_register_id() && isset($register['register_id']))
		{
			$CI->Employee->set_employee_current_register_id($register['register_id']);
		}
	
		$current_register_id = $CI->Employee->get_logged_in_employee_current_register_id();
		$register_info = $CI->Register->get_info($current_register_id);
	
		//IP Tran; supports all platforms
		if($register_info->iptran_device_id)
		{
			require_once(APPPATH.'libraries/Heartlandemvtranscloudprocessor.php');
			$credit_card_processor = new Heartlandemvtranscloudprocessor($CI);
			return $credit_card_processor;
		}
	
		require_once (APPPATH.'libraries/Heartlandemvusbprocessor.php');
		$credit_card_processor = new Heartlandemvusbprocessor($CI);
		return $credit_card_processor;			
	}
	elseif ($CI->Location->get_info_for_key('credit_card_processor') == 'evo')
	{
		$registers = $CI->Register->get_all();
		$register = $registers->row_array();

		if (!$CI->Employee->get_logged_in_employee_current_register_id() && isset($register['register_id']))
		{
			$CI->Employee->set_employee_current_register_id($register['register_id']);
		}
	
		$current_register_id = $CI->Employee->get_logged_in_employee_current_register_id();
		$register_info = $CI->Register->get_info($current_register_id);
	
		require_once (APPPATH.'libraries/Evoemvusbprocessor.php');
		$credit_card_processor = new Evoemvusbprocessor($CI);
		return $credit_card_processor;			
	}
	elseif ($CI->Location->get_info_for_key('credit_card_processor') == 'worldpay')
	{
		$registers = $CI->Register->get_all();
		$register = $registers->row_array();

		if (!$CI->Employee->get_logged_in_employee_current_register_id() && isset($register['register_id']))
		{
			$CI->Employee->set_employee_current_register_id($register['register_id']);
		}
	
		$current_register_id = $CI->Employee->get_logged_in_employee_current_register_id();
		$register_info = $CI->Register->get_info($current_register_id);
	
		require_once (APPPATH.'libraries/Worldpayemvusbprocessor.php');
		$credit_card_processor = new Worldpayemvusbprocessor($CI);
		return $credit_card_processor;			
	}
	elseif ($CI->Location->get_info_for_key('credit_card_processor') == 'firstdata')
	{
		$registers = $CI->Register->get_all();
		$register = $registers->row_array();

		if (!$CI->Employee->get_logged_in_employee_current_register_id() && isset($register['register_id']))
		{
			$CI->Employee->set_employee_current_register_id($register['register_id']);
		}
	
		$current_register_id = $CI->Employee->get_logged_in_employee_current_register_id();
		$register_info = $CI->Register->get_info($current_register_id);
	
		require_once (APPPATH.'libraries/Firstdataemvusbprocessor.php');
		$credit_card_processor = new Firstdataemvusbprocessor($CI);
		return $credit_card_processor;			
	}
	elseif ($CI->Location->get_info_for_key('credit_card_processor') == 'other_usb')
	{
		require_once (APPPATH.'libraries/Otheremvusbprocessor.php');
		$credit_card_processor = new Otheremvusbprocessor($CI);
		return $credit_card_processor;
	}
	
	return false;
}

?>