<?php
require_once ("Creditcardprocessor.php");

class Cardconnectprocessor extends Creditcardprocessor
{
	public $merchant_id;
	public $hsn;
	public $rest_username;
	public $rest_password;
	
	public $base_bolt_url;
	public $bolt_api_key;
	public $disable_confirmation_option_for_emv_credit_card;
	
	//TODO ability to set text on bottom of display
	function __construct($controller)
	{
		parent::__construct($controller);
		$this->controller->load->helper('sale');	
		$current_register_id = $this->controller->Employee->get_logged_in_employee_current_register_id();
		$register_info = $this->controller->Register->get_info($current_register_id);
		$this->hsn = $register_info && property_exists($register_info,'card_connect_hsn') ? $register_info->card_connect_hsn : FALSE;
		$this->merchant_id = $this->controller->Location->get_info_for_key('card_connect_mid');	
		$this->base_bolt_url = (!defined("ENVIRONMENT") or ENVIRONMENT == 'development') ? 'https://bolt-uat.cardpointe.com/api/': 'https://bolt.cardpointe.com/api/';
		$this->bolt_api_key = (!defined("ENVIRONMENT") or ENVIRONMENT == 'development') ? 'ZCb8pPkXcZDVO0CIngLSFrBJgA/BYyUZIHT8zaj3MPg=' : 'pLHjO/lvLwYBD5g3jfj2YnkuWDCPC/NT5eEwwCNtyvs=';	
		$this->disable_confirmation_option_for_emv_credit_card = $this->controller->Location->get_info_for_key('disable_confirmation_option_for_emv_credit_card') ? 1 : 0;
		
		$this->card_pointe_api_url = (!defined("ENVIRONMENT") or ENVIRONMENT == 'development') ? 'https://boltgw-uat.cardconnect.com/cardconnect/rest/': 'https://boltgw.cardconnect.com/cardconnect/rest/';
		$this->rest_username =  $this->controller->Location->get_info_for_key('card_connect_rest_username');
		$this->rest_password =  $this->controller->Location->get_info_for_key('card_connect_rest_password');		
	}
	
	private function make_card_pointe_api_request($method, $api_endpoint,$data = array())
	{
		$data_string = json_encode($data);                                                                                                                                                                                                        
		$ch = curl_init($this->card_pointe_api_url.$api_endpoint);  
		
		if ($method == "POST")
		{                                                                    
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
		curl_setopt($ch, CURLOPT_HEADER, 1);
		//Don't verify ssl...just in case a server doesn't have the ability to verify
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		
		$headers = array(  
			'Authorization: Basic '.base64_encode($this->rest_username.':'.$this->rest_password),
	    'Content-Type: application/json',                                                                                
		);
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
    $response = curl_exec($ch); 
		
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$headers = $this->get_headers_from_curl_response(substr($response, 0, $header_size));
		$body = json_decode(substr($response, $header_size), TRUE);
		
    curl_close($ch);
		
		return array('body' => $body,'headers' => $headers);
		
	}
	
	private function make_bolt_api_request($api_endpoint, $post_data,$session_key = false)
	{
		$data_string = json_encode($post_data);                                                                                                                                                                                                        
		$ch = curl_init($this->base_bolt_url.$api_endpoint);                                                                      
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
		curl_setopt($ch, CURLOPT_HEADER, 1);
		//Don't verify ssl...just in case a server doesn't have the ability to verify
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		
		$headers = array(  
			'Authorization: '.$this->bolt_api_key,
	    'Content-Type: application/json',                                                                                
		);
		
		if ($session_key)
		{
			$headers[] = 'X-CardConnect-SessionKey: '.$session_key;
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
    $response = curl_exec($ch); 
		
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$headers = $this->get_headers_from_curl_response(substr($response, 0, $header_size));
		$body = json_decode(substr($response, $header_size), TRUE);
		
    curl_close($ch);
		
		return array('body' => $body,'headers' => $headers);
	}
	
	
	function get_headers_from_curl_response($response)
	{
    $headers = array();

    $header_text = substr($response, 0, strpos($response, "\r\n\r\n"));

    foreach (explode("\r\n", $header_text) as $i => $line)
        if ($i === 0)
            $headers['http_code'] = $line;
        else
        {
            list ($key, $value) = explode(': ', $line);

            $headers[$key] = $value;
        }

    return $headers;
	}
		
	public function connect()
	{
		$data = array(
		'merchantId' => $this->merchant_id,
		'hsn' => $this->hsn,
		'force' => TRUE,
		);
		
		$result = $this->make_bolt_api_request('v2/connect',$data);
		$headers = $result['headers'];
		if (isset($headers['X-CardConnect-SessionKey']))
		{
			list($session_key,$expire) = explode(';',$headers['X-CardConnect-SessionKey']);
			return $session_key;
		}
		
		return FALSE;
	}
	
	public function disconnect($session_key)
	{
		$data = array(
		'merchantId' => $this->merchant_id,
		'hsn' => $this->hsn,
	);
	
		$this->make_bolt_api_request('v2/disconnect',$data,$session_key);
	}
	
		
	public function start_cc_processing()
	{
		$this->controller->load->view('sales/card_connect_start_cc_processing');
		
	}
		
	public function do_start_cc_processing()
	{			
		$cc_amount = to_currency_no_money($this->controller->cart->get_payment_amount(lang('common_credit')));
		$customer_id = $this->controller->cart->customer_id;
		
		$customer_name = '';
		if ($customer_id != -1)
		{
			$customer_info=$this->controller->Customer->get_info($customer_id);
			$customer_name = $customer_info->first_name.' '.$customer_info->last_name;
		}
		
		$tip_amount = NULL;
		
		if(!$this->controller->cart->use_cc_saved_info)
		{
				$session_key = $this->connect();
				
				if (!$session_key)
				{
					$this->controller->_reload(array('error' => lang('sales_charging_card_failed_please_try_again')), false);
					return;
				}
				
				if ($this->controller->config->item('enable_tips'))
				{
					
					if ($this->controller->config->item('tip_preset_zero'))
					{
						$tip_presets = array(0,15,20);						
					}
					else
					{
						$tip_presets = array(15,20,25);
					}
					$post_data = array(
						"merchantId" => $this->merchant_id,
						"hsn" => $this->hsn,
						"prompt" => lang('sales_tip_prompt'),
						"amount" => (int)(round($cc_amount*100)),
						'tipPercentPresets' => $tip_presets,
					);
					$response = $this->make_bolt_api_request('v3/tip', $post_data,$session_key);
					
					$tip_amount = round($response['body']['tip']/100,2);
				}
			
				$prompt = $this->controller->cart->prompt_for_card;
							
				$post_data = array(
					"merchantId" => $this->merchant_id,
					"hsn" => $this->hsn,
					"amount" => (int)((round($cc_amount*100))+(isset($response['body']['tip']) ? $response['body']['tip'] : 0)),
					"includeSignature" => true,  
					"gzipSignature" => false,
					"signatureFormat" => 'png',
					"includeAmountDisplay" => true,
					"confirmAmount" => $this->disable_confirmation_option_for_emv_credit_card ? FALSE : TRUE,
					"beep" => false, 
					"aid" => "credit",
					"capture" => TRUE,
				);
				
				
				$post_data['userFields']['predictedSaleId'] = $this->controller->Sale->get_next_sale_id();
				
				if (isset($prompt) && $prompt)
				{
					$post_data['includeCVV'] =  TRUE;
					$post_data['includeAVS'] = TRUE;
					$response = $this->make_bolt_api_request('v3/authManual', $post_data,$session_key);
				}
				else
				{
					$response = $this->make_bolt_api_request('v3/authCard', $post_data,$session_key);
				}				
		}
		elseif($customer_info->cc_token)
		{
			if ($cc_amount <= 0)
			{
				$this->controller->_reload(array('error' => lang('sales_charging_card_failed_please_try_again')), false);
				return;
			}			
					
			$post_data = array(
		    'merchid'   => $this->merchant_id,
		    'account'   => $customer_info->cc_token,
		    'expiry'    => $customer_info->cc_expire,
		    'amount'    => $cc_amount,
		    'tokenize'  => "Y",
			'capture'	=> "Y",
			'cof' 		=> 	"C",
			'cofscheduled' 	=> 	"N",
  		);
			$response = $this->make_card_pointe_api_request('POST','auth',$post_data);			
			
		}
				 
		$TextResponse = isset($response['body']['errorMessage'])  ? $response['body']['errorMessage'] : $response['body']['resptext'];
		if (!isset($response['body']['errorCode']) && $response['body']['respstat'] == 'A')
		{
			if (isset($response['body']['emvTagData']))
			{
				$emvTagData = json_decode($response['body']['emvTagData'], TRUE);
				$CardType = $emvTagData['Network Label'];
				$EntryMethod = $emvTagData['Entry method'];
				$ApplicationLabel = $emvTagData['Application Label'];

				$AID = $emvTagData['AID'];
				$TVR = $emvTagData['TVR'];
				$IAD = $emvTagData['IAD'];
				$TSI = $emvTagData['TSI'];
		  }
			else
			{
				$EntryMethod = $prompt ? lang('sales_manual_entry') : lang('common_credit');
				$ApplicationLabel = $customer_info->card_issuer ? $customer_info->card_issuer : $EntryMethod;
				$CardType = $customer_info->card_issuer ? $customer_info->card_issuer : $EntryMethod;
			}
			
			//Catch all
			if (!$CardType && $customer_info->card_issuer)
			{
				$CardType = $customer_info->card_issuer;
			}
			
			 $MerchantID =  $response['body']['merchid'];
		   $AcctNo = substr($response['body']['token'],-4);
		   $TranCode = lang('sales_card_transaction');
		   $AuthCode = $response['body']['authcode'];
		   $RefNo = $response['body']['retref'];
		   $Purchase = $cc_amount + (isset($tip_amount) ? $tip_amount : 0);
		   $Authorize = $response['body']['amount'];
			 
		   $RecordNo = $response['body']['token'];
			$CCExpire = $response['body']['expiry'];
			
			if (isset($response['body']['signature']))
			{
				$signature = base64_decode($response['body']['signature']);
			}
			else
			{
				$signature = '';
			}
			$this->controller->session->set_userdata('ref_no', $RefNo);
			$this->controller->session->set_userdata('tip_amount', $tip_amount);
			$this->controller->session->set_userdata('auth_code', $AuthCode);
			$this->controller->session->set_userdata('cc_token', $RecordNo);
			$this->controller->session->set_userdata('entry_method', $EntryMethod);
			
			if (isset($response['body']['emvTagData']))
			{
				$this->controller->session->set_userdata('aid', $AID);
				$this->controller->session->set_userdata('tvr', $TVR);
				$this->controller->session->set_userdata('iad', $IAD);
				$this->controller->session->set_userdata('tsi', $TSI);
			}
			
			$this->controller->session->set_userdata('application_label', $ApplicationLabel);
			$this->controller->session->set_userdata('tran_type', $TranCode);
			$this->controller->session->set_userdata('text_response', $TextResponse);

			$this->controller->session->set_userdata('cc_signature', $signature);

			//Payment covers purchase amount
			if ((int)(round($Authorize*100)) == (int)(round($Purchase*100)))
			{
				$this->controller->session->set_userdata('masked_account', $AcctNo);
				$this->controller->session->set_userdata('card_issuer', $CardType);
						
				$info=$this->controller->Customer->get_info($this->controller->cart->customer_id);
				
				//We want to save/update card when we have a customer AND they have chosen to save OR we have a customer and they are using a saved card
				if (($this->controller->cart->save_credit_card_info) && $this->controller->cart->customer_id 
				|| ($this->controller->cart->customer_id && $this->controller->cart->use_cc_saved_info))
				{
					$person_info = array('person_id' => $this->controller->cart->customer_id);
					$customer_info = array('cc_token' => $RecordNo, 'cc_expire' => $CCExpire, 'cc_ref_no' => $RefNo, 'cc_preview' => $AcctNo);
					$this->controller->Customer->save_customer($person_info,$customer_info,$this->controller->cart->customer_id);
				}
				
				
				//If the sale payments cover the total, redirect to complete (receipt)
				if ($this->controller->_payments_cover_total())
				{
					$this->controller->session->set_userdata('CC_SUCCESS', TRUE);
					
					if (isset($session_key) && $session_key)
					{
						$this->disconnect($session_key);
					}
					
					redirect(site_url('sales/complete'));
				}
				else //Change payment type to Partial Credit Card and show sales interface
				{							
					$credit_card_amount = to_currency_no_money($this->controller->cart->get_payment_amount(lang('common_credit')));
				
					$partial_transaction = array(
						'AuthCode' => $AuthCode,
						'MerchantID' => $this->merchant_id ,
						'Purchase' => $Purchase,
						'RefNo' => $RefNo,
						'RecordNo' => $RecordNo,
					);
														
					$this->controller->cart->delete_payment($this->controller->cart->get_payment_ids(lang('common_credit')));												
				
					@$this->controller->cart->add_payment(new PHPPOSCartPaymentSale(array(
						'payment_type' => lang('sales_partial_credit'),
						'payment_amount' => $credit_card_amount,
						'payment_date' => date('Y-m-d H:i:s'),
						'truncated_card' => $AcctNo,
						'card_issuer' => $CardType,
						'auth_code' => $AuthCode,
						'ref_no' => $RefNo,
						'cc_token' => $RecordNo,
						'entry_method' => $EntryMethod,
						'aid' => $AID,
						'tvr' => $TVR,
						'iad' => $IAD,
						'tsi' => $TSI,
						'tran_type' => $TranCode,
						'application_label' => $ApplicationLabel,
					)));
					
					$this->controller->cart->add_partial_transaction($partial_transaction);
					$this->controller->cart->save();
					$this->controller->_reload(array('warning' => lang('sales_credit_card_partially_charged_please_complete_sale_with_another_payment_method')), false);			
				}
			}
			elseif($Authorize < $Purchase)
			{
					$partial_transaction = array(
						'AuthCode' => $AuthCode,
						'MerchantID' => $this->merchant_id ,
						'Purchase' => $Authorize,
						'RefNo' => $RefNo,
						'RecordNo' => $RecordNo,
					);
			
					$this->controller->cart->delete_payment($this->controller->cart->get_payment_ids(lang('common_credit')));
					
					@$this->controller->cart->add_payment(new PHPPOSCartPaymentSale(array(
						'payment_type' => lang('sales_partial_credit'),
						'payment_amount' => $Authorize,
						'payment_date' => date('Y-m-d H:i:s'),
						'truncated_card' => $AcctNo,
						'card_issuer' => $CardType,
						'auth_code' => $AuthCode,
						'ref_no' => $RefNo,
						'cc_token' => $RecordNo,
						'entry_method' => $EntryMethod,
						'aid' => $AID,
						'tvr' => $TVR,
						'iad' => $IAD,
						'tsi' => $TSI,
						'tran_type' => $TranCode,
						'application_label' => $ApplicationLabel,
					)));
					
					$this->controller->cart->add_partial_transaction($partial_transaction);
					$this->controller->cart->save();
					$this->controller->_reload(array('warning' => lang('sales_credit_card_partially_charged_please_complete_sale_with_another_payment_method')), false);	
				}
		}
		else
		{
			
			//If we are using saved token and have a failed response remove token from customer
			if ($this->controller->cart->use_cc_saved_info && $this->controller->cart->customer_id)
			{
				//If we have failed, remove cc token and cc preview
				$person_info = array('person_id' => $this->controller->cart->customer_id);
				$customer_info = array('cc_token' => NULL, 'cc_expire' => NULL, 'cc_ref_no' => NULL, 'cc_preview' => NULL, 'card_issuer' => NULL);
				
				if (!$this->controller->config->item('do_not_delete_saved_card_after_failure'))
				{
					$this->controller->Customer->save_customer($person_info,$customer_info,$this->controller->cart->customer_id);
				}
								
				//Clear cc token for using saved cc info
				$this->controller->cart->use_cc_saved_info = NULL;
				$this->controller->cart->save();
			}
			
			if (isset($response['body']['respstat']) && $response['body']['respstat'] == 'C')
			{
				redirect(site_url('sales/declined'));
			}
			else
			{
				$this->controller->_reload(array('error' => $TextResponse), false);
			}
		}
	}
	
	public function finish_cc_processing()
	{
		//No need for this method as it is handled by start method all at once
		return TRUE;
	}
	
	public function cancel_cc_processing()
	{
		$this->controller->cart->delete_payment($this->controller->cart->get_payment_ids(lang('common_credit')));
		$this->controller->cart->save();
		$this->controller->_reload(array('error' => lang('sales_cc_processing_cancelled')), false);
	}
	
	
	private function void_sale_payment($payment_amount,$auth_code,$ref_no,$token,$acq_ref_data,$process_data,$tip_amount = 0)
	{
		$post_data = array(
	    'merchid'   => $this->merchant_id,
			'retref'		=> $ref_no,
		);
		
		//try void first
		$response = $this->make_card_pointe_api_request('POST','void',$post_data);	
		
		if ($response['body']['respstat'] != 'A')
		{
			$response = $this->make_card_pointe_api_request('POST','refund',$post_data);	
			return $response['body']['respstat'] == 'A';					
		}

		return TRUE;
		
	}
	
	private function void_return_payment($payment_amount,$auth_code,$ref_no,$token,$acq_ref_data,$process_data)
	{
		$post_data = array(
	    'merchid'   => $this->merchant_id,
			'retref'		=> $ref_no,
		);
		
		//try void first
		$response = $this->make_card_pointe_api_request('POST','void',$post_data);	
		
		if ($response['body']['respstat'] != 'A')
		{
			$response = $this->make_card_pointe_api_request('POST','refund',$post_data);	
			return $response['body']['respstat'] == 'A';					
		}

		return TRUE;
	}
	
	public function void_partial_transactions()
	{
		$void_success = true;
		
		if ($partial_transactions = $this->controller->cart->get_partial_transactions())
		{
			for ($k = 0;$k<count($partial_transactions);$k++)
			{
				$partial_transaction = $partial_transactions[$k];
				@$void_success = $this->void_sale_payment(to_currency_no_money($partial_transaction['Purchase']),$partial_transaction['AuthCode'],$partial_transaction['RefNo'],$partial_transaction['RecordNo'],$partial_transaction['AcqRefData'],$partial_transaction['ProcessData']);
			}
		}
		return $void_success;
	}	
	public function void_sale($sale_id)
	{
		if ($this->controller->Sale->can_void_cc_sale($sale_id))
		{
			$void_success = true;
			
			$payments = $this->_get_cc_payments_for_sale($sale_id);
			
			foreach($payments as $payment)
			{				
				@$void_success = $this->void_sale_payment($payment['payment_amount'], $payment['auth_code'], $payment['ref_no'], $payment['cc_token'],$payment['acq_ref_data'], $payment['process_data']);
			}
			
			return $void_success;
		}
		
		return FALSE;
	}
	
	public function void_return($sale_id)
	{
		if ($this->controller->Sale->can_void_cc_return($sale_id))
		{
			$void_success = true;
			
			$payments = $this->_get_cc_payments_for_sale($sale_id);
			
			foreach($payments as $payment)
			{
				$void_success = $this->void_return_payment($payment['payment_amount'], $payment['auth_code'], $payment['ref_no'], $payment['cc_token'],$payment['acq_ref_data'], $payment['process_data']);
			}
			
			return $void_success;
		}
		
		return FALSE;	
	}		
	
	//Handled during transaction
	public function tip($sale_id,$tip_amount)
	{
		return FALSE;
	}
	
}