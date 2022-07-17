<?php
		$dates = array();
		
		for($k=0;$k<=30;$k++)
		{
			$dates[date('Y-m-d', strtotime('-'.$k.' days'))] = date(get_date_format(), strtotime('-'.$k.' days'));
		}
		
		foreach($this->Employee->get_authenticated_location_ids($this->Employee->get_logged_in_employee_info()->person_id) as $location_id)
		{
			$locations[$location_id] = $this->Location->get_info($location_id)->name;
		}
	?>
	<div class="form-group hidden-print">
		<form action="" method="GET">
	<br />
		<div class="col-sm-9 col-md-9 col-lg-10">
			<?php echo form_dropdown('date', $dates,$this->input->get('date'), 'class="form-control form-inps" id="date"');?>
			<?php echo form_dropdown('sale_type', array('' => lang('reports_all'),'sales' => lang('reports_sales'),'returns' => lang('reports_returns')),$this->input->get('sale_type'), 'class="form-control form-inps" id="sale_type"');?>
			<?php echo form_dropdown('location_id', $locations,$this->input->get('location_id'), 'class="form-control form-inps" id="location_id"');?>
		</div>
		<input type="submit" class="btn btn-primary" />
	<br />	<br />	<br />	<br />	<br />	<br />	<br />
</form>
	</div>
