<?php $this->load->view("partial/header"); ?>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-piluku">
			<div class="panel-heading">
				<?php echo lang("register_add_subtract_message_$mode"); ?>
			</div>
			<div class="panel-body">
				<?php
				
				if ($mode == 'add')
				{
					echo '<h3>'.lang('register_you_have_already_added'). " $amount ".lang('register_to_the_register').'</h3>';
				}
				else
				{
					echo '<h3>'.lang('register_you_have_already_subtracted'). " $amount ".lang('register_from_the_register').'</h3>';
				}
				
				echo form_open("sales/register_add_subtract/$mode/$payment_type/$return", array('id'=>'register_add_subtract_form','class'=>'form-horizontal'));
				?>
				
				<div class="form-group">
					<?php echo form_label(lang('register_additional_amount_to_'.($mode == 'add' ? 'add' : 'subtract')).':', 'amount',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
				    <div class="col-sm-9 col-md-9 col-lg-10">
					    <?php echo form_input(array(
					        'name'=>'amount',
					        'size'=>'8',
								'class'=>'form-control',
					        'id'=>'amount',
					        'value'=> '')
					    );?>
				    </div>
				</div>
				
				<div class="form-group">
					<?php echo form_label(lang('common_comments').':', 'note',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label wide')); ?>
				    <div class="col-sm-9 col-md-9 col-lg-10">
					    <?php echo form_textarea(array(
						'name'=>'note',
						'id'=>'note',
						'class'=>'form-control text-area',
						'rows'=>'4',
						'cols'=>'30',
						'value'=>''));
						?>
				    </div>
				</div>
				
				
				<?php
					echo form_submit(array(
						'name'=>'submitf',
						'id'=>'submitf',
						'value'=>lang('common_save'),
						'class'=>'submit_button btn btn-primary')
					);
				?>
				<?php
					echo form_close();
				?>
				<div class="from-group text-right">
					<?php echo anchor_popup(site_url('sales/open_drawer'), '<i class="ion-android-open"></i> '.lang('common_pop_open_cash_drawer'),array('class'=>'', 'target' => '_blank')); ?>
				</div>
				
				
			</div>
		</div>
	</div>
</div>
			
<script type='text/javascript'>
$("#amount").focus();

$('#register_add_subtract_form').validate({
	rules:
	{
		amount: {
			required: true,
			number: true
		}
	},
	messages:
	{
		amount: {
			required: <?php echo json_encode(lang('sales_amount_required')); ?>,
			number: <?php echo json_encode(lang('sales_amount_number')); ?>
		}
	}
});

</script>
<?php $this->load->view('partial/footer.php'); ?>