<?php  if ( ! defined('BASEPATH')) exit("No direct script access allowed");

class Migrate extends MY_Controller {

  public function __construct()
  {
    parent::__construct();
    $this->load->library('migration');
		$this->lang->load('migrate');
	}
	
	public function start()
	{
		if (!is_on_phppos_host())
		{
			if ($this->db->table_exists('sessions') && $this->db->field_exists('data','sessions'))
			{
				$this->session->unset_userdata('total_migrations');
				$this->session->unset_userdata('migrations_ran');
			}
			
			$data = array();
			$data['is_new'] = FALSE;
		
			$tables_in_db = $this->db->list_tables();
			//Fill up database for initial load
			if (count($tables_in_db) == 0 || (count($tables_in_db) == 1 && $tables_in_db[0] == $this->db->dbprefix('migrations')))
			{
				$data['is_new'] = TRUE;
			}
			$this->load->view('migrate/start',$data);
		}
	}
	
	function _get_total_number_of_migrations_to_run()
	{
		if ($this->db->table_exists('sessions') && $this->db->field_exists('data','sessions'))
		{			
			if ($this->session->userdata('total_migrations'))
			{
				return $this->session->userdata('total_migrations');
			}

			$total_migrations = 0;
			$migrations = $this->migration->find_migrations();
			foreach($migrations as $migration_key => $value)
			{
				//Count migrations between current migration version 
				if ($this->migration->get_migration_number($migration_key) > $this->migration->get_version())
				{
					$total_migrations++;
				}
			}
			
			$this->session->set_userdata('total_migrations',$total_migrations);
			return $this->session->userdata('total_migrations');
		}
		
		return 1;
	}
	
	function _get_total_number_of_migrations_ran()
	{
		if ($this->db->table_exists('sessions') && $this->db->field_exists('data','sessions'))
		{
			return $this->session->userdata('migrations_ran') ? $this->session->userdata('migrations_ran') : 0;
		}
		return 0;
	}
	
	function _migrations_ran()
	{
		if ($this->db->table_exists('sessions') && $this->db->field_exists('data','sessions'))
		{
			$this->session->set_userdata('migrations_ran',$this->_get_total_number_of_migrations_ran()+1);		
		}
		return 0;
	}
	
	public function migrate_one_step()
	{
		if (!is_on_phppos_host())
		{
			$cur_migration_version = $this->migration->get_version();
			$migrations = $this->migration->find_migrations();
			$total_migrations = $this->_get_total_number_of_migrations_to_run();
			$number_of_migrations_completed = $this->_get_total_number_of_migrations_ran();
			
			$migration_to_run = false;
			if ($cur_migration_version)
			{
				foreach($migrations as $migration_key => $value)
				{				
					//We found in last step; now the next one is it!
					if (isset($found) && $found)
					{
						$migration_to_run = $value;
						break;	
					}
					if ($migration_key == $cur_migration_version)
					{
						$found = TRUE;
					}
				}
			}
			else
			{
				$migration_to_run = array_shift($migrations);
			}
		
			if ($migration_to_run)
			{
				$name = basename($migration_to_run, '.php');
				$version = $this->migration->get_migration_number($name);
				$message = lang('migrate_'.substr($name,strpos($name,'_')+1));
				$percent_complete = floor(($number_of_migrations_completed/$total_migrations)*100);
				$this->migration->version($version);
				$this->_migrations_ran();
				$has_next_step = TRUE;
			}
			else
			{
				$message = lang('migrate_complete');
				$percent_complete = 100;
				$has_next_step = FALSE;
				if ($this->db->table_exists('sessions') && $this->db->field_exists('data','sessions'))
				{
					$this->session->unset_userdata('total_migrations');
					$this->session->unset_userdata('migrations_ran');
				}
			}
			echo json_encode(array('success' => TRUE, 'has_next_step' => $has_next_step, 'percent_complete' => $percent_complete, 'message' => $message));

		}
	}
	
	//$db_override is NOT used at all; but in database.php to select database based on CLI args for cloud
  public function index($db_override = '')
  {
		if ($this->input->is_cli_request())
		{ 
    	$this->migration->current();
		}
	}
	
	//$db_override is NOT used at all; but in database.php to select database based on CLI args for cloud
  public function version($version,$db_override='')
  {
      if($this->input->is_cli_request())
      {
         $migration = $this->migration->version($version);
      }
  }
}
