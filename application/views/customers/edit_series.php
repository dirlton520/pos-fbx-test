<?php $this->load->view("partial/header"); ?>
	<div class="container-fluid" id="form">
		<div class="row">	
			<div class="panel panel-piluku">
				<div class="panel-heading">
					<?php echo lang("common_series"); ?>
				</div>
				<div class="panel-body">
				<?php echo form_open((isset($is_customer_form) && $is_customer_form ? 'customers' : 'reports').'/save_series/'.$series->id.'?'.$_SERVER['QUERY_STRING'],array('id'=>'series_form','class'=>'form-horizontal')); ?>
				
				<div class="form-group">	
					<?php echo form_label(lang('common_customer').':', 'quantity_remaining',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
				<?php
				echo $customer_name;
				?>
			</div>
		</div>
				
				<?php if (time() < strtotime($series->expire_date)) { ?>
				<div class="form-group">	
					<?php echo form_label(lang('common_quantity_remaining').':', 'quantity_remaining',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array('type' => 'number','name'=>'quantity_remaining','value'=>to_quantity($series->quantity_remaining), 'id'=>'quantity_remaining', 'class'=>'form-control'));?>
					</div>
				</div>
				<?php } else { 
					
					?>
					<div class="form-group">	
						<?php echo form_label(lang('common_quantity_remaining').':', 'quantity_remaining',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
						<div class="col-sm-9 col-md-9 col-lg-10">
					<?php
					echo to_quantity($series->quantity_remaining).' ('.lang('common_expired').')';
					echo form_hidden('quantity_remaining',to_quantity($series->quantity_remaining));
					?>
				</div>
			</div>
			<?php
				}?>
				<div class="form-group">	
					<?php echo form_label(lang('common_expire_date').':', 'expire_date',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label ')); ?>
					<div class="col-sm-9 col-md-9 col-lg-10">
						<?php echo form_input(array('name'=>'expire_date','value'=>date(get_date_format(), strtotime($series->expire_date)), 'id'=>'expire_date', 'class'=>'form-control'));?>
					</div>
				</div>
				
				
				<div class="form-actions pull-right">
					<?php echo form_submit(array(
					'name'=>'submitf',
					'id'=>'submitf',
					'value'=>lang('common_save'),
					'class'=>'btn btn-primary')
					); ?>	
				</div>
				<?php echo form_close(); ?>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
$("#employee_id").select2();

$("#series_form").submit(function()
{
  $('input[type=submit]', this).attr('disabled', 'disabled');
});
date_time_picker_field($("#expire_date"),JS_DATE_FORMAT);

</script>
<?php $this->load->view("partial/footer"); ?>