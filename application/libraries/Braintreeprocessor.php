<?php
require_once ("Creditcardprocessor.php");
class Braintreeprocessor extends Creditcardprocessor
{
	function __construct($controller)
	{
		parent::__construct($controller);
		require_once 'braintree/lib/Braintree.php';
		
		$this->gateway = new Braintree\Gateway([
		    'environment' => (!defined("ENVIRONMENT") or ENVIRONMENT == 'development') ? 'sandbox' : 'production',
		    'merchantId' => $this->controller->Location->get_info_for_key('braintree_merchant_id'),
		    'publicKey' => $this->controller->Location->get_info_for_key('braintree_public_key'),
		    'privateKey' => $this->controller->Location->get_info_for_key('braintree_private_key'),
		]);
		
		
	}	
	
	public function start_cc_processing()
	{
		$data = array();
		$cc_amount = $this->controller->cart->get_payment_amount(lang('common_credit'));
		
		if ($cc_amount <=0)
		{
			$this->controller->cart->delete_payment($this->controller->cart->get_payment_ids(lang('common_credit')));
			$this->controller->cart->save();
			$this->controller->_reload(array('error' => lang('sales_cannot_process_sales_less_than_0')), false);
			return;
		}
		if(!$this->controller->cart->use_cc_saved_info)
		{
			$data['cc_amount'] = to_currency($cc_amount);
			
			try
			{
				$data['braintree_clent_token'] = $this->gateway->clientToken()->generate();
				$this->controller->load->view('sales/braintree_checkout', $data);			
			}
			catch(Exception $e)
			{
				$this->controller->_reload(array('error' => lang('sales_credit_card_processing_is_down')), false);
			}
		}
		else
		{
		  	try 
		  	{
				$charge_amount = to_currency_no_money($this->controller->cart->get_payment_amount(lang('common_credit')));
			
				$customer_id = $this->controller->cart->customer_id;
				$customer_info=$this->controller->Customer->get_info($customer_id);
			
				$charge_parameters = array(
		  			"amount" => $charge_amount,
				  	'paymentMethodToken' => $customer_info->cc_token,
				);
			
				$charge_parameters['options']['submitForSettlement'] = TRUE;				
			
				$charge = $this->gateway->transaction()->sale($charge_parameters);
			
				if (!$charge->success)
				{
					throw new Exception('Failed transaction');
				}
				
				$charge_id = $charge->transaction->id;
				$masked_account = $charge->transaction->creditCardDetails->last4;
				$card_brand = $charge->transaction->creditCardDetails->cardType;
				$this->controller->session->set_userdata('ref_no', $charge_id);
				$this->controller->session->set_userdata('masked_account', $masked_account);
				$this->controller->session->set_userdata('card_issuer', $card_brand);
			
				if ($this->controller->_payments_cover_total())
				{
					$this->controller->session->set_userdata('CC_SUCCESS', TRUE);
					redirect(site_url('sales/complete'));
				}
				else //Change payment type to Partial Credit Card and show sales interface
				{
					$credit_card_amount = to_currency_no_money($this->controller->cart->get_payment_amount(lang('common_credit')));

					$partial_transaction = array(
						'charge_id' => $charge_id,
					);
									
					$this->controller->cart->delete_payment($this->controller->cart->get_payment_ids(lang('common_credit')));												
					$this->controller->cart->add_payment(new PHPPOSCartPaymentSale(array(
						'payment_type' => lang('sales_partial_credit'),
						'payment_amount' => $credit_card_amount,
						'payment_date' => date('Y-m-d H:i:s'),
						'truncated_card' => $masked_account,
						'card_issuer' => $card_brand,
						'ref_no' => $charge_id,
					)));
					
					$this->controller->cart->add_partial_transaction($partial_transaction);
					$this->controller->cart->save();
					$this->controller->_reload(array('warning' => lang('sales_credit_card_partially_charged_please_complete_sale_with_another_payment_method')), false);			
					return;
				}
				
			}
		  	catch (Exception $e)
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
				
				
				$this->controller->cart->delete_payment($this->controller->cart->get_payment_ids(lang('common_credit')));
				$this->controller->cart->save();
				$this->controller->_reload(array('error' => $charge->message), false);
				return;
		  	}
		}		
	}
	public function finish_cc_processing()
	{
		$payment_method_nonce = $this->controller->input->post('payment_method_nonce');
		
		if (!$payment_method_nonce)
		{
			$this->controller->cart->delete_payment($this->controller->cart->get_payment_ids(lang('common_credit')));
			$this->controller->cart->save();
			$this->controller->_reload(array('error' => lang('sales_unknown_card_error')), false);
			return;
		}
		
		$charge_amount= to_currency_no_money($this->controller->cart->get_payment_amount(lang('common_credit')));
		
	  	// Get the credit card details submitted by the form
	  	// Create the charge on Stripe's servers - this will charge the user's card
	  	try 
	  	{
			$charge_parameters = array(
	  			"amount" => $charge_amount,
			  	'paymentMethodNonce' => $payment_method_nonce,
			);
			
			$customer_info=$this->controller->Customer->get_info($this->controller->cart->customer_id);
			
			if ($this->controller->cart->customer_id)
			{
		  	 $charge_parameters['customer'] = array(
		      'firstName' => $customer_info->first_name,
		      'lastName' => $customer_info->last_name,
		      'company' => $customer_info->company_name,
		      'phone' => $customer_info->phone_number,
		      'email' => $customer_info->email
				);
			}
			
			//We want to save/update card when we have a customer AND they have chosen to save
			if (($this->controller->cart->save_credit_card_info) && $this->controller->cart->customer_id)
			{
				$charge_parameters['options']['storeInVaultOnSuccess'] = TRUE;				
			}

			$charge_parameters['options']['submitForSettlement'] = TRUE;				
			
			$charge = $this->gateway->transaction()->sale($charge_parameters);
			
			if (!$charge->success)
			{
				throw new Exception('Failed transaction');
			}
			
			$charge_id = $charge->transaction->id;
			$masked_account = $charge->transaction->creditCardDetails->last4;
			$card_brand = $charge->transaction->creditCardDetails->cardType;
			$this->controller->session->set_userdata('ref_no', $charge_id);
			$this->controller->session->set_userdata('masked_account', $masked_account);
			$this->controller->session->set_userdata('card_issuer', $card_brand);
			
			//We want to save/update card when we have a customer AND they have chosen to save
			if (($this->controller->cart->save_credit_card_info) && $this->controller->cart->customer_id)
			{
				$cc_token = $charge->transaction->creditCardDetails->token;
				
				$person_info = array('person_id' => $this->controller->cart->customer_id);
				$customer_info = array('cc_token' => $cc_token, 'cc_preview' => $masked_account, 'card_issuer' => $card_brand);
				$this->controller->Customer->save_customer($person_info,$customer_info,$this->controller->cart->customer_id);
			}
			
			if ($this->controller->_payments_cover_total())
			{
				$this->controller->session->set_userdata('CC_SUCCESS', TRUE);
				redirect(site_url('sales/complete'));
			}
			else //Change payment type to Partial Credit Card and show sales interface
			{
				$credit_card_amount = to_currency_no_money($this->controller->cart->get_payment_amount(lang('common_credit')));

				$partial_transaction = array(
					'charge_id' => $charge_id,
				);
									
				$this->controller->cart->delete_payment($this->controller->cart->get_payment_ids(lang('common_credit')));
				$this->controller->cart->add_payment(new PHPPOSCartPaymentSale(array(
					'payment_type' => lang('sales_partial_credit'),
					'payment_amount' => $credit_card_amount,
					'payment_date' => date('Y-m-d H:i:s'),
					'truncated_card' => $masked_account,
					'card_issuer' => $card_brand,
					'ref_no' => $charge_id,
				)));
				$this->controller->cart->add_partial_transaction($partial_transaction);
				$this->controller->cart->save();
				$this->controller->_reload(array('warning' => lang('sales_credit_card_partially_charged_please_complete_sale_with_another_payment_method')), false);			
				return;
			}
			
			
	  	} 
	  	catch(Exception $e) 
	  	{
			if ($this->controller->cart->customer_id)
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
			}		
		
			$this->controller->cart->delete_payment($this->controller->cart->get_payment_ids(lang('common_credit')));
			$this->controller->cart->save();
			$this->controller->_reload(array('error' => $charge->message), false);
			return;		
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
				
		$partial_transactions = $this->controller->cart->get_partial_transactions() ;
		
		if ($partial_transactions)
		{
			foreach($partial_transactions as $transaction)
			{
				$charge_id = $transaction['charge_id'];
			
				try
				{
					$void_attempt = $this->gateway->transaction()->void($charge_id);
					
					//Try to refund
					if (!$void_attempt->success)
					{
						$refund_attempt = $this->gateway->transaction()->refund($charge_id);
						
						if (!$refund_attempt->success)
						{
							throw new Exception('Cannot void/refund');
						}						
					}
				}
			  	catch (Exception $e)
				{
					$void_success = false;
				}
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
				try
				{
					$charge_id = $payment['ref_no'];
					$void_attempt = $this->gateway->transaction()->void($charge_id);
					
					//Try to refund
					if (!$void_attempt->success)
					{
						$refund_attempt = $this->gateway->transaction()->refund($charge_id);
						
						if (!$refund_attempt->success)
						{
							throw new Exception('Cannot void/refund');
						}						
					}
				}
			  	catch (Exception $e)
				{
					$void_success = false;
				}
			}
			
			return $void_success;
		}
		
		return FALSE;
		
	}
	public function void_return($sale_id)
	{
		//Cannot do in stripe
		return FALSE;
	}
	
	public function tip($sale_id,$tip_amount)
	{
		return FALSE;
	}
}