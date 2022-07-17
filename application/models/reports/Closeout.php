<?php
require_once ("Report.php");
class Closeout extends Report
{
	function __construct()
	{
		$this->load->helper('language');
		$this->discount_langs = get_all_language_values_for_key('common_discount','common');
		parent::__construct();
	}

	public function getInputData()
	{
		$input_data = Report::get_common_report_input_data(FALSE);
		
		$input_params = array();

		if ($this->settings['display'] == 'tabular')
		{
			$input_params = array(
				array('view' => 'date_range', 'with_time' => FALSE, 'end_date_end_of_day' => FALSE),
				array('view' => 'excel_export'),
				array('view' => 'submit'),
			);
		}
		
		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		return $input_data;
	}
	
	public function getOutputData()
	{
		$this->load->model('Category');
		$this->load->model('Sale');
		$this->load->model('Receiving');
		$open_time = $this->config->item('store_opening_time') ? $this->config->item('store_opening_time') : '00:00';
		$close_time = $this->config->item('store_closing_time') ? $this->config->item('store_closing_time') : '23:59';
		
		if(strtotime($open_time) > strtotime($close_time))
		{
			$start_date = date('Y-m-d H:i:00', strtotime(rawurldecode($this->params['start_date']).' '.$open_time));
			$end_date = date('Y-m-d H:i:59', strtotime(rawurldecode($this->params['end_date']).' '.$close_time.' + 1 Day'));
		} else {			
			$start_date = date('Y-m-d H:i:00', strtotime(rawurldecode($this->params['start_date']).' '.$open_time));
			$end_date = date('Y-m-d H:i:59', strtotime(rawurldecode($this->params['end_date']).' '.$close_time));
		}
				
		$hide_next_and_prev_days = isset($this->params['hide_next_and_prev_days']) ? $this->params['hide_next_and_prev_days'] : NULL;
				
		//This needs to be set so the payment functions get the right dates
		$this->setParams(array('hide_next_and_prev_days' => $hide_next_and_prev_days,'start_date'=>$start_date, 'end_date' => $end_date));
				
		
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		$sale_ids_for_payments = $this->get_sale_ids_for_payments();
		
		$sales_totals = array();
		
		$this->db->select('sale_id, SUM(total) as total', false);
		$this->db->from('sales');
		
		if (isset($this->params['employee_id']) && $this->params['employee_id'])
		{
			$this->db->where('employee_id', $this->params['employee_id']);
		}
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
		
			
		$this->db->where('deleted', 0);
		$this->db->group_by('sale_id');
		foreach($this->db->get()->result_array() as $sale_total_row)
		{
			$sales_totals_for_payments[$sale_total_row['sale_id']] = to_currency_no_money($sale_total_row['total'], 2);
		}
		
		
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		$recv_ids_for_payments = $this->get_receiving_ids_for_payments();

		$receivings_totals = array();
		
		$this->db->select('receiving_id, SUM(total) as total', false);
		$this->db->from('receivings');
		if (count($recv_ids_for_payments))
		{
			$this->db->group_start();
			$recv_ids_chunk = array_chunk($recv_ids_for_payments,25);
			foreach($recv_ids_chunk as $recv_ids)
			{
				$this->db->or_where_in('receiving_id',$recv_ids);
			}
			$this->db->group_end();
		}

		$this->db->where('deleted', 0);
		$this->db->group_by('receiving_id');
		foreach($this->db->get()->result_array() as $receiving_total_row)
		{
			$receivings_totals_for_payments[$receiving_total_row['receiving_id']] = to_currency_no_money($receiving_total_row['total'], 2);
		}
		
				
		$model = $this;
		$hide_next_and_prev_days = isset($this->params['hide_next_and_prev_days']) ? $this->params['hide_next_and_prev_days'] : NULL;
		$model->setParams(array('hide_next_and_prev_days' => $hide_next_and_prev_days,'start_date'=>$start_date, 'end_date' => $end_date, 'sales_total_for_payments' => $sales_totals_for_payments, 'receivings_total_for_payments' => $receivings_totals_for_payments,'export_excel' => isset($this->params['export_excel']) ? $this->params['export_excel'] : FALSE));

		$tabular_data = array();
		$report_data = $model->getData();

		foreach($report_data as $row)
		{
			$data_row = array();
		
			@$data_row[] = array('data'=>$row[0], 'align'=> '');
			@$data_row[] = array('data'=>$row[1], 'align'=> '');
		
			$tabular_data[] = $data_row;
		}
		$data = array(
			'view' => 'tabular',
			"title" => lang('reports_closeout'),
			"subtitle" => date(get_date_format().' '.get_time_format(), strtotime($start_date)) .'-'.date(get_date_format().' '.get_time_format(), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(),
			"export_excel" => $this->params['export_excel'],
		);
		
		return $data;
		
	}

	public function getDataColumns()
	{
		$columns = array();
		
		$columns[] = array('data'=>lang('reports_description'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_data'), 'align'=> 'left');
		
		return $columns;		
	}
	
	public function getData()
	{
		
		$return = array();
		
		$yesterday = date('Y-m-d', strtotime($this->params['end_date'].' -1 days'));
		$tomorrow = date('Y-m-d', strtotime($this->params['end_date'].' +1 days'));

		$yesterday_formatted = date(get_date_format().' '.get_time_format(), strtotime($this->params['end_date'].' -1 days'));
		$tomorrow_formatted = date(get_date_format().' '.get_time_format(), strtotime($this->params['end_date'].' +1 days'));
		if (!$this->params['export_excel'])
		{
			if (!isset($this->params['hide_next_and_prev_days']))
			{
				$return[] = array(anchor('reports/generate/closeout?report_type=complex&export_excel=0&start_date='.$yesterday.'&start_date_formatted='.$yesterday_formatted.'&end_date='.$yesterday.'&end_date_formatted='.$yesterday_formatted,'<span class="glyphicon glyphicon-backward"></span> '.lang('common_previous_day'), array('class' => 'pull-left')), anchor('reports/generate/closeout?report_type=complex&export_excel=0&start_date='.$tomorrow.'&start_date_formatted='.$tomorrow_formatted.'&end_date='.$tomorrow.'&end_date_formatted='.$tomorrow_formatted,lang('common_next_day').' <span class="glyphicon glyphicon-forward"></span>', array('class' => 'pull-right')));
			}
		}
		
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
					
		//All transactions
		$this->db->select('sum(total) as total, sum(tax) as tax, sum(profit) as profit, sum(total_quantity_purchased) as quantity', false);
		$this->db->from('sales');
		
		$this->db->where('deleted', 0);
		$this->db->where('sales.suspended !=2');
		$this->sale_time_where();
		
				
		$sales_row = array(
			'total' => 0,
			'tax' => 0,
			'profit' => 0,
			'quantity' => 0,
		);
		
		foreach($this->db->get()->result_array() as $row)
		{
			$sales_row['total'] += to_currency_no_money($row['total'],2);
			$sales_row['tax'] += to_currency_no_money($row['tax'],2);
			$sales_row['profit'] += to_currency_no_money($row['profit'],2);
			$sales_row['quantity'] += $row['quantity'];
		}
				

		$return[] = array('<h1>'.lang('reports_all_transactions').' ('.lang('reports_sales').', '.lang('reports_returns').', and '.lang('reports_exchanges').')</h1>', '--');

		$return[] = array(lang('reports_total'). ' ('.lang('common_without_tax').')', isset($sales_row['total']) ? to_currency($sales_row['total'] - $sales_row['tax']) : 0);
		$return[] = array(lang('reports_total').' ('.lang('reports_items_with_tax').')', isset($sales_row['total']) ? to_currency($sales_row['total']) : 0);
		if($this->has_profit_permission)
		{
			$return[] = array(lang('reports_profit'), isset($sales_row['profit']) ? to_currency($sales_row['profit']) : 0);
		}
					
		$this->load->model('reports/Summary_registers');
		
		$this->Summary_registers->setParams(array('start_date'=>$this->params['start_date'], 'end_date'=>$this->params['end_date'],'sale_type' => 'all'));
		$register_data = $this->Summary_registers->getData();
		
		foreach($register_data as $register_data)
		{
				$return[] = array(lang('reports_register').': '.$register_data['register'], lang('common_tax').': '.to_currency($register_data['tax']).'<br />'.lang('reports_subtotal').': '.to_currency($register_data['subtotal']).'<br />'.lang('reports_total').': '.to_currency($register_data['total']));		
		}
		
		$this->db->select('items.category_id as category_id, categories.name as category , sum('.$this->db->dbprefix('sales_items').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_items').'.total) as total', false);
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales.sale_id = sales_items.sale_id');
		$this->db->join('items', 'sales_items.item_id = items.item_id');
		$this->db->join('categories', 'categories.id = items.category_id');
		
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		$this->db->where('sales.suspended !=2');
		$this->sale_time_where();
		$this->db->group_by('category_id');
		
		$qry3=$this->db->get_compiled_select();
		
		$this->db->select('item_kits.category_id as category_id, categories.name as category , sum('.$this->db->dbprefix('sales_item_kits').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_item_kits').'.total) as total', false);
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales.sale_id = sales_item_kits.sale_id');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id');
		$this->db->join('categories', 'categories.id = item_kits.category_id');
		
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		
		$this->db->where('sales.suspended !=2');
		$this->sale_time_where();
		$this->db->group_by('category_id');
		
		$qry4=$this->db->get_compiled_select();
		
		$query1 = $this->db->query('select category_id, category,sum(subtotal) as subtotal,sum(total) as total from  ( ' .$qry3." UNION ".$qry4. ') as alias group by category order by category desc');
		$res1=$query1->result_array();
		
		$category_sales = $res1;			
		
		foreach($category_sales as $category_sale_row)
		{
			$return[] = array($this->Category->get_full_path($category_sale_row['category_id']),to_currency($category_sale_row['subtotal']).' ('.lang('reports_items_with_tax').': '.to_currency($category_sale_row['total']).')');
		}
		$return[] = array(' ', ' ');
				
		//Sales total count for day
		$this->db->from('sales');
		$this->db->join('sales_items', 'sales.sale_id = sales_items.sale_id');
		$this->db->join('items', 'sales_items.item_id = items.item_id');
		
		$this->db->group_start();
		$this->db->where_not_in('items.name',get_all_transactions_for_discount());
		$this->db->or_where('items.name IS NULL');
		$this->db->group_end();
		
		$this->db->where('sales.deleted', 0);
		$this->db->where('sales.suspended !=2');
		$this->sale_time_where();
		$this->db->group_by('sales.sale_id');
		
		$number_of_sales_transactions = $this->db->get()->num_rows();
		$average_ticket_size = $number_of_sales_transactions > 0 ? $sales_row['total']/$number_of_sales_transactions : 0;
		
		$return[] = array(lang('reports_number_of_transactions'), to_quantity($number_of_sales_transactions));
		$return[] = array(lang('reports_average_ticket_size'), to_currency($average_ticket_size));
		
		$return[] = array(lang('common_items_sold'), isset($sales_row['quantity']) ? to_quantity($sales_row['quantity']) : 0);
		$return[] = array(lang('reports_average_items_sold_per_transaction'),  $number_of_sales_transactions != 0 ? to_quantity($sales_row['quantity']/$number_of_sales_transactions) : 0);
		
		$return[] = array(' ', ' ');
		
		$return[] = array(lang('common_tax'), isset($sales_row['tax']) ? to_currency($sales_row['tax']) : 0);		
		
		$this->load->model('reports/Summary_taxes');
		
		$this->Summary_taxes->setParams(array('start_date'=>$this->params['start_date'], 'end_date'=>$this->params['end_date'],'sale_type' => 'all'));
		$taxes = $this->Summary_taxes->getData();
		
		foreach($taxes as $tax_row)
		{
			if ($tax_row['name'] != lang('reports_non_taxable'))
			{
				$return[] = array($tax_row['name'], lang('common_tax').': '.to_currency($tax_row['tax']).'<br />'.lang('reports_subtotal').': '.to_currency($tax_row['subtotal']).'<br />'.lang('reports_total').': '.to_currency($tax_row['total']));		
			}
		}
		
		if(isset($taxes[lang('reports_non_taxable')]))
		{
			$return[] = array(lang('reports_non_taxable'), to_currency($taxes[lang('reports_non_taxable')]['total']));
		}
		
		$return[] = array(' ', ' ');

		
		$this->db->select('sales_payments.sale_id, sales_payments.payment_type, payment_amount, payment_id', false);
		$this->db->from('sales_payments');
		$this->db->join('sales', 'sales.sale_id=sales_payments.sale_id');
		$this->db->where('payment_date BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']).' and location_id IN('.$location_ids_string.')');
		
		if ($this->config->item('hide_store_account_payments_in_reports'))
		{
			$this->db->where('store_account_payment',0);
		}
				
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		$this->db->order_by('sale_id, payment_date , payment_type');
				
		$sales_payments = $this->db->get()->result_array();

		$payments_by_sale = array();
		foreach($sales_payments as $row)
		{
        	$payments_by_sale[$row['sale_id']][] = $row;
		}
		
		$payment_data = $this->Sale->get_payment_data($payments_by_sale,$this->params['sales_total_for_payments']);
		
		$all_payments = array();
		foreach($payment_data as $payment_row)
		{
			$all_payments[$payment_row['payment_type']] = $payment_row['payment_amount'];
			$return[] = array($payment_row['payment_type'],to_currency($payment_row['payment_amount']));
		}
		
			
		$return[] ='-';
		$payment_data = $this->Sale->get_payment_data_by_register($payments_by_sale,$this->params['sales_total_for_payments']);
		
		$all_payments = array();
		foreach($payment_data as $payment_row)
		{
			$all_payments[$payment_row['payment_type']] = $payment_row['payment_amount'];
			$return[] = array($payment_row['payment_type'],to_currency($payment_row['payment_amount']));
		}
		
		if ($this->settings['condensed'])
		{
			return $return;
		}
		
		
		//Sales
		$this->db->select('('.$this->db->dbprefix('sales').'.total) as total, ('.$this->db->dbprefix('sales').'.tax) as tax, ('.$this->db->dbprefix('sales').'.profit) as profit, ('.$this->db->dbprefix('sales').'.total_quantity_purchased) as quantity', false);
		$this->db->from('sales');
		$this->db->where('total_quantity_purchased > 0');
		
		$this->db->where('sales.deleted', 0);
		$this->db->where('sales.suspended !=2');
		$this->sale_time_where();				
		$sales_row = array(
			'total' => 0,
			'tax' => 0,
			'profit' => 0,
			'quantity' => 0,
		);
		foreach($this->db->get()->result_array() as $row)
		{
			$sales_row['total'] += to_currency_no_money($row['total'],2);
			$sales_row['tax'] += to_currency_no_money($row['tax'],2);
			$sales_row['profit'] += to_currency_no_money($row['profit'],2);
			$sales_row['quantity'] += $row['quantity'];
		}
				


		$return[] = array('<h1>'.lang('reports_sales').'</h1>', '--');
		$return[] = array(lang('reports_total_sales'). ' ('.lang('common_without_tax').')', isset($sales_row['total']) ? to_currency($sales_row['total'] - $sales_row['tax']) : 0);
		$return[] = array(lang('reports_total_sales').' ('.lang('reports_items_with_tax').')', isset($sales_row['total']) ? to_currency($sales_row['total']) : 0);
		if($this->has_profit_permission)
		{
			$return[] = array(lang('reports_profit'), isset($sales_row['profit']) ? to_currency($sales_row['profit']) : 0);
		}
		
		$this->load->model('reports/Summary_registers');
		
		$this->Summary_registers->setParams(array('start_date'=>$this->params['start_date'], 'end_date'=>$this->params['end_date'],'sale_type' => 'sales'));
		$register_data = $this->Summary_registers->getData();
		
		foreach($register_data as $register_data)
		{
				$return[] = array(lang('reports_register').': '.$register_data['register'], lang('common_tax').': '.to_currency($register_data['tax']).'<br />'.lang('reports_subtotal').': '.to_currency($register_data['subtotal']).'<br />'.lang('reports_total').': '.to_currency($register_data['total']));		
		}
		
					
					$this->db->select('items.category_id as category_id, categories.name as category , sum('.$this->db->dbprefix('sales_items').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_items').'.total) as total', false);
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales.sale_id = sales_items.sale_id');
		$this->db->join('items', 'sales_items.item_id = items.item_id');
		$this->db->join('categories', 'categories.id = items.category_id');
		$this->db->where('sales.total_quantity_purchased > 0');
		$this->db->group_start();
		$this->db->where_not_in('items.name',get_all_transactions_for_discount());
		$this->db->or_where('items.name IS NULL');
		$this->db->group_end();
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		$this->db->where('sales.suspended !=2');
		$this->sale_time_where();
		$this->db->group_by('category_id');
		
		$qry7=$this->db->get_compiled_select();
		
		$this->db->select('item_kits.category_id as category_id, categories.name as category , sum('.$this->db->dbprefix('sales_item_kits').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_item_kits').'.total) as total', false);
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales.sale_id = sales_item_kits.sale_id');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id');
		$this->db->join('categories', 'categories.id = item_kits.category_id');
		$this->db->where('sales.total_quantity_purchased > 0');
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		
		$this->db->where('sales.suspended !=2');
		$this->sale_time_where();
		$this->db->group_by('category_id');
		
		$qry8=$this->db->get_compiled_select();
		
		$query2 = $this->db->query('select category_id, category,sum(subtotal) as subtotal,sum(total) as total from  ( ' .$qry7." UNION ".$qry8. ') as alias group by category order by category desc');
		$res2=$query2->result_array();
		
		$category_sales = $res2;					
		
		foreach($category_sales as $category_sale_row)
		{
			$return[] = array($this->Category->get_full_path($category_sale_row['category_id']),to_currency($category_sale_row['subtotal']).' ('.lang('reports_items_with_tax').': '.to_currency($category_sale_row['total']).')');
		}
		$return[] = array(' ', ' ');
		
		
		//Sales total count for day
		$this->db->from('sales');
		$this->db->join('sales_items', 'sales.sale_id = sales_items.sale_id', 'left');
		$this->db->join('items', 'sales_items.item_id = items.item_id', 'left');
		$this->db->group_start();
		$this->db->where_not_in('items.name',get_all_transactions_for_discount());
		$this->db->or_where('items.name IS NULL');
		$this->db->group_end();
		
		$this->db->where('total_quantity_purchased > 0');
		$this->db->where('sales.deleted', 0);
		$this->db->where('sales.suspended !=2');
		$this->sale_time_where();
		$this->db->group_by('sales.sale_id');
		
		$number_of_sales_transactions = $this->db->get()->num_rows();
		$average_ticket_size = $number_of_sales_transactions > 0 ? $sales_row['total']/$number_of_sales_transactions : 0;
		
		$return[] = array(lang('reports_number_of_transactions'), to_quantity($number_of_sales_transactions));
		$return[] = array(lang('reports_average_ticket_size'), to_currency($average_ticket_size));
		
		$return[] = array(lang('common_items_sold'), isset($sales_row['quantity']) ? to_quantity($sales_row['quantity']) : 0);
		
		$return[] = array(' ', ' ');
		
		$return[] = array(lang('common_tax'), isset($sales_row['tax']) ? to_currency($sales_row['tax']) : 0);		
		
		$this->load->model('reports/Summary_taxes');
		
		$this->Summary_taxes->setParams(array('start_date'=>$this->params['start_date'], 'end_date'=>$this->params['end_date'],'sale_type' => 'sales'));
		$taxes = $this->Summary_taxes->getData();
		
		foreach($taxes as $tax_row)
		{
			if ($tax_row['name'] != lang('reports_non_taxable'))
			{
				$return[] = array($tax_row['name'], lang('common_tax').': '.to_currency($tax_row['tax']).'<br />'.lang('reports_subtotal').': '.to_currency($tax_row['subtotal']).'<br />'.lang('reports_total').': '.to_currency($tax_row['total']));		
			}
		}
		
		if(isset($taxes[lang('reports_non_taxable')]))
		{
			$return[] = array(lang('reports_non_taxable'), to_currency($taxes[lang('reports_non_taxable')]['total']));
		}
		
		$return[] = array(' ', ' ');

		
		$this->db->select('sales_payments.sale_id, sales_payments.payment_type, payment_amount, payment_id', false);
		$this->db->from('sales_payments');
		$this->db->join('sales', 'sales.sale_id=sales_payments.sale_id');
		$this->db->where('payment_date BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']).' and location_id IN('.$location_ids_string.')');
		
		if ($this->config->item('hide_store_account_payments_in_reports'))
		{
			$this->db->where('store_account_payment',0);
		}
		
		$this->db->where('payment_amount > 0');
		
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		$this->db->order_by('sale_id, payment_date , payment_type');
				
		$sales_payments = $this->db->get()->result_array();

		$payments_by_sale = array();
		foreach($sales_payments as $row)
		{
        	$payments_by_sale[$row['sale_id']][] = $row;
		}
		
		$payment_data = $this->Sale->get_payment_data($payments_by_sale,$this->params['sales_total_for_payments']);
		
		foreach($payment_data as $payment_row)
		{
			$return[] = array($payment_row['payment_type'],to_currency($payment_row['payment_amount']));
		}
		
		$return[] = '-';
		$payment_data = $this->Sale->get_payment_data_by_register($payments_by_sale,$this->params['sales_total_for_payments']);
		
		foreach($payment_data as $payment_row)
		{
			$return[] = array($payment_row['payment_type'],to_currency($payment_row['payment_amount']));
		}
		
	
		//Suspended Sales
 		$this->db->select('('.$this->db->dbprefix('sales').'.total) as total, ('.$this->db->dbprefix('sales').'.tax) as tax, ('.$this->db->dbprefix('sales').'.profit) as profit, ('.$this->db->dbprefix('sales').'.total_quantity_purchased) as quantity', false);
		$this->db->from('sales');
		$this->db->where('total_quantity_purchased > 0');
		
		$this->db->where('sales.deleted', 0);
		$this->db->where('sales.suspended !=2 and sales.suspended!=0');
		$this->sale_time_where();				
		$sales_row = array(
			'total' => 0,
			'tax' => 0,
			'profit' => 0,
			'quantity' => 0,
		);
		foreach($this->db->get()->result_array() as $row)
		{
			$sales_row['total'] += to_currency_no_money($row['total'],2);
			$sales_row['tax'] += to_currency_no_money($row['tax'],2);
			$sales_row['profit'] += to_currency_no_money($row['profit'],2);
			$sales_row['quantity'] += $row['quantity'];
		}
				


		$return[] = array('<h1>'.lang('reports_suspended_sales').'</h1>', '--');
		$return[] = array(lang('reports_total_sales'). ' ('.lang('common_without_tax').')', isset($sales_row['total']) ? to_currency($sales_row['total'] - $sales_row['tax']) : 0);
		$return[] = array(lang('reports_total_sales').' ('.lang('reports_items_with_tax').')', isset($sales_row['total']) ? to_currency($sales_row['total']) : 0);
		if($this->has_profit_permission)
		{
			$return[] = array(lang('reports_profit'), isset($sales_row['profit']) ? to_currency($sales_row['profit']) : 0);
		}
							
		$this->db->select('items.category_id as category_id, categories.name as category , sum('.$this->db->dbprefix('sales_items').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_items').'.total) as total', false);
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales.sale_id = sales_items.sale_id');
		$this->db->join('items', 'sales_items.item_id = items.item_id');
		$this->db->join('categories', 'categories.id = items.category_id');
		$this->db->where('sales.total_quantity_purchased > 0');
		$this->db->group_start();
		$this->db->where_not_in('items.name',get_all_transactions_for_discount());
		$this->db->or_where('items.name IS NULL');
		$this->db->group_end();
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		$this->db->where('sales.suspended !=2 and sales.suspended!=0');
		$this->sale_time_where();
		$this->db->group_by('category_id');
		
		$qry7=$this->db->get_compiled_select();
		
		$this->db->select('item_kits.category_id as category_id, categories.name as category , sum('.$this->db->dbprefix('sales_item_kits').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_item_kits').'.total) as total', false);
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales.sale_id = sales_item_kits.sale_id');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id');
		$this->db->join('categories', 'categories.id = item_kits.category_id');
		$this->db->where('sales.total_quantity_purchased > 0');
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		
		$this->db->where('sales.suspended !=2 and sales.suspended!=0');
		$this->sale_time_where();
		$this->db->group_by('category_id');
		
		$qry8=$this->db->get_compiled_select();
		
		$query2 = $this->db->query('select category_id, category,sum(subtotal) as subtotal,sum(total) as total from  ( ' .$qry7." UNION ".$qry8. ') as alias group by category order by category desc');
		$res2=$query2->result_array();
		
		$category_sales = $res2;					
		
		foreach($category_sales as $category_sale_row)
		{
			$return[] = array($this->Category->get_full_path($category_sale_row['category_id']),to_currency($category_sale_row['subtotal']).' ('.lang('reports_items_with_tax').': '.to_currency($category_sale_row['total']).')');
		}
		$return[] = array(' ', ' ');
		
		
		//Sales total count for day
		$this->db->from('sales');
		$this->db->join('sales_items', 'sales.sale_id = sales_items.sale_id', 'left');
		$this->db->join('items', 'sales_items.item_id = items.item_id', 'left');
		$this->db->group_start();
		$this->db->where_not_in('items.name',get_all_transactions_for_discount());
		$this->db->or_where('items.name IS NULL');
		$this->db->group_end();
		
		$this->db->where('total_quantity_purchased > 0');
		$this->db->where('sales.deleted', 0);
		$this->db->where('sales.suspended !=2 and sales.suspended!=0');
		$this->sale_time_where();
		$this->db->group_by('sales.sale_id');
		
		$number_of_sales_transactions = $this->db->get()->num_rows();
		$average_ticket_size = $number_of_sales_transactions > 0 ? $sales_row['total']/$number_of_sales_transactions : 0;
		
		$return[] = array(lang('reports_number_of_transactions'), to_quantity($number_of_sales_transactions));
		$return[] = array(lang('reports_average_ticket_size'), to_currency($average_ticket_size));
		
		$return[] = array(lang('common_items_sold'), isset($sales_row['quantity']) ? to_quantity($sales_row['quantity']) : 0);
		
		
		$return[] = array(' ', ' ');

		
		$this->db->select('sales_payments.sale_id, sales_payments.payment_type, payment_amount, payment_id', false);
		$this->db->from('sales_payments');
		$this->db->join('sales', 'sales.sale_id=sales_payments.sale_id');
		$this->db->where('sales.suspended !=2 and sales.suspended!=0');
		
		$this->db->where('payment_date BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']).' and location_id IN('.$location_ids_string.')');
		
		if ($this->config->item('hide_store_account_payments_in_reports'))
		{
			$this->db->where('store_account_payment',0);
		}
		
		$this->db->where('payment_amount > 0');
		
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		$this->db->order_by('sale_id, payment_date , payment_type');
				
		$sales_payments = $this->db->get()->result_array();

		$payments_by_sale = array();
		foreach($sales_payments as $row)
		{
        	$payments_by_sale[$row['sale_id']][] = $row;
		}
		
		$payment_data = $this->Sale->get_payment_data($payments_by_sale,$this->params['sales_total_for_payments']);
		
		foreach($payment_data as $payment_row)
		{
			$return[] = array($payment_row['payment_type'],to_currency($payment_row['payment_amount']));
		}

		//Exchanges
		$this->db->select('('.$this->db->dbprefix('sales').'.total) as total, ('.$this->db->dbprefix('sales').'.tax) as tax, ('.$this->db->dbprefix('sales').'.profit) as profit, ('.$this->db->dbprefix('sales').'.total_quantity_purchased) as quantity', false);
		$this->db->from('sales');
		$this->db->where('total_quantity_purchased = 0');
		
		$this->db->where('sales.deleted', 0);
		$this->db->where('sales.suspended !=2');
		$this->sale_time_where();				
		$sales_row = array(
			'total' => 0,
			'tax' => 0,
			'profit' =>  0,
			'quantity' =>  0,
		);
		foreach($this->db->get()->result_array() as $row)
		{
			$sales_row['total'] += to_currency_no_money($row['total'],2);
			$sales_row['tax'] += to_currency_no_money($row['tax'],2);
			$sales_row['profit'] += to_currency_no_money($row['profit'],2);
			$sales_row['quantity'] += $row['quantity'];
		}
				


		$return[] = array('<h1>'.lang('reports_exchanges').'</h1>', '--');
		$return[] = array(lang('reports_total_sales'). ' ('.lang('common_without_tax').')', isset($sales_row['total']) ? to_currency($sales_row['total'] - $sales_row['tax']) : 0);
		$return[] = array(lang('reports_total_sales').' ('.lang('reports_items_with_tax').')', isset($sales_row['total']) ? to_currency($sales_row['total']) : 0);
		if($this->has_profit_permission)
		{
			$return[] = array(lang('reports_profit'), isset($sales_row['profit']) ? to_currency($sales_row['profit']) : 0);
		}
					
		$this->db->select('items.category_id as category_id, categories.name as category , sum('.$this->db->dbprefix('sales_items').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_items').'.total) as total', false);
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales.sale_id = sales_items.sale_id');
		$this->db->join('items', 'sales_items.item_id = items.item_id');
		$this->db->join('categories', 'categories.id = items.category_id');
		$this->db->where('sales.total_quantity_purchased = 0');
		$this->db->group_start();
		$this->db->where_not_in('items.name',get_all_transactions_for_discount());
		$this->db->or_where('items.name IS NULL');
		$this->db->group_end();
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		$this->db->where('sales.suspended !=2');
		$this->sale_time_where();
		$this->db->group_by('category_id');
		
		$qry7=$this->db->get_compiled_select();
		
		$this->db->select('item_kits.category_id as category_id, categories.name as category , sum('.$this->db->dbprefix('sales_item_kits').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_item_kits').'.total) as total', false);
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales.sale_id = sales_item_kits.sale_id');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id');
		$this->db->join('categories', 'categories.id = item_kits.category_id');
		$this->db->where('sales.total_quantity_purchased = 0');
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		
		$this->db->where('sales.suspended !=2');
		$this->sale_time_where();
		$this->db->group_by('category_id');
		
		$qry8=$this->db->get_compiled_select();
		
		$query2 = $this->db->query('select category_id, category,sum(subtotal) as subtotal,sum(total) as total from  ( ' .$qry7." UNION ".$qry8. ') as alias group by category order by category desc');
		$res2=$query2->result_array();
		
		$category_sales = $res2;					
		
		foreach($category_sales as $category_sale_row)
		{
			$return[] = array($this->Category->get_full_path($category_sale_row['category_id']),to_currency($category_sale_row['subtotal']).' ('.lang('reports_items_with_tax').': '.to_currency($category_sale_row['total']).')');
		}
		$return[] = array(' ', ' ');
		
		
		//Sales total count for day
		$this->db->from('sales');
		$this->db->join('sales_items', 'sales.sale_id = sales_items.sale_id', 'left');
		$this->db->join('items', 'sales_items.item_id = items.item_id', 'left');
		$this->db->group_start();
		$this->db->where_not_in('items.name',get_all_transactions_for_discount());
		$this->db->or_where('items.name IS NULL');
		$this->db->group_end();
		
		$this->db->where('sales.total_quantity_purchased = 0');
		$this->db->where('sales.deleted', 0);
		$this->db->where('sales.suspended !=2');
		$this->sale_time_where();
		$this->db->group_by('sales.sale_id');
		
		$number_of_sales_transactions = $this->db->get()->num_rows();
		
		$average_ticket_size = $number_of_sales_transactions > 0 ? $sales_row['total']/$number_of_sales_transactions : 0;
		
		$return[] = array(lang('reports_number_of_transactions'), to_quantity($number_of_sales_transactions));
		$return[] = array(lang('reports_average_ticket_size'), to_currency($average_ticket_size));
		
		$return[] = array(lang('common_items_sold'), isset($sales_row['quantity']) ? to_quantity($sales_row['quantity']) : 0);
		
		$return[] = array(' ', ' ');
		
		$return[] = array(lang('common_tax'), isset($sales_row['tax']) ? to_currency($sales_row['tax']) : 0);		
		
		$this->load->model('reports/Summary_taxes');
		
		$this->Summary_taxes->setParams(array('start_date'=>$this->params['start_date'], 'end_date'=>$this->params['end_date'],'sale_type' => 'exchanges'));
		$taxes = $this->Summary_taxes->getData();
		
		foreach($taxes as $tax_row)
		{
			if ($tax_row['name'] != lang('reports_non_taxable'))
			{
				$return[] = array($tax_row['name'], lang('common_tax').': '.to_currency($tax_row['tax']).'<br />'.lang('reports_subtotal').': '.to_currency($tax_row['subtotal']).'<br />'.lang('reports_total').': '.to_currency($tax_row['total']));		
			}
		}
		
		if(isset($taxes[lang('reports_non_taxable')]))
		{
			$return[] = array(lang('reports_non_taxable'), to_currency($taxes[lang('reports_non_taxable')]['total']));
		}
		
		$return[] = array(' ', ' ');

		
		$this->db->select('sales_payments.sale_id, sales_payments.payment_type, payment_amount, payment_id', false);
		$this->db->from('sales_payments');
		$this->db->join('sales', 'sales.sale_id=sales_payments.sale_id');
		$this->db->where('payment_date BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']).' and location_id IN('.$location_ids_string.')');
		
		if ($this->config->item('hide_store_account_payments_in_reports'))
		{
			$this->db->where('store_account_payment',0);
		}
		
		$this->db->where('payment_amount = 0');
		
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		$this->db->order_by('sale_id, payment_date , payment_type');
				
		$sales_payments = $this->db->get()->result_array();

		$payments_by_sale = array();
		foreach($sales_payments as $row)
		{
        	$payments_by_sale[$row['sale_id']][] = $row;
		}
		
		$payment_data = $this->Sale->get_payment_data($payments_by_sale,$this->params['sales_total_for_payments']);
		
		foreach($payment_data as $payment_row)
		{
			$return[] = array($payment_row['payment_type'],to_currency($payment_row['payment_amount']));
		}		
				
				
		//Returns
		$this->db->select('sum('.$this->db->dbprefix('sales').'.total) as total, sum('.$this->db->dbprefix('sales').'.tax) as tax, sum(total_quantity_purchased) as quantity', false);
		$this->db->from('sales');
		$this->db->where('sales.deleted', 0);
		$this->db->where('sales.suspended !=2');
		$this->db->where('sales.total_quantity_purchased < 0');
		$this->sale_time_where();
		
		$sales_row = array(
			'total' => 0,
			'tax' => 0,
			'quantity' => 0,
		);
		
		foreach($this->db->get()->result_array() as $row)
		{
			$sales_row['total'] += to_currency_no_money($row['total'],2);
			$sales_row['tax'] += to_currency_no_money($row['tax'],2);
			$sales_row['quantity'] += $row['quantity'];
		}
		
		
		$return[] = array('<h1>'.lang('reports_returns').'</h1>', '--');
		$return[] = array(lang('reports_total'). ' ('.lang('common_without_tax').')', isset($sales_row['total']) ? to_currency(abs($sales_row['total'] - $sales_row['tax'])) : 0);
		$return[] = array(lang('reports_total').' ('.lang('reports_items_with_tax').')', isset($sales_row['total']) ? to_currency(abs($sales_row['total'])) : 0);
		
		$return[] = array(lang('reports_total_returned'), isset($sales_row['total']) ? to_currency(abs($sales_row['total'])) : 0);
		
		$this->load->model('reports/Summary_registers');
		
		$this->Summary_registers->setParams(array('start_date'=>$this->params['start_date'], 'end_date'=>$this->params['end_date'],'sale_type' => 'returns'));
		$register_data = $this->Summary_registers->getData();
		
		foreach($register_data as $register_data)
		{
				$return[] = array(lang('reports_register').': '.$register_data['register'], lang('common_tax').': '.to_currency($register_data['tax']).'<br />'.lang('reports_subtotal').': '.to_currency($register_data['subtotal']).'<br />'.lang('reports_total').': '.to_currency($register_data['total']));		
		}
		
		
			$this->db->select('items.category_id as category_id, categories.name as category , sum('.$this->db->dbprefix('sales_items').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_items').'.total) as total', false);
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales.sale_id = sales_items.sale_id');
		$this->db->join('items', 'sales_items.item_id = items.item_id');
		$this->db->join('categories', 'categories.id = items.category_id');
		$this->db->where('total_quantity_purchased < 0');
		$this->db->group_start();
		$this->db->where_not_in('items.name',get_all_transactions_for_discount());
		$this->db->or_where('items.name IS NULL');
		$this->db->group_end();
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		$this->db->where('sales.suspended !=2');
		$this->sale_time_where();
		$this->db->group_by('category_id');
		//$this->db->order_by('category');
		
		$qry9=$this->db->get_compiled_select();
		
		$this->db->select('item_kits.category_id as category_id, categories.name as category , sum('.$this->db->dbprefix('sales_item_kits').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_item_kits').'.total) as total', false);
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales.sale_id = sales_item_kits.sale_id');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id');
		$this->db->join('categories', 'categories.id = item_kits.category_id');
		$this->db->where('total_quantity_purchased < 0');
		$this->db->group_start();
		$this->db->where_not_in('item_kits.name',get_all_transactions_for_discount());
		$this->db->or_where('item_kits.name IS NULL');
		$this->db->group_end();
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		
		$this->db->where('sales.suspended !=2');
		$this->sale_time_where();
		$this->db->group_by('category_id');
		//$this->db->order_by('category');
		
		$qry10=$this->db->get_compiled_select();
		
		$query3 = $this->db->query('select category_id, category,sum(subtotal) as subtotal,sum(total) as total from  ( ' .$qry9." UNION ".$qry10. ') as alias group by category order by category desc');
		$res3=$query3->result_array();
		
		$category_returns = $res3;
		
		//$category_returns = $this->db->get()->result_array();		
		
		
		foreach($category_returns as $category_sale_row)
		{
			$return[] = array($this->Category->get_full_path($category_sale_row['category_id']),to_currency(abs($category_sale_row['subtotal'])).' ('.lang('reports_items_with_tax').': '.to_currency(abs($category_sale_row['total'])).')');
		}
		
		//Sales total count for day
		$this->db->from('sales');
		$this->db->join('sales_items', 'sales.sale_id = sales_items.sale_id', 'left');
		$this->db->join('items', 'sales_items.item_id = items.item_id', 'left');
		$this->db->group_start();
		$this->db->where_not_in('items.name',get_all_transactions_for_discount());
		$this->db->or_where('items.name IS NULL');
		$this->db->group_end();
		
		$this->db->where('total_quantity_purchased < 0');
		$this->db->where('sales.deleted', 0);
		$this->db->where('sales.suspended !=2');
		$this->sale_time_where();
		$this->db->group_by('sales.sale_id');
		
		$number_of_returned_transactions = $this->db->get()->num_rows();
		
		$return[] = array(lang('reports_number_of_transactions'), to_quantity($number_of_returned_transactions));
		$return[] = array(lang('reports_items_returned'), isset($sales_row['quantity']) ? to_quantity(abs($sales_row['quantity'])) : 0);
		$return[] = array(lang('common_tax'), isset($sales_row['tax']) ? to_currency(abs($sales_row['tax'])) : 0);
		
		
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		
		$this->db->select('sales_payments.sale_id, sales_payments.payment_type, payment_amount, payment_id', false);
		$this->db->from('sales_payments');
		$this->db->join('sales', 'sales.sale_id=sales_payments.sale_id');
		$this->db->where('payment_date BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']).' and location_id IN('.$location_ids_string.')');
		
		if ($this->config->item('hide_store_account_payments_in_reports'))
		{
			$this->db->where('store_account_payment',0);
		}
		
		$this->db->where('payment_amount < 0');
		
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		$this->db->order_by('sale_id, payment_date , payment_type');
				
		$sales_payments = $this->db->get()->result_array();

		$payments_by_sale = array();
		foreach($sales_payments as $row)
		{
        	$payments_by_sale[$row['sale_id']][] = $row;
		}
		
		$payment_data = $this->Sale->get_payment_data($payments_by_sale,$this->params['sales_total_for_payments']);
		
		foreach($payment_data as $payment_row)
		{
			$return[] = array($payment_row['payment_type'],to_currency(abs($payment_row['payment_amount'])));
		}
		
		//Discounts
		$return[] = array('<h1>'.lang('reports_discounts').'</h1>', '--');
		
		$this->db->select('CONCAT(discount_percent, "%") as discount, count(*) as summary', false);
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales.sale_id = sales_items.sale_id');
		$this->sale_time_where();
		$this->db->where('discount_percent > 0');
		
		$this->db->where('sales.deleted', 0);
		
		$this->db->group_by('sales_items.discount_percent');
				
		$qry1=$this->db->get_compiled_select();
				
		$this->db->select('CONCAT(discount_percent, "%") as discount, count(*) as summary', false);
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales.sale_id = sales_item_kits.sale_id');
		$this->sale_time_where();
		$this->db->where('discount_percent > 0');

		$this->db->where('sales.deleted', 0);
		
		$this->db->group_by('sales_item_kits.discount_percent');
		
		$qry2=$this->db->get_compiled_select();
		
		$query = $this->db->query($qry1." UNION ".$qry2. "order by discount desc");
		$percent_discounts=$query->result_array();
		
		foreach($percent_discounts as $discount_percent)
		{
			$return[] = array($discount_percent['discount'], $discount_percent['summary']);
		}
		
		$this->db->select('COUNT(*) as discount_count');
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales.sale_id = sales_items.sale_id');
		$this->db->join('items', 'sales_items.item_id = items.item_id');
		$this->sale_time_where();
		$this->db->where_in('items.name', $this->discount_langs);
		$this->db->where('sales.deleted', 0);
		
		$discount_count = $this->db->get()->row()->discount_count;
				
		$this->db->select('SUM(item_unit_price * quantity_purchased) as discount_total');
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales.sale_id = sales_items.sale_id');
		$this->db->join('items', 'sales_items.item_id = items.item_id');
		$this->sale_time_where();
		$this->db->where_in('items.name', $this->discount_langs);
		$this->db->where('sales.deleted', 0);
		

		$result=$this->db->get();				
		if ($result->num_rows() > 0)
		{
			$query_result = $result->result();
			$flat_discounts = $query_result[0]->discount_total;
			$return[] = array(lang('reports_flat_sale_discounts'), to_currency(abs($flat_discounts)));
		}
		
		
		$this->db->select('SUM(item_unit_price * quantity_purchased*(discount_percent/100)) as discount_total');
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales.sale_id = sales_items.sale_id');
		$this->db->join('items', 'sales_items.item_id = items.item_id');
		
		$this->sale_time_where();
		$this->db->where_not_in('items.name', $this->discount_langs);
		$this->db->where('sales.deleted', 0);
		
		$qry1=$this->db->get_compiled_select();
		
		$this->db->select('SUM(item_kit_unit_price * quantity_purchased*(discount_percent/100)) as discount_total');
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales.sale_id = sales_item_kits.sale_id');
		$this->sale_time_where();
		$this->db->where('sales.deleted', 0);
		
		$qry2=$this->db->get_compiled_select();
		
		$result = $this->db->query("SELECT SUM(discount_total) as discount_total FROM (".$qry1." UNION ALL ".$qry2.") as total_discount");
		if ($result->num_rows() > 0)
		{
			$query_result = $result->result();
			$percent_discounts_total = $query_result[0]->discount_total;
			$total_discounts = abs($flat_discounts) + abs($percent_discounts_total);
			
			$return[] = array(lang('reports_percent_discounts_total'), to_currency(abs($percent_discounts_total)));
			$return[] = array(lang('reports_total_discounts'), to_currency(abs($total_discounts)));
		
		}
		
		$return[] = array('<h1>'.lang('common_inv').'</h1>', '--');		
		$this->load->model('reports/Inventory_summary');
		$model_inv_sum = $this->Inventory_summary;
		$model_inv_sum->setParams(array('date' => date('Y-m-d',strtotime($this->params['end_date'])), 'supplier'=>'','category_id' => '', 'export_excel' => $this->params['export_excel'], 'offset'=>0, 'inventory' => 'all','show_only_pending' => 0));
		
		$summary_data = $model_inv_sum->getSummaryData();
		
		$return[] = array(lang('reports_total_items_in_inventory'), to_quantity($summary_data['total_items_in_inventory']));
		$return[] = array(lang('reports_inventory_total'), to_currency($summary_data['inventory_total']));
		
		
		$return[] = array('', '');
		
		
		if ($this->config->item('enable_customer_loyalty_system') && $this->config->item('loyalty_option') == 'advanced')
		{
			$points = array();
		
			$this->db->select('SUM(points_used) as points_used, SUM(points_gained) as points_gained', false);
			$this->db->from('sales');
			$this->db->where('sale_time BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']).' and location_id IN('.$location_ids_string.')');
			$this->db->where('deleted', 0);
			$this->db->where_in('location_id',$location_ids);
		
			$points = $this->db->get()->row_array();
			$return[] = array('<h1>'.lang('reports_loyalty').'</h1>', '--');
			$return[] = array(lang('reports_points_used'), to_currency_no_money($points['points_used']));
			$return[] = array(lang('reports_points_earned'), to_currency_no_money($points['points_gained']));
		
		}
		if ($this->config->item('customers_store_accounts'))
		{
			$this->db->select("SUM(IF(transaction_amount > 0, `transaction_amount`, 0)) as debits, SUM(IF(transaction_amount < 0, `transaction_amount`, 0)) as credits", false);
			$this->db->from('store_accounts');
			$this->db->join('customers', 'customers.person_id = store_accounts.customer_id');
			$this->db->join('people', 'customers.person_id = people.person_id');
			$this->db->where('date BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']));
			
			$return[] = array('<h1>'.lang('reports_store_account').'</h1>', '--');
			
			//Store account info
			$store_account_credits_and_debits = $this->db->get()->row_array();
		
			$this->db->select('SUM(balance) as total_balance_of_all_store_accounts', false);
			$this->db->from('customers');	
			$this->db->where('deleted',0);	
			$total_store_account_balances = $this->db->get()->row_array();
		
			$store_account_info = array_merge($store_account_credits_and_debits, $total_store_account_balances);
			$return[] = array(lang('reports_debits'),to_currency($store_account_info['debits']));
			$return[] = array(lang('reports_credits'),to_currency(abs($store_account_info['credits'])));
			$return[] = array(lang('reports_total_balance_of_all_store_accounts'),to_currency($store_account_info['total_balance_of_all_store_accounts']));
		}
		
		
		if ($this->config->item('suppliers_store_accounts'))
		{
			$this->db->select("SUM(IF(transaction_amount > 0, `transaction_amount`, 0)) as debits, SUM(IF(transaction_amount < 0, `transaction_amount`, 0)) as credits", false);
			$this->db->from('supplier_store_accounts');
			$this->db->join('suppliers', 'suppliers.person_id = supplier_store_accounts.supplier_id');
			$this->db->join('people', 'suppliers.person_id = people.person_id');
			$this->db->where('date BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']));
			
			$return[] = array('<h1>'.lang('reports_store_account_suppliers').'</h1>', '--');
			
			//Store account info
			$store_account_credits_and_debits = $this->db->get()->row_array();
		
			$this->db->select('SUM(balance) as total_balance_of_all_store_accounts', false);
			$this->db->from('suppliers');		
			$this->db->where('deleted',0);
			$total_store_account_balances = $this->db->get()->row_array();
		
			$store_account_info = array_merge($store_account_credits_and_debits, $total_store_account_balances);
			$return[] = array(lang('reports_debits'),to_currency($store_account_info['debits']));
			$return[] = array(lang('reports_credits'),to_currency(abs($store_account_info['credits'])));
			$return[] = array(lang('reports_total_balance_of_all_store_accounts'),to_currency($store_account_info['total_balance_of_all_store_accounts']));
		}
		
		//Receivings
		
		$this->db->select('sum(total) as total, sum(tax) as tax, sum(total_quantity_purchased) as quantity', false);
		$this->db->from('receivings');
		
		$this->db->where('receivings.deleted', 0);
		$this->db->where('suspended !=2');
		$this->db->where('transfer_to_location_id IS NULL');
		$this->receiving_time_where();
		$this->db->order_by('receiving_time', ($this->config->item('report_sort_order')) ? $this->config->item('report_sort_order') : 'asc');
		
				
		$recvs_row = $this->db->get()->row_array();
		
		$return[] = array('<h1>'.lang('reports_receivings').'</h1>', '--');
		$return[] = array(lang('reports_total_receivings'). ' ('.lang('common_without_tax').')', isset($recvs_row['total']) ? to_currency($recvs_row['total'] - $recvs_row['tax']) : 0);
		$return[] = array(lang('reports_total_receivings').' ('.lang('reports_items_with_tax').')', isset($recvs_row['total']) ? to_currency($recvs_row['total']) : 0);		
		$return[] = array(' ', ' ');
		
		$this->db->select('categories.name as category, category_id, sum('.$this->db->dbprefix('receivings_items').'.subtotal) as subtotal, sum('.$this->db->dbprefix('receivings_items').'.total) as total', false);
		$this->db->from('receivings');
		$this->db->join('receivings_items', 'receivings.receiving_id = receivings_items.receiving_id','left');
		$this->db->join('items', 'items.item_id = receivings_items.item_id','left');
		$this->db->join('categories', 'categories.id = items.category_id','left');
		$this->db->where('transfer_to_location_id IS NULL');
		$this->db->where('total_quantity_purchased > 0');
		
		$this->db->where($this->db->dbprefix('receivings').'.deleted', 0);
		$this->db->where('suspended !=2');
		$this->receiving_time_where();
		$this->db->group_by('category_id');
		$this->db->order_by('category');
		$category_recvs = $this->db->get()->result_array();		
		
		foreach($category_recvs as $category_recv_row)
		{
			$return[] = array($this->Category->get_full_path($category_recv_row['category_id']),to_currency($category_recv_row['subtotal']).' ('.lang('reports_items_with_tax').': '.to_currency($category_recv_row['total']).')');
		}
		$return[] = array(' ', ' ');
		
		
		//rececvings total count for day
		$this->db->from('receivings');
		$this->db->where('deleted', 0);
		$this->db->where('transfer_to_location_id IS NULL');
		$this->db->where('suspended !=2');
		$this->receiving_time_where();
		$this->db->group_by('receiving_id');
		
		$number_of_recevings_transactions = $this->db->get()->num_rows();
		$average_ticket_size = $number_of_recevings_transactions > 0 ? $recvs_row['total']/$number_of_recevings_transactions : 0;
		
		
		$return[] = array(lang('reports_number_of_transactions'), to_quantity($number_of_recevings_transactions));
		$return[] = array(lang('reports_average_ticket_size'), to_currency($average_ticket_size));
		
		$return[] = array(lang('reports_items_recved'), isset($recvs_row['quantity']) ? to_quantity($recvs_row['quantity']) : 0);
		$return[] = array(' ', ' ');
		
		$return[] = array(lang('common_tax'), isset($recvs_row['tax']) ? to_currency($recvs_row['tax']) : 0);
		
		$taxes_data = array();
		$this->load->model('reports/Summary_taxes_receivings');
		
		$this->Summary_taxes_receivings->setParams(array('start_date'=>$this->params['start_date'], 'end_date'=>$this->params['end_date'],'receiving_type' => 'all'));
		$taxes = $this->Summary_taxes_receivings->getData();
		
		foreach($taxes as $tax_row)
		{
			if ($tax_row['name'] != lang('reports_non_taxable'))
			{
				$return[] = array($tax_row['name'], lang('common_tax').': '.to_currency($tax_row['tax']).'<br />'.lang('reports_subtotal').': '.to_currency($tax_row['subtotal']).'<br />'.lang('reports_total').': '.to_currency($tax_row['total']));		
			}
		}
		
		if(isset($taxes[lang('reports_non_taxable')]))
		{
			$return[] = array(lang('reports_non_taxable'), to_currency($taxes[lang('reports_non_taxable')]['total']));
		}

		$this->db->select('receivings_payments.receiving_id, receivings_payments.payment_type, payment_amount, payment_id', false);
		$this->db->from('receivings_payments');
		$this->db->join('receivings', 'receivings.receiving_id=receivings_payments.receiving_id');
		$this->db->where('payment_date BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']).' and location_id IN('.$location_ids_string.')');
		
		if ($this->config->item('hide_store_account_payments_in_reports'))
		{
			$this->db->where('store_account_payment',0);
		}
				
		$this->db->where($this->db->dbprefix('receivings').'.deleted', 0);
		$this->db->order_by('receiving_id, payment_date , payment_type');
				
		$receivings_payments = $this->db->get()->result_array();

		$payments_by_receiving = array();
		foreach($receivings_payments as $row)
		{
        $payments_by_receiving[$row['receiving_id']][] = $row;
		}
		
		$payment_data = $this->Receiving->get_payment_data($payments_by_receiving,$this->params['receivings_total_for_payments']);
		$return[] = array(' ', ' ');
		
		foreach($payment_data as $payment_row)
		{
			if (!isset($all_payments[$payment_row['payment_type']]))
			{
				$all_payments[$payment_row['payment_type']] = 0;
			}
			
			$all_payments[$payment_row['payment_type']]-= $payment_row['payment_amount'];
			$return[] = array($payment_row['payment_type'],to_currency($payment_row['payment_amount']));
		}
		
		
		$return[] = array(' ', ' ');
		
		$this->db->select('expenses_categories.id as category_id,expenses_categories.name as category, SUM(expense_amount) as amount', false);
		$this->db->from('expenses');
		$this->db->join('expenses_categories', 'expenses_categories.id = expenses.category_id','left');
		$this->db->where('expenses.deleted', 0);
		$this->db->group_by('expenses_categories.id');
		$this->db->where($this->db->dbprefix('expenses').'.expense_date BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']).' and location_id IN('.$location_ids_string.')');
		$this->db->order_by('expenses.id');

		$category_expenses = $this->db->get()->result_array();		
		
		$total = 0;
		
		foreach($category_expenses as $category_sale_row)
		{
			$total += $category_sale_row['amount'];
		}
		
		//Expenses
		$return[] = array('<h1>'.lang('common_expenses').'</h1>', '--');
		$return[] = array(lang('reports_total_expenses'), to_currency($total));
		
		foreach($category_expenses as $category_sale_row)
		{
			$return[] = array($this->Expense_category->get_full_path($category_sale_row['category_id']),to_currency($category_sale_row['amount']));
		}
		
		
		$return[] = array('','');
		
		$this->db->select('expense_payment_type as payment_type, SUM(expense_amount + expense_tax) as amount', false);
		$this->db->from('expenses');
		$this->db->where('expenses.deleted', 0);
		$this->db->group_by('expense_payment_type');
		$this->db->where($this->db->dbprefix('expenses').'.expense_date BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']).' and location_id IN('.$location_ids_string.')');
		
		foreach($this->db->get()->result_array() as $payment_row)
		{
			if (!isset($all_payments[$payment_row['payment_type']]))
			{
				$all_payments[$payment_row['payment_type']] = 0;
			}
			$all_payments[$payment_row['payment_type']]-= $payment_row['amount'];
				
			$return[] = array($payment_row['payment_type'],to_currency($payment_row['amount']));
		}
		
		//All payments
		$return[] = array('<h1>'.lang('common_all').' '.lang('reports_payments').'</h1>', '--');
		
		foreach($all_payments as $payment_type => $payment_amount)
		{
			$return[] = array($payment_type,to_currency($payment_amount));
		}
		
		//Cash Tracking
		
		$payment_types =  $this->config->item('track_payment_types') ? unserialize($this->config->item('track_payment_types')) : array();
		
		if ($this->config->item('track_payment_types') && !empty($payment_types))
		{
			$between = 'between ' . $this->db->escape($this->params['start_date'] . ' 00:00:00').' and ' . $this->db->escape($this->params['end_date'] . ' 23:59:59');
			$this->db->select("locations.name as location_name,SUM(open_amount) as open_amount, SUM(close_amount) as close_amount,SUM(payment_sales_amount) as payment_sales_amount,SUM(total_payment_additions) as total_payment_additions,SUM(total_payment_subtractions) as total_payment_subtractions,SUM(close_amount - open_amount - payment_sales_amount - total_payment_additions + total_payment_subtractions) as difference,registers.name as register_name, open_person.first_name as open_first_name, open_person.last_name as open_last_name, close_person.first_name as close_first_name, close_person.last_name as close_last_name, register_log.*");
			$this->db->from('register_log as register_log');
			$this->db->join('registers', 'registers.register_id = register_log.register_id');
			$this->db->join('locations', 'registers.location_id = locations.location_id');
			$this->db->join('register_log_payments','register_log.register_log_id = register_log_payments.register_log_id');
			$this->db->join('people as open_person', 'register_log.employee_id_open=open_person.person_id');
			$this->db->join('people as close_person', 'register_log.employee_id_close=close_person.person_id', 'left');
			$this->db->where('register_log.shift_start ' . $between);
			$this->db->where('register_log.deleted ', 0);
			$this->db->where_in('registers.location_id', $location_ids);
			$this->db->group_by('register_log.register_log_id');
		
			$register_logging = $this->db->get()->result_array();
			
			$return[] = array('<h1>'.lang('common_track_register').'</h1>', '--');
			
			
			foreach($register_logging as $register_logging_row)
			{
				$emp_info_open = $this->Employee->get_info($register_logging_row['employee_id_open']);
								
				$data = lang('common_opening_amount').': '.to_currency($register_logging_row['open_amount']);
				$data.= ' / '.lang('reports_employee_open').': '.$emp_info_open->first_name. ' '.$emp_info_open->last_name;
				
				if ($register_logging_row['shift_end']=='0000-00-00 00:00:00')
				{
					$data.= ' / '.lang('common_closing_amount').': '.lang('reports_register_log_open');
					$data .= ' / '.lang('common_sales').': '.to_currency($register_logging_row['payment_sales_amount']);					
					$data .= ' / '.lang('common_total_additions').': '.to_currency($register_logging_row['total_payment_additions']);					
					$data .= ' / '.lang('common_total_subtractions').': '.to_currency($register_logging_row['total_payment_subtractions']);					
				}
				else
				{					
					$emp_info_close = $this->Employee->get_info($register_logging_row['employee_id_close']);
					
					$data .= ' / '.lang('common_closing_amount').': '.to_currency($register_logging_row['close_amount']);
					$data.= ' / '.lang('reports_close_employee').': '.$emp_info_close->first_name. ' '.$emp_info_close->last_name;
						
					$data .= ' / '.lang('common_sales').': '.to_currency($register_logging_row['payment_sales_amount']);					
					$data .= ' / '.lang('common_total_additions').': '.to_currency($register_logging_row['total_payment_additions']);					
					$data .= ' / '.lang('common_total_subtractions').': '.to_currency($register_logging_row['total_payment_subtractions']);					
				}

				$data .= ' / '.lang('reports_difference').': '.to_currency($register_logging_row['difference']);					
				
				$return[] = array('<h2>'.$register_logging_row['register_name'].' ('.$register_logging_row['location_name'].')</h2>' .date(get_date_format().' '.get_time_format(), strtotime($register_logging_row['shift_start'])).' - '.date(get_date_format().' '.get_time_format(), strtotime($register_logging_row['shift_end'])),$data);
			}
		}
		if (!$this->params['export_excel'])
		{
			if (!isset($this->params['hide_next_and_prev_days']))
			{
				$return[] = array(anchor('reports/generate/closeout?report_type=complex&export_excel=0&start_date='.$yesterday.'&start_date_formatted='.$yesterday_formatted.'&end_date='.$yesterday.'&end_date_formatted='.$yesterday_formatted,'<span class="glyphicon glyphicon-backward"></span> '.lang('common_previous_day'), array('class' => 'pull-left')), anchor('reports/generate/closeout?report_type=complex&export_excel=0&start_date='.$tomorrow.'&start_date_formatted='.$tomorrow_formatted.'&end_date='.$tomorrow.'&end_date_formatted='.$tomorrow_formatted,lang('common_next_day').' <span class="glyphicon glyphicon-forward"></span>', array('class' => 'pull-right')));
		
			}
		}
		
		return $return;
	}
	
		
	
	public function getSummaryData()
	{
		return array();
	}
	
	function get_sale_ids_for_payments()
	{
		$sale_ids = array();
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		
		$this->db->select('sales_payments.sale_id');
		$this->db->distinct();
		$this->db->from('sales_payments');
		$this->db->join('sales', 'sales.sale_id=sales_payments.sale_id');
		$this->db->where('payment_date BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']. ' 23:59:59').' and location_id IN('.$location_ids_string.')');
	
		foreach($this->db->get()->result_array() as $sale_row)
		{
			 $sale_ids[] = $sale_row['sale_id'];
		}
		
		return $sale_ids;
	}
	
	function get_receiving_ids_for_payments()
	{
		$receiving_ids = array();
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		
		$this->db->select('receivings_payments.receiving_id');
		$this->db->distinct();
		$this->db->from('receivings_payments');
		$this->db->join('receivings', 'receivings.receiving_id=receivings_payments.receiving_id');
		$this->db->where('payment_date BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']. ' 23:59:59').' and location_id IN('.$location_ids_string.')');
	
		foreach($this->db->get()->result_array() as $receiving_row)
		{
			 $receiving_ids[] = $receiving_row['receiving_id'];
		}
		
		return $receiving_ids;
	}
}
?>