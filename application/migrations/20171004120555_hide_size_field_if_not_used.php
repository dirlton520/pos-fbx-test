<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_hide_size_field_if_not_used extends MY_Migration 
	{

	    public function up() 
			{
				$this->db->select('count(*) as count');
				$this->db->from('items');
				$this->db->where('size != ""');
				$row = $this->db->get()->row_array();
				
				if ($row['count'] == 0)
				{
					$this->load->model('Appconfig');
					$this->Appconfig->save('hide_size_field',1);
				}
	    }

	    public function down() 
			{
	    }

	}