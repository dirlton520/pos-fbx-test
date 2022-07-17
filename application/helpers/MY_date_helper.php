<?php

/**
* Returns the amount of weeks into the month a date is
* @param $date a YYYY-MM-DD formatted date
* @param $rollover The day on which the week rolls over
*/
function getWeeks($date, $rollover = "sunday")
{
   $cut = substr($date, 0, 8);
   $daylen = 86400;

   $timestamp = strtotime($date);
   $first = strtotime($cut . "00");
   $elapsed = ($timestamp - $first) / $daylen;

   $weeks = 1;

   for ($i = 1; $i <= $elapsed; $i++)
   {
       $dayfind = $cut . (strlen($i) < 2 ? '0' . $i : $i);
       $daytimestamp = strtotime($dayfind);

       $day = strtolower(date("l", $daytimestamp));

       if($day == strtolower($rollover))  $weeks ++;
   }

   return $weeks;
}

function get_date_format_extended()
{
	$CI =& get_instance();
	switch($CI->config->item('date_format'))
	{
		case "middle_endian":
			return "01/30/2000";
		case "little_endian":
			return "30-01-2000";
		case "big_endian":
			return "2000-30-01";
		default:
			return "01/30/2000";
	}
}
	 
function get_date_format()
{
	$CI =& get_instance();
	switch($CI->config->item('date_format'))
	{
		case "middle_endian":
			return "m/d/Y";
		case "little_endian":
			return "d-m-Y";
		case "big_endian":
			return "Y-m-d";
		default:
			return "m/d/Y";
	}
}

function get_mysql_date_format()
{
	$CI =& get_instance();
	switch($CI->config->item('date_format'))
	{
		case "middle_endian":
			return "%m/%d/%Y";
		case "little_endian":
			return "%d-%m-%Y";
		case "big_endian":
			return "%Y-%m-%d";
		default:
			return "%m/%d/%Y";
	}	
}

function get_js_date_format()
{
	$CI =& get_instance();
	switch($CI->config->item('date_format'))
	{
		case "middle_endian":
			return "MM/DD/YYYY";
		case "little_endian":
			return "DD-MM-YYYY";
		case "big_endian":
			return "YYYY-MM-DD";
		default:
		return "MM/DD/YYYY";
	}
}



function get_time_format()
{
	$CI =& get_instance();
	switch($CI->config->item('time_format'))
	{
		case "12_hour":
			return "h:i a";
		case "24_hour":
			return "H:i";
		default:
			return "h:i a";
	}
}

function get_js_time_format()
{
	$CI =& get_instance();
	$locale = get_js_locale();
	
	switch($CI->config->item('time_format'))
	{
		case "12_hour":
			if ($locale == 'id')
			{
				return 'LT';
			}
		return "hh:mm a";
		case "24_hour":
			return "HH:mm";
		default:
			if ($locale == 'id')
			{
				return 'LT';
			}
			return "hh:mm a";
	}
}

function get_js_locale()
{
	$CI =& get_instance();
	$languages = array(
				'english'  => 'en',
				'indonesia'    => 'id',
				'spanish'   => 'es', 
				'french'    => 'fr',
				'italian'    => 'it',
				'german'    => 'de',
				'dutch'    => 'nl',
				'portugues'    => 'pt',
				'arabic' => 'ar-ly',
				'khmer' => 'km',
				'vietnamese'   => 'vi', 
				'chinese' => 'zh-cn',
				'chinese_traditional' => 'zh-tw',
				'tamil' => 'ta',
				);

	return isset($languages[$CI->config->item("language")]) ? $languages[$CI->config->item("language")] : 'en';
}

function datetime_as_display_date($val)
{
	if ($val)
	{
		//Not timestamp
		if (isValidTimeStamp($val))
		{
			return date(get_date_format(), $val);
		}
		
		if(isValidTimeStamp((string)strtotime($val))) 
		{
			$val = strtotime($val);
			return date(get_date_format().' '.get_time_format(), $val);
		}
	}
	
	return lang('common_not_set');
}

function date_as_display_date($val)
{
	if ($val)
	{
		//If we are passed in a valid timestamp
		if (isValidTimeStamp($val))
		{
			return date(get_date_format(), $val);
		}
		
		if(isValidTimeStamp((string)strtotime($val))) 
		{
			$val = strtotime($val);
			return date(get_date_format(), $val);
		}
	}
	
	return lang('common_not_set');
}

function date_as_display_datetime($val)
{
	if ($val)
	{
		//If we are passed in a valid timestamp
		if (isValidTimeStamp($val))
		{
			return date(get_date_format().' '.get_time_format(), $val);
		}
		
		if(isValidTimeStamp((string)strtotime($val))) 
		{
			$val = strtotime($val);
			return date(get_date_format().' '.get_time_format(), $val);
		}
	}
	
	return lang('common_not_set');
}

function isValidTimeStamp($timestamp)
{
    return ((string) (int) $timestamp === $timestamp) 
        && ($timestamp <= PHP_INT_MAX)
        && ($timestamp >= ~PHP_INT_MAX);
}

function getDateFromGMT($date)
{
	return date('Y-m-d H:i:s', strtotime($date."+00:00"));
}

function days_until($date)
{
    return (isset($date)) ? ceil((strtotime($date) - time())/60/60/24) : FALSE;
}

function days_between_dates($date_1,$date_2)
{
	$date_1 = strtotime($date_1); // or your date as well
	$date_2 = strtotime($date_2);
	$datediff = $date_1 - $date_2;

	return round($datediff / (60 * 60 * 24));
}

//https://surniaulula.com/2016/lang/php/php-create-an-array-of-hours/
function get_hours_range( $start = 0, $end = 86400, $step = 3600, $format = 'g:i a' ) 
{
        $times = array();
        foreach ( range( $start, $end, $step ) as $timestamp ) {
                $hour_mins = gmdate( 'H:i', $timestamp );
                if ( ! empty( $format ) )
                        $times[$hour_mins] = gmdate( $format, $timestamp );
                else $times[$hour_mins] = $hour_mins;
        }
        return $times;
}
?>
