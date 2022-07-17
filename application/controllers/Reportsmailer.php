<?php
class Reportsmailer extends MY_Controller 
{	
		function __construct()
		{
			ini_set('memory_limit','1024M');
			parent::__construct();
			if (!is_cli())//Running from web should have report permissions
			{	
				die('Must run from cli');
			}			
		}
				
		/*
		This function is used to send automatic report email each day
		*/
		// $base_url is used NOT used in this function but in application/config/config.php
		//$db_override is NOT used at all; but in database.php to select database based on CLI args for cron in cloud
      public function cron($base_url='', $db_override = '')
      {
		ignore_user_abort(TRUE);
		set_time_limit(0);
		ini_set('max_input_time','-1');
		session_write_close();
		
        //Cron's always run on current server path; but if we are between migrations we should run the cron on the previous folder passing along any arguements
        if (defined('SHOULD_BE_ON_OLD') && SHOULD_BE_ON_OLD)
        {
            global $argc, $argv;
            $prev_folder = isset($_SERVER['CI_PREV_FOLDER']) ?  $_SERVER['CI_PREV_FOLDER'] : 'PHP-Point-Of-Sale-Prev';
            system('php '.FCPATH."$prev_folder/index.php reportsmailer cron ".$argv[3].$prev_folder.'/ '.$argv[4]);
            exit();
        }
		
		require_once (APPPATH."models/reports/Report.php");
		$this->load->helper('report');

		foreach($this->Location->get_all()->result_array() as $location)
		{
			if (!$location['auto_reports_email'])
			{
				continue;
			}
			
			$days_to_subtract = $location['auto_reports_day'] == 'previous_day' ? 1 : 0;
			
			date_default_timezone_set($this->Location->get_info_for_key('timezone',$location['location_id']));
			
			
			//not time to run
			if (date('H') != date('H',strtotime($location['auto_reports_email_time'])))
			{
				continue;
			}
			
			$location_id = $location['location_id'];
			$_GET['location_ids'] = array($location_id);
			$from = $location['auto_reports_email'];
			$to = $location['auto_reports_email'];
			$subject = lang('reports_daily_report').' - '.date(get_date_format(),strtotime("-$days_to_subtract days"));
		
			
			$report_model = Report::get_report_model('closeout');			
			$input_parameters = array('hide_next_and_prev_days' => TRUE,'start_date' => date('Y-m-d',strtotime("-$days_to_subtract days")),'end_date' => date('Y-m-d',strtotime("-$days_to_subtract days")),'export_excel' => 0);
			$report_model->setParams($input_parameters);
			$output_data = $report_model->getOutputData();
			$body = $this->load->view('reports/outputs/tabular_closeout_email',array_merge(array('key' => 'closeout','headersshow' => array()),$output_data,$input_parameters), TRUE);
			
			$this->load->library('email');
			$config['mailtype'] = 'html';
		
			$this->email->initialize($config);
			$this->email->from($from);
			$this->email->to($to);
			$this->email->subject($subject);
			$this->email->message($body);	
			$this->email->send();
			echo lang('common_sent_email').' '.$subject.' '.$to."\n";
		}
	  }
}
?>