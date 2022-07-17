<?php
require_once ("Person_controller.php");
class Suppliers extends Person_controller
{	
	function __construct()
	{
		parent::__construct('suppliers');
		$this->lang->load('suppliers');
		$this->lang->load('module');
		$this->load->model('Supplier_taxes');
		$this->load->model('Supplier');
	}
	
	
	function index($offset=0)
	{
		$params = $this->session->userdata('suppliers_search_data') ? $this->session->userdata('suppliers_search_data') : array('offset' => 0, 'order_col' => 'company_name', 'order_dir' => 'asc', 'search' => FALSE,'deleted' => 0);
		if ($offset!=$params['offset'])
		{
		   redirect('suppliers/index/'.$params['offset']);
		}
		$this->check_action_permission('search');
		$config['base_url'] = site_url('suppliers/sorting');
		$config['per_page'] = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20; 
		
		$data['controller_name']=strtolower(get_class());
		$data['per_page'] = $config['per_page'];
		$data['search'] = $params['search'] ? $params['search'] : "";
		$data['deleted'] = $params['deleted'] ? $params['deleted'] : "";
		if ($data['search'])
		{
			$config['total_rows'] = $this->Supplier->search_count_all($data['search'],$params['deleted']);
			$table_data = $this->Supplier->search($data['search'],$params['deleted'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
		}
		else
		{
			$config['total_rows'] = $this->Supplier->count_all($params['deleted']);
			$table_data = $this->Supplier->get_all($params['deleted'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
		}
		$this->load->library('pagination');$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		$data['order_col'] = $params['order_col'];
		$data['order_dir'] = $params['order_dir'];
		$data['total_rows'] = $config['total_rows'];
		
		$data['manage_table']=get_people_manage_table($table_data,$this);
		$data['default_columns'] = $this->Supplier->get_default_columns();
		$data['selected_columns'] = $this->Employee->get_supplier_columns_to_display();
		$data['all_columns'] = array_merge($data['selected_columns'], $this->Supplier->get_displayable_columns());		
		
		$this->load->view('people/manage',$data);
	}
	
	
	function sorting()
	{
		$params = $this->session->userdata('suppliers_search_data') ? $this->session->userdata('suppliers_search_data') : array('order_col' => 'last_name', 'order_dir' => 'asc','deleted' => 0);
		
		$this->check_action_permission('search');
		$search=$this->input->post('search') ? $this->input->post('search') : "";
		$per_page=$this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;
		$offset = $this->input->post('offset') ? $this->input->post('offset') : 0;
		$order_col = $this->input->post('order_col') ? $this->input->post('order_col') : $params['order_col'];
		$order_dir = $this->input->post('order_dir') ? $this->input->post('order_dir'): $params['order_dir'];
		$deleted = $this->input->post('deleted') ? $this->input->post('deleted') : $params['deleted'];
		

		$suppliers_search_data = array('offset' => $offset, 'order_col' => $order_col, 'order_dir' => $order_dir, 'search' => $search,'deleted' => $deleted);
		$this->session->set_userdata("suppliers_search_data",$suppliers_search_data);
		if ($search)
		{
			$config['total_rows'] = $this->Supplier->search_count_all($search,$deleted);
			$table_data = $this->Supplier->search($search,$deleted,$per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $order_col ,$order_dir);
		}
		else
		{
			$config['total_rows'] = $this->Supplier->count_all($deleted);
			$table_data = $this->Supplier->get_all($deleted,$per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $order_col ,$order_dir);
		}
		$config['base_url'] = site_url('suppliers/sorting');
		$config['per_page'] = $per_page; 
		$this->load->library('pagination');$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		$data['manage_table']=get_people_manage_table_data_rows($table_data,$this);
		echo json_encode(array('manage_table' => $data['manage_table'], 'pagination' => $data['pagination'],'total_rows' => $config['total_rows']));	
	}
	
	function _excel_get_header_row()
	{
		$return = array(lang('suppliers_company_name'),lang('common_first_name'),lang('common_last_name'),lang('common_email'),lang('common_phone_number'),lang('common_address_1'),lang('common_address_2'),lang('common_city'),	lang('common_state'),lang('common_zip'),lang('common_country'),lang('common_comments'),lang('suppliers_account_number'), lang('common_internal_notes'));
		
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
		{
			if ($this->Supplier->get_custom_field($k) !== FALSE)
			{
				$return[] = $this->Supplier->get_custom_field($k);
			}
		}
		
		
		if ($this->config->item('suppliers_store_accounts'))
		{
			$return[] = lang('common_balance');
		}
				
		return $return;
	}
	
	function clear_state()
	{
		$params = $this->session->userdata('suppliers_search_data');
		$this->session->set_userdata('suppliers_search_data', array('offset' => 0, 'order_col' => 'company_name', 'order_dir' => 'asc', 'search' => FALSE, 'deleted' => $params['deleted']));
		redirect('suppliers');
	}


	function excel()
	{
		$this->load->helper('report');
		$header_row = $this->_excel_get_header_row();
		$this->load->helper('spreadsheet');
		array_to_spreadsheet(array($header_row),'import_suppliers.'.($this->config->item('spreadsheet_format') == 'XLSX' ? 'xlsx' : 'csv'));
	}
	
	function do_excel_import()
	{
		ini_set('memory_limit','1024M');
		$this->load->helper('demo');

		$file_info = pathinfo($_FILES['file_path']['name']);
		if($file_info['extension'] != 'xlsx' && $file_info['extension'] != 'csv')
		{
			echo json_encode(array('success'=>false,'message'=>lang('common_upload_file_not_supported_format')));
			return;
		}
		
		set_time_limit(0);
		ini_set('max_input_time','-1');
		$this->check_action_permission('add_update');
		$this->db->trans_start();
				
		$msg = 'do_excel_import';
		$failCodes = array();
		if ($_FILES['file_path']['error']!=UPLOAD_ERR_OK)
		{
			$msg = lang('common_excel_import_failed');
			echo json_encode( array('success'=>false,'message'=>$msg) );
			$this->db->trans_complete();
			return;
		}
		else
		{
			if (($handle = fopen($_FILES['file_path']['tmp_name'], "r")) !== FALSE)
			{
				$this->load->helper('spreadsheet');
				$file_info = pathinfo($_FILES['file_path']['name']);
				
				$sheet = file_to_spreadsheet($_FILES['file_path']['tmp_name'],$file_info['extension']);
				$num_rows = $sheet->getNumberOfRows();
				
				//Loop through rows, skip header row
				for($k = 2;$k<=$num_rows; $k++)
				{
					
					$company_name = $sheet->getCellByColumnAndRow(0, $k);
					if (!$company_name)
					{
						$company_name = '';
					}
					
					
					$first_name = $sheet->getCellByColumnAndRow(1, $k);
					if (!$first_name)
					{
						$first_name = '';
					}
					
					$last_name = $sheet->getCellByColumnAndRow(2, $k);
					if (!$last_name)
					{
						$last_name = '';
					}

					$email = $sheet->getCellByColumnAndRow(3, $k);
					if (!$email)
					{
						$email = '';
					}

					$phone_number = $sheet->getCellByColumnAndRow(4, $k);
					if (!$phone_number)
					{
						$phone_number = '';
					}

					$address_1 = $sheet->getCellByColumnAndRow(5, $k);
					if (!$address_1)
					{
						$address_1 = '';
					}

					$address_2 = $sheet->getCellByColumnAndRow(6, $k);
					if (!$address_2)
					{
						$address_2 = '';
					}

					$city = $sheet->getCellByColumnAndRow(7, $k);
					if (!$city)
					{
						$city = '';
					}

					$state = $sheet->getCellByColumnAndRow(8, $k);
					if (!$state)
					{
						$state = '';
					}

					$zip = $sheet->getCellByColumnAndRow(9, $k);
					if (!$zip)
					{
						$zip = '';
					}

					$country = $sheet->getCellByColumnAndRow(10, $k);
					if (!$country)
					{
						$country = '';
					}

					$comments = $sheet->getCellByColumnAndRow(11, $k);
					if (!$comments)
					{
						$comments = '';
					}

					$account_number = $sheet->getCellByColumnAndRow(12, $k);
					if (!$account_number)
					{
						$account_number = NULL;
					}
					
					$internal_notes = $sheet->getCellByColumnAndRow(13, $k);
					if (!$internal_notes)
					{
						$internal_notes = '';
					}
					
						
					//If we don't have a company name  or first name skip the import
					if (!($company_name || $first_name))
					{
						continue;
					}
					
					$person_data = array(
					'first_name'=>$first_name,
					'last_name'=>$last_name,
					'email'=>$email,
					'phone_number'=>$phone_number,
					'address_1'=>$address_1,
					'address_2'=>$address_2,
					'city'=>$city,
					'state'=>$state,
					'zip'=>$zip,
					'country'=>$country,
					'comments'=>$comments
					);
					
					$supplier_data=array(
					'account_number'=>$account_number,
					'company_name' => $company_name,
					'internal_notes' => $internal_notes,
					);
					
					
					$number_of_custom_fields = 0;
					
					for($a=1;$a<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$a++)
					{
						if ($this->Supplier->get_custom_field($a) !== FALSE)
						{
							$supplier_data["custom_field_{$a}_value"] = $sheet->getcellByColumnAndRow(12+$a,$k);
			
							if ($this->Supplier->get_custom_field($a,'type') == 'checabox')
							{
								$supplier_data["custom_field_{$a}_value"] = $sheet->getcellByColumnAndRow(12+$a,$k);
							}
							elseif($this->Supplier->get_custom_field($a,'type') == 'date')
							{
								$supplier_data["custom_field_{$a}_value"] = strtotime($sheet->getcellByColumnAndRow(12+$a,$k));
							}
							else
							{
								$supplier_data["custom_field_{$a}_value"] = $sheet->getcellByColumnAndRow(12+$a,$k);
							}
							
							$number_of_custom_fields++;
						}
					}
					
					if ($this->config->item('suppliers_store_accounts'))
					{
						$balance = $sheet->getCellByColumnAndRow(13+$number_of_custom_fields, $k);
						if (!$balance)
						{
							$balance = 0;
						}
						$person_id = $sheet->getCellByColumnAndRow(14+$number_of_custom_fields, $k);
					}
					else
					{
						$balance = 0;
						$person_id = $sheet->getCellByColumnAndRow(13+$number_of_custom_fields, $k);
					}
					
					$supplier_data['balance'] = $balance;
					
					if(!$this->Supplier->save_supplier($person_data,$supplier_data,$person_id ? $person_id : FALSE))
					{	
						echo json_encode( array('success'=>false,'message'=>lang('suppliers_duplicate_account_id')));
						$this->db->trans_complete();
						return;
					}
				}
			}
			else 
			{
				echo json_encode( array('success'=>false,'message'=>lang('common_upload_file_not_supported_format')));
				$this->db->trans_complete();
				return;
			}
		}
		$this->db->trans_complete();
		echo json_encode(array('success'=>true,'message'=>lang('suppliers_import_successfull')));
	}
	
	
	function excel_import()
	{
		$this->check_action_permission('add_update');
		$this->load->view("suppliers/excel_import", null);
	}
	
	/* added for excel expert */
	function excel_export() {
		$this->check_action_permission('excel_export');
		ini_set('memory_limit','1024M');
		set_time_limit(0);
		ini_set('max_input_time','-1');


		$params = $this->session->userdata('suppliers_search_data') ? $this->session->userdata('suppliers_search_data') : array('offset' => 0, 'order_col' => 'company_name', 'order_dir' => 'asc', 'search' => FALSE,'deleted'=> 0);
		
		$search = $params['search'] ? $params['search'] : "";
		
		//Filter based on search
		if ($search)
		{
			$data = $this->Supplier->search($search,$params['deleted'],$this->Supplier->search_count_all($search),0,$params['order_col'],$params['order_dir'])->result_object();
		}
		else
		{
			$data = $this->Supplier->get_all($params['deleted'],$this->Supplier->count_all($params['deleted']))->result_object();
		}
		
		$this->load->helper('report');
		$rows = array();
		$header_row = $this->_excel_get_header_row();
		$header_row[] = lang('suppliers_id');
		$rows[] = $header_row;
		
		foreach ($data as $r) {
			$row = array(
				$r->company_name,
				$r->first_name,
				$r->last_name,
				$r->email,
				$r->phone_number,
				$r->address_1,
				$r->address_2,
				$r->city,
				$r->state,
				$r->zip,
				$r->country,
				$r->comments,
				$r->account_number,
				$r->internal_notes,
			);
			
			for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
			{
				$type = $this->Supplier->get_custom_field($k,'type');
				$name = $this->Supplier->get_custom_field($k,'name');
				
				if ($name !== FALSE)
				{
					if ($type == 'date')
					{
						$row[] = date(get_date_format(),$r->{"custom_field_{$k}_value"});
					}
					elseif($type=='checkbox')
					{
						$row[] = $r->{"custom_field_{$k}_value"} ? '1' : '0';					
					}
					else
					{
						$row[] = $r->{"custom_field_{$k}_value"};				
					}
				}
			}
			
			if ($this->config->item('suppliers_store_accounts'))
			{
				$row[] = $r->balance ? to_currency_no_money($r->balance) : '';
			}
			
			
			$row[] = $r->person_id;
			
			$rows[] = $row;
		}
		$this->load->helper('spreadsheet');
		array_to_spreadsheet($rows,'suppliers_export.'.($this->config->item('spreadsheet_format') == 'XLSX' ? 'xlsx' : 'csv'));
	}
	/*
	Returns supplier table data rows. This will be called with AJAX.
	*/
	function search()
	{
		$this->check_action_permission('search');
		$params = $this->session->userdata('suppliers_search_data');
		
		$search=$this->input->post('search');
		$offset = $this->input->post('offset') ? $this->input->post('offset') : 0;
		$order_col = $this->input->post('order_col') ? $this->input->post('order_col') : 'company_name';
		$order_dir = $this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc';
		$deleted = isset($params['deleted']) ? $params['deleted'] : 0;

		$suppliers_search_data = array('offset' => $offset, 'order_col' => $order_col, 'order_dir' => $order_dir, 'search' => $search,'deleted' => $deleted);
		$this->session->set_userdata("suppliers_search_data",$suppliers_search_data);
		$per_page=$this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;
		$search_data=$this->Supplier->search($search,$deleted,$per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $this->input->post('order_col') ? $this->input->post('order_col') : 'company_name' ,$this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc');
		$config['base_url'] = site_url('suppliers/search');
		$config['total_rows'] = $this->Supplier->search_count_all($search,$deleted);
		$config['per_page'] = $per_page ;
		
		$this->load->library('pagination');$this->pagination->initialize($config);				
		$data['pagination'] = $this->pagination->create_links();
		$data['manage_table']=get_people_manage_table_data_rows($search_data,$this);
		echo json_encode(array('manage_table' => $data['manage_table'], 'pagination' => $data['pagination'],'total_rows' => $config['total_rows']));
		
	}
	
	function mailing_labels($supplier_ids)
	{
		$data['mailing_labels'] = array();
		
		foreach(explode('~', $supplier_ids) as $supplier_id)
		{			
			$supplier_info = $this->Supplier->get_info($supplier_id);
			
			$label = array();
			$label['name'] = $supplier_info->company_name. ': '.$supplier_info->first_name.' '.$supplier_info->last_name;
			$label['address_1'] = $supplier_info->address_1;
			$label['address_2'] = $supplier_info->address_2;
			$label['city'] = $supplier_info->city;
			$label['state'] = $supplier_info->state;
			$label['zip'] = $supplier_info->zip;
			$label['country'] = $supplier_info->country;
			
			$data['mailing_labels'][] = $label;
			
		}
		$data['type'] = $this->config->item('mailing_labels_type') == 'excel' ? 'excel' : 'pdf';
		$this->load->view("mailing_labels", $data);	
	}
	
	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		//allow parallel searchs to improve performance.
		session_write_close();
		$params = $this->session->userdata('suppliers_search_data') ? $this->session->userdata('suppliers_search_data') : array('deleted' => 0);
		
		$suggestions = $this->Supplier->get_supplier_search_suggestions($this->input->get('term'),$params['deleted'],100);
		echo json_encode(H($suggestions));
	}
	
	/*
	Loads the supplier edit form
	*/
	function view($supplier_id=-1, $redirect = 0)
	{
 		$this->load->model('Appfile');
		
		$this->check_action_permission('add_update');	
		$this->load->model('Tax_class');
		
		$data = array();
		$data['tax_classes'] = array();
		$data['tax_classes'][''] = lang('common_none');
		
		foreach($this->Tax_class->get_all()->result_array() as $tax_class)
		{
			$data['tax_classes'][$tax_class['id']] = $tax_class['name'];
		}
		$data['controller_name']=strtolower(get_class());
		$data['person_info']=$this->Supplier->get_info($supplier_id);
		$data['supplier_tax_info']=$this->Supplier_taxes->get_info($supplier_id);
		$data['redirect']=$redirect;
		$data['files'] = $this->Person->get_files($supplier_id)->result();
		$data['current_location'] = $this->Employee->get_logged_in_employee_current_location_id();
		$this->load->view("suppliers/form",$data);
	}
	
	/*
	Inserts/updates a supplier
	*/
	function save($supplier_id=-1)
	{
		$this->check_action_permission('add_update');		
		
		//Catch an error if our company name is NOT set. This can happen if logo uploaded is larger than post size
		if ($this->input->post('company_name') === NULL)
		{
			echo json_encode(array('success'=>false,'message'=>lang('suppliers_error_adding_updating').' '.
			$supplier_data['company_name'],'person_id'=>-1));
			exit;
		}
		
		$person_data = array(
		'first_name'=>$this->input->post('first_name'),
		'last_name'=>$this->input->post('last_name'),
		'email'=>$this->input->post('email'),
		'phone_number'=>$this->input->post('phone_number'),
		'address_1'=>$this->input->post('address_1'),
		'address_2'=>$this->input->post('address_2'),
		'city'=>$this->input->post('city'),
		'state'=>$this->input->post('state'),
		'zip'=>$this->input->post('zip'),
		'country'=>$this->input->post('country'),
		'comments'=>$this->input->post('comments')
		);
		$supplier_data=array(
		'company_name'=>$this->input->post('company_name'),
		'account_number'=>$this->input->post('account_number')=='' ? null:$this->input->post('account_number'),
		'override_default_tax'=> $this->input->post('override_default_tax') ? $this->input->post('override_default_tax') : 0,
		'tax_class_id'=> $this->input->post('tax_class') ? $this->input->post('tax_class') : NULL,
		'internal_notes'=>$this->input->post('internal_notes'),
		);
		
		
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
		{
			if ($this->Supplier->get_custom_field($k) !== FALSE)
			{
				if ($this->Supplier->get_custom_field($k,'type') == 'checkbox')
				{
					$supplier_data["custom_field_{$k}_value"] = $this->input->post("custom_field_{$k}_value");
				}
				elseif($this->Supplier->get_custom_field($k,'type') == 'date')
				{
					$supplier_data["custom_field_{$k}_value"] = $this->input->post("custom_field_{$k}_value") !== '' ? strtotime($this->input->post("custom_field_{$k}_value")) : NULL;
				}
				elseif(isset($_FILES["custom_field_{$k}_value"]['tmp_name']) && $_FILES["custom_field_{$k}_value"]['tmp_name'])
				{
					if($this->Supplier->get_custom_field($k,'type') == 'image')
					{
				    $this->load->library('image_lib');
					
						$allowed_extensions = array('png', 'jpg', 'jpeg', 'gif');
						$extension = strtolower(pathinfo($_FILES["custom_field_{$k}_value"]['name'], PATHINFO_EXTENSION));
				    if (in_array($extension, $allowed_extensions))
				    {
					    $config['image_library'] = 'gd2';
					    $config['source_image']	= $_FILES["custom_field_{$k}_value"]['tmp_name'];
					    $config['create_thumb'] = FALSE;
					    $config['maintain_ratio'] = TRUE;
					    $config['width']	 = 1200;
					    $config['height']	= 900;
							$this->image_lib->initialize($config);
					    $this->image_lib->resize();
				   	 	$this->load->model('Appfile');
					    $image_file_id = $this->Appfile->save($_FILES["custom_field_{$k}_value"]['name'], file_get_contents($_FILES["custom_field_{$k}_value"]['tmp_name']));
							$supplier_data["custom_field_{$k}_value"] = $image_file_id;
						}
					
					}
					else
					{
		   	 		$this->load->model('Appfile');
					
			    	$custom_file_id = $this->Appfile->save($_FILES["custom_field_{$k}_value"]['name'], file_get_contents($_FILES["custom_field_{$k}_value"]['tmp_name']));
						$supplier_data["custom_field_{$k}_value"] = $custom_file_id;
					}
				}
				elseif($this->Supplier->get_custom_field($k,'type') != 'image' && $this->Supplier->get_custom_field($k,'type') != 'file')
				{
					$supplier_data["custom_field_{$k}_value"] = $this->input->post("custom_field_{$k}_value");
				}
			}
		}
		
		
		if ($this->input->post('balance')!== NULL && is_numeric($this->input->post('balance')))
		{
			$supplier_data['balance'] = $this->input->post('balance');
		}
		
		
		$redirect = $this->input->post('redirect');
		
		if($this->Supplier->save_supplier($person_data,$supplier_data,$supplier_id))
		{			
			if ($this->Location->get_info_for_key('mailchimp_api_key'))
			{
				$this->Person->update_mailchimp_subscriptions($this->input->post('email'), $this->input->post('first_name'), $this->input->post('last_name'), $this->input->post('mailing_lists'));
			}
			
			if ($this->Location->get_info_for_key('platformly_api_key'))
			{
				$this->Person->update_platformly_subscriptions($this->input->post('email'), $this->input->post('first_name'), $this->input->post('last_name'), $this->input->post('segments'));
			}
			
			
			$success_message = '';
			
			//New supplier
			if($supplier_id==-1)
			{
				$success_message = H(lang('suppliers_successful_adding').' '.$supplier_data['company_name']);
				echo json_encode(array('success'=>true, 'redirect'=> $redirect, 'message'=>$success_message,'person_id'=>$supplier_data['person_id']));
				$supplier_id = $supplier_data['person_id'];
				
			}
			else //previous supplier
			{
				$success_message = H(lang('suppliers_successful_updating').' '.$supplier_data['company_name']);
				$this->session->set_flashdata('manage_success_message', $success_message);
				echo json_encode(array('success'=>true,'redirect'=> $redirect, 'message'=>$success_message,'person_id'=>$supplier_id));
			}
			
			$suppliers_taxes_data = array();
			$tax_names = $this->input->post('tax_names');
			$tax_percents = $this->input->post('tax_percents');
			$tax_cumulatives = $this->input->post('tax_cumulatives');
			for($k=0;$k<count($tax_percents ? $tax_percents : array());$k++)
			{
				if (is_numeric($tax_percents[$k]))
				{
					$suppliers_taxes_data[] = array('name'=>$tax_names[$k], 'percent'=>$tax_percents[$k], 'cumulative' => isset($tax_cumulatives[$k]) ? $tax_cumulatives[$k] : '0' );
				}
			}
			$this->Supplier_taxes->save($suppliers_taxes_data, $supplier_id);
			
			$supplier_info = $this->Supplier->get_info($supplier_id);
			
			//Delete Image
			if($this->input->post('del_image') && $supplier_id != -1)
			{
			    if($supplier_info->image_id != null)
			    {
					$this->Person->update_image(NULL,$supplier_id);
					$this->load->model('Appfile');
					$this->Appfile->delete($supplier_info->image_id);
			    }
			}

			//Save Image File
			if(!empty($_FILES["image_id"]) && $_FILES["image_id"]["error"] == UPLOAD_ERR_OK)
			{			    
			    $allowed_extensions = array('png', 'jpg', 'jpeg', 'gif');
				$extension = strtolower(pathinfo($_FILES["image_id"]["name"], PATHINFO_EXTENSION));

			    if (in_array($extension, $allowed_extensions))
			    {
				    $config['image_library'] = 'gd2';
				    $config['source_image']	= $_FILES["image_id"]["tmp_name"];
				    $config['create_thumb'] = FALSE;
				    $config['maintain_ratio'] = TRUE;
				    $config['width']	 = 1200;
				    $config['height']	= 900;
				    $this->load->library('image_lib', $config); 
				    $this->image_lib->resize();
					 $this->load->model('Appfile');
				    $image_file_id = $this->Appfile->save($_FILES["image_id"]["name"], file_get_contents($_FILES["image_id"]["tmp_name"]), NULL, $supplier_info->image_id);
			    }

				if($supplier_id==-1)
				{
	    			$this->Person->update_image($image_file_id,$supplier_data['person_id']);
				}
				else
				{
					$this->Person->update_image($image_file_id,$supplier_id);
    			
				}
			}
			
			if (isset($_FILES['files']))
			{	$this->load->model('Appfile');
				for($k=0; $k<count($_FILES['files']['name']); $k++)
				{
		   	 		if($_FILES['files']['tmp_name'][$k])
						{
					$file_id = $this->Appfile->save($_FILES['files']['name'][$k], file_get_contents($_FILES['files']['tmp_name'][$k]));
					$this->Person->add_file($supplier_id==-1 ? $supplier_data['person_id'] : $supplier_id, $file_id);
					}
				}
			}				
			
		}
		else//failure
		{	
			echo json_encode(array('success'=>false,'message'=>lang('suppliers_error_adding_updating').' '.
			$supplier_data['company_name'],'person_id'=>-1));
		}
	}
		
	function account_number_exists()
	{
		if($this->Supplier->account_number_exists($this->input->post('account_number')))
		echo 'false';
		else
		echo 'true';
		
	}
	/*
	This deletes suppliers from the suppliers table
	*/
	function delete()
	{
		$this->check_action_permission('delete');
		$suppliers_to_delete=$this->input->post('ids');
		
		if($this->Supplier->delete_list($suppliers_to_delete))
		{
			echo json_encode(array('success'=>true,'message'=>lang('suppliers_successful_deleted').' '.
			count($suppliers_to_delete).' '.lang('suppliers_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('suppliers_cannot_be_deleted')));
		}
	}
		
	/*
	This undeletes suppliers from the suppliers table
	*/
	function undelete()
	{
		$this->check_action_permission('delete');
		$suppliers_to_undelete=$this->input->post('ids');
		
		if($this->Supplier->undelete_list($suppliers_to_undelete))
		{
			echo json_encode(array('success'=>true,'message'=>lang('suppliers_successful_undeleted').' '.
			count($suppliers_to_undelete).' '.lang('suppliers_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('suppliers_cannot_be_undeleted')));
		}
	}
		
	
		
	function cleanup()
	{
		$this->Supplier->cleanup();
		echo json_encode(array('success'=>true,'message'=>lang('suppliers_cleanup_sucessful')));
	}
	
	function pay_now($supplier_id)
	{
		$can_receive_store_account_payment = $this->Employee->has_module_action_permission('receivings', 'receive_store_account_payment', $this->Employee->get_logged_in_employee_info()->person_id);		
		
		if($can_receive_store_account_payment)
		{
		
			$this->load->model('Receiving');
			$this->load->model('Supplier');
			$this->load->model('Tier');
			$this->load->model('Category');
			$this->load->model('Giftcard');
			$this->load->model('Tag');
			$this->load->model('Item');
			$this->load->model('Item_location');
			$this->load->model('Item_kit_location');
			$this->load->model('Item_kit_location_taxes');
			$this->load->model('Item_kit');
			$this->load->model('Item_kit_items');
			$this->load->model('Item_kit_taxes');
			$this->load->model('Item_location_taxes');
			$this->load->model('Item_taxes');
			$this->load->model('Item_taxes_finder');
			$this->load->model('Item_kit_taxes_finder');
			require_once (APPPATH."models/cart/PHPPOSCartRecv.php");
			$cart = PHPPOSCartRecv::get_instance('receiving');
	    $cart->destroy();
			$cart->supplier_id = $supplier_id;
			$cart->set_mode('store_account_payment');
			$store_account_payment_item_id = $this->Item->create_or_update_store_account_item();
			$cart->add_item(new PHPPOSCartItemRecv(array('cost_price' => 0,'unit_price' => 0,'scan' => $store_account_payment_item_id.'|FORCE_ITEM_ID|','cart' => $cart)));
			$cart->save();
			redirect('receivings');
		}
		else
		{
			redirect('no_access/receivings');
		}
	}
	
	function reload_table()
	{
		$params = $this->session->userdata('suppliers_search_data') ? $this->session->userdata('suppliers_search_data') : array('offset' => 0, 'order_col' => 'last_name', 'order_dir' => 'asc', 'search' => FALSE,'deleted' => 0);
		$config['base_url'] = site_url('suppliers/sorting');
		$config['per_page'] = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20; 
		
		$data['controller_name']=strtolower(get_class());
		$data['per_page'] = $config['per_page'];
		$data['search'] = $params['search'] ? $params['search'] : "";
		if ($data['search'])
		{
			$config['total_rows'] = $this->Supplier->search_count_all($data['search'],$params['deleted']);
			$table_data = $this->Supplier->search($data['search'],$params['deleted'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
		}
		else
		{
			$config['total_rows'] = $this->Supplier->count_all($params['deleted']);
			$table_data = $this->Supplier->get_all($params['deleted'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
		}
		$this->load->library('pagination');$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		$data['order_col'] = $params['order_col'];
		$data['order_dir'] = $params['order_dir'];
		
		echo get_people_manage_table($table_data,$this);
	}
	
	function save_column_prefs()
	{
		$this->load->model('Employee_appconfig');
		
		if ($this->input->post('columns'))
		{
			$this->Employee_appconfig->save('supplier_column_prefs',serialize($this->input->post('columns')));
		}
		else
		{
			$this->Employee_appconfig->delete('supplier_column_prefs');			
		}
	}
	
	function custom_fields()
	{
		$fields_prefs = $this->config->item('supplier_custom_field_prefs') ? unserialize($this->config->item('supplier_custom_field_prefs')) : array();
		$data = array_merge(array('controller_name' => strtolower(get_class())),$fields_prefs);
		$locations_list = $this->Location->get_all()->result();
		$data['locations'] = $locations_list;
		$this->load->view('custom_fields',$data);
	}
	
	function save_custom_fields()
	{
		$this->load->model('Appconfig');
		$this->Appconfig->save('supplier_custom_field_prefs',serialize($this->input->post()));
	}
	
	function toggle_show_deleted($deleted=0)
	{
		$this->check_action_permission('search');
		
		$params = $this->session->userdata('suppliers_search_data') ? $this->session->userdata('suppliers_search_data') : array('offset' => 0, 'order_col' => 'last_name', 'order_dir' => 'asc', 'search' => FALSE, 'deleted' => 0);
		$params['deleted'] = $deleted;
		$params['offset'] = 0;
		
		$this->session->set_userdata("suppliers_search_data",$params);
	}	
	
	function delete_custom_field_value($person_id,$k)
	{
		$this->load->model('Supplier');
		$supplier_info = $this->Supplier->get_info($person_id);
		$file_id = $supplier_info->{"custom_field_{$k}_value"};
		$this->load->model('Appfile');
		$this->Appfile->delete($file_id);
		$person_data = array();
		$supplier_data = array();
		$supplier_data["custom_field_{$k}_value"] = NULL;
		$this->Supplier->save_supplier($person_data,$supplier_data,$person_id);
	}
}
?>