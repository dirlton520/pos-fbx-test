<?php
function get_config_key_shared($key)
{
	$CI =& get_instance();
	
	if(is_on_phppos_host())
	{	
		$site_db = $CI->load->database('site', TRUE);
		
		$site_db->from('site_config');	
		$site_db->where('key',$key);
		$query = $site_db->get();
		$return = $query->row_array();
		if(isset($return['value']) && $return['value'])
		{
			return $return['value'];
		}		
	}
	else
	{
		return $CI->config->item($key);
	}
	
	return NULL;
}