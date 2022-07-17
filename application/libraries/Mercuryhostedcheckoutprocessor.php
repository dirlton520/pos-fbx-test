<?php
require_once ("Creditcardprocessor.php");
class Mercuryhostedcheckoutprocessor extends Creditcardprocessor
{	
	function __construct($controller)
	{
		parent::__construct($controller);	
	}
	
	public function start_cc_processing()
	{
		$service_url = (!defined("ENVIRONMENT") or ENVIRONMENT == 'development') ? 'https://hc.mercurycert.net/hcws/hcservice.asmx?WSDL': 'https://hc.mercurypay.com/hcws/hcservice.asmx?WSDL';
		$cc_amount = to_currency_no_money($this->controller->cart->get_payment_amount(lang('common_credit')));
		$tax_amount = to_currency_no_money(($this->controller->cart->get_total() - $this->controller->cart->get_subtotal()) * ($cc_amount / $this->controller->cart->get_total()));
		$customer_id = $this->controller->cart->customer_id;
		$customer_name = '';
		if ($customer_id != -1)
		{
			$customer_info=$this->controller->Customer->get_info($customer_id);
			$customer_name = $customer_info->first_name.' '.$customer_info->last_name;
		}
		
		if(!$this->controller->cart->use_cc_saved_info)
		{
			$invoice_number = $this->_get_session_invoice_no();

			$parameters = array(
				'request' => array(
					'MerchantID' => $this->controller->Location->get_info_for_key('hosted_checkout_merchant_id'),
					'Password' => $this->controller->Location->get_info_for_key('hosted_checkout_merchant_password'),
					'TranType' => $cc_amount > 0 ? 'Sale' : 'Return',
					'TotalAmount' => to_currency_no_money(abs($cc_amount)),
					'PartialAuth' => 'On',
					'Frequency' => 'OneTime',
					'OperatorID' => (!defined("ENVIRONMENT") or ENVIRONMENT == 'development') ? 'test' : $this->controller->Employee->get_logged_in_employee_info()->person_id,
					'Invoice' => $invoice_number,
					'Memo' => 'PHP POS HC '.APPLICATION_VERSION,
					'TaxAmount' => to_currency_no_money(abs($tax_amount)),
					'CardHolderName' => substr(preg_replace("/[^A-Za-z ]/", '', $customer_name),0,30),
					'ForceManualTablet' => 'Off',
					'ProcessCompleteUrl' => site_url('sales/finish_cc_processing'),
					'ReturnUrl' => site_url('sales/cancel_cc_processing'),
					'LaneID' => $this->controller->Employee->get_logged_in_employee_current_register_id()  && $this->controller->Employee->get_logged_in_employee_current_register_id() <=999 ? $this->controller->Employee->get_logged_in_employee_current_register_id()  : 0
				)
			);
			

			if (isset($customer_info) && $customer_info->zip && $this->_is_valid_zip($customer_info->zip))
			{
				$customer_info->zip = str_replace('-','',$customer_info->zip);
				$parameters['request']['AVSZip'] = $customer_info->zip;
			}
			try
			{
				$client = new SoapClient($service_url,array('trace' => TRUE));
				$result = $client->InitializePayment($parameters);
				$response_code = $result->InitializePaymentResult->ResponseCode;

				if ($response_code == 0)
				{
					$payment_id = $result->InitializePaymentResult->PaymentID;
					$hosted_checkout_url = (!defined("ENVIRONMENT") or ENVIRONMENT == 'development') ? 'https://hc.mercurycert.net/CheckoutPOS.aspx' : 'https://hc.mercurypay.com/CheckoutPOS.aspx';
					$this->controller->load->view('sales/mercury_hosted_checkout', array('payment_id' => $payment_id, 'hosted_checkout_url' =>$hosted_checkout_url ));
				}
				else
				{
					$this->controller->_reload(array('error' => lang('sales_credit_card_processing_is_down')), false);
				}			
			}
			catch (SoapFault $e) 
			{
				$this->controller->_reload(array('error' => lang('sales_credit_card_processing_is_down')), false);
			} 
		}
		elseif($customer_info->cc_token) //We have saved credit card information, process it
		{
			if ($cc_amount <= 0)
			{
				$this->controller->_reload(array('error' => lang('sales_charging_card_failed_please_try_again')), false);
				return;
			}
			
			$service_url = (!defined("ENVIRONMENT") or ENVIRONMENT == 'development') ? 'https://hc.mercurycert.net/tws/transactionservice.asmx?WSDL': 'https://hc.mercurypay.com/tws/transactionservice.asmx?WSDL';

			try
			{
				$client = new SoapClient($service_url,array('trace' => TRUE));
				$invoice_number = $this->_get_session_invoice_no();
			
				$parameters = array(
					'request' => array(
						'Token' => $customer_info->cc_token,
						'MerchantID' => $this->controller->Location->get_info_for_key('hosted_checkout_merchant_id'),
						'PurchaseAmount' => $cc_amount,
						'PartialAuth' => FALSE,
						'Frequency' => 'OneTime',
						'OperatorID' => (!defined("ENVIRONMENT") or ENVIRONMENT == 'development') ? 'test' : $this->controller->Employee->get_logged_in_employee_info()->person_id,
						'Invoice' => $invoice_number,
						'Memo' => 'PHP POS HC '.APPLICATION_VERSION,
						'TaxAmount' => $tax_amount,
						'CardHolderName' => substr(preg_replace("/[^A-Za-z ]/", '', $customer_name),0,30),
					),
					'password' => $this->controller->Location->get_info_for_key('hosted_checkout_merchant_password'),
				);

				if (isset($customer_info) && $customer_info->zip && $this->_is_valid_zip($customer_info->zip))
				{
					$customer_info->zip = str_replace('-','',$customer_info->zip);
					$parameters['request']['Zip'] = $customer_info->zip;
				}
				$result = $client->CreditSaleToken($parameters);
			
				$status = $result->CreditSaleTokenResult->Status;

			
				if ($status == 'Approved')	
				{
					//Make sure we remove invoice number in case of partial auth...We need a new invoice number
					$this->controller->cart->invoice_no = NULL;
					$this->controller->cart->save();
					$token =  $result->CreditSaleTokenResult->Token;
					$ref_no =  $result->CreditSaleTokenResult->RefNo;
					$auth_code = $result->CreditSaleTokenResult->AuthCode;
					$masked_account = $customer_info->cc_preview;
					$card_issuer = $customer_info->card_issuer;
					$acq_ref_data = $result->CreditSaleTokenResult->AcqRefData;
					$process_data =  $result->CreditSaleTokenResult->ProcessData;
					$tran_type = lang('sales_card_on_file');
				
					$person_info = array('person_id' => $this->controller->cart->customer_id);
					$customer_info = array('cc_token' => $token);
					$this->controller->Customer->save_customer($person_info,$customer_info,$this->controller->cart->customer_id);
					$this->controller->session->set_userdata('ref_no', $ref_no);
					$this->controller->session->set_userdata('auth_code', $auth_code);
					$this->controller->session->set_userdata('masked_account', $masked_account);
					$this->controller->session->set_userdata('card_issuer', $card_issuer);
					$this->controller->session->set_userdata('cc_token', $token);
					$this->controller->session->set_userdata('acq_ref_data', $acq_ref_data);
					$this->controller->session->set_userdata('process_data', $process_data);
					$this->controller->session->set_userdata('tran_type', $tran_type);
				
					//If the sale payments cover the total, redirect to complete (receipt)
					if ($this->controller->_payments_cover_total())
					{
						$this->controller->session->set_userdata('CC_SUCCESS', TRUE);
						redirect(site_url('sales/complete'));
					}
					else //Change payment type to Partial Credit Card and show sales interface
					{
						$credit_card_amount = to_currency_no_money($this->controller->cart->get_payment_amount(lang('common_credit')));

						$partial_transaction = array(
							'AuthCode' => $auth_code,
							'Frequency' => 'OneTime',
							'Memo' => 'PHP POS HC '.APPLICATION_VERSION,
							'Invoice' => $invoice_number,
							'MerchantID' => $this->controller->Location->get_info_for_key('hosted_checkout_merchant_id'),
							'OperatorID' => (!defined("ENVIRONMENT") or ENVIRONMENT == 'development') ? 'test' : $this->controller->Employee->get_logged_in_employee_info()->person_id,
							'PurchaseAmount' => $credit_card_amount,
							'RefNo' => $ref_no,
							'Token' => $token,
							'AcqRefData' =>$acq_ref_data,
							'ProcessData' => $process_data,
						);
										
						$this->controller->cart->delete_payment($this->controller->cart->get_payment_ids(lang('common_credit')));
						$this->controller->cart->add_payment(new PHPPOSCartPaymentSale(array(
							'payment_type' => lang('sales_partial_credit'),
							'payment_amount' => $credit_card_amount,
							'payment_date' => date('Y-m-d H:i:s'),
							'truncated_card' => $masked_account,
							'card_issuer' => $card_issuer,
							'auth_code' => $auth_code,
							'ref_no' => $ref_no,
							'cc_token' => $token,
							'acq_ref_data' => $acq_ref_data,
							'process_date' => $process_data,
							'tran_type' => $tran_type,
						)));
						$this->controller->cart->add_partial_transaction($partial_transaction);
						$this->controller->cart->save();
						$this->controller->_reload(array('warning' => lang('sales_credit_card_partially_charged_please_complete_sale_with_another_payment_method')), false);			
					}
				}
				else
				{
					//If we have failed, remove cc token and cc preview
					$person_info = array('person_id' => $this->controller->cart->customer_id);
					$customer_info = array('cc_token' => NULL, 'cc_preview' => NULL, 'card_issuer' => NULL);
					
					if (!$this->controller->config->item('do_not_delete_saved_card_after_failure'))
					{
						$this->controller->Customer->save_customer($person_info,$customer_info,$this->controller->cart->customer_id);
					}
					
					//Clear cc token for using saved cc info
					$this->controller->cart->use_cc_saved_info = NULL;
					$this->controller->cart->save();
					if ($status == 'Declined')
					{
						redirect(site_url('sales/declined'));
					}
					else
					{
						$this->controller->_reload(array('error' => lang('sales_charging_card_failed_please_try_again')), false);
					}
				}
			}
			catch (SoapFault $e) 
			{
				$this->controller->_reload(array('error' => lang('sales_charging_card_failed_please_try_again')), false);
			}
		}
	}
	
	public function finish_cc_processing()
	{
		$return_code = $this->controller->input->get("ReturnCode");		
		$service_url = (!defined("ENVIRONMENT") or ENVIRONMENT == 'development') ? 'https://hc.mercurycert.net/hcws/hcservice.asmx?WSDL': 'https://hc.mercurypay.com/hcws/hcservice.asmx?WSDL';
		$parameters = array(
			'request' => array(
				'MerchantID' => $this->controller->Location->get_info_for_key('hosted_checkout_merchant_id'),
				'PaymentID' => $this->controller->input->get('PaymentID'),
				'Password' => $this->controller->Location->get_info_for_key('hosted_checkout_merchant_password'),
			)
		);
		
		try
		{
			$client = new SoapClient($service_url,array('trace' => TRUE));
			$result = $client->VerifyPayment($parameters);
			$response_code = $result->VerifyPaymentResult->ResponseCode;
			$status = $result->VerifyPaymentResult->Status;
			$total_amount = $result->VerifyPaymentResult->Amount;
			$auth_amount = $result->VerifyPaymentResult->AuthAmount;
		
			$auth_code = $result->VerifyPaymentResult->AuthCode;
			$acq_ref_data = $result->VerifyPaymentResult->AcqRefData;
			$ref_no =  $result->VerifyPaymentResult->RefNo;
			$token =  $result->VerifyPaymentResult->Token;
			$masked_account = $result->VerifyPaymentResult->MaskedAccount;
			$process_data =  $result->VerifyPaymentResult->ProcessData;
			$card_issuer = $result->VerifyPaymentResult->CardType;
			$tran_type = $result->VerifyPaymentResult->TranType;
		
			if ($response_code == 0 && $status == 'Approved')
			{
				//Make sure we remove invoice number in case of partial auth...We need a new invoice number
				$this->controller->cart->invoice_no = NULL;
				$this->controller->cart->save();
				$result = $client->AcknowledgePayment($parameters);
				$response_code = $result->AcknowledgePaymentResult;
			
				$this->controller->session->set_userdata('ref_no', $ref_no);
				$this->controller->session->set_userdata('auth_code', $auth_code);
				$this->controller->session->set_userdata('cc_token', $token);
				$this->controller->session->set_userdata('acq_ref_data', $acq_ref_data);
				$this->controller->session->set_userdata('process_data', $process_data);
				$this->controller->session->set_userdata('tran_type', $tran_type);
			
				if ($response_code == 0 && $auth_amount == $total_amount)
				{
					$this->controller->session->set_userdata('masked_account', $masked_account);
					$this->controller->session->set_userdata('card_issuer', $card_issuer);
				
					$info=$this->controller->Customer->get_info($this->controller->cart->customer_id);
				
					//We want to save/update card when we have a customer AND they have chosen to save
					if (($this->controller->cart->save_credit_card_info) && $this->controller->cart->customer_id)
					{
						$person_info = array('person_id' => $this->controller->cart->customer_id);
						$customer_info = array('cc_token' => $token, 'cc_preview' => $masked_account, 'card_issuer' => $card_issuer);
						$this->controller->Customer->save_customer($person_info,$customer_info,$this->controller->cart->customer_id);
					}
								
					//If the sale payments cover the total, redirect to complete (receipt)
					if ($this->controller->_payments_cover_total())
					{
						$this->controller->session->set_userdata('CC_SUCCESS', TRUE);
						redirect(site_url('sales/complete'));
					}
					else //Change payment type to Partial Credit Card and show sales interface
					{
						$invoice_number = substr((date('mdy')).(time() - strtotime("today")).($this->controller->Employee->get_logged_in_employee_info()->person_id), 0, 16);
					
						$credit_card_amount = to_currency_no_money($this->controller->cart->get_payment_amount(lang('common_credit')));
					
						$partial_transaction = array(
							'AuthCode' => $auth_code,
							'Frequency' => 'OneTime',
							'Memo' => 'PHP POS HC '.APPLICATION_VERSION,
							'Invoice' => $invoice_number,
							'MerchantID' => $this->controller->Location->get_info_for_key('hosted_checkout_merchant_id'),
							'OperatorID' => (!defined("ENVIRONMENT") or ENVIRONMENT == 'development') ? 'test' : $this->controller->Employee->get_logged_in_employee_info()->person_id,
							'PurchaseAmount' => $credit_card_amount,
							'RefNo' => $ref_no,
							'Token' => $token,
							'AcqRefData' =>$acq_ref_data,
							'ProcessData' => $process_data,
						);
															
						$this->controller->cart->delete_payment($this->controller->cart->get_payment_ids(lang('common_credit')));
						$this->controller->cart->add_payment(new PHPPOSCartPaymentSale(array(
							'payment_type' => lang('sales_partial_credit'),
							'payment_amount' => $credit_card_amount,
							'payment_date' => date('Y-m-d H:i:s'),
							'truncated_card' => $masked_account,
							'card_issuer' => $card_issuer,
							'auth_code' => $auth_code,
							'ref_no' => $ref_no,
							'cc_token' => $token,
							'acq_ref_data' => $acq_ref_data,
							'process_date' => $process_data,
							'tran_type' => $tran_type,
						)));
												
						$this->controller->cart->add_partial_transaction($partial_transaction);
						$this->controller->cart->save();
						$this->controller->_reload(array('warning' => lang('sales_credit_card_partially_charged_please_complete_sale_with_another_payment_method')), false);			
					}
				}
				elseif($response_code == 0 && $auth_amount < $total_amount)
				{
					$invoice_number = substr((date('mdy')).(time() - strtotime("today")).($this->controller->Employee->get_logged_in_employee_info()->person_id), 0, 16);
				
					$partial_transaction = array(
						'AuthCode' => $auth_code,
						'Frequency' => 'OneTime',
						'Memo' => 'PHP POS HC '.APPLICATION_VERSION,
						'Invoice' => $invoice_number,
						'MerchantID' => $this->controller->Location->get_info_for_key('hosted_checkout_merchant_id'),
						'OperatorID' => (!defined("ENVIRONMENT") or ENVIRONMENT == 'development') ? 'test' : $this->controller->Employee->get_logged_in_employee_info()->person_id,
						'PurchaseAmount' => $auth_amount,
						'RefNo' => $ref_no,
						'Token' => $token,
						'AcqRefData' =>$acq_ref_data,
						'ProcessData' => $process_data,
					);
				
					$this->controller->cart->delete_payment($this->controller->cart->get_payment_ids(lang('common_credit')));				
					$this->controller->cart->add_payment(new PHPPOSCartPaymentSale(array(
						'payment_type' => lang('sales_partial_credit'),
						'payment_amount' => $auth_amount,
						'payment_date' => date('Y-m-d H:i:s'),
						'truncated_card' => $masked_account,
						'card_issuer' => $card_issuer,
						'auth_code' => $auth_code,
						'ref_no' => $ref_no,
						'cc_token' => $token,
						'acq_ref_data' => $acq_ref_data,
						'process_date' => $process_data,
						'tran_type' => $tran_type,
					)));
					$this->controller->cart->add_partial_transaction($partial_transaction);
					$this->controller->cart->save();
					$this->controller->_reload(array('warning' => lang('sales_credit_card_partially_charged_please_complete_sale_with_another_payment_method')), false);
				}
				else
				{
					$this->controller->_reload(array('error' => lang('sales_acknowledge_payment_failed_please_contact_support')), false);
				}
			}
			else
			{
				$client->AcknowledgePayment($parameters);
				if ($status == 'Declined')
				{
					redirect(site_url('sales/declined'));
				}
				else
				{
					$this->controller->_reload(array('error' => $result->VerifyPaymentResult->StatusMessage.': '.$result->VerifyPaymentResult->DisplayMessage), false);
				}
			}
		}
		catch (SoapFault $e) 
		{
			$this->controller->_reload(array('error' => lang('sales_charging_card_failed_please_try_again')), false);
		}
	}
	public function cancel_cc_processing()
	{
		$this->controller->cart->delete_payment($this->controller->cart->get_payment_ids(lang('common_credit')));
		$this->controller->cart->save();
		$this->controller->_reload(array('error' => lang('sales_cc_processing_cancelled')), false);
	}
	
	public function void_partial_transactions()
	{
		$void_success = true;
		
		if ($partial_transactions = $this->controller->cart->get_partial_transactions())
		{
			$service_url = (!defined("ENVIRONMENT") or ENVIRONMENT == 'development') ? 'https://hc.mercurycert.net/tws/transactionservice.asmx?WSDL': 'https://hc.mercurypay.com/tws/transactionservice.asmx?WSDL';
			foreach($partial_transactions as $partial_transaction)
			{
				$void_success = $this->void_sale_payment($partial_transaction['PurchaseAmount'], $partial_transaction['AuthCode'], $partial_transaction['RefNo'], $partial_transaction['Token'], $partial_transaction['AcqRefData'], $partial_transaction['ProcessData']);
			}
		}
		
		return $void_success;
	}	

	private function void_return_payment($payment_amount,$auth_code,$ref_no,$token,$acq_ref_data, $process_data)
	{
		$void_success = true;
		
		$service_url = (!defined("ENVIRONMENT") or ENVIRONMENT == 'development') ? 'https://hc.mercurycert.net/tws/transactionservice.asmx?WSDL': 'https://hc.mercurypay.com/tws/transactionservice.asmx?WSDL';
		
		$invoice_number = substr((date('mdy')).(time() - strtotime("today")).($this->controller->Employee->get_logged_in_employee_info()->person_id), 0, 16);
		
		$transaction = array(
			'AuthCode' => $auth_code,
			'Frequency' => 'OneTime',
			'Memo' => 'PHP POS HC '.APPLICATION_VERSION,
			'Invoice' => $invoice_number,
			'MerchantID' => $this->controller->Location->get_info_for_key('hosted_checkout_merchant_id'),
			'OperatorID' => (!defined("ENVIRONMENT") or ENVIRONMENT == 'development') ? 'test' : $this->controller->Employee->get_logged_in_employee_info()->person_id,
			'PurchaseAmount' => to_currency_no_money(abs($payment_amount)),
			'RefNo' => $ref_no,
			'Token' => $token
		);
		
		$parameters = array(
			'request' => $transaction,
			'password' => $this->controller->Location->get_info_for_key('hosted_checkout_merchant_password'),
		);
		
		try
		{
			$client = new SoapClient($service_url,array('trace' => TRUE));
			$result = $client->CreditVoidReturnToken($parameters);
		
			$status = $result->CreditVoidReturnTokenResult->Status;
			if ($status != 'Approved')
			{
				$void_success = false;
			}	
		
			return $void_success;	
		}
		catch (SoapFault $e) 
		{
			return false;
		}
	}	
	
	private function void_sale_payment($payment_amount,$auth_code,$ref_no,$token,$acq_ref_data, $process_data,$tip_amount=0)
	{
		$void_success = true;
		
		$service_url = (!defined("ENVIRONMENT") or ENVIRONMENT == 'development') ? 'https://hc.mercurycert.net/tws/transactionservice.asmx?WSDL': 'https://hc.mercurypay.com/tws/transactionservice.asmx?WSDL';
		
		$invoice_number = substr((date('mdy')).(time() - strtotime("today")).($this->controller->Employee->get_logged_in_employee_info()->person_id), 0, 16);
		
		$transaction = array(
			'AuthCode' => $auth_code,
			'Frequency' => 'OneTime',
			'Memo' => 'PHP POS HC '.APPLICATION_VERSION,
			'Invoice' => $invoice_number,
			'MerchantID' => $this->controller->Location->get_info_for_key('hosted_checkout_merchant_id'),
			'OperatorID' => (!defined("ENVIRONMENT") or ENVIRONMENT == 'development') ? 'test' : $this->controller->Employee->get_logged_in_employee_info()->person_id,
			'PurchaseAmount' => to_currency_no_money($payment_amount),
			'GratuityAmount' => to_currency_no_money($tip_amount),
			'RefNo' => $ref_no,
			'Token' => $token,
			'AcqRefData' =>$acq_ref_data,
			'ProcessData' => $process_data,
		);
		
		$parameters = array(
			'request' => $transaction,
			'password' => $this->controller->Location->get_info_for_key('hosted_checkout_merchant_password'),
		);
		
		try
		{
			$client = new SoapClient($service_url,array('trace' => TRUE));
			$result = $client->CreditReversalToken($parameters);
		
			$status = $result->CreditReversalTokenResult->Status;
			if ($status != 'Approved')
			{
				unset($parameters['AcqRefData']);
				unset($parameters['ProcessData']);
				$result = $client->CreditVoidSaleToken($parameters);
				$status = $result->CreditVoidSaleTokenResult->Status;
			
				if ($status != 'Approved')
				{
					$void_success = false;
				}
			}	
		
			return $void_success;	
		}
		catch (SoapFault $e)
		{
			return false;
		}
	}
	
	public function void_sale($sale_id)
	{
		if ($this->controller->Sale->can_void_cc_sale($sale_id))
		{
			$void_success = true;
			
			$payments = $this->_get_cc_payments_for_sale($sale_id);
			
			$counter = 0;
			
			foreach($payments as $payment)
			{
				if ($counter == 0)
				{
					$sale_info = $this->controller->Sale->get_info($sale_id)->row();
					$tip = $sale_info->tip;
				}
				else
				{
					$tip = 0;
				}
				
				$void_success = $this->void_sale_payment($payment['payment_amount'], $payment['auth_code'], $payment['ref_no'], $payment['cc_token'], $payment['acq_ref_data'], $payment['process_data'],$tip);
				
				$counter++;
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
				$void_success = $this->void_return_payment($payment['payment_amount'], $payment['auth_code'], $payment['ref_no'], $payment['cc_token'], $payment['acq_ref_data'], $payment['process_data']);
			}
			
			return $void_success;
		}
		
		return FALSE;
	}
	
	public function tip($sale_id,$tip_amount)
	{
		$payments = $this->_get_cc_payments_for_sale($sale_id);
		
		$payment_to_add_tip_to = $payments[0];
		
		if($payment_to_add_tip_to)
		{
			$auth_code = $payment_to_add_tip_to['auth_code'];
			$ref_no  = $payment_to_add_tip_to['ref_no'];
			$acq_ref_data = $payment_to_add_tip_to['acq_ref_data'];
			$process_data = $payment_to_add_tip_to['process_data'];
			$payment_amount = $payment_to_add_tip_to['payment_amount'];
			$token = $payment_to_add_tip_to['cc_token'];
			
			$invoice_number = substr((date('mdy')).(time() - strtotime("today")).($this->controller->Employee->get_logged_in_employee_info()->person_id), 0, 16);
			
			$transaction = array(
				'AuthCode' => $auth_code,
				'Frequency' => 'OneTime',
				'Memo' => 'PHP POS HC '.APPLICATION_VERSION,
				'Invoice' => $invoice_number,
				'MerchantID' => $this->controller->Location->get_info_for_key('hosted_checkout_merchant_id'),
				'OperatorID' => (!defined("ENVIRONMENT") or ENVIRONMENT == 'development') ? 'test' : $this->controller->Employee->get_logged_in_employee_info()->person_id,
				'PurchaseAmount' => to_currency_no_money($payment_amount),
				'GratuityAmount' => to_currency_no_money($tip_amount),
				'RefNo' => $ref_no,
				'Token' => $token,
				'AcqRefData' =>$acq_ref_data,
				'ProcessData' => $process_data,
			);
		
			$parameters = array(
				'request' => $transaction,
				'password' => $this->controller->Location->get_info_for_key('hosted_checkout_merchant_password'),
			);
		
			try
			{
				$service_url = (!defined("ENVIRONMENT") or ENVIRONMENT == 'development') ? 'https://hc.mercurycert.net/tws/transactionservice.asmx?WSDL': 'https://hc.mercurypay.com/tws/transactionservice.asmx?WSDL';
				
				$client = new SoapClient($service_url,array('trace' => TRUE));
				$result = $client->CreditAdjustToken($parameters);
				$status = $result->CreditAdjustTokenResult->Status;
				if ($status == 'Approved')
				{
					echo "<script>show_feedback('success',".json_encode(lang('sales_tip_added_successfully')).','.json_encode(lang('sales_tip_added_successfully')).');</script>';			
					
					return true;
				}
				
				return $status;
			}
			catch (SoapFault $e)
			{
				return false;
			}
		}
		
		return FALSE;
	}
	
	
}