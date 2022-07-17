<?php

function simple_date_range_to_date($simple_key,$with_time = false,$end_date_end_of_day = true)
{
	$CI =& get_instance();
	$week_start_day = $CI->config->item('week_start_day') ? $CI->config->item('week_start_day') : 'monday';
	$week_end_day = $week_start_day == 'monday' ? 'sunday' : 'saturday' ;
	
	if(!$with_time)
	{
		$end_of_all_time = date('Y-m-d',strtotime('2037-12-31'));
		$today =  date('Y-m-d');
		$yesterday = date('Y-m-d', mktime(0,0,0,date("m"),date("d")-1,date("Y")));
		$six_days_ago = date('Y-m-d', mktime(0,0,0,date("m"),date("d")-6,date("Y")));
		$twenty_nine_days_ago = date('Y-m-d', mktime(0,0,0,date("m"),date("d")-29,date("Y")));
		$start_of_this_month = date('Y-m-d', mktime(0,0,0,date("m"),1,date("Y")));
		$end_of_this_month = date('Y-m-d',strtotime('-1 second',strtotime('+1 month',strtotime(date('m').'/01/'.date('Y').' 00:00:00'))));
		$start_of_last_month = date('Y-m-d', mktime(0,0,0,date("m")-1,1,date("Y")));
		$end_of_last_month = date('Y-m-d',strtotime('-1 second',strtotime('+1 month',strtotime((date('m') - 1).'/01/'.date('Y').' 00:00:00'))));
		$start_of_this_year =  date('Y-m-d', mktime(0,0,0,1,1,date("Y")));
		$end_of_this_year =  date('Y-m-d', mktime(0,0,0,12,31,date("Y")));
		$start_of_last_year =  date('Y-m-d', mktime(0,0,0,1,1,date("Y")-1));
		$end_of_last_year =  date('Y-m-d', mktime(0,0,0,12,31,date("Y")-1));
		$start_of_time =  date('Y-m-d', 0);

		$previous_week = strtotime("-1 week +1 day");
		$current_week = strtotime("-0 week +1 day");

		$previous_start_week = strtotime("last $week_start_day midnight",$previous_week);
		$previous_end_week = strtotime("next $week_end_day",$previous_start_week);

		$previous_start_week = date("Y-m-d",$previous_start_week);
		$previous_end_week = date("Y-m-d",$previous_end_week);

		$current_start_week = strtotime("last $week_start_day midnight",$current_week);
		$current_end_week = strtotime("next $week_end_day",$current_start_week);

		$current_start_week = date("Y-m-d",$current_start_week);
		$current_end_week = date("Y-m-d",$current_end_week);
		
		
		$current_month = date('m');
		$current_year = date('Y');
		
		
		if($current_month>=1 && $current_month<=3)
		{
			$start_of_this_quarter = strtotime('1-January-'.$current_year); 
			$end_of_this_quarter = strtotime('31-March-'.$current_year);
		}
		elseif($current_month>=4 && $current_month<=6)
		{
			$start_of_this_quarter = strtotime('1-April-'.$current_year);
			$end_of_this_quarter = strtotime('30-June-'.$current_year); 
		}
		elseif($current_month>=7 && $current_month<=9)
		{
			$start_of_this_quarter = strtotime('1-July-'.$current_year);
			$end_of_this_quarter = strtotime('30-September-'.$current_year);
		}
		elseif($current_month>=10 && $current_month<=12)
		{
			$start_of_this_quarter = strtotime('1-October-'.$current_year);
			$end_of_this_quarter = strtotime('31-December-'.$current_year);
		}
		$start_of_this_quarter = date("Y-m-d", $start_of_this_quarter);
		$end_of_this_quarter = date("Y-m-d", $end_of_this_quarter);
		
		
		if($current_month>=1 && $current_month<=3)
		{
			$start_of_last_quarter = strtotime('1-October-'.($current_year-1));
			$end_of_last_quarter = strtotime('31-December-'.($current_year-1)); 
		} 
		elseif($current_month>=4 && $current_month<=6)
		{
			$start_of_last_quarter = strtotime('1-January-'.$current_year);
			$end_of_last_quarter = strtotime('31-March-'.$current_year); 
		}
		elseif($current_month>=7 && $current_month<=9)
		{
			$start_of_last_quarter = strtotime('1-April-'.$current_year);
			$end_of_last_quarter = strtotime('30-June-'.$current_year);
		}
		elseif($current_month>=10 && $current_month<=12)
		{
			$start_of_last_quarter = strtotime('1-July-'.$current_year);
			$end_of_last_quarter = strtotime('30-September-'.$current_year);
		}

		$start_of_last_quarter = date("Y-m-d", $start_of_last_quarter);
		$end_of_last_quarter = date("Y-m-d", $end_of_last_quarter);
		
		$end_of_day_suffix = '';
		
		if ((!isset($with_time) || $with_time == false) && (!isset($end_date_end_of_day) || $end_date_end_of_day === TRUE))
		{
			$end_of_day_suffix ='  23:59:59';
		}
	
		$dates = array(
		'TODAY'			=> 		array('start_date' => $today,'end_date'=> $today.$end_of_day_suffix),
		'YESTERDAY'	=> 		array('start_date' =>$yesterday ,'end_date'=> $yesterday.$end_of_day_suffix),
		'LAST_7'			=> 	array('start_date' =>$six_days_ago ,'end_date' =>$today.$end_of_day_suffix),
		'LAST_30'			=> 	array('start_date' =>$twenty_nine_days_ago ,'end_date' =>$today.$end_of_day_suffix),
		'THIS_WEEK'	=> 		array('start_date' =>$current_start_week ,'end_date' =>$current_end_week.$end_of_day_suffix),
		'LAST_WEEK'	=> 		array('start_date' =>$previous_start_week ,'end_date' =>$previous_end_week.$end_of_day_suffix),
		'THIS_MONTH'	=> 	array('start_date' => $start_of_this_month,'end_date' => $end_of_this_month.$end_of_day_suffix),
		'LAST_MONTH'	=> 	array('start_date' =>$start_of_last_month ,'end_date' => $end_of_last_month.$end_of_day_suffix),
		'THIS_QUARTER'	=>array('start_date' =>$start_of_this_quarter ,'end_date' =>$end_of_this_quarter.$end_of_day_suffix),
		'LAST_QUARTER'	=>array('start_date' =>$start_of_last_quarter ,'end_date' => $end_of_last_quarter.$end_of_day_suffix),
		'THIS_YEAR'	=> 	array('start_date' =>$start_of_this_year ,'end_date' =>$end_of_this_year.$end_of_day_suffix),
		'LAST_YEAR'	=> 	array('start_date' => $start_of_last_year,'end_date' =>$end_of_last_year.$end_of_day_suffix),
		'ALL_TIME'	=> 	array('start_date' =>$start_of_time ,'end_date' =>$end_of_all_time.$end_of_day_suffix)
		);
	}
	else
	{
		$end_of_all_time = date('Y-m-d',strtotime('2037-12-31')).'. 00:00:00';
		
		$today =  date('Y-m-d').' 00:00:00';
		$end_of_today=date('Y-m-d').' 23:59:59';
		$yesterday = date('Y-m-d', mktime(0,0,0,date("m"),date("d")-1,date("Y"))).' 00:00:00';
		$end_of_yesterday=date('Y-m-d', mktime(0,0,0,date("m"),date("d")-1,date("Y"))).' 23:59:59';
		$six_days_ago = date('Y-m-d', mktime(0,0,0,date("m"),date("d")-6,date("Y"))).' 00:00:00';
		$twenty_nine_days_ago = date('Y-m-d', mktime(0,0,0,date("m"),date("d")-29,date("Y"))).' 00:00:00';
		$start_of_this_month = date('Y-m-d', mktime(0,0,0,date("m"),1,date("Y"))).' 00:00:00';
		$end_of_this_month = date('Y-m-d',strtotime('-1 second',strtotime('+1 month',strtotime(date('m').'/01/'.date('Y').' 00:00:00')))).' 23:59:59';
		$start_of_last_month = date('Y-m-d', mktime(0,0,0,date("m")-1,1,date("Y"))).' 00:00:00';
		$end_of_last_month = date('Y-m-d',strtotime('-1 second',strtotime('+1 month',strtotime((date('m') - 1).'/01/'.date('Y').' 00:00:00')))).' 23:59:59';
		$start_of_this_year =  date('Y-m-d', mktime(0,0,0,1,1,date("Y"))).' 00:00:00';
		$end_of_this_year =  date('Y-m-d', mktime(0,0,0,12,31,date("Y"))).' 23:59:59';
		$start_of_last_year =  date('Y-m-d', mktime(0,0,0,1,1,date("Y")-1)).' 00:00:00';
		$end_of_last_year =  date('Y-m-d', mktime(0,0,0,12,31,date("Y")-1)).' 23:59:59';
		$start_of_time =  date('Y-m-d', 0);

		$previous_week = strtotime("-1 week +1 day");
		$current_week = strtotime("-0 week +1 day");

		$previous_start_week = strtotime("last $week_start_day midnight",$previous_week);
		$previous_end_week = strtotime("next $week_end_day",$previous_start_week);

		$previous_start_week = date("Y-m-d",$previous_start_week).' 00:00:00';
		$previous_end_week = date("Y-m-d",$previous_end_week).' 23:59:59';

		$current_start_week = strtotime("last $week_start_day midnight",$current_week);
		$current_end_week = strtotime("next $week_end_day",$current_start_week);

		$current_start_week = date("Y-m-d",$current_start_week).' 00:00:00';
		$current_end_week = date("Y-m-d",$current_end_week).' 23:59:59';
		
		$current_month = date('m');
		$current_year = date('Y');
		
		if($current_month>=1 && $current_month<=3)
		{
			$start_of_this_quarter = strtotime('1-January-'.$current_year);
			$end_of_this_quarter = strtotime('31-March-'.$current_year); 
		}
		elseif($current_month>=4 && $current_month<=6)
		{
			$start_of_this_quarter = strtotime('1-April-'.$current_year);
			$end_of_this_quarter = strtotime('30-June-'.$current_year);
		}
		elseif($current_month>=7 && $current_month<=9)
		{
			$start_of_this_quarter = strtotime('1-July-'.$current_year);
			$end_of_this_quarter = strtotime('30-September-'.$current_year);
		}
		elseif($current_month>=10 && $current_month<=12)
		{
			$start_of_this_quarter = strtotime('1-October-'.$current_year); 
			$end_of_this_quarter = strtotime('31-December-'.($current_year)); 
		}
		$start_of_this_quarter = date("Y-m-d", $start_of_this_quarter).' 00:00:00';
		$end_of_this_quarter = date("Y-m-d", $end_of_this_quarter).' 23:59:59';
		
		
		if($current_month>=1 && $current_month<=3)
		{
			$start_of_last_quarter = strtotime('1-October-'.($current_year-1));
			$end_of_last_quarter = strtotime('31-December-'.($current_year-1));
		} 
		elseif($current_month>=4 && $current_month<=6)
		{
			$start_of_last_quarter = strtotime('1-January-'.$current_year);
			$end_of_last_quarter = strtotime('31-March-'.$current_year); 
		}
		elseif($current_month>=7 && $current_month<=9)
		{
			$start_of_last_quarter = strtotime('1-April-'.$current_year);
			$end_of_last_quarter = strtotime('30-June-'.$current_year);
		}
		elseif($current_month>=10 && $current_month<=12)
		{
			$start_of_last_quarter = strtotime('1-July-'.$current_year); 
			$end_of_last_quarter = strtotime('30-September-'.$current_year);
		}

		
		$start_of_last_quarter = date("Y-m-d", $start_of_last_quarter).' 00:00:00';
		$end_of_last_quarter = date("Y-m-d", $end_of_last_quarter).' 23:59:59';
		
		$dates = array(
		'TODAY'			=> 		array('start_date' => $today,'end_date'=> $end_of_today),
		'YESTERDAY'	=> 		array('start_date' =>$yesterday ,'end_date'=> $end_of_yesterday),
		'LAST_7'			=> 	array('start_date' =>$six_days_ago ,'end_date' =>$end_of_today),
		'LAST_30'			=> 	array('start_date' =>$twenty_nine_days_ago ,'end_date' =>$end_of_today),
		'THIS_WEEK'	=> 		array('start_date' =>$current_start_week ,'end_date' =>$current_end_week),
		'LAST_WEEK'	=> 		array('start_date' =>$previous_start_week ,'end_date' =>$previous_end_week),
		'THIS_MONTH'	=> 	array('start_date' => $start_of_this_month,'end_date' => $end_of_this_month),
		'LAST_MONTH'	=> 	array('start_date' =>$start_of_last_month ,'end_date' => $end_of_last_month),
		'THIS_QUARTER'	=>array('start_date' =>$start_of_this_quarter ,'end_date' =>$end_of_this_quarter),
		'LAST_QUARTER'	=>array('start_date' =>$start_of_last_quarter ,'end_date' => $end_of_last_quarter),
		'THIS_YEAR'	=> 	array('start_date' =>$start_of_this_year ,'end_date' =>$end_of_this_year),
		'LAST_YEAR'	=> 	array('start_date' => $start_of_last_year,'end_date' =>$end_of_last_year),
		'ALL_TIME'	=> 	array('start_date' =>$start_of_time ,'end_date' =>$end_of_all_time),
	);
		
	}
	
	$start_next_period = FALSE;
	$end_next_period = FALSE;
	$start_previous_period = FALSE;
	$end_previous_period = FALSE;
	
	
		
	if ($CI->input->get('report_type') == 'simple')
	{
		$current_start_date = $dates[$CI->input->get('report_date_range_simple')]['start_date'];
		$current_end_date = $dates[$CI->input->get('report_date_range_simple')]['end_date'];
	}
	elseif($CI->input->get('report_type') == 'complex')
	{
		$current_start_date = $_GET['start_date'];
		$current_end_date = $_GET['end_date'];
	}
	
	$days_between_start_and_end =  abs(floor((strtotime($current_start_date) - strtotime($current_end_date)) / (60 * 60 * 24)));
	
	$start_next_period = date('Y-m-d H:i:s',strtotime("+".$days_between_start_and_end." days", strtotime($current_start_date)));
	$end_next_period = date('Y-m-d H:i:s',strtotime("+".$days_between_start_and_end." days", strtotime($current_end_date)));; 
	
	$start_prev_period = date('Y-m-d H:i:s',strtotime("-".$days_between_start_and_end." days", strtotime($current_start_date)));
	$end_prev_period = date('Y-m-d H:i:s',strtotime("-".$days_between_start_and_end." days", strtotime($current_end_date)));
	
	$start_date_same_date_last_year = date('Y-m-d H:i:s',strtotime("-1 year", strtotime($current_start_date)));
	$end_date_same_date_last_year = date('Y-m-d H:i:s',strtotime("-1 year", strtotime($current_end_date)));

	$dates['NEXT_PERIOD'] = array('start_date' => $start_next_period,'end_date'=> $end_next_period);
	$dates['PREVIOUS_PERIOD'] = array('start_date' => $start_prev_period,'end_date'=> $end_prev_period);
	$dates['SAME_DATE_LAST_YEAR'] = array('start_date' => $start_date_same_date_last_year,'end_date'=> $end_date_same_date_last_year);
	
	return $dates[$simple_key];
}

//Some reports need time information others do not. So this allows us to reuse this function. The $time parameter should be passed from the corresponding
//date_input_excel_whatever_specific_blabla that calls the private function: _get_common_report_data, that in turn, calls this helper function.
function get_simple_date_ranges()
{
	return array(
		'TODAY'			=> 		lang('reports_today'),
		'YESTERDAY'	=> 		lang('reports_yesterday'),
		'LAST_7'			=> 	lang('reports_last_7'),
		'LAST_30'			=> lang('common_last_30_days'),
		'THIS_WEEK'	=> 		lang('reports_this_week'),
		'LAST_WEEK'	=> 		lang('reports_last_week'),
		'THIS_MONTH'	=> 	lang('reports_this_month'),
		'LAST_MONTH'	=> 	lang('reports_last_month'),
		'THIS_QUARTER'	=>lang('reports_this_quarter'),
		'LAST_QUARTER'	=>lang('reports_last_quarter'),
		'THIS_YEAR'	=> 	lang('reports_this_year'),
		'LAST_YEAR'	=> 	lang('reports_last_year'),
		'ALL_TIME'	=> 	lang('reports_all_time'),
		'CUSTOM' => lang('reports_custom_date_range'),
	);
	
}

function get_simple_data_ranges_compare()
{
	$compare_ranges = array(
		'NEXT_PERIOD'			=> lang('reports_next_period'),
		'PREVIOUS_PERIOD'		=> 		lang('reports_previous_period'),
		'SAME_DATE_LAST_YEAR'		=> 	lang('reports_same_dates_last_year'),
	
	);
	
	return $compare_ranges + get_simple_date_ranges();
}

function get_simple_date_ranges_expire()
{
	return array(
		'TODAY'			=> 		lang('reports_today'),
		'THIS_WEEK'	=> 		lang('reports_this_week'),
		'THIS_MONTH'	=> 	lang('reports_this_month'),
		'CUSTOM' => lang('reports_custom_date_range'));
}

function get_months()
{
	$months = array();
	for($k=1;$k<=12;$k++)
	{
		$cur_month = mktime(0, 0, 0, $k, 1, 2000);
		$months[date("m", $cur_month)] = get_month_translation(date("m", $cur_month));
	}

	return $months;
}

function get_month_translation($month_numeric)
{
	return lang('reports_month_'.$month_numeric);
}

function get_days()
{
	$days = array();

	for($k=1;$k<=31;$k++)
	{
		$cur_day = mktime(0, 0, 0, 1, $k, 2000);
		$days[date('d',$cur_day)] = date('j',$cur_day);
	}

	return $days;
}

function get_years()
{
	$years = array();
	for($k=0;$k<10;$k++)
	{
		$years[date("Y")-$k] = date("Y")-$k;
	}

	return $years;
}

function get_hours($time_format)
    {
       $hours = array();
	   if($time_format == '24_hour')
	   {
       for($k=0;$k<24;$k++)
		{
          $hours[$k] = $k;
		}
	   }
	   else 
	   {
		for($k=0;$k<24;$k++)
		{
		
          $hours[$k]  = date('h a', mktime($k));
		
		}
		
		
	   }
       return $hours;
    }


    function get_minutes()
    {
       $hours = array();
       for($k=0;$k<60;$k++)
       {
          $minutes[$k] = $k;
       }
       return $minutes;
    }


function get_random_colors($how_many)
{
	$colors = array();

	for($k=0;$k<$how_many;$k++)
	{
		$colors[] = '#'.random_color();
	}

	return $colors;
}

function random_color()
{
    mt_srand((double)microtime()*1000000);
    $c = '';
    while(strlen($c)<6){
        $c .= sprintf("%02X", mt_rand(0, 255));
    }
    return $c;
}

function get_template_colors()
{
	//https://flatuicolors.com
	return array('#1abc9c','#16a085','#f1c40f','#f39c12','#2ecc71','#27ae60','#e67e22','#d35400','#3799dc','#2980b9','#e74c3c','#c0392b','#9b59b6','#8e44ad','#ecf0f1','#bdc3c7','#34495e','#2c3e50','#95a5a6','#7f8c8d');
}

function get_time_intervals()
{
	return array(
		1800 => '30 '.lang('common_minutes'),
		3600 => '60 '.lang('common_minutes'),
		5400 => '90 '.lang('common_minutes'),
		7200 => '120 '.lang('common_minutes'),
		9000 => '150 '.lang('common_minutes'),
		10800 => '180 '.lang('common_minutes'),
	);
}

function can_display_graphical_report()
{
	$CI =& get_instance();
	return !$CI->agent->is_android_less_than_4_4();
}

function get_all_transactions_for_discount()
{
	$CI =& get_instance();
	$return = array();
		
	$CI->lang->load('reports');

	$CI->load->helper('directory');
	$language_folder = directory_map(APPPATH.'language',1);

	$languages = array();

	foreach($language_folder as $language_folder)
	{
		$languages[] = substr($language_folder,0,strlen($language_folder)-1);
	}

	foreach($languages as $language)
	{
		$CI->lang->load('common', $language);
		$return[] = lang('common_discount');
	}

	//Switch back
	$CI->lang->switch_to($CI->config->item('language'));
	
	return $return;

}