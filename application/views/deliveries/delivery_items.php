<?php if ($items) { ?>
    <table class="table table-bordered">
        <tr>
            <th></th>
            <th><?php echo lang('common_item_name'); ?></th>
            <th><?php echo lang('common_quantity'); ?></th>
        </tr>
        <?php foreach ($items as $k => $item) { ?>
            <input type="hidden" name="delivery_items[<?php echo $k; ?>][item_id]" value="<?php echo $item['item_id']; ?>"/>
            <input type="hidden" name="delivery_items[<?php echo $k; ?>][item_variation_id]" value="<?php echo $item['item_variation_id']; ?>"/>
            <input type="hidden" name="delivery_items[<?php echo $k; ?>][item_kit_id]" value="<?php echo $item['item_kit_id']; ?>"/>
            <input type="hidden" name="delivery_items[<?php echo $k; ?>][quantity]" value="<?php echo $item['quantity']; ?>"/>
            <tr>
                <td><a href="#" data-item_id="<?php echo $item["item_id"]; ?>" data-item_kit_id="<?php echo $item["item_kit_id"]; ?>" data-quantity="<?php echo $item["quantity"]; ?>" data-item_variation_id="<?php echo $item["item_variation_id"]; ?>" class="delete-item" tabindex="-1"><i class="icon ion-android-cancel"></i></a></td>
                <td><?php echo ($item['category']) ? $item['name']." (".$item['category'].")" : $item['name']; ?> - <?php echo $item['variation']; ?></td>
                <td><a href="#" id="quantity_<?php echo $k; ?>" class="xeditable" data-type="text" data-validate-number="true" data-pk="1" data-name="quantity" data-url="<?php echo site_url('deliveries/edit_item/' . $k); ?>" data-title="<?php echo lang('common_quantity') ?>"><?php echo to_quantity($item['quantity']); ?></a></td>
            </tr>
        <?php } ?>
    </table>
<?php } ?>

<div class="modal fade look-up-receipt" id="choose_var" role="dialog" aria-labelledby="lookUpReceipt" aria-hidden="true">
	<div class="modal-dialog customer-recent-sales">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label=<?php echo json_encode(lang('common_close')); ?>><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="lookUpReceipt"><?php echo lang('common_variation'); ?></h4>
			</div>
			<div class="modal-body clearfix">
				<?php
				echo "<div class='placeholder_attribute_vals pull-left'>";
				if (isset($additional_data['variation_choices_model'])) {
					foreach ($additional_data['variation_choices_model'] as $key => $variation) {
                        $variation_name = explode(":", $variation);
                        $variation_name = isset($variation_name[1]) ? $variation_name[1] : $variation_name[0];
						echo "<span class='popup_button' style='margin:5px;cursor:pointer;' data-item_id='" . trim($additional_data["item_id"]) . "' data-item_variation_id='" . trim($key) . "' >" . trim($variation_name) . "</span>";
					}
				}
				echo "</div>";

				?>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script type="text/javascript">
    $('.xeditable').editable({
        validate: function(value) {
            if ($.isNumeric(value) == '' && $(this).data('validate-number')) {
                return <?php echo json_encode(lang('common_only_numbers_allowed')); ?>;
            }
        },
        success: function(response, newValue) {
            last_focused_id = $(this).attr('id');
            $("#item_container").html(response);
        },
        savenochange: true
    });

    $('.xeditable').on('hidden', function(e, editable) {
        last_focused_id = $(this).attr('id');
        $('#' + last_focused_id).focus();
        $('#' + last_focused_id).select();
    });

    <?php if(isset($additional_data['message'])){ ?>
        show_feedback('error', "<?php echo $additional_data['message']; ?>", <?php echo json_encode(lang('common_error')); ?> );
    <?php } ?>
    <?php if(isset($additional_data['has_variation'])){ ?>
        $('#choose_var').modal('show');
    <?php } ?>
</script>