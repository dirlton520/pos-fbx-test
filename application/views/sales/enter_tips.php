<?php $this->load->view("partial/header"); ?>
<div class="row">
	<div class="col-md-12">
		<div class="panel-body panel panel-piluku">
			<div class="table-responsive">
				<table class="table table-bordered table-striped table-reports tablesorter" id="sortable_table">
					<thead>
						<tr>
							<th><?php echo lang('common_sale_id');?></th>
							<th><?php echo lang('common_sale_date');?></th>
							<th><?php echo lang('common_total');?></th>
							<th><?php echo lang('common_tip');?></th>
						</tr>
					</thead>
				
					<tbody style="text-align: center;">
						<?php foreach($this->Sale->get_sales_that_tip_can_be_adjusted() as $row){?>
						<tr>
							<td><?php echo $row['sale_id'];?></td>
							<td><?php echo date(get_date_format().' '.get_time_format(),strtotime($row['sale_time']));?></td>
							<td><?php echo to_currency($row['total']);?></td>
							<td>
		 					 	<?php echo form_open('sales/save_tip/'.$row['sale_id'],array('id'=>'tips_form_'.$row['sale_id'],'class'=>'form-horizontal tips_form')); ?>
									<div style="display:flex; justify-content: space-between;">
										<input type="text" class="form-control" name="tip" value="<?php echo $row['tip'] !== NULL ? to_currency_no_money($row['tip']) : ''; ?>">									
										<input type="submit" class="btn btn-primary" style="margin-left: 20px;"/>
									</div>
								</form>
								
							</td>
						</tr>
						<?php } ?>
					</tbody>
			</table>
		</div>
		</div>
	</div>
</div>
<script>
	$("#sortable_table").stacktable({headIndex: 1});
	
 	$('.tips_form').ajaxForm({target: "#target",success: tipAddSuccess, error: tipAddFailure });
	
	function tipAddSuccess(responseText, statusText,xhr,form)
	{
		form.parent().parent().fadeOut();
	}
	
	function tipAddFailure(responseText, statusText,xhr,form)
	{
		bootbox.alert({
			title: <?php echo json_encode(lang('common_error')); ?>,
			message : <?php echo json_encode(lang('sales_cannot_add_tip')); ?>+" "+responseText.responseText
		});
	}
</script>

<div id="target"></div>
<?php $this->load->view("partial/footer"); ?>
