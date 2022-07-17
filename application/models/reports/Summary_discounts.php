<?php
require_once ("Report.php");
class Summary_discounts extends Report
{
	function __construct()
	{
		$this->load->helper('language');
		$this->discount_langs = get_all_language_values_for_key('common_discount','common');
		
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		return array(array('data'=>lang('common_discount'), 'align'=> 'left'),array('data'=>lang('common_count').'/'.lang('reports_total'), 'align'=> 'left'));
	}
	
	public function getInputData()
	{
		
		$specific_entity_data = array();
		$specific_entity_data['view']  = 'specific_entity';
		$specific_entity_data['specific_input_name'] = 'employee_id';
		$specific_entity_data['specific_input_label'] = lang('reports_employee');
		$employees = array('' => lang('common_all'));

		foreach($this->Employee->get_all()->result() as $employee)
		{
			$employees[$employee->person_id] = $employee->first_name .' '.$employee->last_name;
		}
		$specific_entity_data['specific_input_data'] = $employees;
		
		
		$input_params = array();

		if ($this->settings['display'] == 'tabular')
		{
			$input_data = Report::get_common_report_input_data(TRUE);
			
			$input_params = array(
				array('view' => 'date_range', 'with_time' => TRUE),
				$specific_entity_data,
				array('view' => 'dropdown','dropdown_label' =>lang('reports_sale_type'),'dropdown_name' => 'sale_type','dropdown_options' =>array('all' => lang('reports_all'), 'sales' => lang('reports_sales'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all'),
				array('view' => 'excel_export'),
				array('view' => 'locations'),
				array('view' => 'submit'),
			);
		}
		
		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		return $input_data;
	}
	
	function getOutputData()
	{
		$this->load->model('Category');
		
		
		$subtitle = date(get_date_format(), strtotime($this->params['start_date'])) .'-'.date(get_date_format(), strtotime($this->params['end_date']));
	
		$report_data = $this->getData();
		$summary_data = $this->getSummaryData();

		if ($this->settings['display'] == 'tabular')
		{				
		
			$this->setupDefaultPagination();
		

			foreach($report_data as $row)
			{
				$tabular_data[] = array(array('data'=>$row['discount'], 'align'=>'left'),array('data'=>$row['summary'], 'align'=>'left'));
			}

			$data = array(
				"view" => 'tabular',
				"title" => lang('reports_discounts_summary_report'),
				"subtitle" => date(get_date_format(), strtotime($this->params['start_date'])) .'-'.date(get_date_format(), strtotime($this->params['end_date'])),
				"headers" => $this->getDataColumns(),
				"data" => $tabular_data,
				"summary_data" => $summary_data,
				"export_excel" => $this->params['export_excel'],
				"pagination" => $this->pagination->create_links(),
			);
		}
		
		return $data;
	}
	public function getData()
	{
		$return = array();
		$this->db->select('CONCAT(discount_percent, "%") as discount, count(*) as summary', false);
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales.sale_id = sales_items.sale_id');
		$this->sale_time_where();
		$this->db->where('discount_percent > 0');
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('quantity_purchased < 0');
		}
		
		if (isset($this->params['employee_id']) && $this->params['employee_id'])
		{
			$this->db->where('sales.employee_id', $this->params['employee_id']);			
		}
		
		$this->db->where('sales.deleted', 0);
		
		$this->db->group_by('sales_items.discount_percent');
				
		$qry1=$this->db->get_compiled_select();
				
		$this->db->select('CONCAT(discount_percent, "%") as discount, count(*) as summary', false);
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales.sale_id = sales_item_kits.sale_id');
		$this->sale_time_where();
		$this->db->where('discount_percent > 0');
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('quantity_purchased < 0');
		}

		if (isset($this->params['employee_id']) && $this->params['employee_id'])
		{
			$this->db->where('sales.employee_id', $this->params['employee_id']);			
		}

		$this->db->where('sales.deleted', 0);
		
		$this->db->group_by('sales_item_kits.discount_percent');
		
		$qry2=$this->db->get_compiled_select();
		
		$query = $this->db->query($qry1." UNION ".$qry2. "order by discount desc");
		$res=$query->result_array();
				
		$percent_discounts = $res;
		$return = $percent_discounts;
		
		$this->db->select('COUNT(*) as discount_count');
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales.sale_id = sales_items.sale_id');
		$this->db->join('items', 'sales_items.item_id = items.item_id');
		$this->sale_time_where();
		$this->db->where_in('items.name', $this->discount_langs);
		$this->db->where('sales.deleted', 0);
		
		if (isset($this->params['employee_id']) && $this->params['employee_id'])
		{
			$this->db->where('sales.employee_id', $this->params['employee_id']);			
		}
		
		$discount_count = $this->db->get()->row()->discount_count;
				
		$this->db->select('SUM(item_unit_price * quantity_purchased) as discount_total');
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales.sale_id = sales_items.sale_id');
		$this->db->join('items', 'sales_items.item_id = items.item_id');
		$this->sale_time_where();
		$this->db->where_in('items.name', $this->discount_langs);
		$this->db->where('sales.deleted', 0);
		if (isset($this->params['employee_id']) && $this->params['employee_id'])
		{
			$this->db->where('sales.employee_id', $this->params['employee_id']);			
		}
		

		$result=$this->db->get();				
		if ($result->num_rows() > 0)
		{
			$query_result = $result->result();
			$flat_discounts = $query_result[0]->discount_total;
			$return[] = array('discount' => lang('reports_flat_sale_discounts'), 'summary' => to_currency(abs($flat_discounts)));
		}
		
		
		$this->db->select('discount_percent,SUM(item_unit_price * quantity_purchased*(discount_percent/100)) as discount_total');
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales.sale_id = sales_items.sale_id');
		$this->db->join('items', 'sales_items.item_id = items.item_id');
		
		$this->sale_time_where();
		$this->db->where_not_in('items.name', $this->discount_langs);
		$this->db->where('sales.deleted', 0);
		
		if (isset($this->params['employee_id']) && $this->params['employee_id'])
		{
			$this->db->where('sales.employee_id', $this->params['employee_id']);			
		}
		
		$this->db->group_by('discount_percent');
		
		$qry1=$this->db->get_compiled_select();
		
		$this->db->select('discount_percent,SUM(item_kit_unit_price * quantity_purchased*(discount_percent/100)) as discount_total');
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales.sale_id = sales_item_kits.sale_id');
		$this->sale_time_where();
		$this->db->where('sales.deleted', 0);
		
		if (isset($this->params['employee_id']) && $this->params['employee_id'])
		{
			$this->db->where('sales.employee_id', $this->params['employee_id']);			
		}
		
		$this->db->group_by('discount_percent');
		
		$qry2=$this->db->get_compiled_select();
		
		$result = $this->db->query("SELECT discount_percent,SUM(discount_total) as discount_total FROM (".$qry1." UNION ALL ".$qry2.") as total_discount GROUP BY discount_percent");
		
		$percent_discounts_totals = 0;
		
		if ($result->num_rows() > 0)
		{
			foreach($result->result_array() as $query_result)
			{
				$percent_discounts_total = $query_result['discount_total'];
				$percent_discounts_totals+=$percent_discounts_total;
				$return[] = array('discount' => $query_result['discount_percent'].'% '.lang('reports_percent_discounts_total'), 'summary' => to_currency(abs($percent_discounts_total)));
			}
		}

		$total_discounts = abs($flat_discounts) + abs($percent_discounts_totals);

		$return[] = array('discount' => lang('reports_total_discounts'), 'summary' => to_currency(abs($total_discounts)));
		
		return $return;
	}
	
	function getTotalRows()
	{	
		$this->db->select('COUNT(DISTINCT(discount_percent)) as discount_count');
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales.sale_id = sales_items.sale_id');
		$this->sale_time_where();
		$this->db->where('discount_percent > 0');
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('quantity_purchased < 0');
		}
		
		if (isset($this->params['employee_id']) && $this->params['employee_id'])
		{
			$this->db->where('sales.employee_id', $this->params['employee_id']);			
		}
		
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);

		$this->db->group_by('sales_items.discount_percent');
		
				
		$qry1=$this->db->get_compiled_select();
				
		$this->db->select('COUNT(DISTINCT(discount_percent)) as discount_count');
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales.sale_id = sales_item_kits.sale_id');
		$this->sale_time_where();
		$this->db->where('discount_percent > 0');
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('quantity_purchased < 0');
		}
		
		if (isset($this->params['employee_id']) && $this->params['employee_id'])
		{
			$this->db->where('sales.employee_id', $this->params['employee_id']);			
		}
		
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		
		$this->db->group_by('sales_item_kits.discount_percent');
		
		$qry2=$this->db->get_compiled_select();
		
		$query = $this->db->query($qry1." UNION ALL ".$qry2);
		$ret=$query->row_array();

		return $ret['discount_count'] + 1; // + 1 for flat discount
	}
	
	
	public function getSummaryData()
	{
		$this->db->select('sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax,sum(profit) as profit', false);
		$this->db->from('sales');
		$this->sale_time_where();
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('total_quantity_purchased < 0');
		}
		
		if (isset($this->params['employee_id']) && $this->params['employee_id'])
		{
			$this->db->where('sales.employee_id', $this->params['employee_id']);			
		}
		
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		
		$this->db->group_by('sale_id');
		
		$return = array(
			'subtotal' => 0,
			'total' => 0,
			'tax' => 0,
			'profit' => 0,
		);
		
		foreach($this->db->get()->result_array() as $row)
		{
			$return['subtotal'] += to_currency_no_money($row['subtotal'],2);
			$return['total'] += to_currency_no_money($row['total'],2);
			$return['tax'] += to_currency_no_money($row['tax'],2);
			$return['profit'] += to_currency_no_money($row['profit'],2);
		}
		if(!$this->has_profit_permission)
		{
			unset($return['profit']);
		}
		return $return;
	}
}
?>