<?php
	class MY_Model extends CI_Model 
	{
		public function __construct() 
		{
			parent::__construct();	
		}
		
		/*
		Returns the count of last query
		Reference: https://dev.mysql.com/doc/refman/8.0/en/information-functions.html#function_found-rows
		*/
		function count_last_query_results()
		{
			$this->db->select('FOUND_ROWS() as found_rows');
			$total_rows_query = $this->db->get();
			$ret = $total_rows_query->row();
			return $ret->found_rows;
		}
	}