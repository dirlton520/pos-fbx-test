<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_items_per_search_suggestion_store_config extends MY_Migration 
	{

	    public function up() 
		{
			$this->load->model('Appconfig');
			$this->Appconfig->save('items_per_search_suggestions',20);
			
			if ($per_page = $this->config->item('number_of_items_per_page'))
			{
				$this->Appconfig->save('items_per_search_suggestions',$per_page);				
			}
			
	    }

	    public function down() 
		{
	    }

	}