<?php
class MY_Email extends CI_Email 
{
	public function __construct($config = array())
	{
		if ($this->is_email_configured_in_store_config())
		{
			$CI =& get_instance();
			
			$email_config = array(
				'smtp_crypto'=>$CI->config->item('smtp_crypto') ? $CI->config->item('smtp_crypto') : '',
				'protocol'=>$CI->config->item('protocol'),
				'smtp_host'=>$CI->config->item('smtp_host'),
				'smtp_user'=>$CI->config->item('smtp_user'),
				'smtp_pass'=>$CI->config->item('smtp_pass'),
				'smtp_port'=>$CI->config->item('smtp_port'),
				'email_charset'=>$CI->config->item('email_charset') ? $CI->config->item('email_charset') : 'utf-8',
				'newline'=>$CI->config->item('newline') ? $CI->config->item('newline') : "\n",
				'crlf'=>$CI->config->item('crlf') ? $CI->config->item('crlf') : "\n",
				'smtp_timeout'=>$CI->config->item('smtp_timeout') ? $CI->config->item('smtp_timeout') : 5,
			);
			
			parent::__construct($email_config);
		}
		else
		{
			parent::__construct($config);
		}
	}

	private function is_email_configured_in_store_config()
	{
		$required = array(
			'smtp_host','smtp_user','smtp_pass','smtp_port'
		);
		
		$CI =& get_instance();
		foreach($required as $require_key)
		{
			if (!$CI->config->item($require_key))
			{
				return FALSE;
			}
		}
		
		return TRUE;
	}
	
	public function test_email($email)
	{
		$CI =& get_instance();
		$this->from($CI->Location->get_info_for_key('email') ? $CI->Location->get_info_for_key('email') : 'no-reply@mg.phppointofsale.com', $CI->config->item('company'));
		$this->to($email); 
		
		$this->subject(lang('common_test'));
		$this->message(lang('common_this_is_a_test_email'));	
		if (!$this->send())
		{
			  ob_start();
			  echo $this->print_debugger();
				$output = ob_get_clean();
				return '<pre>'.auto_link(strip_tags($output),'url',TRUE).'</pre>';
		}
		
		return TRUE;
	}
		
	public function from($from, $name = '', $return_path = NULL)
	{
		$CI =& get_instance();
		
		if (is_on_phppos_host())
		{
			$this->reply_to($from, $name);
			$from = 'no-reply@mg.phppointofsale.com';
		}
		elseif ($this->is_email_configured_in_store_config() && $CI->config->item('smtp_host')=='smtp.gmail.com')
		{	
			$from = $CI->config->item('smtp_user');
		}
		parent::from($from,$name,$return_path);
		 
	}
	
	/**
	 * SMTP Connect
	 *
	 * @return	string
	 */
	protected function _smtp_connect()
	{
		if (is_resource($this->_smtp_connect))
		{
			return TRUE;
		}
		$context = stream_context_create();
		$result = stream_context_set_option($context, 'ssl', 'verify_peer', false);
		$ssl = ($this->smtp_crypto === 'ssl') ? 'ssl://' : '';
		
		$this->_smtp_connect = stream_socket_client($ssl.$this->smtp_host . ':'.$this->smtp_port, $errno, $errstr, $this->smtp_timeout, STREAM_CLIENT_CONNECT, $context);
		if ( ! is_resource($this->_smtp_connect))
		{
			$this->_set_error_message('lang:email_smtp_error', $errno.' '.$errstr);
			return FALSE;
		}

		stream_set_timeout($this->_smtp_connect, $this->smtp_timeout);
		$this->_set_error_message($this->_get_smtp_data());


		if ($this->smtp_crypto === 'tls')
		{
			$this->_send_command('hello');
			$this->_send_command('starttls');

			$crypto = stream_socket_enable_crypto($this->_smtp_connect, TRUE, STREAM_CRYPTO_METHOD_TLS_CLIENT);

			if ($crypto !== TRUE)
			{
				$this->_set_error_message('lang:email_smtp_error', $this->_get_smtp_data());
				return FALSE;
			}
		}

		return $this->_send_command('hello');
	}
}