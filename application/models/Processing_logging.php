<?php
class Processing_logging extends CI_Model
{
	function insert_log($data)
	{
		return $this->db->insert('processing_return_logs',$data);
	}
	
}