<div class="modal-dialog">
  	<div class="modal-content">
        <div class="modal-header">
          	<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
          	<h4 class="modal-title"><?php echo lang('lookup_receipt') ?></h4>
        </div>
        <div class="modal-body">
          	<?php echo form_open("sales/receipt", array('class'=>'lookup_form','autocomplete'=> 'off')); ?>				
				<input type="text" class="form-control text-center" name="sale_id" id="sale_id" placeholder="<?php echo lang('common_sale_id') ?>">
				<?php echo form_submit('submit_lookup_form',lang("lookup_receipt"),'class="btn btn-block btn-primary submit_lookup_form"'); ?>
			<?php echo form_close(); ?>
        </div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
<script>
	$(document).ready(function(){
		$('#sale_id').focus();

		$(document).on('click', '.submit_lookup_form', function(e){
			e.preventDefault();
			var receipt_id = $('#sale_id').val();

			window.location = <?php echo json_encode(site_url('sales/receipt/')) ?> + '/' + receipt_id;
		});
	});
</script>