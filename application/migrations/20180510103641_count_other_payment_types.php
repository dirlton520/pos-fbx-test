<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_count_other_payment_types extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180510103641_count_other_payment_types.sql'));
				
				if ($this->config->item('track_cash'))
				{
					$this->load->model('Appconfig');
					$this->Appconfig->save('track_payment_types', serialize(array('common_cash')));
				}
	    }

	    public function down() 
			{
	    }

	}