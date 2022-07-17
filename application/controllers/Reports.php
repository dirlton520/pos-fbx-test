<?php
require_once ("Secure_area.php");
require_once (APPPATH."traits/creditcardProcessingTrait.php");

class Reports extends Secure_area 
{	
	use creditcardProcessingTrait;
	
	function __construct()
	{
		parent::__construct('reports');
		$this->has_profit_permission = $this->Employee->has_module_action_permission('reports','show_profit',$this->Employee->get_logged_in_employee_info()->person_id);
		$this->has_cost_price_permission = $this->Employee->has_module_action_permission('reports','show_cost_price',$this->Employee->get_logged_in_employee_info()->person_id);
		//Need to query database directly as load config hook doesn't happen until after constructor
		$this->decimals = $this->Appconfig->get_raw_number_of_decimals();
		$this->decimals = $this->decimals !== NULL && $this->decimals!= '' ? $this->decimals : 2;
		
		require_once (APPPATH.'models/reports/Report.php');
		$this->load->helper('report');
		$this->lang->load('reports');
		$this->lang->load('sales');
		$this->lang->load('module');
		$this->load->model('Sale');
	}
	/* function for save preferences */
	function save_column_prefs_reports() {
		$this->load->model('Employee_appconfig');
		if ($this->input->post('columns'))
		{
			$this->Employee_appconfig->save($this->input->post('keyname'),serialize($this->input->post('columns')));
		}
		else
		{
			$this->Employee_appconfig->delete($this->input->post('keyname'));			
		}
	}
	/* end function for preferences */
	function generate($report)
	{

		$report_model = Report::get_report_model($report);

		$this->check_action_permission($report_model->settings['permission_action']);
		$output_data = array();
		$get = $this->input->get();
		
		if (!empty($get))
		{ 
			if ($this->input->get('report_type') == 'simple')
			{
				$dates = simple_date_range_to_date($this->input->get('report_date_range_simple'), (boolean)$this->input->get('with_time'),(boolean)$this->input->get('end_date_end_of_day')); 
				$_GET['start_date'] = $dates['start_date'];
				$_GET['end_date'] = $dates['end_date'];
			
			}
		
			if ($this->input->get('report_type_compare') == 'simple')
			{
				$dates = simple_date_range_to_date($this->input->get('report_date_range_simple_compare'), (boolean)$this->input->get('compare_with_time'),(boolean)$this->input->get('compare_end_date_end_of_day')); 
				$_GET['start_date_compare'] = $dates['start_date'];
				$_GET['end_date_compare'] = $dates['end_date'];
			}
	
			$report_model->setParams($this->input->get());
			$output_data = $report_model->getOutputData();
			$this->load->model('Employee_appconfig');
			$output_data['preferences'] = $this->Employee_appconfig->get($this->uri->segment(3));
			$output_data['headersshow'] = '';
			if(isset($output_data['headers']['summary'])) {
				foreach($output_data['headers']['summary'] as $keys => $col_key) {
					$output_data['headers']['summary'][$keys]['column_id'] = 'id_'.md5($col_key['data']);
					$output_data['headers']['summary'][$keys]['view'] = 1;
				}
				$headersnew = array();
				$cols = unserialize($output_data['preferences']);
				if(!empty($cols)) {
					foreach($output_data['headers']['summary'] as $head) {
						if(!in_array($head['column_id'],$cols)) {
							$head['view'] = 0;
							$headersnew[] = $head;
						}else {
							$head['view'] = 1;
							$headersnew[] = $head;
						}
					}
					$output_data['headersshow'] = $headersnew;
				}else {
					$output_data['headersshow'] = $output_data['headers']['summary'];
				}
			}elseif(isset($output_data['headers'])) {
				foreach($output_data['headers'] as $keys => $col_key) {
					$output_data['headers'][$keys]['column_id'] = 'id_'.md5($col_key['data']);
					$output_data['headers'][$keys]['view'] = 1;
				}
				$headersnew = array();
				$cols = unserialize($output_data['preferences']);
				if(!empty($cols)) {
					foreach($output_data['headers'] as $head) {
						if(!in_array($head['column_id'],$cols)) {
							$head['view'] = 0;
							$headersnew[] = $head;
						}else {
							$head['view'] = 1;
							$headersnew[] = $head;
						}
					}
					$output_data['headersshow'] = $headersnew;
				}else {
					$output_data['headersshow'] = $output_data['headers'];
				}
			}
		} 
		$data = array_merge(array('input_data' => $report_model->getInputData()),array('output_data' => $output_data),array('key' => $this->input->get('key'),'report' => $report));
		
		$this->load->view('reports/generate',$data);
		
	}
	
	//Initial report listing screen
	function index()
	{
		$credit_card_processor = $this->_get_cc_processor();
		$cc_processor_class_name = FALSE;
		if ($credit_card_processor)
		{
			$cc_processor_class_name = strtoupper(get_class($credit_card_processor));
		}
		$this->load->view("reports/listing",array('cc_processor_class_name' => $cc_processor_class_name));	
	}
		
	// Sales Generator Reports 
	function sales_generator() 
	{			
		$this->load->model('Category');
		$this->load->model('reports/Sales_generator');
		$model = $this->Sales_generator;
		
		$this->check_action_permission('view_sales_generator');
		
		if ($this->input->get('act') == 'autocomplete') 
		{ // Must return a json string
			if ($this->input->get('w') != '') { // From where should we return data
				if ($this->input->get('term') != '') { // What exactly are we searchin
					
					//allow parallel searchs to improve performance.
					session_write_close();
					
					switch($this->input->get('w')) {
						case 'customers': 
						$this->load->model('Customer');
							$t = $this->Customer->search($this->input->get('term'),'', 0, 100, 0, 'last_name', 'asc')->result_object();
							$tmp = array();
							foreach ($t as $k=>$v) { 
								$display_name = $v->last_name.", ".$v->first_name;
								
								if ($v->email)
								{
									$display_name.=" - ".$v->email;
								}

								if ($v->phone_number)
								{
									$display_name.=" - ".$v->phone_number;
								}
								
								$tmp[$k] = array('id'=>$v->person_id, 'name'=>$display_name); 
							}
							die(json_encode($tmp));
						break;
						case 'employees':
						case 'salesPerson':
							$t = $this->Employee->search($this->input->get('term'), 0, 100, 0, 'last_name', 'asc')->result_object();
							$tmp = array();
							foreach ($t as $k=>$v) { $tmp[$k] = array('id'=>$v->person_id, 'name'=>$v->last_name.", ".$v->first_name." - ".$v->email); }
							die(json_encode($tmp));
						break;
						case 'itemsCategory':
							$this->load->model('Category');
							$t = $this->Category->get_search_suggestions($this->input->get('term'));
							$tmp = array();
							foreach ($t as $k=>$v) { $tmp[$k] = array('id'=>$v['id'], 'name'=>$v['label']); }
							die(json_encode($tmp));
						break;
						case 'manufacturer':
							$this->load->model('Manufacturer');
							$t = $this->Manufacturer->get_manufacturer_suggestions($this->input->get('term'));
							$tmp = array();
							foreach ($t as $k=>$v) { $tmp[$k] = array('id'=>$v['id'], 'name'=>$v['label']); }
							die(json_encode($tmp));
						break;
						case 'suppliers':
							$this->load->model('Supplier');
							$t = $this->Supplier->search($this->input->get('term'),0, 100, 0, 'last_name', 'asc')->result_object();
							$tmp = array();
							foreach ($t as $k=>$v) { $tmp[$k] = array('id'=>$v->person_id, 'name'=>$v->last_name.", ".$v->first_name." - ".$v->company_name." - ".$v->email); }
							die(json_encode($tmp));
						break;
						case 'itemsKitName':
							$this->load->model('Item_kit');
							$t = $this->Item_kit->search($this->input->get('term'), 0,FALSE, 100, 0, 'name', 'asc')->result_object();
							$tmp = array();
							foreach ($t as $k=>$v) { $tmp[$k] = array('id'=>$v->item_kit_id, 'name'=>$v->name." / #".$v->item_kit_number); }
							die(json_encode($tmp));
						break;
						case 'itemsName':
						$this->load->model('Item');
						$t = $this->Item->get_item_search_suggestions($this->input->get('term'));
						$tmp = array();
						foreach ($t as $v) { $tmp[] = array('id'=>$v['value'], 'name'=>$v['label']); }
						die(json_encode($tmp));
						break;
						case 'tierName':
						$this->load->model('Tier');
						$t = $this->Tier->get_tier_search_suggestions($this->input->get('term'));
						$tmp = array();
						foreach ($t as $v) { $tmp[] = array('id'=>$v['value'], 'name'=>$v['label']); }
						die(json_encode($tmp));
						break;
						
						case 'itemVariationNumber':
						$this->load->model('Item');
						$t = $this->Item->get_item_search_suggestions($this->input->get('term'));
						$tmp = array();
						
						foreach ($t as $v) 
						{ 
							if(strpos($v['value'], '#'))
							{
								list($item_id,$variation_id) = explode('#',$v['value']);
								$tmp[] = array('id'=>$variation_id, 'name'=>$v['label'].': '.$v['attributes']); 								
							}
						}
						die(json_encode($tmp));
						break;
						case 'paymentType':
							$t = array(lang('common_cash'),lang('common_check'), lang('common_giftcard'),lang('common_debit'),lang('common_credit'));
							
							if($this->config->item('customers_store_accounts')) 
							{
								$t[] =lang('common_store_account');
							}
							
							foreach($this->Appconfig->get_additional_payment_types() as $additional_payment_type)
							{
								$t[] = $additional_payment_type;
							}

							$tmp = array();
							foreach ($t as $k => $v) { $tmp[$k] = array('id'=>$v, 'name'=>$v); }
							die(json_encode($tmp));
						break;		
					}
				} else {
					die;	
				}
			} else {
				die(json_encode(array('value' => 'No such data found!')));
			}
		}		
		
		$this->load->helper('report');
		$data = Report::get_common_report_input_data();
		$data["title"] = lang('reports_sales_generator');
		$data["subtitle"] = lang('reports_sales_report_generator');
		
		$setValues = array(	'report_type' => '', 'sreport_date_range_simple' => '', 
										'start_month' => date("m"), 'start_day' => date('d'), 'start_year' => date("Y"),
										'end_month' => date("m"), 'end_day' => date('d'), 'end_year' => date("Y"),
										'matchType' => '',
										'matched_items_only' => FALSE,
										'tax_exempt' => FALSE,
										);
										
		foreach ($setValues as $k => $v) { 
			if (empty($v) && !isset($data[$k])) { 
				$data[$k] = ''; 		
			} else {
				$data[$k] = $v;
			}
		}		
		if ($this->input->get('generate_report')) { // Generate Custom Raport
			$data['report_type'] = $this->input->get('report_type');
			$data['sreport_date_range_simple'] = $this->input->get('report_date_range_simple');
			

			
			if ($data['report_type'] == 'simple') {
				
				$dates = simple_date_range_to_date($this->input->get('report_date_range_simple'), $this->input->get('with_time'),$this->input->get('end_date_end_of_day')); 
				
				list($data['start_year'], $data['start_month'], $data['start_day']) = explode("-", $dates['start_date']);
				list($data['end_year'], $data['end_month'], $data['end_day']) = explode("-", $dates['end_date']);
				
				
			
			}
			else
			{
				list($data['start_year'], $data['start_month'], $data['start_day']) = explode("-", $this->input->get('start_date'));
				list($data['end_year'], $data['end_month'], $data['end_day']) = explode("-", $this->input->get('end_date'));
				
				
			}
			$data['matchType'] = $this->input->get('matchType');
			$data['matched_items_only'] = $this->input->get('matched_items_only') ? TRUE : FALSE;
			$data['tax_exempt'] = $this->input->get('tax_exempt') ? TRUE : FALSE;

			$data['field'] = $this->input->get('field');
			$data['condition'] = $this->input->get('condition');
			$data['value'] = $this->input->get('value');
			
			$data['prepopulate'] = array();
			
			$field = $this->input->get('field');
			$condition = $this->input->get('condition');
			$value = $this->input->get('value');
			
			$tmpData = array();
			foreach ($field as $a => $b) {
				@$uData = explode(",",$value[$a]);
				$tmp = $tmpID = array();
				switch ($b) {
					case '1': // Customer
						$this->load->model('Customer');
						$t = $this->Customer->get_multiple_info($uData)->result_object();
						foreach ($t as $k=>$v) { $tmpID[] = $v->person_id; $tmp[$k] = array('id'=>$v->person_id, 'name'=>$v->last_name.", ".$v->first_name." - ".$v->email); }
					break;
					case '2': // Item Serial Number
						$tmpID[] = $value[$a];
					break;
					case '3': // Employees
						$t = $this->Employee->get_multiple_info($uData)->result_object();
						foreach ($t as $k=>$v) { $tmpID[] = $v->person_id;  $tmp[$k] = array('id'=>$v->person_id, 'name'=>$v->last_name.", ".$v->first_name." - ".$v->email); }
					break;
					case '4': // Items Category
					$this->load->model('Category');
					$t = $this->Category->get_multiple_info($uData)->result_object();
					foreach ($t as $k=>$v) { $tmpID[] = $v->id;  $tmp[$k] = array('id'=>$v->id, 'name'=>$v->name); }
					break;
					case '5': // Suppliers 
						$this->load->model('Supplier');
						$t = $this->Supplier->get_multiple_info($uData)->result_object();
						foreach ($t as $k=>$v) { $tmpID[] = $v->person_id;  $tmp[$k] = array('id'=>$v->person_id, 'name'=>$v->last_name.", ".$v->first_name." - ".$v->company_name." - ".$v->email); }
					break;
					case  '6': // Sale Type
						$tmpID[] = $condition[$a];
					break;
					case '7': // Sale Amount
						$tmpID[] = $value[$a];
					break;
					case '8': // Item Kits
						$this->load->model('Item_kit');
						$t = $this->Item_kit->get_multiple_info($uData)->result_object();
						foreach ($t as $k=>$v) { $tmpID[] = $v->item_kit_id;  $tmp[$k] = array('id'=>$v->item_kit_id, 'name'=>$v->name." / #".$v->item_kit_number); }
					break;
					case '9': // Items Name
						$this->load->model('Item');
						$t = $this->Item->get_multiple_info($uData)->result_object();
						foreach ($t as $k => $v) { $tmpID[] = $v->item_id;  $tmp[$k] = array('id'=>$v->item_id, 'name'=>$v->name); }
					break;				
					case '10': // SaleID
						if(strpos(strtolower($value[$a]), strtolower($this->config->item('sale_prefix'))) !== FALSE)
						{							
							$value[$a] =(int)substr(strtolower($value[$a]), strpos(strtolower($value[$a]),$this->config->item('sale_prefix').' ') + strlen(strtolower($this->config->item('sale_prefix')).' '));	
						}
						$tmpID[] = $value[$a];
					break;
					case '11': // Payment type
						foreach ($uData as $k=>$v) { $tmpID[] = $v;  $tmp[$k] = array('id'=>$v, 'name'=>$v); }
					break;
					
					case '12': // Sale Item Description
						$tmpID[] = $value[$a];
					break;
					case '13': // Employees
						$t = $this->Employee->get_multiple_info($uData)->result_object();
						foreach ($t as $k=>$v) { $tmpID[] = $v->person_id;  $tmp[$k] = array('id'=>$v->person_id, 'name'=>$v->last_name.", ".$v->first_name." - ".$v->email); }
					break;
					case '15': // Manufactor
						$this->load->model('Manufacturer');
						$t = $this->Manufacturer->get_multiple_info($uData)->result_object();
						foreach ($t as $k=>$v) { $tmpID[] = $v->id;  $tmp[$k] = array('id'=>$v->id, 'name'=>$v->name); }
						case '16': // Sale Item Description
							$tmpID[] = $value[$a];
						break;
					case '18': // Tier ID
					$this->load->model('Tier');
					$t = $this->Tier->get_multiple_info($uData)->result_object();
					foreach ($t as $k => $v) { $tmpID[] = $v->id;  $tmp[$k] = array('id'=>$v->id, 'name'=>$v->name); }
						break;
					case '17': //Item Variation Number
					$this->load->model('Item_variations');
					$t = $this->Item_variations->get_multiple_info($uData);
					$counter = 0;
					foreach ($t as $k => $v) 
					{ 
						$tmpID[] = $k; 
						$item_info = $this->Item_variations->get_item_info_for_variation($k);
						$tmp[$counter] = array('id'=>$k, 'name'=>$item_info->name.' '.$v['label']); 
						$counter++;
						}
						break;
					break;
					
					default: // Custom fields
						@$tmpID[] = $value[$a];
					break;
									
					
				}
				$data['prepopulate']['field'][$a][$b] = $tmp;			

				// Data for sql
				@$tmpData[] = array('f' => $b, 'o' => $condition[$a], 'i' => $tmpID);
			}
			
			$params['matchType'] = $data['matchType'];
			$params['matched_items_only'] = $data['matched_items_only'];
			$params['tax_exempt'] = $data['tax_exempt'];
			$params['ops'] = array(
				1 => " = 'xx'", 
				2 => " != 'xx'", 
				5 => " IN ('xx')", 
				6 => " NOT IN ('xx')", 
				7 => " > xx", 
				8 => " < xx", 
				9 => " = xx",
				10 => '', // Sales
				11 => '', // Returns
				14 => " IN ('xx')", 
				15 => " IN ('xx')", 
				16 => " LIKE '%xx%'", 
				17 => " NOT LIKE '%xx%'", 
				
			);

			$params['tables'] = array(
				1 => 'sales.customer_id', // Customers
				2 => 'sales_items.serialnumber', // Item Sale Serial number
				3 => 'sales.employee_id', // Employees
				4 => 'items.category_id', // Item Category
				5 => 'suppliers.person_id', // Suppliers
				6 => '', // Sale Type
				7 => '', // Sale Amount
				8 => 'item_kits.item_kit_id', // Item Kit Name
				9 => 'items.item_id', // Item Name
				10 => 'sales.sale_id', // Sale ID
				11 => 'sales.payment_type', // Payment Type
				12 => 'sales_items.description', // Item Sale Serial number
				13 => 'sales.sold_by_employee_id', // Item Sale Serial number
				15 => '', // Manufactor
				16 => 'sales.comment', // sale comment
				17 => 'sales_items.item_variation_id', // variation
				18 => 'sales.tier_id', // tier 
				19 => 'sales.custom_field_1_value',
				20 => 'sales.custom_field_2_value',
				21 => 'sales.custom_field_3_value',
				22 => 'sales.custom_field_4_value',
				23 => 'sales.custom_field_5_value',
				24 => 'sales.custom_field_6_value',
				25 => 'sales.custom_field_7_value',
				26 => 'sales.custom_field_8_value',
				27 => 'sales.custom_field_9_value',
				28 => 'sales.custom_field_10_value',
				29 => 'phppos_items.tags',
			);			
			$params['values'] = $tmpData;
			$params['offset'] = $this->input->get('per_page')  ? $this->input->get('per_page') : 0;
			$offset=$params['offset'];
			$params['export_excel'] = $this->input->get('export_excel') ? 1 : 0;
			
			$export_excel=$params['export_excel'];
			$model->setParams($params);		
			$model->setCats();
			
			// Sales Interval Reports
			$interval = 
			array(
				'start_date' => $data['start_year'].'-'.$data['start_month'].'-'.$data['start_day'], 
				'end_date' => $data['end_year'].'-'.$data['end_month'].'-'.$data['end_day']
				);							
							
			$this->load->model('Sale');
			$config = array();
			
			//Remove per_page from url so we don't have it duplicated
			$config['base_url'] = preg_replace('/&per_page=[0-9]*/','',current_url());
			$config['total_rows'] = $model->getTotalRows();
			$config['per_page'] = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20; 
			$config['page_query_string'] = TRUE;
			$this->load->library('pagination');$this->pagination->initialize($config);
			
			$tabular_data = array();
			$report_data = $model->getData();
			
			$summary_data = array();
			$details_data = array();
			
			$location_count = count(Report::get_selected_location_ids());			
			
			foreach(isset($export_excel) == 1 && isset($report_data['summary']) ? $report_data['summary']:$report_data as $key=>$row)
			{
				$summary_data_row = array();				
				$summary_data_row[] = array('data'=>anchor('sales/receipt/'.$row['sale_id'], '<i class="ion-printer"></i>', array('target' => '_blank')).' '.anchor('sales/edit/'.$row['sale_id'], '<i class="ion-document-text"></i>', array('target' => '_blank')).' '.anchor('sales/edit/'.$row['sale_id'], lang('common_edit').' '.$row['sale_id'], array('target' => '_blank')).'<br />'.anchor('sales/clone_sale/'.$row['sale_id'], lang('common_clone'), 
			array('target' => '_blank','class'=>'hidden-print')), 'align'=>'left', 'sale_id' => $row['sale_id']);				
				if ($location_count > 1)
				{
					$summary_data_row[] = array('data'=>$row['location_name'], 'align'=>'left');
				}
				
				$summary_data_row[] = array('data'=>date(get_date_format().'-'.get_time_format(), strtotime($row['sale_time'])), 'align'=>'left');
				$summary_data_row[] = array('data'=>$row['register_name'], 'align'=>'left');
				$summary_data_row[] = array('data'=>to_quantity($row['items_purchased']), 'align'=>'center');
				$summary_data_row[] = array('data'=>$row['employee_name'].($row['sold_by_employee'] && $row['sold_by_employee'] != $row['employee_name'] ? '/'. $row['sold_by_employee']: ''), 'align'=>'left');
				$summary_data_row[] = array('data'=>$row['customer_name'].(isset($row['account_number']) && $row['account_number'] ? ' ('.$row['account_number'].')' : ''), 'align'=>'left');
				$summary_data_row[] = array('data'=>to_currency($row['subtotal']), 'align'=>'right');
				$summary_data_row[] = array('data'=>to_currency($row['total']), 'align'=>'right');
				$summary_data_row[] = array('data'=>to_currency($row['tax']), 'align'=>'right');
				
				if($this->has_profit_permission)
				{
					$summary_data_row[] = array('data'=>to_currency($row['profit']), 'align'=>'right');
				}
								
				$summary_data_row[] = array('data'=>$row['payment_type'], 'align'=>'right');
				$summary_data_row[] = array('data'=>$row['comment'], 'align'=>'right');
				$summary_data_row[] = array('data'=>$row['discount_reason'], 'align'=>'right');
			
			  for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) 
				{
					$custom_field = $this->Sale->get_custom_field($k);
					if($custom_field !== FALSE)
					{
						if ($this->Sale->get_custom_field($k,'type') == 'checkbox')
						{
							$format_function = 'boolean_as_string';
						}
						elseif($this->Sale->get_custom_field($k,'type') == 'date')
						{
							$format_function = 'date_as_display_date';				
						}
						elseif($this->Sale->get_custom_field($k,'type') == 'email')
						{
							$format_function = 'strsame';					
						}
						elseif($this->Sale->get_custom_field($k,'type') == 'url')
						{
							$format_function = 'strsame';					
						}
						elseif($this->Sale->get_custom_field($k,'type') == 'phone')
						{
							$format_function = 'strsame';					
						}
						elseif($this->Sale->get_custom_field($k,'type') == 'image')
						{
							$this->load->helper('url');
							$format_function = 'file_id_to_image_thumb';					
						}
						elseif($this->Sale->get_custom_field($k,'type') == 'file')
						{
							$this->load->helper('url');
							$format_function = 'file_id_to_download_link';					
						}
						else
						{
							$format_function = 'strsame';
						}
					
						$summary_data_row[] = array('data'=>$format_function($row["custom_field_${k}_value"]), 'align'=>'right');					
					}
				}
			
				
				$summary_data[$key] = $summary_data_row;
				
				if($export_excel == 1)
				{
				foreach($report_data['details'][$key] as $drow)
				{
					$details_data_row = array();
				
					$details_data_row[] = array('data'=>$drow['item_number'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['item_product_id'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['item_name'], 'align'=>'left');
				
				$details_data_row[] = array('data'=>$this->Category->get_full_path($drow['category_id']), 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['size'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['serialnumber'], 'align'=>'left');
				$details_data_row[] = array('data'=>character_limiter($drow['description'],150), 'align'=>'left');
				$details_data_row[] = array('data'=>to_currency($drow['current_selling_price']), 'align'=>'left');
				$details_data_row[] = array('data'=>to_quantity($drow['quantity_purchased']), 'align'=>'left');
				$details_data_row[] = array('data'=>to_currency($drow['subtotal']), 'align'=>'right');
				$details_data_row[] = array('data'=>to_currency($drow['total']), 'align'=>'right');
				$details_data_row[] = array('data'=>to_currency($drow['tax']), 'align'=>'right');
				
					if($this->has_profit_permission)
					{
						$details_data_row[] = array('data'=>to_currency($drow['profit']), 'align'=>'right');					
					}
					
					if($this->has_cost_price_permission)
					{
						$details_data_row[] = array('data'=>to_currency($drow['cost_prices']), 'align'=>'right');
					}
									
					$details_data_row[] = array('data'=>$drow['discount_percent'].'%', 'align'=>'left');
					$details_data[$key][] = $details_data_row;
					
				}
			}
			}
			$reportdata = array(
				"title" => lang('reports_sales_generator'),
				"subtitle" => lang('reports_sales_report_generator')." - ".date(get_date_format(), strtotime($interval['start_date'])) .'-'.date(get_date_format(), strtotime($interval['end_date']))." - ".$config['total_rows'].' '.lang('reports_sales_report_generator_results_found'),
				"headers" => $model->getDataColumns(),
				"summary_data" => $summary_data,
				"details_data" => $details_data,
				"overall_summary_data" => $model->getSummaryData(),
				'pagination' => $this->pagination->create_links(),
				'export_excel' =>$this->input->get('export_excel'),
				'report_model' =>"sales_generator",
				'params'=>$params,
				'key' => $this->input->get('key')
			);
			
			// Fetch & Output Data 
			
			if (!$this->input->get('export_excel'))
			{
				$data['results'] = $this->load->view("reports/sales_generator_tabular_details", $reportdata, true);	
			}
		}	
		
		if (!$this->input->get('export_excel'))
		{
			$this->load->view("reports/sales_generator",$data);
		}
		else //Excel export use regular tabular_details
		{
			$this->load->view("reports/outputs/tabular_details_lazy_load",$reportdata);
		}
	}		
	function register_log_details($id)
	{
		$this->check_action_permission('view_register_log');
		
		$data = array(
			'output_data' => array(
				'view' => 'register_log_details',
				'register_log' => $this->Register->get_register_log($id),
				'register_log_details' => $this->Register->get_register_log_details($id),
				'key' => $this->input->get('key'),
			)
		);
		
		$this->load->view('reports/generate',$data);
	}
			
	function delete_register_log($register_log_id)
	{
		$this->check_action_permission('delete_register_log');
		$this->load->model('reports/Detailed_register_log');
		if($this->Detailed_register_log->delete_register_log($register_log_id))
		{
			redirect($this->agent->referrer());
		}
	}

	function edit_register_log($register_log_id)
	{
		$this->check_action_permission('edit_register_log');
		redirect('sales/edit_register/'.$register_log_id);
	}
			
		
	function get_report_details()
	{
		$ids=$this->input->post('ids');
		$reportType=filter_var($this->input->post('key'), FILTER_SANITIZE_STRING);
		$result='';
		$model=$this->load->model('reports/'.$reportType);
		$model = $this->$reportType;
		$model->report_key = $reportType;
		$model->setParams(json_decode($this->input->post('params'), TRUE));
		$data=$model->get_report_details($ids);
		print_r(json_encode($data));
		exit;
	}

	function get_report_details_sales_generator()
	{	
		$params=json_decode($this->input->post('params'), TRUE);
		$ids=$this->input->post('ids');
		$reportType=filter_var($this->input->post('key'), FILTER_SANITIZE_STRING);
		$result='';
		$model=$this->load->model('reports/'.$reportType);
		$model = $this->$reportType;
		$model->setParams($params);
		$model->setCats();
		
		$data=$model->get_report_details($ids,$params['export_excel']);
		print_r(json_encode($data));
		exit;
	}
		
	function customer_search($hide_all = 0)
	{
		$this->load->model('Customer');
		
		//allow parallel searchs to improve performance.
		session_write_close();
		$suggestions = $this->Customer->get_customer_search_suggestions($this->input->get('term'),0,100);
		
		if (!$hide_all)
		{
			array_unshift($suggestions, array('value' => '', 'label' => lang('common_all')));		
		}
		
		echo json_encode($suggestions);
	}
	
	function person_search($hide_all = 0)
	{
		$this->load->model('Person');
		
		//allow parallel searchs to improve performance.
		session_write_close();
		$suggestions = $this->Person->get_person_search_suggestions($this->input->get('term'),100);
		
		if (!$hide_all)
		{
			array_unshift($suggestions, array('value' => '', 'label' => lang('common_all')));		
		}
		
		echo json_encode($suggestions);
	}
	

	function item_search()
	{
		$this->load->model('Item');
		
		//allow parallel searchs to improve performance.
		session_write_close();
		$suggestions = $this->Item->get_item_search_suggestions($this->input->get('term'),0,'unit_price',25);
		array_unshift($suggestions, array('value' => '', 'label' => lang('common_all')));		
		echo json_encode($suggestions);
	}
	
	function item_kit_search()
	{
		$this->load->model('Item_kit');
		
		//allow parallel searchs to improve performance.
		session_write_close();
		$suggestions = $this->Item_kit->get_item_kit_search_suggestions_sales_recv($this->input->get('term'),0,'unit_price',100);
		array_unshift($suggestions, array('value' => '', 'label' => lang('common_all')));		
		echo json_encode($suggestions);
	}
	
	
	function supplier_search($hide_all = 0)
	{
		$this->load->model('Supplier');
		
		//allow parallel searchs to improve performance.
		session_write_close();
		$suggestions = $this->Supplier->get_supplier_search_suggestions($this->input->get('term'),0,100);
		
		if (!$hide_all)
		{
			array_unshift($suggestions, array('value' => '', 'label' => lang('common_all')));		
		}
		
		echo json_encode($suggestions);
	}
	
	function store_account_statements_email_customer()
	{
		if (!empty($_GET))
		{
			if ($this->input->get('report_type') == 'simple')
			{
				$dates = simple_date_range_to_date($this->input->get('report_date_range_simple'), (boolean)$this->input->get('with_time'),(boolean)$this->input->get('end_date_end_of_day')); 
				$_GET['start_date'] = $dates['start_date'];
				$_GET['end_date'] = $dates['end_date'];
			
			}
		
			if ($this->input->get('report_type_compare') == 'simple')
			{
				$dates = simple_date_range_to_date($this->input->get('report_date_range_simple_compare'), (boolean)$this->input->get('compare_with_time'),(boolean)$this->input->get('compare_end_date_end_of_day')); 
				$_GET['start_date_compare'] = $dates['start_date'];
				$_GET['end_date_compare'] = $dates['end_date'];
			}
		}
		
		$report_model = Report::get_report_model('store_account_statements_email_customer');
		$this->check_action_permission($report_model->settings['permission_action']);
		$report_model->setParams($this->input->get());
		$report_data = $report_model->getData();
		
		$customer_info = $this->Customer->get_info($this->input->get('customer_id'));
		$data = array(
			"title" => lang('reports_store_account_statement'),
			"subtitle" => date(get_date_format(), strtotime($this->input->get('start_date'))) .'-'.date(get_date_format(), strtotime($this->input->get('end_date'))),
			'report_data' => $report_data,
			'hide_items' => $this->input->get('hide_items'),
			'date_column' => $this->input->get('pull_payments_by') == 'payment_date' ? 'date' : 'sale_time',
		);
		
		if (!empty($customer_info->email))
		{
			$this->load->library('email');
			$config = array();
			$config['mailtype'] = 'html';
					
			$this->email->initialize($config);
			$this->email->from($this->Location->get_info_for_key('email') ? $this->Location->get_info_for_key('email') : 'no-reply@mg.phppointofsale.com', $this->config->item('company'));
			$this->email->to($customer_info->email);
			
			if($this->Location->get_info_for_key('cc_email'))
			{
				$this->email->cc($this->Location->get_info_for_key('cc_email'));
			}
			
			if($this->Location->get_info_for_key('bcc_email'))
			{
				$this->email->bcc($this->Location->get_info_for_key('bcc_email'));
			}

			$this->email->subject(lang('reports_store_account_statement'));
			$this->email->message($this->load->view("reports/outputs/store_account_statement_email",$data, true));	
			$this->email->send();
		}
	}

	function layaway_statements_email_customer()
	{
		if (!empty($_GET))
		{
			if ($this->input->get('report_type') == 'simple')
			{
				$dates = simple_date_range_to_date($this->input->get('report_date_range_simple'), (boolean)$this->input->get('with_time'),(boolean)$this->input->get('end_date_end_of_day')); 
				$_GET['start_date'] = $dates['start_date'];
				$_GET['end_date'] = $dates['end_date'];
			
			}
		
			if ($this->input->get('report_type_compare') == 'simple')
			{
				$dates = simple_date_range_to_date($this->input->get('report_date_range_simple_compare'), (boolean)$this->input->get('compare_with_time'),(boolean)$this->input->get('compare_end_date_end_of_day')); 
				$_GET['start_date_compare'] = $dates['start_date'];
				$_GET['end_date_compare'] = $dates['end_date'];
			}
		}
		
		$report_model = Report::get_report_model('layaway_statements_email_customer');
		$this->check_action_permission($report_model->settings['permission_action']);
		$report_model->setParams($this->input->get());
		$report_data = $report_model->getData();
		
		$customer_info = $this->Customer->get_info($this->input->get('customer_id'));
		$data = array(
			"title" => lang('reports_layaway_statements'),
			"subtitle" => date(get_date_format(), strtotime($this->input->get('start_date'))) .'-'.date(get_date_format(), strtotime($this->input->get('end_date'))),
			'report_data' => $report_data,
			'hide_items' => $this->input->get('hide_items'),
			'date_column' => 'sale_time',
		);
		
		if (!empty($customer_info->email))
		{
			$this->load->library('email');
			$config = array();
			$config['mailtype'] = 'html';
					
			$this->email->initialize($config);
			$this->email->from($this->Location->get_info_for_key('email') ? $this->Location->get_info_for_key('email') : 'no-reply@mg.phppointofsale.com', $this->config->item('company'));
			$this->email->to($customer_info->email);
			
			if($this->Location->get_info_for_key('cc_email'))
			{
				$this->email->cc($this->Location->get_info_for_key('cc_email'));
			}
			
			if($this->Location->get_info_for_key('bcc_email'))
			{
				$this->email->bcc($this->Location->get_info_for_key('bcc_email'));
			}

			$this->email->subject(lang('reports_layaway_statements'));
			$this->email->message($this->load->view("reports/outputs/layaway_statement_email",$data, true));	
			$this->email->send();
		}
	}
	
	function store_account_outstanding_mark_as_paid()
	{
		$sale_id = $this->input->get('sale_id');
		$this->check_action_permission('view_store_account');
		$this->db->insert('store_accounts_paid_sales',array('sale_id' => $sale_id,'store_account_payment_sale_id' => NULL));
		redirect($this->agent->referrer());	
	}
	
	function store_account_outstanding_mark_as_unpaid()
	{
		$sale_id = $this->input->get('sale_id');
		$this->check_action_permission('view_store_account');
		$this->db->delete('store_accounts_paid_sales',array('sale_id' => $sale_id));
		redirect($this->agent->referrer());	
	}
	
	function store_account_outstanding_mark_all_as_paid()
	{
		$customer_id = $this->input->get('customer_id');
		$this->check_action_permission('view_store_account');	
		$this->load->model('Sale');
		$this->Sale->mark_all_unpaid_sales_paid($customer_id);
		redirect($this->agent->referrer());	
	}
			
	function store_account_statements_email_supplier()
	{
		
		if (!empty($_GET))
		{
			if ($this->input->get('report_type') == 'simple')
			{
				$dates = simple_date_range_to_date($this->input->get('report_date_range_simple'), (boolean)$this->input->get('with_time'),(boolean)$this->input->get('end_date_end_of_day')); 
				$_GET['start_date'] = $dates['start_date'];
				$_GET['end_date'] = $dates['end_date'];
			
			}
		
			if ($this->input->get('report_type_compare') == 'simple')
			{
				$dates = simple_date_range_to_date($this->input->get('report_date_range_simple_compare'), (boolean)$this->input->get('compare_with_time'),(boolean)$this->input->get('compare_end_date_end_of_day')); 
				$_GET['start_date_compare'] = $dates['start_date'];
				$_GET['end_date_compare'] = $dates['end_date'];
			}
		}
		
		
		$this->load->model('Receiving');
		$this->load->model('Supplier');
		
		
			$report_model = Report::get_report_model('store_account_statements_email_supplier');
			$this->check_action_permission($report_model->settings['permission_action']);
			$report_model->setParams($this->input->get());
			$report_data = $report_model->getData();
		
			$supplier_info = $this->Supplier->get_info($this->input->get('supplier_id'));
			$data = array(
				"title" => lang('reports_store_account_statement'),
				"subtitle" => date(get_date_format(), strtotime($this->input->get('start_date'))) .'-'.date(get_date_format(), strtotime($this->input->get('end_date'))),
				'report_data' => $report_data,
				'hide_items' => $this->input->get('hide_items'),
				'date_column' => $this->input->get('pull_payments_by') == 'payment_date' ? 'date' : 'receiving_time',
			);
		
		if (!empty($supplier_info->email))
		{
			$this->load->library('email');
			$config = array();
			$config['mailtype'] = 'html';
					
			$this->email->initialize($config);
			$this->email->from($this->Location->get_info_for_key('email') ? $this->Location->get_info_for_key('email') : 'no-reply@mg.phppointofsale.com', $this->config->item('company'));
			$this->email->to($supplier_info->email); 
			
			if($this->Location->get_info_for_key('cc_email'))
			{
				$this->email->cc($this->Location->get_info_for_key('cc_email'));
			}
			
			if($this->Location->get_info_for_key('bcc_email'))
			{
				$this->email->bcc($this->Location->get_info_for_key('bcc_email'));
			}

			$this->email->subject(lang('reports_store_account_statement'));
			$this->email->message($this->load->view("reports/outputs/supplier_store_account_statement_email",$data, true));	
			$this->email->send();
		}
	}
	
	function supplier_store_account_outstanding_mark_as_paid()
	{
		$receiving_id = $this->input->get('receiving_id');
		$supplier_id = $this->input->get('supplier_id');
		$show_paid = $this->input->get('show_paid');
		$offset = $this->input->get('offset');
			
		$this->check_action_permission('view_store_account_suppliers');
		$this->db->insert('supplier_store_accounts_paid_receivings',array('receiving_id' => $receiving_id,'store_account_payment_receiving_id' => NULL));
		redirect($this->agent->referrer());
	}
	
	function supplier_store_account_outstanding_mark_as_unpaid()
	{
		$receiving_id = $this->input->get('receiving_id');
		$this->check_action_permission('view_store_account_suppliers');
		$this->db->delete('supplier_store_accounts_paid_receivings',array('receiving_id' => $receiving_id));
		redirect($this->agent->referrer());	
	}
	
	function supplier_store_account_outstanding_mark_all_as_paid()
	{
		$supplier_id = $this->input->get('supplier_id');
		$show_paid = $this->input->get('show_paid');
		$offset = $this->input->get('offset');
			
		$this->check_action_permission('view_store_account_suppliers');	
		$this->load->model('Receiving');
		$this->Receiving->mark_all_unpaid_receivings_paid($supplier_id);
		redirect($this->agent->referrer());	
	}
	
	public function add_saved_report()
	{
		$key = Report::save_report($this->input->post('name'),$this->input->post('url'));
		echo json_encode(array('message' => lang('reports_save_success'), 'key' => $key));
	}
	
	function delete_saved_report($key)
	{
		Report::delete_saved_report($key);
		echo json_encode(array('message' => lang('reports_unsaved_success')));
	}
	
	function save_reports()
	{
		$reports_post = $this->input->post('reports');
		foreach($reports_post as $report)
		{
			$name = $report['name'];
			$url = $report['url'];
			$key = md5($name.$url);
			$reports[$key] = array('name' => $name, 'url' => $url);
		}
		$this->load->model('Employee_appconfig');
		$this->Employee_appconfig->save('saved_reports',serialize($reports));
	}
	
	function export_recv($recv_id)
	{
		$this->load->model('Item_taxes');
		$this->load->model('Item_location');
		$this->load->model('Item_location_taxes');
		$this->load->model('Item_taxes_finder');
		$this->load->model('Receiving');
		$this->load->model('Item');
		$this->load->model('Category');
		$rows = array();
		
		$header_row = array();
		$header_row[] = lang('common_item_name');
		$header_row[] = lang('common_category');
		$header_row[] = lang('common_product_id');
		$header_row[] = lang('common_item_number');

		if(!$this->config->item('hide_size_field'))
		{
			$header_row[] = lang('common_size');			
		}
		
		$header_row[] = lang('common_description');
		$header_row[] = lang('common_cost_price');
		$header_row[] = lang('common_quantity');
		$header_row[] = lang('common_sub_total');
		$header_row[] = lang('common_tax');
		$header_row[] = lang('common_total');
		$rows[] = $header_row;
		foreach($this->Receiving->get_receiving_items($recv_id)->result() as $item)
		{
			$item_info = $this->Item->get_info($item->item_id);
			$row = array();
			$row[] = $item_info->name;
			$row[] = $item_info->category_id ? $this->Category->get_full_path($item_info->category_id): '';
			$row[] = $item_info->product_id ?  $item_info->product_id : '';
			$row[] = $item_info->item_number ?  $item_info->item_number : '';
			if(!$this->config->item('hide_size_field'))
			{
				$row[] = $item_info->size ?  $item_info->size : '';				
			}
			$row[] = $item->description;
			$row[] = to_currency_no_money($item->item_unit_price);
			$row[] = to_quantity($item->quantity_purchased);
			$row[] = to_currency_no_money($item->subtotal);
			$row[] = to_currency_no_money($item->tax);
			$row[] = to_currency_no_money($item->total);
			$rows[] = $row;
		}
		
		$this->load->helper('spreadsheet');
		$title = lang('common_receiving').'_'.$recv_id;
		array_to_spreadsheet($rows, strip_tags($title) . '.'.($this->config->item('spreadsheet_format') == 'XLSX' ? 'xlsx' : 'csv'), true);
		exit;
	}
	
	function view_series($id)
	{
		$this->load->model('Customer');
		$series_info = $this->Customer->get_series_info($id);
		$data = array('series' => $series_info);
		$customer_info = $this->Customer->get_info($series_info->customer_id);
		$data['customer_name'] = $customer_info->first_name.' '.$customer_info->last_name;
		$this->load->view('customers/edit_series',$data);
	}
	
	function save_series($id)
	{
		$this->load->model('Customer');
		$series_data = array('quantity_remaining' => $this->input->post('quantity_remaining'),'expire_date' => date("Y-m-d",strtotime($this->input->post('expire_date'))));
		$this->Customer->update_series($id,$series_data);
		redirect('reports/generate/customers_series?'.$_SERVER['QUERY_STRING']);
	}
	
	function delete_series($id)
	{
		$this->load->model('Customer');
		$this->Customer->delete_series($id);
		redirect('reports/generate/customers_series?'.$_SERVER['QUERY_STRING']);
		
	}
}

?>