<?php
require_once ("Creditcardprocessor.php");
require_once (APPPATH."libraries/blockchyp/vendor/autoload.php");

use \BlockChyp\BlockChyp;

class Coreclearblockchypprocessor extends Creditcardprocessor
{	
	function __construct($controller)
	{
		parent::__construct($controller);
		$this->controller->load->helper('sale');	
		
		$current_register_id = $this->controller->Employee->get_logged_in_employee_current_register_id();
		$register_info = $this->controller->Register->get_info($current_register_id);
		$this->emv_terminal_id = $register_info && property_exists($register_info,'emv_terminal_id') ? $register_info->emv_terminal_id : FALSE;
		$this->test_mode = (boolean)$this->controller->Location->get_info_for_key('blockchyp_test_mode');
		$this->is_card_not_present = !$this->emv_terminal_id;
		
		try
		{
	    	BlockChyp::setApiKey($this->controller->Location->get_info_for_key('blockchyp_api_key'));
	    	BlockChyp::setBearerToken($this->controller->Location->get_info_for_key('blockchyp_bearer_token'));
	    	BlockChyp::setSigningKey($this->controller->Location->get_info_for_key('blockchyp_signing_key'));
		}
		catch(Exception $e)
		{
			
		}
		
	}
	
	public function start_cc_processing()
	{
		//When we charge a card on file we don't want to do manual checkout
		if ($this->is_card_not_present && !$this->controller->cart->use_cc_saved_info)
		{
			$cc_amount = $this->controller->cart->get_payment_amount(lang('common_credit'));
		
			$data['cc_amount'] = to_currency($cc_amount);
			$data['amount'] = $cc_amount;
			$data['test_mode'] = $this->test_mode;
			
			$this->controller->load->view('sales/coreclear_blockchyp_manual_checkout', $data);
			
		}
		else
		{
			$this->controller->load->view('sales/coreclear_blockchyp_start_cc_processing');
		}
	}	
			
	public function do_start_cc_processing()
	{			
		$cc_amount = to_currency_no_money($this->controller->cart->get_payment_amount(lang('common_credit')));
		$ebt_amount = to_currency_no_money($this->controller->cart->get_payment_amount(lang('common_ebt')));
		$this->controller->load->helper('sale');
		$is_ebt = is_ebt_sale($this->controller->cart);
		if ($is_ebt)
		{
			$cc_amount = $ebt_amount;
		}
		
		$customer_id = $this->controller->cart->customer_id;
		
		$customer_name = '';
		if ($customer_id != -1)
		{
			$customer_info=$this->controller->Customer->get_info($customer_id);
			$customer_name = $customer_info->first_name.' '.$customer_info->last_name;
		}
				
		$prompt = $this->controller->cart->prompt_for_card;
				
		if(!$this->controller->cart->use_cc_saved_info)
		{
			if ($this->is_card_not_present)
			{
				list($cc_month,$cc_year) = explode('/',$this->controller->input->post('cc_exp_date'));
				
		        $charge_data = [
		            'pan' => $this->controller->input->post('cc_number'),
		            'expMonth' => $cc_month,
		            'expYear' => $cc_year,
					'amount' => $cc_amount,
					'test' => $this->test_mode,
					'enroll' => TRUE,
		        ];				
			}
			else
			{
				$charge_data = array(
					'test' => $this->test_mode,
					'terminalName' => $this->emv_terminal_id,
					'amount' => $cc_amount,
					'enroll' => TRUE,
					'sigFormat' => BlockChyp::SIGNATURE_FORMAT_PNG,
					'sigWidth' => 400,
				);
				
				if ($is_ebt)
				{
					$charge_data['cardType'] = BlockChyp::CARD_TYPE_EBT;
				}
				
				if ($prompt)
				{
					$charge_data['manualEntry'] = TRUE;
				}
				
				if ($this->controller->config->item('enable_tips'))
				{
					$charge_data['promptForTip'] = TRUE;
				}
				
				
				if ($this->controller->config->item('disable_signature_capture_on_terminal_for_phppos_credit_card_processing'))
				{
					$charge_data['disableSignature'] = TRUE;
				}
			}
			
		}
		elseif($customer_info->cc_token)
		{			
			$charge_data = array(
				'test' => $this->test_mode,
				'token' => $customer_info->cc_token,
				'amount' => $cc_amount,
				'enroll' => TRUE,
			);			
		}
		
		try
		{
			$charge_data['transactionRef'] = $this->_get_session_invoice_no();
			
			if ($cc_amount <=0)
			{
				$charge_data['amount'] = to_currency_no_money(abs($cc_amount));
				$response = BlockChyp::refund($charge_data);
			}
			else
			{
				$response = BlockChyp::charge($charge_data);
			}
			
			if ($response === NULL)
			{
				$status_request = [
					'test' => $this->test_mode,
				    'transactionRef' => $this->_get_session_invoice_no()
				];
			
				$response = BlockChyp::transactionStatus($status_request);
			}
			
		}
		catch(Exception $e)
		{
			
		}
						 
		$TextResponse = isset($response['error']) && $response['error']  ? $response['error'] : $response['responseDescription'];
		if ($response['success'] && $response['approved'])
		{
			if (isset($response['receiptSuggestions']))
			{
				@$CardType = $response['paymentType'];
				@$EntryMethod = $response['entryMethod'];
				@$ApplicationLabel = $response['receiptSuggestions']['applicationLabel'];

				@$AID = $response['receiptSuggestions']['aid'];
				@$TVR = $response['receiptSuggestions']['tvr'];
				@$IAD = $response['receiptSuggestions']['iad'];
				@$TSI = $response['receiptSuggestions']['tsi'];
		  	}
			else
			{
				@$EntryMethod = $prompt ? lang('sales_manual_entry') : lang('common_credit');
				@$ApplicationLabel = $customer_info->card_issuer ? $customer_info->card_issuer : $EntryMethod;
				@$CardType = $customer_info->card_issuer ? $customer_info->card_issuer : $EntryMethod;
			}
			
			//Catch all
			if (!$CardType && $customer_info->card_issuer)
			{
				$CardType = $customer_info->card_issuer;
			}
			
		   $MerchantID =  '';
		   $Signature = hex2bin($response['sigFile']);
		   $tip_amount = make_currency_no_money($response['tipAmount']);
		   $AcctNo = $response['maskedPan'];
		   $TranCode = lang('sales_card_transaction');
		   $AuthCode = $response['authCode'];
		   $RefNo = $response['transactionId'];
		   $Purchase = to_currency_no_money($cc_amount + $tip_amount);
		   
		   $Authorize = make_currency_no_money($response['authorizedAmount']);
		   
		   $RecordNo = $response['token'];
		   $CCExpire = lang('common_unknown');
			
			$this->controller->session->set_userdata('ref_no', $RefNo);
			$this->controller->session->set_userdata('tip_amount', $tip_amount);
			$this->controller->session->set_userdata('auth_code', $AuthCode);
			$this->controller->session->set_userdata('cc_token', $RecordNo);
			$this->controller->session->set_userdata('entry_method', $EntryMethod);
			$this->controller->session->set_userdata('cc_signature', $Signature);
			$this->controller->session->set_userdata('tip_amount', $tip_amount);
			
			if (isset($response['receiptSuggestions']))
			{
				$this->controller->session->set_userdata('aid', $AID);
				$this->controller->session->set_userdata('tvr', $TVR);
				$this->controller->session->set_userdata('iad', $IAD);
				$this->controller->session->set_userdata('tsi', $TSI);
			}
			
			$this->controller->session->set_userdata('application_label', $ApplicationLabel);
			$this->controller->session->set_userdata('tran_type', $TranCode);
			$this->controller->session->set_userdata('text_response', $TextResponse);
			
			
			//return amount we need negative value
			if ($Purchase < 0)
			{
				$Authorize = $Authorize*-1;
			}
			//Payment covers purchase amount
			if ($Authorize == $Purchase)
			{
				$this->controller->session->set_userdata('masked_account', $AcctNo);
				$this->controller->session->set_userdata('card_issuer', $CardType);
						
				
				//We want to save/update card when we have a customer AND they have chosen to save OR we have a customer and they are using a saved card
				if (($this->controller->cart->save_credit_card_info) && $this->controller->cart->customer_id 
				|| ($this->controller->cart->customer_id && $this->controller->cart->use_cc_saved_info))
				{
					if ($RecordNo)
					{
						$person_info = array('person_id' => $this->controller->cart->customer_id);
						$customer_info = array('cc_token' => $RecordNo, 'cc_expire' => $CCExpire, 'cc_ref_no' => $RefNo, 'cc_preview' => $AcctNo);
						$this->controller->Customer->save_customer($person_info,$customer_info,$this->controller->cart->customer_id);
					}
				}
				
				
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
				$customer_info = array('cc_token' => NULL, 'cc_ref_no' => NULL, 'cc_preview' => NULL, 'card_issuer' => NULL);
				
				if (!$this->controller->config->item('do_not_delete_saved_card_after_failure'))
				{
					$this->controller->Customer->save_customer($person_info,$customer_info,$this->controller->cart->customer_id);
				}
								
				//Clear cc token for using saved cc info
				$this->controller->cart->use_cc_saved_info = NULL;
				$this->controller->cart->save();
			}
			
			$this->controller->_reload(array('error' => $TextResponse), false);
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
		
		$void_data = array(
			'test' => $this->test_mode,
			'transactionId' => $ref_no,
		);
		
		//try void first
		try
		{
			$response = BlockChyp::void($void_data);
		
			if (!($response['success'] && $response['approved']))
			{
				$payment_amount = to_currency_no_money($payment_amount);
			
				$refund_data = array(
					'test' => $this->test_mode,
					'transactionId' => $ref_no,
				    'amount' => $payment_amount,
				);
			
				$response = BlockChyp::refund($refund_data);
			
				return $response['success'] && $response['approved'];
			}
		}
		catch(Exception $e)
		{
			
		}
		
		return TRUE;
		
	}
	
	private function void_return_payment($payment_amount,$auth_code,$ref_no,$token,$acq_ref_data,$process_data)
	{
		$void_data = array(
			'test' => $this->test_mode,
			'transactionId' => $ref_no,
		);
		
		try
		{
			//try void first
			$response = BlockChyp::void($void_data);
		
			if (!($response['success'] && $response['approved']))
			{
				$payment_amount = to_currency_no_money($payment_amount);
			
				$refund_data = array(
					'test' => $this->test_mode,
					'transactionId' => $ref_no,
				    'amount' => $payment_amount,
				);
			
				$response = BlockChyp::refund($refund_data);
			
				return $response['success'] && $response['approved'];
			}
		}
		catch(Exception $e)
		{
			
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
	
	//Not implemented on device
	public function tip($sale_id,$tip_amount)
	{
		return FALSE;
	}
	
	public function update_transaction_display($cart)
	{
		$items = array();
		
		if (count($cart->get_items()) == 0)
		{
			$this->clear_terminal();
			return;
		}
		
		foreach($cart->get_items() as $cart_item)
		{
			$item = array();
			$item['description'] = $cart_item->name;
			$item['price'] = to_currency_no_money($cart_item->unit_price - ($cart_item->unit_price*$cart_item->discount/100));
			$item['quantity'] = (float)$cart_item->quantity;
			$item['extended'] = to_currency_no_money($cart_item->unit_price*$cart_item->quantity-$cart_item->unit_price*$cart_item->quantity*$cart_item->discount/100);
			
			$total_discount = to_currency_no_money(($cart_item->unit_price*$cart_item->discount/100)*$cart_item->quantity);
			
			if ($total_discount > 0)
			{
				$item['discounts'] = [
                    [
                        'description' => lang('common_discount'),
                        'amount' => $total_discount,
                    ],
				];
			}
			$items[] = $item;
		}
		
		
		// Populate request values
		$request = [
			'test' => $this->test_mode,
			'terminalName' => $this->emv_terminal_id,
		    'transaction' => [
		        'subtotal' => to_currency_no_money($cart->get_subtotal()),
		        'tax' => to_currency_no_money($cart->get_tax_total_amount()),
		        'total' => to_currency_no_money($cart->get_total()),
		        'items' => $items,
		        ],
		];
		
		
		try
		{
			BlockChyp::newTransactionDisplay($request);
		}
		catch(Exception $e)
		{
			
		}
	}
	
	function get_transaction_history($params=array())
	{
		try
		{
			return BlockChyp::transactionHistory($params);
		}
		catch(Exception $e)
		{
			
		}
	}
	
	function get_batch_history($params=array())
	{
		try
		{
			return BlockChyp::batchHistory($params);
		}
		catch(Exception $e)
		{
			
		}
	}
	
	function get_batch_details($params=array())
	{
		try
		{
			return BlockChyp::batchDetails($params);
		}
		catch(Exception $e)
		{
			
		}
	}
	
	function void_return_transaction_by_id($transaction_id,$amount = NULL)
	{
		$void_data = array(
			'test' => $this->test_mode,
			'transactionId' => $transaction_id,
		);
		
		//try void first
		try
		{
			if ($amount === NULL)
			{
				$response = BlockChyp::void($void_data);
			}
			else
			{
				$response['success'] = FALSE;
			}
			
			if (!($response['success'] && $response['approved']))
			{			
				$refund_data = array(
					'test' => $this->test_mode,
					'transactionId' => $transaction_id,
				);
				
				if ($amount !== NULL)
				{
					$refund_data['amount'] = to_currency_no_money($amount);
				}
			
				$response = BlockChyp::refund($refund_data);
			
				if ($response['success'] && $response['approved'])
				{
					return $response;
				}
				
				return FALSE;
			}
			else
			{
				return $response;
			}
		}
		catch(Exception $e)
		{
			
		}
		
		return FALSE;
		
	}
	
	function clear_terminal()
	{
		try
		{
			return BlockChyp::clear(array('terminalName' => $this->emv_terminal_id));
		}
		catch(Exception $e)
		{
			
		}
	}
	
}