<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_scan_and_add_sales_and_receivings_seperate extends MY_Migration 
	{

	    public function up() 
			{
				if ($this->config->item('scan_and_set'))
				{
					$this->Appconfig->save('scan_and_set_sales',1);
					$this->Appconfig->save('scan_and_set_recv',1);
				}
	    }

	    public function down() 
			{
	    }

	}