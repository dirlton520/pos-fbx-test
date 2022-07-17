<?php
require_once ("Secure_area.php");
class Timeclocks extends Secure_area 
{
	function __construct()
	{
		parent::__construct();	
		$this->lang->load('timeclocks');
		$this->lang->load('module');		
		
	}
	
	function index()
	{
		$data = array();
		$data['is_clocked_in'] = $this->Employee->is_clocked_in();
		
		$clocked_in_at_another = false;
		$this->load->model('Location');
		
		foreach($this->Location->get_all()->result_array() as $location)
		{
			$location_id = $location['location_id'];
			
			if ($location_id != $this->Employee->get_logged_in_employee_current_location_id())
			{
				if ($this->Employee->is_clocked_in(false, $location_id))
				{
					$clocked_in_at_another = TRUE;
					break;
				}
			}
		}
		
		$data['is_clocked_at_another_location'] = $clocked_in_at_another;
		$this->load->view("timeclocks/manage",$data);
	}
	
	function request_time_off($id = FALSE)
	{
		//Time off request processing
		if ($this->input->post('hours_requested'))
		{
			$time_off_request = array(
			'start_day' => date('Y-m-d',strtotime($this->input->post('start_day'))),
			'end_day' => date('Y-m-d',strtotime($this->input->post('end_day'))),
			'hours_requested' => (float)$this->input->post('hours_requested'),
			'is_paid' => $this->input->post('is_paid') ? 1 : 0,
			'reason' => $this->input->post('reason'),
			);
			
			//Only do this the first time when request put in
			if (!$id)
			{
				$time_off_request['employee_requested_person_id'] = $this->Employee->get_logged_in_employee_info()->person_id;
				$time_off_request['employee_requested_location_id'] = $this->Employee->get_logged_in_employee_current_location_id();
			}
			$success = $this->Employee->request_time_off($time_off_request,$id);
			$data['success'] = $success;
			
			if ($id)
			{
				redirect('reports/generate/time_off?'.$_SERVER['QUERY_STRING']);
			}
			else
			{
				$this->load->view("timeclocks/request_time_off",$data);
			}
		}
		else
		{
			//Show form to request time off
			$data = array();
			
			if ($id)
			{
				$_POST = $this->Employee->get_time_off_request($id);
			}
			
			$this->load->view("timeclocks/request_time_off",$data);
		}
	}
	
	function in()
	{
		if (!$this->Employee->is_clocked_in() && $this->Employee->clock_in($this->input->post('comment')))
		{
			echo json_encode(array('success'=>true,'message'=>lang('timeclocks_clock_in_success')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('timeclocks_clock_in_failure')));			
		}
	}
	
	function out()
	{
		if ($this->Employee->clock_out($this->input->post('comment')))
		{
			echo json_encode(array('success'=>true,'message'=>lang('timeclocks_clock_out_success')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('timeclocks_clock_out_failure')));			
		}
	}
	
	function view($id)
	{
		if ($this->Employee->has_module_action_permission('reports', 'view_timeclock', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			$data = array();
			
			$timeclock_entry = $this->Employee->get_timeclock($id);
			
			$data['id'] = $timeclock_entry->id ? $timeclock_entry->id : -1;
			$data['employee_id'] = $timeclock_entry->employee_id;
			$data['location_id'] = $timeclock_entry->location_id ? $timeclock_entry->location_id : $this->Employee->get_logged_in_employee_current_location_id();
			$data['hourly_pay_rate'] = $timeclock_entry->hourly_pay_rate;
			
			$data['employees'] = array();
			foreach ($this->Employee->get_all()->result() as $employee)
			{
				$data['employees'][$employee->person_id] = $employee->first_name . ' '. $employee->last_name;
			}
			
			$data['in'] = $timeclock_entry->clock_in ? $timeclock_entry->clock_in :  date('Y-m-d H:i:s');
			$data['out'] = $timeclock_entry->clock_out ? $timeclock_entry->clock_out :  date('Y-m-d H:i:s');
			$data['in_comment'] = $timeclock_entry->clock_in_comment;
			$data['out_comment'] = $timeclock_entry->clock_out_comment;
			$this->load->view('timeclocks/form',$data);
		}
		else
		{
			redirect('no_access/timeclocks');
		}
	}
	
	function save($id)
	{
		$employee_id = $this->input->post('employee_id');
		$location_id = $this->input->post('location_id');
		$clock_in = $this->input->post('clock_in');
		$clock_out = $this->input->post('clock_out');
		$clock_in_comment = $this->input->post('clock_in_comment');
		$clock_out_comment = $this->input->post('clock_out_comment');
		$hourly_pay_rate = (float)$this->input->post('hourly_pay_rate');
		
		
		$this->Employee->save_timeclock(array(
			'id'=> $id,
			'employee_id' => $employee_id,
			'location_id' => $location_id,
			'clock_in'=> $clock_in,
			'clock_out'=> $clock_out,
			'clock_in_comment'=> $clock_in_comment,
			'clock_out_comment'=> $clock_out_comment,	
			'hourly_pay_rate' => $hourly_pay_rate,		
		));
		redirect('reports/generate/detailed_timeclock?'.$_SERVER['QUERY_STRING']);

	}
	
	function delete($id)
	{
		if ($this->Employee->has_module_action_permission('reports', 'view_timeclock', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			$this->Employee->delete_timeclock($id);
		}
		
		redirect('reports/generate/detailed_timeclock?'.$_SERVER['QUERY_STRING']);
	}
	
	function delete_time_off($id)
	{
		if ($this->Employee->has_module_action_permission('reports', 'view_timeclock', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			$this->Employee->delete_time_off($id);
		}
		
		redirect('reports/generate/time_off?'.$_SERVER['QUERY_STRING']);
	}
	
	function approve_time_off($id)
	{
		if ($this->Employee->has_module_action_permission('reports', 'view_timeclock', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			$this->Employee->approve_time_off($id);
		}
		
		redirect('reports/generate/time_off?'.$_SERVER['QUERY_STRING']);
	}
	
	
	function punches()
	{
		$this->lang->load('reports');
		$start_date=date('Y-m-d', strtotime('-2 weeks'));
		$end_date=date('Y-m-d').' 23:59:59';
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$offset = 0;
		$export_excel = 0;
		$this->load->model('reports/Detailed_timeclock');
		$model = $this->Detailed_timeclock;
		$model->setParams(array('is_view_only_self' => true,'start_date'=>$start_date, 'end_date'=>$end_date, 'employee_id' =>$employee_id, 'offset' => $offset, 'export_excel'=> $export_excel));

		$headers = $model->getDataColumns();
		$report_data = $model->getData();

		$tabular_data = array();
		$report_data = $model->getData();

		foreach($report_data as $row)
		{
			$data_row = array();

			$data_row[] = array('data'=>date(get_date_format().' '.get_time_format(), strtotime($row['clock_in'])), 'align' => 'left');
			
			if ($row['clock_out'] != '0000-00-00 00:00:00')
			{
				$data_row[] = array('data'=>date(get_date_format().' '.get_time_format(), strtotime($row['clock_out'])), 'align' => 'left');
				$t1 = strtotime ($row['clock_out']);
				$t2 = strtotime ($row['clock_in']);
				$diff = $t1 - $t2;
				$hours = $diff / ( 60 * 60 );
				
				//Not really the purpose of this function; but it rounds to 2 decimals
				$hours = to_currency_no_money($hours,2);	
			}
			else
			{
				$data_row[] = array('data'=>lang('reports_not_clocked_out'), 'align' => 'left');
				$hours = lang('reports_not_clocked_out');				
			}
			
			$data_row[] = array('data'=>$hours, 'align' => 'left');			
			$data_row[] = array('data'=>to_currency($row['hourly_pay_rate']), 'align' => 'left');			
			$data_row[] = array('data'=>to_currency($row['hourly_pay_rate'] * $hours), 'align' => 'left');			
			$data_row[] = array('data'=>$row['clock_in_comment'], 'align' => 'left');			
			$data_row[] = array('data'=>$row['clock_out_comment'], 'align' => 'left');	
			$data_row[] = array('data'=>$row['ip_address_clock_in'], 'align' => 'left');
			$data_row[] = array('data'=>$row['ip_address_clock_out'], 'align' => 'left');
					
			$tabular_data[] = $data_row;			
		} 

		$employee_info = $this->Employee->get_info($employee_id);

		$data = array(
			"title" => ($employee_id != -1 ? $employee_info->first_name . ' '.$employee_info->last_name . ' - ' : ' ').lang('reports_detailed_timeclock_report'),
			"subtitle" => date(get_date_format(), strtotime($start_date)) .'-'.date(get_date_format(), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(),
			"export_excel" => $export_excel,
			"pagination" => false,
		);

		$this->load->view("timeclocks/report",$data);
	}
	
	function time_off()
	{
		$this->lang->load('reports');
		$start_date=date('Y-m-d', strtotime('-12 weeks'));
		$end_date=date('Y-m-d',strtotime('+1 year')).' 23:59:59';
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$offset = 0;
		$export_excel = 0;
		$this->load->model('reports/Detailed_time_off');
		$model = $this->Detailed_time_off;
		$model->setParams(array('is_view_only_self' => true,'start_date'=>$start_date, 'end_date'=>$end_date, 'employee_id' =>$employee_id, 'offset' => $offset, 'export_excel'=> $export_excel));

		$headers = $model->getDataColumns();
		$report_data = $model->getData();

		$tabular_data = array();
		$report_data = $model->getData();

		foreach($report_data as $row)
		{
			$data_row = array();
			$data_row[] = array('data'=>date(get_date_format(), strtotime($row['start_day'])), 'align' => 'left');
			$data_row[] = array('data'=>date(get_date_format(), strtotime($row['end_day'])), 'align' => 'left');
			$data_row[] = array('data'=>to_quantity($row['hours_requested']), 'align' => 'left');			
			$data_row[] = array('data'=>boolean_as_string($row['is_paid']), 'align' => 'left');			
			$data_row[] = array('data'=>$row['reason'], 'align' => 'left');
			$data_row[] = array('data'=>!$row['approved'] ? (!$row['deleted'] ? lang('reports_pending') : lang('reports_not_approved')) : lang('common_approved'), 'align' => 'center');
					
			$tabular_data[] = $data_row;			
		} 

		$employee_info = $this->Employee->get_info($employee_id);

		$data = array(
			"title" => ($employee_id != -1 ? $employee_info->first_name . ' '.$employee_info->last_name . ' - ' : ' ').lang('reports_time_off_report'),
			"subtitle" => date(get_date_format(), strtotime($start_date)) .'-'.date(get_date_format(), strtotime($end_date)),
			"headers" => $model->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $model->getSummaryData(),
			"export_excel" => $export_excel,
			"pagination" => false,
		);

		$this->load->view("timeclocks/report",$data);
	}
	
}
?>