<?php
require_once (APPPATH."libraries/blockchyp/vendor/autoload.php");

use \BlockChyp\BlockChyp;

trait creditcardProcessingTrait
{
	function _get_cc_processor()
	{
		if (!$this->Location->get_info_for_key('enable_credit_card_processing'))
		{
			return false;
		}
										
		//If we have setup Mercury....or if it is not set then default to Mercury
		if ($this->Location->get_info_for_key('credit_card_processor') == 'mercury' || !$this->Location->get_info_for_key('credit_card_processor'))
		{
			$registers = $this->Register->get_all();
			$register = $registers->row_array();
		
			if (!$this->Employee->get_logged_in_employee_current_register_id() && isset($register['register_id']))
			{
				$this->Employee->set_employee_current_register_id($register['register_id']);
			}
			
			$current_register_id = $this->Employee->get_logged_in_employee_current_register_id();
			$register_info = $this->Register->get_info($current_register_id);
			
			//IP Tran; supports all platforms
			if($register_info->iptran_device_id)
			{
				require_once(APPPATH.'libraries/Mercuryemvtranscloudprocessor.php');
				$credit_card_processor = new Mercuryemvtranscloudprocessor($this);
				return $credit_card_processor;
			}
			
			//Mobile always uses hosted checkout as we do NOT have mobile support for EMV
			if ($this->agent->is_mobile())
			{
				require_once (APPPATH.'libraries/Mercuryhostedcheckoutprocessor.php');
				$credit_card_processor = new Mercuryhostedcheckoutprocessor($this);	
				return $credit_card_processor;
			}
		
			//EMV
			if ($this->Location->get_info_for_key('emv_merchant_id') && $this->Location->get_info_for_key('com_port') && $this->Location->get_info_for_key('listener_port'))
			{
				require_once (APPPATH.'libraries/Mercuryemvusbprocessor.php');
				$credit_card_processor = new Mercuryemvusbprocessor($this);
				return $credit_card_processor;
			}
			else //Default hosted checkout
			{
				require_once (APPPATH.'libraries/Mercuryhostedcheckoutprocessor.php');
				$credit_card_processor = new Mercuryhostedcheckoutprocessor($this);
				return $credit_card_processor;
			}
		}
		elseif ($this->Location->get_info_for_key('credit_card_processor') == 'card_connect')
		{
			$registers = $this->Register->get_all();
			$register = $registers->row_array();
		
			if (!$this->Employee->get_logged_in_employee_current_register_id() && isset($register['register_id']))
			{
				$this->Employee->set_employee_current_register_id($register['register_id']);
			}
			
			$current_register_id = $this->Employee->get_logged_in_employee_current_register_id();
			$register_info = $this->Register->get_info($current_register_id);
			
			require_once(APPPATH.'libraries/Cardconnectprocessor.php');
			$credit_card_processor = new Cardconnectprocessor($this);	
			return $credit_card_processor;			
		}
		elseif ($this->Location->get_info_for_key('credit_card_processor') == 'heartland')
		{
			$registers = $this->Register->get_all();
			$register = $registers->row_array();
		
			if (!$this->Employee->get_logged_in_employee_current_register_id() && isset($register['register_id']))
			{
				$this->Employee->set_employee_current_register_id($register['register_id']);
			}
			
			$current_register_id = $this->Employee->get_logged_in_employee_current_register_id();
			$register_info = $this->Register->get_info($current_register_id);
			
			//IP Tran; supports all platforms
			if($register_info->iptran_device_id)
			{
				require_once(APPPATH.'libraries/Heartlandemvtranscloudprocessor.php');
				$credit_card_processor = new Heartlandemvtranscloudprocessor($this);
				return $credit_card_processor;
			}
			
			require_once (APPPATH.'libraries/Heartlandemvusbprocessor.php');
			$credit_card_processor = new Heartlandemvusbprocessor($this);
			return $credit_card_processor;			
		}
		elseif ($this->Location->get_info_for_key('credit_card_processor') == 'evo')
		{
			$registers = $this->Register->get_all();
			$register = $registers->row_array();
		
			if (!$this->Employee->get_logged_in_employee_current_register_id() && isset($register['register_id']))
			{
				$this->Employee->set_employee_current_register_id($register['register_id']);
			}
			
			$current_register_id = $this->Employee->get_logged_in_employee_current_register_id();
			$register_info = $this->Register->get_info($current_register_id);
			
			require_once (APPPATH.'libraries/Evoemvusbprocessor.php');
			$credit_card_processor = new Evoemvusbprocessor($this);
			return $credit_card_processor;			
		}
		elseif ($this->Location->get_info_for_key('credit_card_processor') == 'worldpay')
		{
			$registers = $this->Register->get_all();
			$register = $registers->row_array();
		
			if (!$this->Employee->get_logged_in_employee_current_register_id() && isset($register['register_id']))
			{
				$this->Employee->set_employee_current_register_id($register['register_id']);
			}
			
			$current_register_id = $this->Employee->get_logged_in_employee_current_register_id();
			$register_info = $this->Register->get_info($current_register_id);
			
			require_once (APPPATH.'libraries/Worldpayemvusbprocessor.php');
			$credit_card_processor = new Worldpayemvusbprocessor($this);
			return $credit_card_processor;			
		}
		elseif ($this->Location->get_info_for_key('credit_card_processor') == 'firstdata')
		{
			$registers = $this->Register->get_all();
			$register = $registers->row_array();
		
			if (!$this->Employee->get_logged_in_employee_current_register_id() && isset($register['register_id']))
			{
				$this->Employee->set_employee_current_register_id($register['register_id']);
			}
			
			$current_register_id = $this->Employee->get_logged_in_employee_current_register_id();
			$register_info = $this->Register->get_info($current_register_id);
			
			require_once (APPPATH.'libraries/Firstdataemvusbprocessor.php');
			$credit_card_processor = new Firstdataemvusbprocessor($this);
			return $credit_card_processor;			
		}
		elseif ($this->Location->get_info_for_key('credit_card_processor') == 'stripe')
		{
			require_once (APPPATH.'libraries/Stripeprocessor.php');
			$credit_card_processor = new Stripeprocessor($this);
			return $credit_card_processor;
		}
		elseif ($this->Location->get_info_for_key('credit_card_processor') == 'braintree')
		{
			require_once (APPPATH.'libraries/Braintreeprocessor.php');
			$credit_card_processor = new Braintreeprocessor($this);
			return $credit_card_processor;
		}
		elseif ($this->Location->get_info_for_key('credit_card_processor') == 'other_usb')
		{
			require_once (APPPATH.'libraries/Otheremvusbprocessor.php');
			$credit_card_processor = new Otheremvusbprocessor($this);
			return $credit_card_processor;
		}
		elseif ($this->Location->get_info_for_key('credit_card_processor') == 'square')
		{
			require_once (APPPATH.'libraries/Squareprocessor.php');
			$credit_card_processor = new Squareprocessor($this);
			return $credit_card_processor;
		}
		elseif ($this->Location->get_info_for_key('credit_card_processor') == 'coreclear2')
		{
			require_once (APPPATH.'libraries/Coreclearblockchypprocessor.php');
			$credit_card_processor = new Coreclearblockchypprocessor($this);
			return $credit_card_processor;
		}
		return false;
	}
	
	private function _is_terminal_online($terminal_name,$blockchyp_api_key,$blockchyp_bearer_token,$blockchyp_signing_key)
	{
		try
		{
	    	BlockChyp::setApiKey($blockchyp_api_key);
	    	BlockChyp::setBearerToken($blockchyp_bearer_token);
	    	BlockChyp::setSigningKey($blockchyp_signing_key);
			
			$params = array();
			$params['terminalName'] = $terminal_name;
			$return = BlockChyp::ping($params);
			return $return['success'];
		}
		catch(Exception $e)
		{
			
		}	
		
		return FALSE;
	}
	
	
	function get_blockchyp_terminal_status()
	{
		session_write_close();
		
		if ($this->input->get('register_id'))
		{		
			$terminalName = $this->Employee->get_logged_in_employee_current_register_id() ? $this->Register->get_info($this->Employee->get_logged_in_employee_current_register_id())->emv_terminal_id : '';
			$blockchyp_api_key = $this->Location->get_info_for_key('blockchyp_api_key');
			$blockchyp_bearer_token = $this->Location->get_info_for_key('blockchyp_bearer_token');
			$blockchyp_signing_key = $this->Location->get_info_for_key('blockchyp_signing_key');		
		}
		else
		{
			$terminalName = $this->input->get('terminalName');
			$blockchyp_api_key = $this->input->get('blockchyp_api_key');
			$blockchyp_bearer_token = $this->input->get('blockchyp_bearer_token');
			$blockchyp_signing_key = $this->input->get('blockchyp_signing_key');
		}
		
		if ($terminalName)
		{
			$return['online'] = $this->_is_terminal_online($terminalName,$blockchyp_api_key,$blockchyp_bearer_token,$blockchyp_signing_key);
		}
		else
		{
			$return['online'] = TRUE;
		}
		echo json_encode($return);
		
	}
}
