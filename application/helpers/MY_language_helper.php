<?php

function is_rtl_lang()
{
	$CI =& get_instance();
	return ($CI->Employee->is_logged_in() && $CI->Employee->get_logged_in_employee_info()->language  =='arabic') 
			|| ($CI->Employee->is_logged_in() && !$CI->Employee->get_logged_in_employee_info()->language && $CI->config->item('language') == 'arabic') 
			|| !$CI->Employee->is_logged_in() && $CI->config->item('language') == 'arabic';
}

function lang($line, $for = '', $attributes = array(),$second_language = false)
{
	$lazy_load = (!defined("LAZY_LOAD") or LAZY_LOAD == TRUE);
	$CI =& get_instance();
	
	$orig_line = $line;
	$orig_language = $CI->config->item('language');
	$line = get_instance()->lang->line($line);
	
	if (!$line)
	{		
		if ($lazy_load)
		{
			$langfile = substr($orig_line,0,strpos($orig_line,'_')).'_lang.php';
		
			$langpath = APPPATH.'language/'.$CI->config->item('language').'/'.$langfile;
			if (!file_exists($langpath))
			{
				$log_message = "Couldn't load language file $langfile CURRENT_URL: ".current_url().' REQUEST '.var_export($_REQUEST, TRUE);
				log_message('debug', $log_message);
			}
			else
			{
				$CI->lang->load($langfile);
				$log_message = "Lazy Loaded language $langfile for $orig_line CURRENT_URL: ".current_url().' REQUEST '.var_export($_REQUEST, TRUE);
				log_message('debug', $log_message);
				$line = get_instance()->lang->line($orig_line);
			}
		}
		
		if (!$line)
		{
			$log_message = "Couldn't load language key $orig_line CURRENT_URL: ".current_url().' REQUEST '.var_export($_REQUEST, TRUE);
			log_message('debug', $log_message);
			if (ENVIRONMENT =='development')
			{
				die("Couldn't load language key $orig_line");
			}
		}
	}
	
	if ($second_language && $CI->config->item('second_language'))
	{
		$CI->lang->switch_to($CI->config->item('second_language'));
		$line.='/'.lang($orig_line,$for,$attributes,false);
		$CI->lang->switch_to($orig_language);
	}
	
	if ($for !== '')
	{
		$line = '<label for="'.$for.'"'._stringify_attributes($attributes).'>'.$line.'</label>';
	}

	return $line;
}

function get_all_language_values_for_key($lang_key,$langfile = false)
{
	static $languages = array();
	
	$values = array();
	
	$CI =& get_instance();
	$CI->load->helper('directory');
	$language_folder = directory_map(APPPATH.'language',1);


	if (!$languages)
	{
		foreach($language_folder as $language_folder)
		{
			$languages[] = substr($language_folder,0,strlen($language_folder)-1);
		}
	}
	
	foreach($languages as $language)
	{
		if (!$langfile)
		{
			$langfile = substr($lang_key,0,strpos($lang_key,'_')).'_lang.php';
		}
		
		$CI->lang->load($langfile, $language);
		$values[] = lang($lang_key);

	}
	
	//Switch back
	$CI->lang->switch_to($CI->config->item('language'));
	
	return array_unique($values);
}