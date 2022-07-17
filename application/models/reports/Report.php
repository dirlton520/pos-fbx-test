<?php
abstract class Report extends MY_Model 
{
	var $CI;
	var $params	= array();
	var $settings = array();
	static $reports;
	function __construct()
	{
		parent::__construct();
		ini_set('memory_limit','1024M');
		$this->report_limit = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;
		$this->report_key = NULL;

		@$this->has_profit_permission = $this->Employee->has_module_action_permission('reports','show_profit',$this->Employee->get_logged_in_employee_info()->person_id);
		@$this->has_cost_price_permission = $this->Employee->has_module_action_permission('reports','show_cost_price',$this->Employee->get_logged_in_employee_info()->person_id);
		
		//Need to query database directly as load config hook doesn't happen until after constructor
		@$this->decimals = $this->Appconfig->get_raw_number_of_decimals();
		@$this->decimals = $this->decimals !== NULL && $this->decimals!= '' ? $this->decimals : 2;
		
		//Make sure the report is not cached by the browser
		$this->output->set_header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate");
		$this->output->set_header("Cache-Control: post-check=0, pre-check=0", false);
		$this->output->set_header("Pragma: no-cache");		
			
	}
	
	public abstract function getInputData();
	public abstract function getOutputData();
	public abstract function getData();
	public abstract function getSummaryData();
	
	public static function get_report_model($report_key)
	{
		if (isset(Report::$reports[$report_key]))
		{
			$CI =& get_instance();
			$report = Report::$reports[$report_key];
			$CI->load->model('reports/'.$report['model']);
			$model = $CI->{$report['model']};
			$model->report_key = $report_key;
			$model->setSettings($report['settings']);
			return $model;
		}
		
		return NULL;
	}
	
	public function getTotalRows()
	{
		$this->db->select("COUNT(DISTINCT(sale_id)) as sale_count");
		$this->db->from('sales');
		$ret = $this->db->get()->row_array();
		return $ret['sale_count'];
	}
	
	public function setParams(array $params)
	{
		$this->params = $params;
	}
	
	public function setSettings(array $settings)
	{
		$this->settings = $settings;
	}
	
	public function receiving_time_where($sql_requrn=false)
	{
		$CI =& get_instance();
		
		if (!$CI->input->get('location_ids'))
		{
			static $location_ids;
		}
		else
		{
			$location_ids  = NULL;
		}
		
		if (!$location_ids)
		{
			$location_ids = implode(',',Report::get_selected_location_ids());
		}
		
		$where = 'receiving_time BETWEEN "'.$this->params['start_date'].'" and "'.$this->params['end_date'].'"'.' and '.$this->db->dbprefix('receivings').'.location_id IN ('.$location_ids.')'.(($this->config->item('hide_store_account_payments_in_reports') ) ? ' and '.$this->db->dbprefix('receivings').'.store_account_payment=0' : '');

		//Added for detailed_suspended_report, we don't need this for other reports as we are always going to have start + end date
		if (isset($this->settings['force_suspended']) && $this->settings['force_suspended'])
		{
			$where .=' and suspended != 0';				
		}
		elseif ($this->config->item('hide_suspended_recv_in_reports'))
		{
			$where .=' and suspended = 0';
		}
		
		if($sql_requrn == true){
			return $where;
		}

		return $this->db->where($where);
		
	}
	
	public function sale_time_where()
	{
		$CI =& get_instance();
		
		if (!$CI->input->get('location_ids'))
		{
			static $location_ids;
		}
		else
		{
			$location_ids  = NULL;
		}
		
		
		if (isset($this->params['override_location_id']))
		{
			$location_ids = array($this->params['override_location_id']);
			$location_ids = implode(',',$location_ids);
			
		}
		elseif (!$location_ids)
		{
			$location_ids = isset($this->params['override_location_id']) ? array($this->params['override_location_id']) : Report::get_selected_location_ids();
			$location_ids = implode(',',$location_ids);
		}
		
		$where = 'sale_time BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']).' and '.$this->db->dbprefix('sales').'.location_id IN ('.$location_ids.')'. (($this->config->item('hide_store_account_payments_in_reports') ) ? ' and '.$this->db->dbprefix('sales').'.store_account_payment=0' : '');
	
		//Added for detailed_suspended_report, we don't need this for other reports as we are always going to have start + end date
		
		if (!isset($this->params['show_all_suspended']) || !$this->params['show_all_suspended'])
		{
			if (isset($this->settings['force_suspended']) && $this->settings['force_suspended'])
			{
				$where .=' and (suspended != 0 or (was_layaway = 1 or was_estimate = 1))';				
			}	
			elseif ($this->config->item('hide_layaways_sales_in_reports'))
			{
				$where .=' and suspended = 0';
			}
			else
			{
				$where .=' and suspended < 2';					
			}
		}
		$this->db->where($where);
	}
	
	public function delivery_time_where()
	{
		$CI =& get_instance();
		
		if (!$CI->input->get('location_ids'))
		{
			static $location_ids;
		}
		else
		{
			$location_ids  = NULL;
		}
		
		
		if (!$location_ids)
		{
			$location_ids = implode(',',Report::get_selected_location_ids());
		}
		
		$where = 'sale_time BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']).' and '.$this->db->dbprefix('sales_deliveries').'.location_id IN ('.$location_ids.')'. (($this->config->item('hide_store_account_payments_in_reports') ) ? ' and '.$this->db->dbprefix('sales').'.store_account_payment=0 ' : ' ');
		$where .= 'or estimated_shipping_date BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']).' and '.$this->db->dbprefix('sales_deliveries').'.location_id IN ('.$location_ids.')'. (($this->config->item('hide_store_account_payments_in_reports') ) ? ' and '.$this->db->dbprefix('sales').'.store_account_payment=0 ' : ' ');
		$where .= 'or actual_shipping_date BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']).' and '.$this->db->dbprefix('sales_deliveries').'.location_id IN ('.$location_ids.')'. (($this->config->item('hide_store_account_payments_in_reports') ) ? ' and '.$this->db->dbprefix('sales').'.store_account_payment=0 ' : ' ');
		$where .= 'or estimated_delivery_or_pickup_date BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']).' and '.$this->db->dbprefix('sales_deliveries').'.location_id IN ('.$location_ids.')'. (($this->config->item('hide_store_account_payments_in_reports') ) ? ' and '.$this->db->dbprefix('sales').'.store_account_payment=0 ' : ' ');
	
		//Added for detailed_suspended_report, we don't need this for other reports as we are always going to have start + end date
		if (isset($this->settings['force_suspended']) && $this->settings['force_suspended'])
		{
			$where .=' and (suspended != 0 or (was_layaway = 1 or was_estimate = 1))';				
		}
		elseif ($this->config->item('hide_layaways_sales_in_reports'))
		{
			$where .=' and suspended = 0';
		}
		else
		{
			$where .=' and suspended < 2';					
		}
		
		$this->db->where($where);
	}
	
	public static function get_selected_location_ids()
	{
		$CI =& get_instance();
		
		if ($CI->input->get('location_ids'))
		{
			return $CI->input->get('location_ids');
		}
		else
		{
			return array($CI->Employee->get_logged_in_employee_current_location_id());
		}
	}
	
	public static function get_common_report_input_data($time=false)
	{
		$CI =& get_instance();
		
		$data = array();
		$data['report_date_range_simple'] = get_simple_date_ranges();
		$data['report_date_range_simple_compare'] = get_simple_data_ranges_compare();
		$data['months'] = get_months();
		$data['days'] = get_days();
		$data['years'] = get_years();
		$data['hours'] = get_hours($CI->config->item('time_format'));
		$data['minutes'] = get_minutes();
		$data['selected_month']=date('m');
		$data['selected_day']=date('d');
		$data['selected_year']=date('Y');
		$data['intervals'] = get_time_intervals();	
	
		return $data;
	}
		
	public static function get_simple_date_ranges_expire()
	{	
		$CI =& get_instance();
		
		$data = array();
		$data['report_date_range_simple'] = get_simple_date_ranges_expire();
		$data['months'] = get_months();
		$data['days'] = get_days();
		$data['years'] = get_years();
		$data['hours'] = get_hours($CI->config->item('time_format'));
		$data['minutes'] = get_minutes();
		$data['selected_month']=date('m');
		$data['selected_day']=date('d');
		$data['selected_year']=date('Y');
		$data['intervals'] = get_time_intervals();
		
		return $data;	

	}
		
	protected function setupDefaultPagination()
	{
		$config = array();
		$config['reuse_query_string'] = TRUE;
		$config['page_query_string'] = TRUE;
		$config['query_string_segment'] = 'offset';
		$config['base_url'] = site_url("reports/generate/".$this->report_key);
		$config['total_rows'] = $this->getTotalRows();
		$config['per_page'] = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20; 
		
		$this->load->library('pagination');
		$this->pagination->initialize($config);
	}
	
	function get_details_data_columns_sales()
	{
		$details = array();
		$details[] = array('data'=>lang('common_item_number'), 'align'=> 'left');
		$details[] = array('data'=>lang('common_product_id'), 'align'=> 'left');
		$details[] = array('data'=>lang('reports_name'), 'align'=> 'left');
		$details[] = array('data'=>lang('reports_category'), 'align'=> 'left');
		$details[] = array('data'=>lang('common_size'), 'align'=> 'left');
		$details[] = array('data'=>lang('common_supplier'), 'align'=> 'left');
		$details[] = array('data'=>lang('reports_serial_number'), 'align'=> 'left');
		if (!$this->config->item('hide_item_descriptions_in_reports') || (isset($this->params['export_excel']) && $this->params['export_excel']))
		{
			$details[] = array('data'=>lang('reports_description'), 'align'=> 'left');
		}
		
		$details[] = array('data'=>lang('common_unit_price'), 'align'=> 'left');
		
		$details[] = array('data'=>lang('reports_quantity_purchased'), 'align'=> 'left');
		$details[] = array('data'=>lang('reports_subtotal'), 'align'=> 'right');
		$details[] = array('data'=>lang('reports_total'), 'align'=> 'right');
		$details[] = array('data'=>lang('common_tax'), 'align'=> 'right');
		if($this->has_profit_permission)
		{
			$details[] = array('data'=>lang('common_profit'), 'align'=> 'right');			
			$details[] = array('data'=>lang('common_cogs'), 'align'=> 'right');			
		}
		
		
		if (strpos($this->report_key, 'commission') !== false)
		{
			$details[] = array('data'=>lang('reports_commission'), 'align'=> 'right');			
		}
		
		$details[] = array('data'=>lang('common_discount'), 'align'=> 'right');
		return $details;
	}
	
	function get_report_details($ids, $export_excel=0)
	{
		$this->db->select('sales_items.item_unit_price as unit_price,sales_items.item_variation_id, items.item_id, sales_items.sale_id, items.category_id, items.item_number, items.product_id as item_product_id, items.name as item_name, categories.name as category, quantity_purchased, serialnumber, sales_items.description, subtotal, total, tax, profit, commission, discount_percent, items.size as size, items.unit_price as current_selling_price, suppliers.company_name as supplier_name, suppliers.person_id as supplier_id', false);
		$this->db->from('sales_items');
		$this->db->join('items', 'sales_items.item_id = items.item_id', 'left');
		$this->db->join('categories', 'categories.id = items.category_id', 'left');
		$this->db->join('suppliers', 'items.supplier_id = suppliers.person_id', 'left');
		if (!empty($ids))
		{
			$sale_ids_chunk = array_chunk($ids,25);
			$this->db->group_start();
			foreach($sale_ids_chunk as $sale_ids)
			{
				$this->db->or_where_in('sales_items.sale_id', $sale_ids);
			}
			$this->db->group_end();
		}
		else
		{
			$this->db->where('1', '2', FALSE);		
		}		
		$qry1=$this->db->get_compiled_select();
		
		$this->db->select('sales_item_kits.item_kit_unit_price as unit_price, 0 as item_variation_id, item_kits.item_kit_id, sales_item_kits.sale_id,item_kits.category_id, item_kits.item_kit_number as item_number, item_kits.product_id as item_product_id, item_kits.name as item_name, categories.name as category, quantity_purchased, NULL as serialnumber, sales_item_kits.description, subtotal, total, tax, profit, commission, discount_percent, NULL as size, item_kits.unit_price as current_selling_price, NULL as supplier_name, NULL as supplier_id', false);
		$this->db->from('sales_item_kits');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id', 'left');
		$this->db->join('categories', 'categories.id = item_kits.category_id', 'left');
		if (!empty($ids))
		{
			$sale_ids_chunk = array_chunk($ids,25);
			$this->db->group_start();
			foreach($sale_ids_chunk as $sale_ids)
			{
				$this->db->or_where_in('sales_item_kits.sale_id', $sale_ids);
			}
			$this->db->group_end();
		}
		else
		{
			$this->db->where('1', '2', FALSE);		
		}
		
		$qry2=$this->db->get_compiled_select();
		
		$query = $this->db->query($qry1." UNION ALL ".$qry2);
		//echo $this->db->last_query();exit;
		$res=$query->result_array();
		
		if($export_excel == 1)
		{
			return $res;
			exit;
		}
		$this->load->model('Category');
		
		$variation_ids = array();
		foreach($res as $key=>$drow)
		{			
			if (isset($row['variation_id']) && $row['variation_id'])
			{
				$variation_ids[] = $row['variation_id'];
			}
		}
		
		
		$this->load->model('Item_variations');
		$variation_attrs = $this->Item_variations->get_attributes($variation_ids);

		$variation_labels = array();
		
		foreach($variation_attrs as $variation_id => $attrs)
		{
			 $variation_labels[$variation_id] = implode(', ', array_column($attrs,'label'));
		}
		
		$details_data = array();
		foreach($res as $key=>$drow)
			{	
				$details_data_row = array();
				$details_data_row[] = array('data'=>$drow['item_number'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['item_product_id'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['item_name'].(isset($variation_labels[$drow['item_variation_id']]) ? ': '.$variation_labels[$drow['item_variation_id']] : ''), 'align'=>'left');
				$details_data_row[] = array('data'=>$this->Category->get_full_path($drow['category_id']), 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['size'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['supplier_name'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['serialnumber'], 'align'=>'left');
				if (!$this->config->item('hide_item_descriptions_in_reports') || (isset($this->params['export_excel']) && $this->params['export_excel']))
				{
					$details_data_row[] = array('data'=>character_limiter($drow['description'],150), 'align'=>'left');
				}
				$details_data_row[] = array('data'=>to_currency($drow['unit_price']), 'align'=>'left');
				$details_data_row[] = array('data'=>to_quantity($drow['quantity_purchased']), 'align'=>'left');
				
				$details_data_row[] = array('data'=>to_currency($drow['subtotal']), 'align'=>'right');
				$details_data_row[] = array('data'=>to_currency($drow['total']), 'align'=>'right');
				$details_data_row[] = array('data'=>to_currency($drow['tax']), 'align'=>'right');
				
				if($this->has_profit_permission)
				{
					$details_data_row[] = array('data'=>to_currency($drow['profit']), 'align'=>'right');					
					$details_data_row[] = array('data'=>to_currency($drow['subtotal']-$drow['profit']), 'align'=>'right');					
				}
				
				if (strpos($this->report_key, 'commission') !== false)
				{
					$details_data_row[] = array('data'=>to_currency($drow['commission']), 'align'=>'right');					
				}
				$details_data_row[] = array('data'=>$drow['discount_percent'].'%', 'align'=> 'left');
				
				$details_data[$key][$drow['sale_id']] = $details_data_row;
			}
		
		$data=array(
		"headers" => $this->getDataColumns(),
		"details_data" => $details_data
		);
		
		return $data;
	}
	
	function get_details_data_column_recv()
	{
		return array(array('data'=>lang('reports_name'), 'align'=>'left'),array('data'=>lang('common_product_id'), 'align'=> 'left'), array('data'=>lang('reports_category'), 'align'=>'left'),array('data'=>lang('common_size'), 'align'=>'left'), 		array('data'=>lang('reports_items_ordered'), 'align'=>'left'),array('data'=>lang('common_qty_received'), 'align'=>'left'), array('data'=>lang('reports_subtotal'), 'align'=>'right'), array('data'=>lang('reports_total'), 'align'=>'right'),  		array('data'=>lang('common_tax'), 'align'=>'right'), array('data'=>lang('common_discount'), 'align'=>'left'));
	}
	public static function get_saved_reports()
	{
		$CI =& get_instance();
		$CI->load->model('Employee_appconfig');
		return $CI->Employee_appconfig->get('saved_reports') ? unserialize($CI->Employee_appconfig->get('saved_reports')) : array();
	}
	
	public static function delete_saved_report($key)
	{
		$CI =& get_instance();
		$CI->load->model('Employee_appconfig');
		$reports  = unserialize($CI->Employee_appconfig->get('saved_reports'));
		unset($reports[$key]);
		$CI->Employee_appconfig->save('saved_reports',serialize($reports));
	}
	
	public static function save_report($name,$url)
	{
		$CI =& get_instance();
		$CI->load->model('Employee_appconfig');
		$reports  = unserialize($CI->Employee_appconfig->get('saved_reports'));
		$key = md5($name.$url);
		$reports[$key] = array('name' => $name, 'url' => $url);
		$CI->Employee_appconfig->save('saved_reports',serialize($reports));
		return $key;
	}
	
	function get_report_details_recv($ids, $export_excel=0)
	{

		$this->db->select('receivings_items.item_variation_id,receivings_items.receiving_id, items.category_id, items.item_number, items.product_id , items.name, categories.name as category, quantity_purchased,quantity_received, serialnumber, items.description, subtotal, total, tax, profit, discount_percent, items.size as size, items.unit_price as current_selling_price, suppliers.company_name as supplier_name, suppliers.person_id as supplier_id', false);
		$this->db->from('receivings_items');
		$this->db->join('items', 'receivings_items.item_id = items.item_id', 'left');
		$this->db->join('categories', 'categories.id = items.category_id', 'left');
		$this->db->join('suppliers', 'items.supplier_id = suppliers.person_id', 'left');
		if (!empty($ids))
		{
			$sale_ids_chunk = array_chunk($ids,25);
			$this->db->group_start();
			foreach($sale_ids_chunk as $sale_ids)
			{
				$this->db->or_where_in('receivings_items.receiving_id', $sale_ids);
			}
			$this->db->group_end();
		}
		else
		{
			$this->db->where('1', '2', FALSE);		
		}		
		$qry=$this->db->get_compiled_select();
		

		$query = $this->db->query($qry);
		
		$res=$query->result_array();
		
		if($export_excel == 1)
		{
			return $res;
			exit;
		}
		$this->load->model('Category');
		
		$variation_ids = array();
		foreach($res as $key=>$drow)
		{			
			if (isset($row['variation_id']) && $row['variation_id'])
			{
				$variation_ids[] = $row['variation_id'];
			}
		}
		
		
		$this->load->model('Item_variations');
		$variation_attrs = $this->Item_variations->get_attributes($variation_ids);

		$variation_labels = array();
		
		foreach($variation_attrs as $variation_id => $attrs)
		{
			 $variation_labels[$variation_id] = implode(', ', array_column($attrs,'label'));
		}
		
		$details_data = array();
		foreach($res as $key=>$drow)
			{	
				$details_data_row = array();
				$details_data_row[] = array('data'=>$drow['name'].(isset($variation_labels[$drow['item_variation_id']]) ? ': '.$variation_labels[$drow['item_variation_id']] : ''), 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['product_id'], 'align'=>'left');
				$details_data_row[] = array('data'=>$this->Category->get_full_path($drow['category_id']), 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['size'], 'align'=>'left');
				$details_data_row[] = array('data'=>to_quantity($drow['quantity_purchased']), 'align'=>'left');
				$details_data_row[] = array('data'=>to_quantity($drow['quantity_received']), 'align'=> 'left');
				$details_data_row[] = array('data'=>to_currency($drow['subtotal']), 'align'=>'right');
				$details_data_row[] = array('data'=>to_currency($drow['total']), 'align'=>'right');
				$details_data_row[] = array('data'=>to_currency($drow['tax']), 'align'=>'right');
				
				
				$details_data_row[] = array('data'=>$drow['discount_percent'].'%', 'align'=> 'left');
				
				$details_data[$key][$drow['receiving_id']] = $details_data_row;
			}
		
		$data=array(
		"headers" => $this->getDataColumns(),
		"details_data" => $details_data
		);
		
		return $data;
	
	}
	
}

Report::$reports = array(
	'summary_sales' => array(
		'model' => 'Summary_sales',
		'settings' => array(
			'permission_action' => 'view_sales',
			'display' => 'tabular'
			),
		),
		'summary_sales_locations' => array(
			'model' => 'Summary_sales_locations',
			'settings' => array(
				'permission_action' => 'view_sales',
				'display' => 'tabular'
				),
			),
		'graphical_summary_sales'  => array(
			'model' => 'Summary_sales',
			'settings' => array(
				'permission_action' => 'view_sales',
				'display' => 'graphical'
				),
		),
		'detailed_sales' => array(
			'model' => 'Detailed_sales',
			'settings' => array(
				'permission_action' => 'view_sales',
				'display' => 'tabular'
				),
			),
			'detailed_ecommerce_sales' => array(
				'model' => 'Detailed_ecommerce_sales',
				'settings' => array(
					'permission_action' => 'view_sales',
					'display' => 'tabular'
					),
				),
			
			'summary_sales_time' => array(
				'model' => 'Summary_sales_time',
				'settings' => array(
					'permission_action' => 'view_sales',
					'display' => 'tabular'
					),
				),
				'graphical_summary_sales_time' => array(
					'model' => 'Summary_sales_time',
					'settings' => array(
						'permission_action' => 'view_sales',
						'display' => 'graphical'
						),
				 ),
 			  'summary_categories' => array(
 				 'model' => 'Summary_categories',
 				 'settings' => array(
 				 	'permission_action' => 'view_categories',
 				 	'display' => 'tabular'
 				 	),
 				 ),
 			  'graphical_summary_categories' => array(
 				 'model' => 'Summary_categories',
 				 'settings' => array(
 				 	'permission_action' => 'view_categories',
 				 	'display' => 'graphical'
 				 	),
 				 ),
		 		'closeout' => array(
		 			'model' => 'Closeout',
		 			'settings' => array(
		 				'permission_action' => 'view_closeout',
		 				'display' => 'tabular',
							'condensed' => FALSE,
		 				),
		 			),
					'closeout_condensed' => array(
		 			'model' => 'Closeout',
		 			'settings' => array(
		 				'permission_action' => 'view_closeout',
		 				'display' => 'tabular',
						'condensed' => TRUE,
		 				),
		 			),
					'summary_commissions' => array(
						'model' => 'Summary_commissions',
						'settings' => array(
							'permission_action' => 'view_commissions',
							'display' => 'tabular'
						),
					),
					'graphical_summary_commissions' => array(
						'model' => 'Summary_commissions',
						'settings' => array(
							'permission_action' => 'view_commissions',
							'display' => 'graphical'
					),
				),
				'detailed_commissions' => array(
					'model' => 'Detailed_commissions',
					'settings' => array(
						'permission_action' => 'view_commissions',
						'display' => 'tabular'
					),
				),
				'graphical_summary_customers' => array(
					'model' => 'Summary_customers',
					'settings' => array(
						'permission_action' => 'view_customers',
						'display' => 'graphical'
					),
				),
				'summary_customers' => array(
					'model' => 'Summary_customers',
					'settings' => array(
						'permission_action' => 'view_customers',
						'display' => 'tabular'
					),
				),
				'customers_series' => array(
					'model' => 'Series_customers',
					'settings' => array(
						'permission_action' => 'view_customers',
						'display' => 'tabular'
					),
				),
				'new_customers' => array(
					'model' => 'Summary_new_customers',
					'settings' => array(
						'permission_action' => 'view_customers',
						'display' => 'tabular'
					),
				),
				'summary_customers_zip' => array(
					'model' => 'Summary_customers_zip',
					'settings' => array(
						'permission_action' => 'view_customers',
						'display' => 'tabular'
					),
				),
				'graphical_customers_zip' => array(
					'model' => 'Summary_customers_zip',
					'settings' => array(
						'permission_action' => 'view_customers',
						'display' => 'graphical'
				),
			),
			'specific_customer' => array(
				'model' => 'Specific_customer',
				'settings' => array(
					'permission_action' => 'view_customers',
					'display' => 'tabular'
				),
			),
			
			'deleted_sales' => array(
				'model' => 'Deleted_sales',
				'settings' => array(
					'permission_action' => 'view_deleted_sales',
					'display' => 'tabular'
					),
				),
				'voided_transactions' => array(
					'model' => 'Voided_transactions',
					'settings' => array(
						'permission_action' => 'view_deleted_sales',
						'display' => 'tabular'
						),
					),
			
				'summary_discounts' => array(
					'model' => 'Summary_discounts',
					'settings' => array(
						'permission_action' => 'view_discounts',
						'display' => 'tabular'
					),
				),	 
			'summary_employees' => array(
				'model' => 'Summary_employees',
				'settings' => array(
					'permission_action' => 'view_employees',
					'display' => 'tabular'
				),
			),
			'graphical_summary_employees' => array(
				'model' => 'Summary_employees',
				'settings' => array(
					'permission_action' => 'view_employees',
					'display' => 'graphical'
			),
		),
		'specific_employee' => array(
			'model' => 'Specific_employee',
			'settings' => array(
				'permission_action' => 'view_employees',
				'display' => 'tabular'
			),
		),
	  'summary_expenses' => array(
		 'model' => 'Summary_expenses',
		 'settings' => array(
		 	'permission_action' => 'view_expenses',
		 	'display' => 'tabular'
		 	),
		 ),
 	  'detailed_expenses' => array(
 		 'model' => 'Detailed_expenses',
 		 'settings' => array(
 		 	'permission_action' => 'view_expenses',
 		 	'display' => 'tabular'
 		 	),
 		 ),
			'summary_giftcards' => array(
				'model' => 'Summary_giftcards',
				'settings' => array(
				'permission_action' => 'view_giftcards',
				'display' => 'tabular'
				),
			),
				 
			'giftcard_audit' => array(
				'model' => 'Giftcard_audit',
				'settings' => array(
				'permission_action' => 'view_giftcards',
				'display' => 'tabular'
					),
				),
				 
				'summary_giftcard_sales' => array(
				 'model' => 'Summary_giftcards_sales',
				 'settings' => array(
				 	'permission_action' => 'view_giftcards',
				 	'display' => 'tabular'
				 	),
				 ),
				 
			  'detailed_giftcards' => array(
				 'model' => 'Detailed_giftcards',
				 'settings' => array(
				 	'permission_action' => 'view_giftcards',
				 	'display' => 'tabular'
				 	),
				 ),

 			  'inventory_low' => array(
 				 'model' => 'Inventory_low',
 				 'settings' => array(
 				 	'permission_action' => 'view_inventory_reports',
 				 	'display' => 'tabular'
 				 	),
 				 ),
 			  'inventory_summary' => array(
 				 'model' => 'Inventory_summary',
 				 'settings' => array(
 				 	'permission_action' => 'view_inventory_reports',
 				 	'display' => 'tabular'
 				 	),
 				 ),

  			  'inventory_at_past_date' => array(
  				 'model' => 'Inventory_past_date_summary',
  				 'settings' => array(
  				 	'permission_action' => 'view_inventory_reports',
  				 	'display' => 'tabular'
  				 	),
  				 ),
  			  'detailed_inventory' => array(
  				 'model' => 'Detailed_inventory',
  				 'settings' => array(
  				 	'permission_action' => 'view_inventory_reports',
  				 	'display' => 'tabular'
  				 	),
  				),
 			  'summary_count_report' => array(
 				 'model' => 'Summary_inventory_count_report',
 				 'settings' => array(
 				 	'permission_action' => 'view_inventory_reports',
 				 	'display' => 'tabular'
 				 	),
 				 ),

 			  'detailed_count_report' => array(
 				 'model' => 'Detailed_inventory_count_report',
 				 'settings' => array(
 				 	'permission_action' => 'view_inventory_reports',
 				 	'display' => 'tabular'
 				 	),
 				 ),
 			  'expiring_inventory' => array(
 				 'model' => 'Inventory_expire_summary',
 				 'settings' => array(
 				 	'permission_action' => 'view_inventory_reports',
 				 	'display' => 'tabular'
 				 	),
 				 ),

		  'graphical_summary_item_kits' => array(
			 'model' => 'Summary_item_kits',
			 'settings' => array(
			 	'permission_action' => 'view_item_kits',
			 	'display' => 'graphical'
			 	),
			 ),
		  'summary_item_kits' => array(
			 'model' => 'Summary_item_kits',
			 'settings' => array(
			 	'permission_action' => 'view_item_kits',
			 	'display' => 'tabular'
			 	),
			 ),
		  'summary_item_kits_variance' => array(
			 'model' => 'Summary_item_kits_price_variance',
			 'settings' => array(
			 	'permission_action' => 'view_item_kits',
			 	'display' => 'tabular'
			 	),
			 ),
 		  'graphical_summary_items' => array(
 			 'model' => 'Summary_items',
 			 'settings' => array(
 			 	'permission_action' => 'view_items',
 			 	'display' => 'graphical'
 			 	),
 			 ),
 		  'summary_items' => array(
 			 'model' => 'Summary_items',
 			 'settings' => array(
 			 	'permission_action' => 'view_items',
 			 	'display' => 'tabular'
 			 	),
 			 ),
			 'top_sellers' => array(
 			 'model' => 'Summary_items_top_sellers',
 			 'settings' => array(
 			 	'permission_action' => 'view_items',
 			 	'display' => 'tabular'
 			 	),
 			 ),
			'worse_sellers' => array(
 			 'model' => 'Summary_items_worse_sellers',
 			 'settings' => array(
 			 	'permission_action' => 'view_items',
 			 	'display' => 'tabular'
 			 	),
 			 ),
 		  'summary_items_variance' => array(
 			 'model' => 'Summary_items_price_variance',
 			 'settings' => array(
 			 	'permission_action' => 'view_items',
 			 	'display' => 'tabular'
 			 	),
 			 ),
 		  'graphical_summary_manufacturers' => array(
 			 'model' => 'Summary_manufacturers',
 			 'settings' => array(
 			 	'permission_action' => 'view_manufacturers',
 			 	'display' => 'graphical'
 			 	),
 			 ),
 		  'summary_manufacturers' => array(
 			 'model' => 'Summary_manufacturers',
 			 'settings' => array(
 			 	'permission_action' => 'view_manufacturers',
 			 	'display' => 'tabular'
 			 	),
 			 ),
  		  'graphical_summary_payments' => array(
  			 'model' => 'Summary_payments',
  			 'settings' => array(
  			 	'permission_action' => 'view_payments',
  			 	'display' => 'graphical'
  			 	),
  			 ),
    		  'summary_payments' => array(
    			 'model' => 'Summary_payments',
    			 'settings' => array(
    			 	'permission_action' => 'view_payments',
    			 	'display' => 'tabular'
    			 	),
    			 ),
	    		  'summary_payments_registers' => array(
	    			 'model' => 'Summary_payments_registers',
	    			 'settings' => array(
	    			 	'permission_action' => 'view_payments',
	    			 	'display' => 'tabular'
	    			 	),
	    			 ),
   		  'detailed_payments' => array(
   			 'model' => 'Detailed_payments',
   			 'settings' => array(
   			 		'permission_action' => 'view_payments',
   			 		'display' => 'tabular'
   					)
					),
	   		  'summary_profit_and_loss' => array(
	   			 'model' => 'Summary_profit_and_loss',
	   			 'settings' => array(
	   			 	'permission_action' => 'view_profit_and_loss',
	   			 	'display' => 'tabular'
	   			 	),
	   			 ),
	   		  'detailed_profit_and_loss' => array(
	   			 'model' => 'Detailed_profit_and_loss',
	   			 'settings' => array(
	   			 	'permission_action' => 'view_profit_and_loss',
	   			 	'display' => 'tabular'
	   			 	),
	   			 ),
					 
					 'detailed_receivings' => array(
	   			 'model' => 'Detailed_receivings',
	   			 'settings' => array(
	   			 	'permission_action' => 'view_receivings',
	   			 	'display' => 'tabular'
	   			 	),
	   			 ),
					 'detailed_suspended_receivings' => array(
	   			 'model' => 'Detailed_receivings',
	   			 'settings' => array(
	   			 	'permission_action' => 'view_receivings',
	   			 	'display' => 'tabular',
						'force_suspended' => TRUE,
	   			 	),
	   			 ),
					 'deleted_receivings' => array(
	   			 'model' => 'Deleted_receivings',
	   			 'settings' => array(
	   			 	'permission_action' => 'view_receivings',
	   			 	'display' => 'tabular'
	   			 	),
	   			 ),
					 'summary_taxes_receivings' => array(
	   			 'model' => 'Summary_taxes_receivings',
	   			 'settings' => array(
	   			 	'permission_action' => 'view_receivings',
	   			 	'display' => 'tabular'
	   			 	),
	   			 ),
					 'graphical_summary_taxes_receivings' => array(
	   			 'model' => 'Summary_taxes_receivings',
	   			 'settings' => array(
	   			 	'permission_action' => 'view_receivings',
	   			 	'display' => 'graphical'
	   			 	),
	   			 ),
					 'receivings_detailed_payments' => array(
	   			 'model' => 'Detailed_payments_receivings',
	   			 'settings' => array(
	   			 	'permission_action' => 'view_receivings',
	   			 	'display' => 'tabular'
	   			 	),
	   			 ),
					 'receivings_summary_payments' => array(
	   			 'model' => 'Summary_payments_receivings',
	   			 'settings' => array(
	   			 	'permission_action' => 'view_receivings',
	   			 	'display' => 'tabular'
	   			 	),
	   			 ),
					 'receivings_graphical_summary_payments' => array(
	   			 'model' => 'Summary_payments_receivings',
	   			 'settings' => array(
	   			 	'permission_action' => 'view_receivings',
	   			 	'display' => 'graphical'
	   			 	),
	   			 ),
					 'store_account_statements' => array(
	   			 'model' => 'Store_account_statements',
	   			 'settings' => array(
	   			 	'permission_action' => 'view_store_account',
	   			 	'display' => 'tabular'
	   			 	),
	   			 ),
					 'store_account_statements_email_customer' => array(
	   			 'model' => 'Store_account_statements',
	   			 'settings' => array(
	   			 	'permission_action' => 'view_store_account',
	   			 	'display' => 'email'
	   			 	),
	   			 ),
					 'summary_store_accounts' => array(
	   			 'model' => 'Summary_store_accounts',
	   			 'settings' => array(
	   			 	'permission_action' => 'view_store_account',
	   			 	'display' => 'tabular'
	   			 	),
	   			 ),
					 'specific_customer_store_account' => array(
	   			 'model' => 'Specific_customer_store_account',
	   			 'settings' => array(
	   			 	'permission_action' => 'view_store_account',
	   			 	'display' => 'tabular'
	   			 	),
	   			 ),
					 'store_account_activity' => array(
	   			 'model' => 'Store_account_activity',
	   			 'settings' => array(
	   			 	'permission_action' => 'view_store_account',
	   			 	'display' => 'tabular'
	   			 	),
	   			 ),
					 'store_account_activity_summary' => array(
	   			 'model' => 'Store_account_activity_summary',
	   			 'settings' => array(
	   			 	'permission_action' => 'view_store_account',
	   			 	'display' => 'tabular'
	   			 	),
	   			 ),
					 'store_account_outstanding' => array(
	   			 'model' => 'Store_account_outstanding',
	   			 'settings' => array(
	   			 	'permission_action' => 'view_store_account',
	   			 	'display' => 'tabular'
	   			 	),
	   			 ),
					 'supplier_store_account_statements' => array(
	   			 'model' => 'Store_account_statements_supplier',
	   			 'settings' => array(
	   			 	'permission_action' => 'view_store_account_suppliers',
	   			 	'display' => 'tabular'
	   			 	),
	   			 ),
					 'store_account_statements_email_supplier' => array(
	   			 'model' => 'Store_account_statements_supplier',
	   			 'settings' => array(
	   			 	'permission_action' => 'view_store_account_suppliers',
	   			 	'display' => 'email'
	   			 	),
	   			 ),
					 'supplier_summary_store_accounts' => array(
	   			 'model' => 'Summary_store_accounts_supplier',
	   			 'settings' => array(
	   			 	'permission_action' => 'view_store_account_suppliers',
	   			 	'display' => 'tabular'
	   			 	),
	   			 ),
					 'supplier_specific_store_account' => array(
	   			 'model' => 'Specific_supplier_store_account',
	   			 'settings' => array(
	   			 	'permission_action' => 'view_store_account_suppliers',
	   			 	'display' => 'tabular'
	   			 	),
	   			 ),
					 'supplier_store_account_activity' => array(
	   			 'model' => 'Store_account_activity_supplier',
	   			 'settings' => array(
	   			 	'permission_action' => 'view_store_account_suppliers',
	   			 	'display' => 'tabular'
	   			 	),
	   			 ),
					 'supplier_store_account_activity_summary' => array(
	   			 'model' => 'Store_account_activity_supplier_summary',
	   			 'settings' => array(
	   			 	'permission_action' => 'view_store_account_suppliers',
	   			 	'display' => 'tabular'
	   			 	),
	   			 ),
					 'supplier_store_account_outstanding' => array(
	   			 'model' => 'Store_account_outstanding_supplier',
	   			 'settings' => array(
	   			 	'permission_action' => 'view_store_account_suppliers',
	   			 	'display' => 'tabular'
	   			 	),
	   			 ),
    		  'summary_suppliers' => array(
    			 'model' => 'Summary_suppliers',
    			 'settings' => array(
    			 	'permission_action' => 'view_suppliers',
    			 	'display' => 'tabular'
    			 	),
    			 ),
    		  'graphical_summary_suppliers' => array(
    			 'model' => 'Summary_suppliers',
    			 'settings' => array(
    			 		'permission_action' => 'view_suppliers',
    			 		'display' => 'graphical'
    					)
 					),
    		  'summary_suppliers_receivings' => array(
    			 'model' => 'Summary_suppliers_receivings',
    			 'settings' => array(
    			 	'permission_action' => 'view_suppliers',
    			 	'display' => 'tabular'
    			 	),
    			 ),
    		  'graphical_summary_suppliers_receivings' => array(
    			 'model' => 'Summary_suppliers_receivings',
    			 'settings' => array(
    			 		'permission_action' => 'view_suppliers',
    			 		'display' => 'graphical'
    					)
 					),
					'specific_supplier' =>  array(
    			 'model' => 'Specific_supplier',
    			 'settings' => array(
    			 	'permission_action' => 'view_suppliers',
    			 	'display' => 'tabular'
    			 	),
    			 ),
 					'specific_supplier_summary' =>  array(
     			 'model' => 'Specific_supplier_summary',
     			 'settings' => array(
     			 	'permission_action' => 'view_suppliers',
     			 	'display' => 'tabular'
     			 	),
     			 ),
 					'specific_supplier_receivings' =>  array(
     			 'model' => 'Specific_supplier_receiving',
     			 'settings' => array(
     			 	'permission_action' => 'view_suppliers',
     			 	'display' => 'tabular'
     			 	),
     			 ),
  					'detailed_suspended_sales' =>  array(
      			 'model' => 'Detailed_suspended_sales',
      			 'settings' => array(
      			 	'permission_action' => 'view_suspended_sales',
      			 	'display' => 'tabular'
      			 	),
      			 ),

				   'layaway_statements' =>  array(
					'model' => 'Layaway_statements',
					'settings' => array(
						'permission_action' => 'view_suspended_sales',
						'display' => 'tabular'
						),
					),

					'layaway_statements_email_customer' => array(
						'model' => 'Layaway_statements',
						'settings' => array(
							'permission_action' => 'view_suspended_sales',
							'display' => 'email'
							),
						),

 	     		  'summary_tags' => array(
 	     			 'model' => 'Summary_tags',
 	     			 'settings' => array(
 	     			 	'permission_action' => 'view_tags',
 	     			 	'display' => 'tabular'
 	     			 	),
 	     			 ),
 	     		  'graphical_summary_tags' => array(
 	     			 'model' => 'Summary_tags',
 	     			 'settings' => array(
 	     			 		'permission_action' => 'view_tags',
 	     			 		'display' => 'graphical'
 	     					)
 	  					),
		     		  'summary_taxes' => array(
		     			 'model' => 'Summary_taxes',
		     			 'settings' => array(
		     			 	'permission_action' => 'view_taxes',
		     			 	'display' => 'tabular'
		     			 	),
		     			 ),
		     		  'graphical_summary_taxes' => array(
		     			 'model' => 'Summary_taxes',
		     			 'settings' => array(
		     			 		'permission_action' => 'view_taxes',
		     			 		'display' => 'graphical'
		     					)
		  					),
			     		  'summary_tiers' => array(
			     			 'model' => 'Summary_tiers',
			     			 'settings' => array(
			     			 	'permission_action' => 'view_tiers',
			     			 	'display' => 'tabular'
			     			 	),
			     			 ),
 			     		  'detailed_register_log' => array(
 			     			 'model' => 'Detailed_register_log',
 			     			 'settings' => array(
 			     			 	'permission_action' => 'view_register_log',
 			     			 	'display' => 'tabular'
 			     			 	),
 			     			 ),
			     		  'summary_timeclock' => array(
			     			 'model' => 'Summary_timeclock',
			     			 'settings' => array(
			     			 	'permission_action' => 'view_timeclock',
			     			 	'display' => 'tabular'
			     			 	),
			     			 ),
 			     		  'detailed_timeclock' => array(
 			     			 'model' => 'Detailed_timeclock',
 			     			 'settings' => array(
 			     			 	'permission_action' => 'view_timeclock',
 			     			 	'display' => 'tabular'
 			     			 	),
 			     			 ),
								 
								 'time_off' => array(
			     			 'model' => 'Detailed_time_off',
			     			 'settings' => array(
			     			 	'permission_action' => 'view_timeclock',
			     			 	'display' => 'tabular'
			     			 	),
			     			 ),
 			     		  'summary_registers' => array(
 			     			 'model' => 'Summary_registers',
 			     			 'settings' => array(
 			     			 	'permission_action' => 'view_registers',
 			     			 	'display' => 'tabular'
 			     			 	),
 			     			 ),
  			     		  'graphical_summary_registers' => array(
  			     			 'model' => 'Summary_registers',
  			     			 'settings' => array(
  			     			 	'permission_action' => 'view_registers',
  			     			 	'display' => 'graphical'
  			     			 	),
  			     			 ),
						  		  'graphical_summary_items_receivings' => array(
						  			 'model' => 'Summary_items_receivings',
						  			 'settings' => array(
						  			 	'permission_action' => 'view_receivings',
						  			 	'display' => 'graphical'
						  			 	),
						  			 ),
 						  		  'summary_items_receivings' => array(
 						  			 'model' => 'Summary_items_receivings',
 						  			 'settings' => array(
 						  			 	'permission_action' => 'view_receivings',
 						  			 	'display' => 'tabular'
 						  			 	),
 						  			 ),
 						  		  'detailed_deliveries' => array(
 						  			 'model' => 'Detailed_deliveries',
 						  			 'settings' => array(
 						  			 	'permission_action' => 'view_deliveries',
 						  			 	'display' => 'tabular'
 						  			 	),
 						  			 ),
										 'summary_price_rules' => array(
 						  			 'model' => 'Summary_price_rules',
 						  			 'settings' => array(
 						  			 	'permission_action' => 'view_price_rules',
 						  			 	'display' => 'tabular'
 						  			 	),
 						  			 ),
										 'transfers' => array(
							   			 'model' => 'Detailed_transfers',
							   			 'settings' => array(
							   			 	'permission_action' => 'view_receivings',
							   			 	'display' => 'tabular'
							   			 	),
										 ),
										 'summary_appointments' => array(
 						  			 'model' => 'Summary_appointments',
 						  			 'settings' => array(
 						  			 	'permission_action' => 'view_appointments',
 						  			 	'display' => 'tabular'
 						  			 	),
 						  			 ),
										 'detailed_appointments' => array(
 						  			 'model' => 'Detailed_appointments',
 						  			 'settings' => array(
 						  			 	'permission_action' => 'view_appointments',
 						  			 	'display' => 'tabular'
 						  			 	),
 						  			 ),
										 'summary_sales_day_of_week' => array(
 						  			 'model' => 'Summary_sales_day_of_week',
 						  			 'settings' => array(
 						  			 	'permission_action' => 'view_sales',
 						  			 	'display' => 'tabular'
 						  			 	),
 						  			 ),
										 'summary_tips' => array(
 						  			 'model' => 'Summary_tips',
 						  			 'settings' => array(
 						  			 	'permission_action' => 'view_sales',
 						  			 	'display' => 'tabular'
 						  			 	),
 						  			 ),
										 
										 'detailed_last_4_cc' => array(
	 						  			 'model' => 'Detailed_last_4_cc',
	 						  			 'settings' => array(
	 						  			 	'permission_action' => 'view_sales',
	 						  			 	'display' => 'tabular'
	 						  			 	),
										 ),
 									 	'item_price_history' => array(
 									 		'model' => 'Item_price_history',
 									 		'settings' => array(
 									 			'permission_action' => 'view_items',
 									 			'display' => 'tabular'
 									 			),
 									 		),
										 	'item_kit_price_history' => array(
										 		'model' => 'Item_kit_price_history',
										 		'settings' => array(
										 			'permission_action' => 'view_item_kits',
										 			'display' => 'tabular'
										 			),
										 		),
												'serial_numbers_sold' => array(
										 		'model' => 'Serial_numbers_sold',
										 		'settings' => array(
										 			'permission_action' => 'view_items',
										 			'display' => 'tabular'
										 			),
										 		),
												'serial_number_history' => array(
										 		'model' => 'Serial_number_history',
										 		'settings' => array(
										 			'permission_action' => 'view_items',
										 			'display' => 'tabular'
										 			),
										 		),
												'detailed_damaged_items' =>  array(
										 		'model' => 'Detailed_damaged_items',
										 		'settings' => array(
										 			'permission_action' => 'view_inventory_reports',
										 			'display' => 'tabular'
										 			),
										 		),
												
												'summary_categories_receivings' =>  array(
										 		'model' => 'Summary_categories_receivings',
										 		'settings' => array(
										 			'permission_action' => 'view_receivings',
										 			'display' => 'tabular'
										 			),
												 ),
												 'cheapest_supplier' =>  array(
													'model' => 'Cheapest_supplier',
													'settings' => array(
														'permission_action' => 'view_receivings',
														'display' => 'tabular'
													),
												),
												'summary_non_taxable_customers' =>  array(
													'model' => 'Summary_non_taxable_customers',
													'settings' => array(
														'permission_action' => 'view_customers',
														'display' => 'tabular'
													),
												),
												'summary_journal' =>  array(
													'model' => 'Summary_journal',
													'settings' => array(
														'permission_action' => 'view_sales',
														'display' => 'tabular'
													),
												),
									 ); 
?>