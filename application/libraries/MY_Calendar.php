<?php
class MY_Calendar extends CI_Calendar
{
	/**
	 * Generate the calendar (monthly, weekly,daily)
	 *
	 * @param	int	the year
	 * @param	int	the month
	 * @param	int	the week in month (1,2,3,4,5,6)
	 * @param	int	the day in month (1...31)
	 * @param	array	the data to be shown in the calendar cells
	 * @return	string
	 */
	public function generate($year = '', $month = '', $week='',$day='', $data = array())
	{
		//If we are doing monthy calendar fall back to parent place
		if (!$week && !$day)
		{
			return parent::generate($year,$month,$data);
		}
		
		//Weekly Calendar
		if ($week && !$day)
		{
			return $this->generate_week($year,$month,$week,$data);
		}
		
		//Daily Calendar
		if ($day)
		{
			return $this->generate_day($year,$month,$week,$day,$data);			
		}
	}
	
	public function generate_week($year,$month,$week,$data)
	{
		$local_time = time();

		// Set and validate the supplied month/year
		if (empty($year))
		{
			$year = date('Y', $local_time);
		}
		elseif (strlen($year) === 1)
		{
			$year = '200'.$year;
		}
		elseif (strlen($year) === 2)
		{
			$year = '20'.$year;
		}

		if (empty($month))
		{
			$month = date('m', $local_time);
		}
		elseif (strlen($month) === 1)
		{
			$month = '0'.$month;
		}

		$adjusted_date = $this->adjust_date($month, $year);

		$month	= $adjusted_date['month'];
		$year	= $adjusted_date['year'];

		// Determine the total days in the month
		$total_days = $this->get_total_days($month, $year);

		// Set the starting day of the week
		$start_days	= array('sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6);
		$start_day	= isset($start_days[$this->start_day]) ? $start_days[$this->start_day] : 0;

		// Set the starting day number
		$local_date = mktime(12, 0, 0, $month, 1, $year);
		$date = getdate($local_date);
		$day  = $start_day + 1 - $date['wday'];

		while ($day > 1)
		{
			$day -= 7;
		}

		// Set the current month/year/day
		// We use this to determine the "today" date
		$cur_year	= date('Y', $local_time);
		$cur_month	= date('m', $local_time);
		$cur_day	= date('j', $local_time);

		$is_current_month = ($cur_year == $year && $cur_month == $month);

		// Generate the template data array
		$this->parse_template();

		// Begin building the calendar output
		$out = $this->replacements['table_open']."\n\n".$this->replacements['heading_row_start']."\n";

		// "previous" week link
		if ($this->show_next_prev === TRUE)
		{
			// Add a trailing slash to the URL if needed
			$this->next_prev_url = preg_replace('/(.+?)\/*$/', '\\1/', $this->next_prev_url);

			$adjusted_date = array();
			$adjusted_date['year']=$year;
			$adjusted_date['month']=$month;
			$adjusted_date['week'] = $week;
			
			if ($week > 1)
			{
				$adjusted_date['week']--;
			}
			else
			{
				if ($adjusted_date['month'] == 1)
				{
					$adjusted_date['month'] = 12;
					$adjusted_date['year']--;
				}
				else
				{
					$adjusted_date['month']--;
				}
				
				$adjusted_date['week'] = $this->num_weeks($adjusted_date['month'], $adjusted_date['year']);
				
			}
			
			$out .= str_replace('{previous_url}', $this->next_prev_url.$adjusted_date['year'].'/'.$adjusted_date['month'].'/'.$adjusted_date['week'], $this->replacements['heading_previous_cell'])."\n";
		}

		// Heading containing the month/year
		$colspan = ($this->show_next_prev === TRUE) ? 5 : 7;

		$this->replacements['heading_title_cell'] = str_replace('{colspan}', $colspan,
								str_replace('{heading}', $this->get_month_name($month).'&nbsp;'.$year, $this->replacements['heading_title_cell']));

		$out .= $this->replacements['heading_title_cell']."\n";

		// "next" week link
		if ($this->show_next_prev === TRUE)
		{
			$adjusted_date = array();
			$adjusted_date['year']=$year;
			$adjusted_date['month']=$month;
			$adjusted_date['week'] = $week;
			$num_weeks_cur_month = $this->num_weeks($month,$year);
			
			if ($week < $num_weeks_cur_month)
			{
				$adjusted_date['week']++;
			}
			else
			{
				if ($adjusted_date['month'] == 12)
				{
					$adjusted_date['month'] = 1;
					$adjusted_date['year']++;
				}
				else
				{
					$adjusted_date['month']++;
				}
				
				$adjusted_date['week'] = 1;
				
			}
			
			
			$out .= str_replace('{next_url}', $this->next_prev_url.$adjusted_date['year'].'/'.$adjusted_date['month'].'/'.$adjusted_date['week'], $this->replacements['heading_next_cell']);
		}

		$out .= "\n".$this->replacements['heading_row_end']."\n\n"
			// Write the cells containing the days of the week
			.$this->replacements['week_row_start']."\n";

		$day_names = $this->get_day_names();

		for ($i = 0; $i < 7; $i ++)
		{
			$out .= str_replace('{week_day}', $day_names[($start_day + $i) %7], $this->replacements['week_day_cell']);
		}

		$out .= "\n".$this->replacements['week_row_end']."\n";
		
		$week_counter = 1;
		
		// Build the main body of the calendar
		while ($day <= $total_days)
		{
			if ($week_counter == $week)
			{
				$out .= "\n".$this->replacements['cal_row_start']."\n";
			}
			
			for ($i = 0; $i < 7; $i++)
			{
				if ($day > 0 && $day <= $total_days)
				{
					if ($week_counter == $week)
					{
						$out .= ($is_current_month === TRUE && $day == $cur_day) ? $this->replacements['cal_cell_start_today'] : $this->replacements['cal_cell_start'];
					}
					
					if (isset($data[$day]))
					{
						// Cells with content
						$temp = ($is_current_month === TRUE && $day == $cur_day) ?
								$this->replacements['cal_cell_content_today'] : $this->replacements['cal_cell_content'];
						
						if ($week_counter == $week)
						{
							$out .= str_replace(array('{content}', '{day}'), array($data[$day], $day), $temp);
						}
					}
					else
					{
						// Cells with no content
						$temp = ($is_current_month === TRUE && $day == $cur_day) ?
								$this->replacements['cal_cell_no_content_today'] : $this->replacements['cal_cell_no_content'];
						if ($week_counter == $week)
						{
							$out .= str_replace('{day}', $day, $temp);
						}
					}

					
					if ($week_counter == $week)
					{
						$out .= ($is_current_month === TRUE && $day == $cur_day) ? $this->replacements['cal_cell_end_today'] : $this->replacements['cal_cell_end'];
					}
				}
				elseif ($this->show_other_days === TRUE)
				{
					
					if ($week_counter == $week)
					{
						$out .= $this->replacements['cal_cell_start_other'];
					}
					
					if ($day <= 0)
					{
						// Day of previous month
						$prev_month = $this->adjust_date($month - 1, $year);
						$prev_month_days = $this->get_total_days($prev_month['month'], $prev_month['year']);
						if ($week_counter == $week)
						{
							$out .= str_replace('{day}', $prev_month_days + $day, $this->replacements['cal_cell_other']);
						}
					}
					else
					{
						// Day of next month
						if ($week_counter == $week)
						{
							$out .= str_replace('{day}', $day - $total_days, $this->replacements['cal_cell_other']);
						}
					}

					if ($week_counter == $week)
					{
						$out .= $this->replacements['cal_cell_end_other'];
					}
				}
				else
				{
					// Blank cells
					if ($week_counter == $week)
					{
						$out .= $this->replacements['cal_cell_start'].$this->replacements['cal_cell_blank'].$this->replacements['cal_cell_end'];
					}
				}

				$day++;
			}
			
			if ($week_counter == $week)
			{
				$out .= "\n".$this->replacements['cal_row_end']."\n";
			}
		
		$week_counter++;
		
		}

		return $out .= "\n".$this->replacements['table_close'];
	}
	
	public function generate_day($year,$month,$week,$day,$data)
	{
		$today = strtotime($year.'-'.$month.'-'.$day);
		$previous_day_year  = date('Y',strtotime('-1 day', $today));
		$previous_day_month = date('m',strtotime('-1 day', $today));
		$previous_day_day = date('j',strtotime('-1 day', $today));

		$next_day_year  = date('Y',strtotime('+1 day', $today));
		$next_day_month = date('m',strtotime('+1 day', $today));
		$next_day_day = date('j',strtotime('+1 day', $today));
		
		$this->next_prev_url = preg_replace('/(.+?)\/(.+?)\/(.+?)\*$/', '\\1\2\3/', $this->next_prev_url);
		
		$out = '<div class="text-center"><h4><span class="ion-calendar"></span> ' . date(get_date_format(), $today) . '</h4></div>';
	
		
		$out .= '</div>';
		
		$out.='<ul class="list-group">';
		foreach($data as $data_point)
		{
			$out.= $data_point;
		}
		$out.='</ul>';
		
		$out .= '<div class="panel-footer hidden-print">';
		if ($this->show_next_prev === TRUE)
		{
			$out.='<a class="btn btn-default" href="'.$this->next_prev_url.'/'.$previous_day_year.'/'.$previous_day_month.'/'.$week.'/'.$previous_day_day.'"><span class="ion-ios-arrow-left"></span> '.lang('common_previous_day').'</a>'; 
			$out.='<a class="btn btn-default pull-right" href="'.$this->next_prev_url.'/'.$next_day_year.'/'.$next_day_month.'/'.$week.'/'.$next_day_day.'">'.lang('common_next_day').' <span class="ion-ios-arrow-right"></span></a>'; 
		}
		$out .= '<div>';
		
		
		return $out;
	}
	
	function num_weeks($month, $year)
	{
      $firstday = date("w", mktime(0, 0, 0, $month, 1, $year)); 
      $lastday = date("t", mktime(0, 0, 0, $month, 1, $year));
      $count_weeks = 1 + ceil(($lastday-8+$firstday)/7);
      return $count_weeks;
	} 
}
?>