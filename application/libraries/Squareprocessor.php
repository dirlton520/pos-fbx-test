<?php
require_once ("Creditcardprocessor.php");

class Squareprocessor extends Creditcardprocessor
{
	private $callback_url;
	
	function __construct($controller)
	{
		parent::__construct($controller);	
	}
	
	public function start_cc_processing()
	{
		$cc_amount = to_currency_no_money($this->controller->cart->get_payment_amount(lang('common_credit')));
		
		//Square requires 1.00 minimum
		if ($cc_amount < 1)
		{
			$this->controller->_reload(array('error' => lang('sales_charging_card_failed_please_try_again')), false);
			return;
		}

		$this->controller->load->view('sales/square',				
		array(
		'square_location_id' => $this->controller->Location->get_info_for_key('square_location_id'),
		'amount' => to_currency_no_money($cc_amount*$this->controller->Location->get_info_for_key('square_currency_multiplier')),
		'notes' => $this->controller->cart->comment,
		'currency' => $this->controller->Location->get_info_for_key('square_currency_code') ? $this->controller->Location->get_info_for_key('square_currency_code') : 'USD',
	));
		
	}
	
	public function finish_cc_processing()
	{
		if ($this->controller->_payments_cover_total())
		{
			$this->controller->session->set_userdata('CC_SUCCESS', TRUE);
			$this->controller->session->set_userdata('ref_no', $this->controller->input->get('transactionID'));
			$this->controller->session->set_userdata('masked_account', 'XXXX');
			$this->controller->session->set_userdata('card_issuer', lang('common_credit'));
			
			redirect(site_url('sales/complete'));
		}
		else //Change payment type to Partial Credit Card and show sales interface
		{
			$credit_card_amount = to_currency_no_money($this->controller->cart->get_payment_amount(lang('common_credit')));

			$partial_transaction = array(
				'charge_id' => $this->controller->input->get('transactionID'),
			);
								
			$this->controller->cart->delete_payment($this->controller->cart->get_payment_ids(lang('common_credit')));
			$this->controller->cart->add_payment(new PHPPOSCartPaymentSale(array(
				'payment_type' => lang('sales_partial_credit'),
				'payment_amount' => $credit_card_amount,
				'payment_date' => date('Y-m-d H:i:s'),
				'ref_no' => $this->controller->input->get('transactionID'),
				'truncated_card' => 'XXXX',
				'card_issuer' => lang('sales_partial_credit'),
			)));
			
			$this->controller->cart->add_partial_transaction($partial_transaction);
			$this->controller->cart->save();
			$this->controller->_reload(array('warning' => lang('sales_credit_card_partially_charged_please_complete_sale_with_another_payment_method')), false);			
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
		return true;		
	}
	public function void_sale($sale_id)
	{
		return false;
	}
	public function void_return($sale_id)
	{
		return false;
	}
	
	public function tip($sale_id,$tip_amount)
	{
		return FALSE;
	}
}