<?php
function create_bitrixbreadcrumb($return)
{
	$ci = &get_instance();

	if($ci->uri->segment(1) == 'bitrix')
	{
		if ($ci->uri->segment(2) == NULL) //Main page
		{
			$bitrix_home_link =create_current_page_url(lang('module_bitrix'));
		}
		else
		{			
			$bitrix_home_link = '<a tabindex = "-1" href="'.site_url('bitrix').'">'.lang('module_bitrix').'</a>';
		}
		
		$return.=$bitrix_home_link;
		
		if($ci->uri->segment(2) == 'add')
		{
			if ($ci->uri->segment(3) == -1)
			{
  				$return.=create_current_page_url(lang('bitrix_add'));
			}
			else
			{
  				$return.=create_current_page_url(lang('bitrix_edit'));
			}
		}
	}	
  	return $return;
}
?>