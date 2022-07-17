<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_tax_classes extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170327133306_tax_classes.sql'));
				
				$this->load->model('Location');
				$this->load->model('Tax_class');
				$this->load->model('Appconfig');
				
				foreach($this->Location->get_all()->result() as $location_info)
				{
					$location_taxes = array();
					$location_id = $location_info->location_id;
					
					//Logic for migrating location taxes to tax class
					$default_tax_1_rate = $this->Location->get_info_for_key('default_tax_1_rate',$location_id);
					$default_tax_1_name = $this->Location->get_info_for_key('default_tax_1_name',$location_id);
				
					$default_tax_2_rate = $this->Location->get_info_for_key('default_tax_2_rate',$location_id);
					$default_tax_2_name = $this->Location->get_info_for_key('default_tax_2_name',$location_id);
					$default_tax_2_cumulative = $this->Location->get_info_for_key('default_tax_2_cumulative',$location_id) ? $this->Location->get_info_for_key('default_tax_2_cumulative',$location_id) : 0;
		
					$default_tax_3_rate = $this->Location->get_info_for_key('default_tax_3_rate',$location_id);
					$default_tax_3_name = $this->Location->get_info_for_key('default_tax_3_name',$location_id);
		
					$default_tax_4_rate = $this->Location->get_info_for_key('default_tax_4_rate',$location_id);
					$default_tax_4_name = $this->Location->get_info_for_key('default_tax_4_name',$location_id);
		
					$default_tax_5_rate = $this->Location->get_info_for_key('default_tax_5_rate',$location_id);
					$default_tax_5_name = $this->Location->get_info_for_key('default_tax_5_name',$location_id);
		
					if ($default_tax_1_rate && is_numeric($default_tax_1_rate))
					{
						$location_taxes[] = array(
							'name' => $default_tax_1_name,
							'percent' => $default_tax_1_rate,
							'cumulative' => 0
						);
					}
		
					if ($default_tax_2_rate && is_numeric($default_tax_2_rate))
					{
						$location_taxes[] = array(
							'name' => $default_tax_2_name,
							'percent' => $default_tax_2_rate,
							'cumulative' => $default_tax_2_cumulative
						);
					}

					if ($default_tax_3_rate && is_numeric($default_tax_3_rate))
					{
						$location_taxes[] = array(
							'name' => $default_tax_3_name,
							'percent' => $default_tax_3_rate,
							'cumulative' => 0
						);
					}


					if ($default_tax_4_rate && is_numeric($default_tax_4_rate))
					{
						$location_taxes[] = array(
							'name' => $default_tax_4_name,
							'percent' => $default_tax_4_rate,
							'cumulative' => 0
						);
					}


					if ($default_tax_5_rate && is_numeric($default_tax_5_rate))
					{
						$location_taxes[] = array(
							'name' => $default_tax_5_name,
							'percent' => $default_tax_5_rate,
							'cumulative' => 0
						);
					}
		
					if (!empty($location_taxes))
					{
						$location_taxes_data = array('location_id' => $location_id,'name' => $location_info->name.' '.lang('reports_taxes'));
						$this->Tax_class->save($location_taxes_data);
						
						$tax_class_id = $location_taxes_data['id'];
						$location_info = array('tax_class_id' => $tax_class_id);
						$this->Location->save($location_info, $location_id);

						$order = 1;
						foreach($location_taxes as $location_tax)
						{
							$location_tax['order'] = $order;
							$location_tax['tax_class_id'] = $tax_class_id;
							$this->Tax_class->save_tax($location_tax);
							$order++;
						}
						
					}
				}
				//Logic for migrating store config taxes to a tax class
				$default_tax_1_rate = $this->config->item('default_tax_1_rate');
				$default_tax_1_name = $this->config->item('default_tax_1_name');
				
				$default_tax_2_rate = $this->config->item('default_tax_2_rate');
				$default_tax_2_name = $this->config->item('default_tax_2_name');
				$default_tax_2_cumulative = $this->config->item('default_tax_2_cumulative') ? $this->config->item('default_tax_2_cumulative') : 0;
		
				$default_tax_3_rate = $this->config->item('default_tax_3_rate');
				$default_tax_3_name = $this->config->item('default_tax_3_name');
		
				$default_tax_4_rate = $this->config->item('default_tax_4_rate');
				$default_tax_4_name = $this->config->item('default_tax_4_name');
		
				$default_tax_5_rate = $this->config->item('default_tax_5_rate');
				$default_tax_5_name = $this->config->item('default_tax_5_name');
		
				$default_taxes = array();
		
				if ($default_tax_1_rate && is_numeric($default_tax_1_rate))
				{
					$default_taxes[] = array(
						'name' => $default_tax_1_name,
						'percent' => $default_tax_1_rate,
						'cumulative' => 0
					);
				}
		
				if ($default_tax_2_rate && is_numeric($default_tax_2_rate))
				{
					$default_taxes[] = array(
						'name' => $default_tax_2_name,
						'percent' => $default_tax_2_rate,
						'cumulative' => $default_tax_2_cumulative
					);
				}

				if ($default_tax_3_rate && is_numeric($default_tax_3_rate))
				{
					$default_taxes[] = array(
						'name' => $default_tax_3_name,
						'percent' => $default_tax_3_rate,
						'cumulative' => 0
					);
				}

				if ($default_tax_4_rate && is_numeric($default_tax_4_rate))
				{
					$default_taxes[] = array(
						'name' => $default_tax_4_name,
						'percent' => $default_tax_4_rate,
						'cumulative' => 0
					);
				}

				if ($default_tax_5_rate && is_numeric($default_tax_5_rate))
				{
					$default_taxes[] = array(
						'name' => $default_tax_5_name,
						'percent' => $default_tax_5_rate,
						'cumulative' => 0
					);
				}
				
				$global_tax_data = array('location_id' => NULL,'name' => lang('reports_taxes'));
				$this->Tax_class->save($global_tax_data);
				$tax_class_id = $global_tax_data['id'];
				$this->Appconfig->save('tax_class_id',$tax_class_id);
				$order = 1;
				foreach($default_taxes as $tax)
				{
					$tax['order'] = $order;
					$tax['tax_class_id'] = $tax_class_id;
					$this->Tax_class->save_tax($tax);
					$order++;
				}
	    }

	    public function down() 
			{
	    }

	}