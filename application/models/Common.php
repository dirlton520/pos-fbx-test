<?php

class Common extends CI_Model
{	
	function __construct()
	{		
		$this->load->model('Location');
	}

	function send_sms($to_phone_number,$text_message)
	{				
		$account_sid = $this->Location->get_info_for_key('twilio_sid');
		$auth_token = $this->Location->get_info_for_key('twilio_token');
		$twilio_sms_from = $this->Location->get_info_for_key('twilio_sms_from');
		
		if($account_sid && $auth_token && $twilio_sms_from)
		{
			$params = array(
				'account_sid' => $account_sid, 
				'auth_token' => $auth_token
			);

			$this->load->library("Citwilio", $params);
			return $this->citwilio->send_sms($twilio_sms_from, $to_phone_number, $text_message);
		}

		return false;
	}

	function send_email($to_email,$subject,$message)
	{				
		$this->load->library('email');
		$config['mailtype'] = 'html';
		$this->email->initialize($config);
		$this->email->from($this->Location->get_info_for_key('email') ? $this->Location->get_info_for_key('email') : 'no-reply@coreware.com', $this->config->item('company'));
		$this->email->to($to_email);
		
		if($this->Location->get_info_for_key('cc_email'))
		{
			$this->email->cc($this->Location->get_info_for_key('cc_email'));
		}
		
		if($this->Location->get_info_for_key('bcc_email'))
		{
			$this->email->bcc($this->Location->get_info_for_key('bcc_email'));
		}
		
		$this->email->subject($subject);
		$this->email->message($message);
		$this->email->send();
	}
}
?>
