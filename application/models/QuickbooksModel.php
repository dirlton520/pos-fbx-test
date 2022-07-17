<?php
class QuickbooksModel extends MY_Model
{
	function test()
	{
		return false;
	}
	
	function getEndOfDay($date = NULL)
	{
		if ($date == NULL)
		{
			$date = date("Y-m-d");
		}
		
		
		$return = array();
		
		$this->load->model('Item');
		$giftcard_item_id = $this->Item->get_item_id(lang('common_giftcard'));
		
		foreach($this->Location->get_all()->result_array() as $location)
		{
			$sales_items_table = $this->db->dbprefix('sales_items');
			
			$this->db->select("sum($sales_items_table.subtotal) as giftcard_sales", false);
			$this->db->from('sales');
			$this->db->join('sales_items','sales_items.sale_id = sales.sale_id');
			$this->db->where('sales.deleted', 0);
			$this->db->where('sales_items.item_id', $giftcard_item_id);
			$this->db->where('sales.store_account_payment', 0);
			$this->db->where('sales.suspended',0);
			$this->db->where('sales.total_quantity_purchased > 0');
			
			$this->db->where('sales.sale_time BETWEEN '.$this->db->escape($date).' and '.$this->db->escape($date.' 23:59:59'));
			$this->db->where('sales.location_id',$location['location_id']);
			$giftcard_sales_row = $this->db->get()->row_array();					
			
			$this->db->select('sum(subtotal) as gross, sum(subtotal - profit) as cogs', false);
			$this->db->from('sales');
			$this->db->where('deleted', 0);
			$this->db->where('store_account_payment', 0);
			$this->db->where('sales.suspended',0);
			$this->db->where('sales.total_quantity_purchased > 0');
			
			$this->db->where('sale_time BETWEEN '.$this->db->escape($date).' and '.$this->db->escape($date.' 23:59:59'));
			$this->db->where('sales.location_id',$location['location_id']);
			$sales_row = $this->db->get()->row_array();	
			
			$this->db->select('sum(total) as total', false);
			$this->db->from('sales');
			$this->db->where('deleted', 0);
			$this->db->where('store_account_payment', 1);
			$this->db->where('sales.suspended',0);
			
			$this->db->where('sale_time BETWEEN '.$this->db->escape($date).' and '.$this->db->escape($date.' 23:59:59'));
			$this->db->where('sales.location_id',$location['location_id']);
			$store_account_payments = $this->db->get()->row_array();	
			
		
			$total_gross_sales = $sales_row['gross']  - $giftcard_sales_row['giftcard_sales'];
			$total_cogs = $sales_row['cogs'];
		
			$this->db->select('sum(subtotal) as gross, sum(subtotal - profit) as cogs', false);
			$this->db->from('sales');
			$this->db->where('deleted', 0);
			$this->db->where('store_account_payment', 0);
			$this->db->where('sales.suspended',0);
			$this->db->where('sales.total_quantity_purchased < 0');
			$this->db->where('sale_time BETWEEN '.$this->db->escape($date).' and '.$this->db->escape($date.' 23:59:59'));		
			$this->db->where('sales.location_id',$location['location_id']);
			$returns_row = $this->db->get()->row_array();		
		
			$total_gross_returns = $returns_row['gross'];
		
			$payments_summary = array();
		
			$sale_ids_for_payments = $this->get_sale_ids_for_payments($date);
		
			$sales_totals = array();
		
			$this->db->select('sale_id, SUM(total) as total', false);
			$this->db->from('sales');
			$this->db->where('sales.location_id',$location['location_id']);
			$this->db->where('sales.total_quantity_purchased > 0');
		
			if (count($sale_ids_for_payments))
			{
				$this->db->group_start();
				$sale_ids_chunk = array_chunk($sale_ids_for_payments,25);
				foreach($sale_ids_chunk as $sale_ids)
				{
					$this->db->or_where_in('sale_id',$sale_ids);
				}
				$this->db->group_end();
			}
			else
			{
				$this->db->where('1=2');
			}
		
			
			$this->db->where('deleted', 0);
			$this->db->group_by('sale_id');
			foreach($this->db->get()->result_array() as $sale_total_row)
			{
				$sales_totals[$sale_total_row['sale_id']] = to_currency_no_money($sale_total_row['total'], 2);
			}
			$this->db->select('sales_payments.sale_id, sales_payments.payment_type, payment_amount, payment_id', false);
			$this->db->from('sales_payments');
			$this->db->join('sales', 'sales.sale_id=sales_payments.sale_id');			
			if (count($sale_ids_for_payments))
			{
				$this->db->group_start();
				$sale_ids_chunk = array_chunk($sale_ids_for_payments,25);
				foreach($sale_ids_chunk as $sale_ids)
				{
					$this->db->or_where_in('sales_payments.sale_id',$sale_ids);
				}
				$this->db->group_end();
			}
			else
			{
				$this->db->where('1=2');
			}
			
				
			$this->db->where('sales.location_id',$location['location_id']);
			$this->db->where('sales.total_quantity_purchased > 0');
			$this->db->where('store_account_payment',0);
				
			$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
			$this->db->order_by('sale_id, payment_date, payment_type');
				
			$sales_payments = $this->db->get()->result_array();
		
			$payments_by_sale = array();
			foreach($sales_payments as $row)
			{
	        $payments_by_sale[$row['sale_id']][] = $row;
			}
		
			$this->load->model('Sale');
			$payments = $this->Sale->get_payment_data($payments_by_sale,$sales_totals);
			foreach($payments as $payment_type => $payment)
			{
				if (strpos($payment_type,lang('common_giftcard')) !== FALSE)
				{
					$payment_type = 'Gift Card';
				}
			
				if ($payment_type == lang('common_store_account'))
				{
					$payment_type = 'Store Account';
				}
			
				if ($payment_type == 'Gift Card')
				{
					if (!isset($payments_summary['sales']['Gift Card']))
					{
						$payments_summary['sales']['Gift Card'] = 0;
					}
				
					$payments_summary['sales']['Gift Card']+= to_currency_no_money($payment['payment_amount'],2);
				}
				else
				{
					$payments_summary['sales'][$payment_type] = to_currency_no_money($payment['payment_amount'],2);
				}
			}
			
			$sale_ids_for_payments = $this->get_sale_ids_for_payments($date);
		
			$sales_totals = array();
		
			$this->db->select('sale_id, SUM(total) as total', false);
			$this->db->from('sales');
			$this->db->where('sales.location_id',$location['location_id']);
			$this->db->where('sales.total_quantity_purchased < 0');
		
			if (count($sale_ids_for_payments))
			{
				$this->db->group_start();
				$sale_ids_chunk = array_chunk($sale_ids_for_payments,25);
				foreach($sale_ids_chunk as $sale_ids)
				{
					$this->db->or_where_in('sale_id',$sale_ids);
				}
				$this->db->group_end();
			}
			else
			{
				$this->db->where('1=2');
			}
						
			$this->db->where('deleted', 0);
			$this->db->group_by('sale_id');
			foreach($this->db->get()->result_array() as $sale_total_row)
			{
				$sales_totals[$sale_total_row['sale_id']] = to_currency_no_money($sale_total_row['total'], 2);
			}
			$this->db->select('sales_payments.sale_id, sales_payments.payment_type, payment_amount, payment_id', false);
			$this->db->from('sales_payments');
			$this->db->join('sales', 'sales.sale_id=sales_payments.sale_id');
			if (count($sale_ids_for_payments))
			{
				$this->db->group_start();
				$sale_ids_chunk = array_chunk($sale_ids_for_payments,25);
				foreach($sale_ids_chunk as $sale_ids)
				{
					$this->db->or_where_in('sales_payments.sale_id',$sale_ids);
				}
				$this->db->group_end();
			}
			else
			{
				$this->db->where('1=2');
			}
			
			$this->db->where('sales.location_id',$location['location_id']);
			$this->db->where('sales.total_quantity_purchased < 0');
			$this->db->where('store_account_payment',0);
				
			$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
			$this->db->order_by('sale_id, payment_date, payment_type');
				
			$sales_payments = $this->db->get()->result_array();
		
			$payments_by_sale = array();
			foreach($sales_payments as $row)
			{
	        $payments_by_sale[$row['sale_id']][] = $row;
			}
		
			$this->load->model('Sale');
			$payments = $this->Sale->get_payment_data($payments_by_sale,$sales_totals);
			foreach($payments as $payment_type => $payment)
			{
				if (strpos($payment_type,lang('common_giftcard')) !== FALSE)
				{
					$payment_type = 'Gift Card';
				}
			
				if ($payment_type == lang('common_store_account'))
				{
					$payment_type = 'Store Account';
				}
			
				if ($payment_type == 'Gift Card')
				{
					if (!isset($payments_summary['returns']['Gift Card']))
					{
						$payments_summary['returns']['Gift Card'] = 0;
					}
				
					$payments_summary['returns']['Gift Card']+= to_currency_no_money($payment['payment_amount'],2);
				}
				else
				{
					$payments_summary['returns'][$payment_type] = to_currency_no_money($payment['payment_amount'],2);
				}
			}
			
			
			$this->load->model('reports/Summary_taxes');
		
		
			$this->Summary_taxes->setParams(array('override_location_id' => $location['location_id'], 'start_date'=>$date, 'end_date'=>$date.' 23:59:59','sale_type' => 'sales'));
			$taxes = array();
		
			foreach($this->Summary_taxes->getData() as $tax_row)
			{
				if ($tax_row['name'] != lang('reports_non_taxable'))
				{
					$taxes[$tax_row['name']] = array('tax' => to_currency_no_money($tax_row['tax'],2), 'subtotal' => to_currency_no_money($tax_row['subtotal'],2), 'total' => to_currency_no_money($tax_row['total'],2));		
				}
			}
								
			$return[$location['location_id']]['total_gross_sales'] = to_currency_no_money($total_gross_sales,2);
				
			$return[$location['location_id']]['refunds'] = to_currency_no_money($total_gross_returns,2);
		
			$return[$location['location_id']]['payments'] = $payments_summary;
		
		
			$return[$location['location_id']]['cogs'] = to_currency_no_money($total_cogs,2);
			$return[$location['location_id']]['gift_card_item'] = to_currency_no_money($giftcard_sales_row['giftcard_sales'],2);
			$return[$location['location_id']]['house_account_item'] = to_currency_no_money($store_account_payments['total'],2);
		
			
			$total_tax_amount = 0;
			foreach($taxes as $key=>$value)
			{
					$total_tax_amount+=$value['tax'];
			}
			
			$return[$location['location_id']]['taxes'] = $taxes;
		
			$this->load->model('reports/Detailed_register_log');
		
			$this->Detailed_register_log->setParams(array('override_location_id' => $location['location_id'], 'start_date'=>$date, 'end_date'=>$date.' 23:59:59'));
			$register_log_data = $this->Detailed_register_log->getSummaryData();
		
			$return[$location['location_id']]['amount_over'] = to_currency_no_money(abs($register_log_data['total_overages']),2);
			$return[$location['location_id']]['amount_short'] = to_currency_no_money(abs($register_log_data['total_shortages']),2);
		}
	
		return $return;
		
	}
		
	function get_sale_ids_for_payments($date)
	{
		$sale_ids = array();
		
		$this->db->select('sales.sale_id');
		$this->db->from('sales');
		$this->db->where('sale_time BETWEEN '.$this->db->escape($date).' and '.$this->db->escape($date.' 23:59:59'));		

		foreach($this->db->get()->result_array() as $sale_row)
		{
			 $sale_ids[] = $sale_row['sale_id'];
		}
		
		return $sale_ids;
	}
	
	
	function get_sync_progress()
	{
		return array('percent_complete' => $this->config->item('qb_sync_percent_complete'), 'message'=> $this->config->item('qb_sync_message'));
	}

	//TODO MAKE SURE WE RESET ALL FIELDS
	function reset_qb()
	{
		$this->Appconfig->delete('quickbooks_access_token');
		$this->Appconfig->delete('quickbooks_refresh_token');
		$this->Appconfig->delete('quickbooks_realm_id');
		$this->Appconfig->delete('qb_export_date');
		$this->Appconfig->delete('qb_journal_entry_records');
		$this->Appconfig->delete('qb_classes');
		$this->Appconfig->delete('qb_export_start_date');
	}
}
?>