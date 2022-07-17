<?php $this->load->view("partial/header");



 ?>

<style type="text/css" scoped>
	.ui-autocomplete-loading {
        background: white url('images/spinner_small.gif') 0% 5% no-repeat;
    }
	.item_table { padding-left: 40px; font: 12px Arial;}
	span.required { color: #FF0000; }

	/* Add / Remove Images */
	a.AddCondition {
		background-image: url(data:image/gif;base64,R0lGODlhEAAQAPcAAAAAAP///5i+lTyGNUeNQEiOQVKVTJi+lN7p3d3o3ESMO0WMO0iPP1SVTLjRtUKNNkOPOEmOP3DBY16bVWCdV4HMdXm9boXNeYbJfI7Sg4nIf47MhJTTisXowFCZQXrGa2OgV37Hb4rPfYbJeozLgZXUipfVi5bUi5PNiJ3YkqDZlaDZlqXbm6/fprfgr73ktqTGnqPFnc7pydzx2GWrVXG+X2qsW3zDa2eiWpbTia/fpbPdqbXfrL7ittfu0tTrz8XawODy3OPs4eLr4FeeRWSgVGaiV3etaHWsZ5nRi5jMirTdqrLbqLbdrLjdr8bawebu5HC4WW61WGirU2+1WXnBZGuqWGyqWn7BaX25an25a3+5bYrCearUncvmw8jcwsncw2+1WHK5W3O6XHS3XHe8YH2+Z3i0ZIm+eI/CfZfMhZLFgIm5eJjLhpjMh7XbqLXRrLfTrnS3W3y6ZH28Zn6yaoK1boK0bo2+e4i3dpfHhaTOlKbQlqbPlqvUnLTaprPZpbTZpbfaqrvcr+bu44a2cZS/gZW/gurx54y7dpO/fpK+fsLatsHZtdXlzerw5/n5+f///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAJEALAAAAAAQABAAAAjPACEJHEiwYMFHjA4VusMmDiGDkBwt2uPlhwwnW46AKego0aAgL1is0NGBi5EnAxEpEjQjBYoNJ0pkcKEFhxCBjfT4UMFBQ4AAF26E4EEEhkBDTVqQwGDhp40yY5KsoSDQTg8TI35qDRAFSxcGAuvsEFFh688oZvwoEJhHSY4PEmicjSJGDZ4GAuFMWVKlRpmzUsj8gXBAIBQkWZhAjUIljBxAVwogGPilyJk3bujMaRPIygAHBYGA8JCGTx80DwiANjgkxoQICwwISACxdsGAADs=);	
		display: block;
		float: left;
		text-indent: -9999px;
		width: 16px;
		height: 16px;
	}
	a.DelCondition {
		background-image: url(data:image/gif;base64,R0lGODlhEAAQAPcAAAAAAP///+FvcON4efDi3ePAtfDh3LpSNL5WOb9cP71bP8FmS9mjk9mklMRQNL9TOcBZPsNkS8ZqU/zHusFNM8BVPfaCaMhqVfaEbPiMdu6KdfCMd/GOeveTfviUf/qah/qjkfi2qN6mm/3b1PLj4MxSPPNzXfN5Y8tlVMxoV/iGcPCFcPmSfvqTf/eRfvCRf/qdi/qrnfq6rt6nnfzUzfnTzOnFv/Li3+5mUs1gUc5iU/J3Y/upnPWsofivpPWvpfS0qvm5r/nLxOrFv9BPPtNwZPSOge6MgferofarouvFwevGwv3c2OlZTeZYTOlbT+ZZTupcUNtWS/BkVuxfVNddUepmXNZgVO5qXuNrYeNuY+JwZtVtY+l7cO+GfvGdlvjDvuZWTOZaUuljW+BlXOR4cfCDe+l/eOJ7ddx3cu6EfeqDfe2TjvGclvWmofOno+etqeivrPTk4+ZWUOddWdtoZNtraNxuaeh6dd54c+6Sj/SinvWjn/Ktqt1rauJ4eOJ+fOF+fPSgnu2ysu2zs/LLy/bm5vn5+f///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAIgALAAAAAAQABAAAAjLAA8JHEiwYEFDhAL5sZMnjhyDhwr9YQOmhhAgW7gsKVhIQB8mMkDA4DGhi44hAw0NeDPiwwsOHlhkCKElBQmBg9bQgNFBA4afGE74IDJDIKAfMTZQWcp0h5EzFwTeCdJiRYCrWJtg+VJBYB0kKixgzWqlDQKBaY64MNGkrdsnZtBEEAhHSpIpONw2cSKGjwMGAuUUydKDSpQmUMLM2XMlAYGBSnKQceNlDB01gqocKFDQBooSePToKUNBAWeDN0RIgPBgQQMDEGMXDAgAOw==);		display: block;
		float: left;
		text-indent: -9999px;
		width: 16px;
		height: 16px;
	}
	span.actionCondition {
		float: left;
		font-weight: bold;
		margin-right: 5px;
	}
	
	/*table.conditions {
		width: 100%;
		border: 1px solid #DDDDDD;
	}
	
	table.conditions tr.duplicate td {
		padding: 10px 0px;
	}

	table.conditions tr.duplicate td.field {
		padding: 5px;
	}

	table.conditions tr.duplicate td.field select {
		width: 200px;
	}
	
	table.conditions tr.duplicate td.value textarea {
		height: 20px;
		resize: none;
		overflow-y: hidden;
		padding: 16px;
		-webkit-transition:height .1s ease-in-out;
		-moz-transition:height .1s ease-in-out;
		-o-transition:height .1s ease-in-out;
		-ms-transition:height .1s ease-in-out;
		transition:height .1s ease-in-out;	
	}*/
	
</style>

<script type="text/javascript">
(function($) 
{
  	$.fn.tokenize = function(options)
	{
		var settings = $.extend({}, {prePopulate: false}, options);
    	return this.each(function() 
		{
      		$(this).tokenInput('<?php echo site_url("reports/sales_generator"); ?>?act=autocomplete',
			{
				theme: "facebook",
				queryParam: "term",
				extraParam: "w",
				hintText: <?php echo json_encode(lang("reports_sales_generator_autocomplete_hintText"));?>,
				noResultsText: <?php echo json_encode(lang("reports_sales_generator_autocomplete_noResultsText"));?>,
				searchingText: <?php echo json_encode(lang("reports_sales_generator_autocomplete_searchingText"));?>,
				preventDuplicates: true,
				prePopulate: settings.prePopulate
			});
    	});
 	}
})(jQuery);

$(document).on('change', "#matchType", function(){
	if ($(this).val() == 'matchType_All')
	{
		$("#matched_items_only").prop('disabled', false);
		$(".actions span.actionCondition").html(<?php echo json_encode(lang("reports_sales_generator_matchType_All_TEXT"));?>);
	}
	else 
	{
		$("#matched_items_only").prop('checked', false);
		$("#matched_items_only").prop('disabled', true);
		$(".actions span.actionCondition").html(<?php echo json_encode(lang("reports_sales_generator_matchType_Or_TEXT"));?>);
	}
});


$(document).on('click', "a.AddCondition", function(e){
	var sInput = $("<input />").attr({"type": "text", "name": "value[]", "w":"", "value":"", "class":"form-control"});
	$('.conditions tr.duplicate:last').clone().insertAfter($('.conditions tr.duplicate:last'));
	$("input", $('.conditions tr.duplicate:last')).parent().html("").append(sInput).children("input").tokenize();
	$("option", $('.conditions tr.duplicate:last select')).removeAttr("disabled").removeAttr("selected").first().prop("selected", true);
	
	$('.conditions tr.duplicate:last').trigger('change');
	e.preventDefault();
})

$(document).on('click', "a.DelCondition", function(e){
	if ($(this).parent().parent().parent().children().length > 1)
		$(this).parent().parent().remove();
	
	e.preventDefault();
})

$(document).on('change', ".selectField", function(){
	var sInput = $("<input />").attr({"type": "text", "name": "value[]", "w":"", "value":"", "class":"form-control"});
	var field = $(this);
	// Remove Value Field
	field.parent().parent().children("td.value").html("");
	if ($(this).val() == 0) 
	{
		field.parent().parent().children("td.condition").children(".selectCondition").prop("disabled", true);	
		field.parent().parent().children("td.value").append(sInput.prop("disabled", true));		
	} 
	else 
	{
		field.parent().parent().children("td.condition").children(".selectCondition").removeAttr("disabled");	
		if ($(this).val() == 2 || $(this).val() == 7 || $(this).val() == 29 || $(this).val() == 10 || $(this).val() == 12 || $(this).val() == 16 || ($(this).val() >= 19 && $(this).val() <= 28)) 
		{
			field.parent().parent().children("td.value").append(sInput);		
		} 
		else 
		{
			if ($(this).val() == 6) 
			{
				field.parent().parent().children("td.value").append($("<input />").attr({"type": "hidden", "name": "value[]", "value":"", "class":"form-control"}));		
			} 
			else 
			{
				field.parent().parent().children("td.value").append(sInput.attr("w", $("option:selected", field).attr('rel'))).children("input").tokenize();		
			}
		}
		disableConditions(field, true);
	}
});

$(function() {
	<?php 
		if (isset($prepopulate) and count($prepopulate) > 0) {
			echo "var prepopulate = ".json_encode($prepopulate).";";
		}
	?>
	var sInput = $("<input />").attr({"type": "text", "name": "value[]", "w":"", "value":"", "class":"form-control"});
	$(".selectField").each(function(i) {
		if ($(this).val() == 0) {
			$(this).parent().parent().children("td.condition").children(".selectCondition").prop("disabled", true);
			$(this).parent().parent().children("td.value").html("").append(sInput.prop("disabled", true));	
		} else {
			if ($(this).val() != 2 && $(this).val() != 6 && $(this).val() != 7 && $(this).val() != 10 && $(this).val() != 12 && $(this).val() != 16 && $(this).val() < 19) {
				$(this).parent().parent().children("td.value").children("input").attr("w", $("option:selected", $(this)).attr('rel')).tokenize({prePopulate: prepopulate.field[i][$(this).val()] });	
			}
			if ($(this).val() == 6) {
				$(this).parent().parent().children("td.value").html("").append($("<input />").attr({"type": "hidden", "name": "value[]", "value":"", "class":"form-control"}));	
			}
			disableConditions($(this), false);
		}
	});
});

function disableConditions(elm, q) {
	var allowed1 = ['1', '2','16','17'];
	var allowed2 = ['7', '8', '9'];
	var allowed3 = ['10', '11'];
	var allowed4 = ['1', '2', '7', '8', '9','29'];
	var allowed5 = ['1'];
	var disabled = elm.parent().parent().children("td.condition").children(".selectCondition");
	
	if (q == true)
		$("option", disabled).removeAttr("selected");
	
	$("option", disabled).prop("disabled", true);
	$("option", disabled).each(function() {
		if (elm.val() == 11 && $.inArray($(this).attr("value"), allowed5) != -1) {
			$(this).removeAttr("disabled");
		}else if (elm.val() == 10 && $.inArray($(this).attr("value"), allowed4) != -1) {
			$(this).removeAttr("disabled");
		} else if (elm.val() == 6 && $.inArray($(this).attr("value"), allowed3) != -1) {
			$(this).removeAttr("disabled");
		} else if (elm.val() == 7 && $.inArray($(this).attr("value"), allowed2) != -1) {
			$(this).removeAttr("disabled");
		} else if (elm.val() != 6 && elm.val() != 7 && elm.val() != 10 && elm.val() != 11 && $.inArray($(this).attr("value"), allowed1) != -1) {
			$(this).removeAttr("disabled");
		} 
		
	});
	
	if (q == true)
		$("option:not(:disabled)", disabled).first().prop("selected", true);
}

</script>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-piluku">				
			<div class="panel-heading hidden-print">
				<?php echo lang('reports_date_range'); ?>
			</div>
			<div class="panel-body hidden-print">
				<form id="salesReportGenerator" name="salesReportGenerator" action="<?php echo site_url("reports/sales_generator"); ?>" method="get" class="form-horizontal form-horizontal-mobiles">
						<?php
						$this->load->helper('view');
						load_cleaned_view('reports/inputs/date_range',array('with_time' => FALSE,'report_type' => $report_type, 'date_range_simple_value' => $sreport_date_range_simple, 'start_date_value' => $this->input->get('start_date_formatted'), 'end_date_value' => $this->input->get('end_date_formatted')));
						?>
					<?php $this->load->view('partial/reports/locations_select');?>

					<div class="form-group">
						<?php echo form_label(lang('reports_sales_generator_matchType').':', 'matchType', array('class'=>'required text-danger col-sm-3 col-md-3 col-lg-2 control-label')); ?>
						<div class="controls col-sm-9 col-md-7 col-lg-7">
							<select name="matchType" id="matchType" class="form-control">
								<option value="matchType_All"<?php if ($matchType != 'matchType_All') { echo " selected='selected'"; }?>><?php echo lang('reports_sales_generator_matchType_All')?></option>
								<option value="matchType_Or"<?php if ($matchType == 'matchType_Or') { echo " selected='selected'"; }?>><?php echo lang('reports_sales_generator_matchType_Or')?></option>
							</select>
							<em>
								<?php echo lang('reports_sales_generator_matchType_Help')?>
							</em>
						</div>
						
					</div>

					<div class="form-group">
						<?php echo form_label(lang('reports_sales_generator_show_only_matched_items').':', 'matched_items_only', array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
						<div class="controls col-sm-9 col-md-9 col-lg-9">
							<?php
								$matched_items_checkbox =	array(
							    'name'        => 'matched_items_only',
							    'id'          => 'matched_items_only',
							    'value'       => '1',
							    'checked'     => $matched_items_only,
						    	);
								
								if ($matchType == 'matchType_Or')
								{
									$matched_items_checkbox['disabled'] = 'disabled';
								}
							?>
							<?php echo form_checkbox($matched_items_checkbox).'<label for="matched_items_only"><span></span></label>'; ?>
						</div>
					</div>
					
					<div class="form-group">
						<?php echo form_label(lang('reports_tax_exempt').':', 'tax_exempt', array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
						<div class="controls col-sm-9 col-md-9 col-lg-9">
							<?php echo form_checkbox(array(
						    'name'        => 'tax_exempt',
						    'id'          => 'tax_exempt',
						    'value'       => '1',
						    'checked'     => $tax_exempt,
							 
					    	)).'<label for="tax_exempt"><span></span></label>'; ?>
						</div>
					</div>
					
					<div class="form-group">
						<?php echo form_label(lang('reports_export_to_excel').':', 'export_excel', array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
						<div class="controls col-sm-9 col-md-9 col-lg-9">
							<?php echo form_checkbox(array(
						    'name'        => 'export_excel',
						    'id'          => 'export_excel',
						    'value'       => '1'
					    	)).'<label for="export_excel"><span></span></label>'; ?>
						</div>
					</div>

					<div class="table-responsive">
						<table class="table conditions custom-report">
							<?php
							$this->lang->load('deliveries');
								if (isset($field) and $field[0] > 0) {
									foreach ($field as $k => $v) {
							?>
							<tr class="duplicate">
								<td class="field">
									<select name="field[]" class="selectField form-control">
										<option value="0"><?php echo lang("reports_sales_generator_selectField_0") ?></option>						
										<option value="1" rel="customers"<?php if($field[$k] == 1) echo " selected='selected'";?>><?php echo lang("reports_sales_generator_selectField_1") ?></option>
										<option value="2" rel="itemsSN"<?php if($field[$k] == 2) echo " selected='selected'";?>><?php echo lang("reports_sales_generator_selectField_2") ?></option>
										<option value="3" rel="employees"<?php if($field[$k] == 3) echo " selected='selected'";?>><?php echo lang("reports_sales_generator_selectField_3") ?></option>
										<option value="4" rel="itemsCategory"<?php if($field[$k] == 4) echo " selected='selected'";?>><?php echo lang("reports_sales_generator_selectField_4") ?></option>
										<option value="5" rel="suppliers"<?php if($field[$k] == 5) echo " selected='selected'";?>><?php echo lang("reports_sales_generator_selectField_5") ?></option>
										<option value="6" rel="saleType"<?php if($field[$k] == 6) echo " selected='selected'";?>><?php echo lang("reports_sales_generator_selectField_6") ?></option>
										<option value="7" rel="saleAmount"<?php if($field[$k] == 7) echo " selected='selected'";?>><?php echo lang("reports_sales_generator_selectField_7") ?></option>
										<option value="8" rel="itemsKitName"<?php if($field[$k] == 8) echo " selected='selected'";?>><?php echo lang("reports_sales_generator_selectField_8") ?></option>
										<option value="9" rel="itemsName"<?php if($field[$k] == 9) echo " selected='selected'";?>><?php echo lang("reports_sales_generator_selectField_9") ?></option>
										<option value="10" rel="saleID"<?php if($field[$k] == 10) echo " selected='selected'";?>><?php echo lang("reports_sales_generator_selectField_10") ?></option>
										<option value="11" rel="paymentType"<?php if($field[$k] == 11) echo " selected='selected'";?>><?php echo lang("reports_sales_generator_selectField_11") ?></option>
										<option value="12" rel="saleItemDescription"<?php if($field[$k] == 12) echo " selected='selected'";?>><?php echo lang("reports_sales_generator_selectField_12") ?></option>
										<option value="13" rel="salesPerson"<?php if($field[$k] == 13) echo " selected='selected'";?>><?php echo lang("common_sales_person") ?></option>
										<option value="15" rel="manufacturer"<?php if($field[$k] == 15) echo " selected='selected'";?>><?php echo lang("common_manufacturer") ?></option>
										<option value="16" rel="saleComment"<?php if($field[$k] == 16) echo " selected='selected'";?>><?php echo lang("deliveries_sale_comment") ?></option>
										<option value="17" rel="itemVariationNumber"<?php if($field[$k] == 17) echo " selected='selected'";?>><?php echo lang("common_item_variation_item_number") ?></option>
										<option value="18" rel="tierName"<?php if($field[$k] == 18) echo " selected='selected'";?>><?php echo lang("common_tier_name") ?></option>
										<option value="29" rel="tierName"<?php if($field[$k] == 29) echo " selected='selected'";?>><?php echo lang("common_tag") ?></option>
										
										<?php
									 
 									 for($i=19,$counter=1;$i<=28;$i++,$counter++)
									 {
									 	 $custom_field = $this->Sale->get_custom_field($counter);
									 	 if($custom_field !== FALSE)
										 {
											?>
	 										<option value="<?php echo $i;?>" rel="customField<?php echo $i;?>"<?php if($field[$k] == $i) echo " selected='selected'";?>><?php echo $custom_field; ?></option>									 	
										 <?php
										 }
									 }
										?>
									</select>
								</td>
								<td class="condition">
									<select name="condition[]" class="selectCondition form-control">
										<option value="1"<?php if($condition[$k] == 1) echo " selected='selected'";?>><?php echo lang("reports_sales_generator_selectCondition_1")?></option>
										<option value="2"<?php if($condition[$k] == 2) echo " selected='selected'";?>><?php echo lang("reports_sales_generator_selectCondition_2")?></option>
										<option value="7"<?php if($condition[$k] == 7) echo " selected='selected'";?>><?php echo lang("reports_sales_generator_selectCondition_7")?></option>
										<option value="8"<?php if($condition[$k] == 8) echo " selected='selected'";?>><?php echo lang("reports_sales_generator_selectCondition_8")?></option>
										<option value="9"<?php if($condition[$k] == 9) echo " selected='selected'";?>><?php echo lang("reports_sales_generator_selectCondition_9")?></option>
										<option value="10"<?php if($condition[$k] == 10) echo " selected='selected'";?>><?php echo lang("reports_sales_generator_selectCondition_10")?></option>
										<option value="11"<?php if($condition[$k] == 11) echo " selected='selected'";?>><?php echo lang("reports_sales_generator_selectCondition_11")?></option>
										<option value="16"<?php if($condition[$k] == 16) echo " selected='selected'";?>><?php echo lang("reports_sales_generator_selectCondition_16")?></option>
										<option value="17"<?php if($condition[$k] == 17) echo " selected='selected'";?>><?php echo lang("reports_sales_generator_selectCondition_17")?></option>
									</select>
								</td>
								<td class="value">
										<input type="text" name="value[]" w="" value="<?php echo $value[$k]; ?>" class="form-control"/>	
								</td>
								<td class="actions">
									<span class="actionCondition">
									<?php 
										if ($matchType == 'matchType_Or') {
											echo lang("reports_sales_generator_matchType_Or_TEXT");
										} else {
											echo lang("reports_sales_generator_matchType_All_TEXT");					
										}
									?>
									</span>
									<a class="AddCondition" href="#" title="<?php echo lang("reports_sales_generator_addCondition")?>"><?php echo lang("reports_sales_generator_addCondition")?></a>
									<a class="DelCondition" href="#" title="<?php echo lang("reports_sales_generator_delCondition")?>"><?php echo lang("reports_sales_generator_delCondition")?></a>
								</td>
							</tr>				
							<?php
									}
								} else {
							?>
							<tr class="duplicate">
								<td class="field">
									<select name="field[]" class="selectField span7 form-control">
										<option value="0"><?php echo lang("reports_sales_generator_selectField_0") ?></option>						
										<option value="1" rel="customers"><?php echo lang("reports_sales_generator_selectField_1") ?></option>
										<option value="2" rel="itemsSN"><?php echo lang("reports_sales_generator_selectField_2") ?></option>
										<option value="3" rel="employees"><?php echo lang("reports_sales_generator_selectField_3") ?></option>
										<option value="4" rel="itemsCategory"><?php echo lang("reports_sales_generator_selectField_4") ?></option>
										<option value="5" rel="suppliers"><?php echo lang("reports_sales_generator_selectField_5") ?></option>
										<option value="6" rel="saleType"><?php echo lang("reports_sales_generator_selectField_6") ?></option>
										<option value="7" rel="saleAmount"><?php echo lang("reports_sales_generator_selectField_7") ?></option>
										<option value="8" rel="itemsKitName"><?php echo lang("reports_sales_generator_selectField_8") ?></option>
										<option value="9" rel="itemsName"><?php echo lang("reports_sales_generator_selectField_9") ?></option>
										<option value="10" rel="saleID"><?php echo lang("reports_sales_generator_selectField_10") ?></option>
										<option value="11" rel="paymentType"><?php echo lang("reports_sales_generator_selectField_11") ?></option>
										<option value="12" rel="saleItemDescription"><?php echo lang("reports_sales_generator_selectField_12") ?></option>
										<option value="13" rel="salesPerson"><?php echo lang("common_sales_person") ?></option>
										<option value="15" rel="manufacturer"><?php echo lang("common_manufacturer") ?></option>
										<option value="16" rel="saleComment"><?php echo lang("deliveries_sale_comment") ?></option>
										<option value="17" rel="itemVariationNumber"><?php echo lang("common_item_variation_item_number") ?></option>
										<option value="18" rel="tierName"><?php echo lang("common_tier_name") ?></option>
										<option value="29" rel="tagName"><?php echo lang("common_tag") ?></option>
										
 										<?php
									 
										
 									 for($i=19,$counter=1;$i<=28;$i++,$counter++)
 									 {
 									 	 $custom_field = $this->Sale->get_custom_field($counter);
 									 	 if($custom_field !== FALSE)
 										 {
 											?>
 	 										<option value="<?php echo $i;?>" rel="customField<?php echo $i;?>"><?php echo $custom_field; ?></option>									 	
 										 <?php
 										 }
 									 }
 										?>
										
										
									</select>
								</td>
								<td class="condition">
									<select name="condition[]" class="selectCondition form-control">
										<option value="1"><?php echo lang("reports_sales_generator_selectCondition_1")?></option>
										<option value="2"><?php echo lang("reports_sales_generator_selectCondition_2")?></option>
										<option value="7"><?php echo lang("reports_sales_generator_selectCondition_7")?></option>
										<option value="8"><?php echo lang("reports_sales_generator_selectCondition_8")?></option>
										<option value="9"><?php echo lang("reports_sales_generator_selectCondition_9")?></option>
										<option value="10"><?php echo lang("reports_sales_generator_selectCondition_10")?></option>
										<option value="11"><?php echo lang("reports_sales_generator_selectCondition_11")?></option>
										<option value="16"><?php echo lang("reports_sales_generator_selectCondition_16")?></option>
										<option value="17"><?php echo lang("reports_sales_generator_selectCondition_17")?></option>
										
									</select>
								</td>
								<td class="value">
									<input type="text" name="value[]" w="" value="" class="form-control"/>	
								</td>
								<td class="actions">
									<span class="actionCondition">
									<?php 
										if ($matchType == 'matchType_Or') {
											echo lang("reports_sales_generator_matchType_Or_TEXT");
										} else {
											echo lang("reports_sales_generator_matchType_All_TEXT");					
										}
									?>
									</span>
									<a class="AddCondition" href="#" title="<?php echo lang("reports_sales_generator_addCondition")?>"><?php echo lang("reports_sales_generator_addCondition")?></a>
									<a class="DelCondition" href="#" title="<?php echo lang("reports_sales_generator_delCondition")?>"><?php echo lang("reports_sales_generator_delCondition")?></a>
								</td>
							</tr>
							
							<?php
								}
							?>
						</table>
					</div>

					<div class="form-actions text-center">
						<button name="generate_report" type="submit" value="1" id="generate_report" class="submit_button btn btn-primary btn-lg"><?php echo lang('common_submit')?></button>
					</div>
				</form>

				
			</div>
		</div>

		<?php 
			if (isset($results)) echo $results;
		?>
	</div>
</div>

<script type="text/javascript" language="javascript">
var base_sheet_url = '';
$(document).ready(function()
{
	$(".tablesorter a.expand").click(function(event)
	{
		$(event.target).parent().parent().next().find('td.innertable').toggle();
		
		if ($(event.target).text() == '+')
		{
			$(event.target).text('-');
			id=$(event.target).attr("id");
			show_report_details(id);
		}
		else
		{
			$(event.target).text('+');
		}
		return false;
	});
	
	$(".tablesorter a.expand_all").click(function(event)
	{
		$('td.innertable').toggle();
		
		if ($(event.target).text() == '+')
		{
			$(event.target).text('-');
			$(".tablesorter a.expand").text('-');
			
			ids='<?php echo $ids; ?>';
				show_report_details(ids);
			
		}
		else
		{
			$(event.target).text('+');
			$(".tablesorter a.expand").text('+');
		}
		return false;
	});
	
	$(".generate_barcodes_from_recv").click(function()
	{
		base_sheet_url = $(this).attr('href');
		$("#skip-labels").modal('show');
		return false;
	
	});
		
	$("#generate_barcodes_form").submit(function(e)
	{
		e.preventDefault()
		var num_labels_skip = $("#num_labels_skip").val() ? $("#num_labels_skip").val() : 0;
		var url = base_sheet_url+'/'+num_labels_skip;
		window.location = url;
		return false;
	});
});

function print_report()
{
	window.print();
}

function show_report_details(ids){
        if(ids){
            var report_model = '<?php echo $report_model; ?>';
			var url = '<?php echo site_url('reports/get_report_details'); ?>';
            var ids = ids.split(',');
			$.ajax({
                url: url,
				type: 'POST',
				data:{'ids':ids,'key':report_model},
				datatype: 'json',
				cache: false,
				success:function(data){
				
				var obj = JSON.parse(data);
				var headers = obj.headers['details'];
				var cellData= obj.details_data;
				var summary= obj.headers['summary'];
				for (i = 0; i < ids.length; i++) { 
					
					var res = '#res_'+ids[i];
					
					var tableData='<td colspan="' + (summary.length+1) +'" class="innertable"><table class="table table-bordered">';
					tableData+='<thead>';
					tableData+='<tr>';
					$.each(headers, function (k, v) {
						tableData += '<th align="'+ v.align + '">' + v.data + '</th>';					
					});
					tableData +='</tr></thead>';
					
					tableData+='<tbody>';
					$.each(cellData, function (x) {
					var transData= cellData[x];
						$.each(transData, function (key, value){
							var rowId=key;
							var rowData=value;
							if(rowId == ids[i])
							{
								tableData+='<tr>';
								$.each(rowData, function (a,b) {
									if(b.data == null){b.data='';}
									tableData += '<td align="'+ b.align + '">' + b.data + '</td>';					
								});
								tableData+='</tr>';
								
							}
						
						});
						
					});
					tableData+='</tbody>';
					tableData+='</table></td>';
					
					//document.getElementById(res).innerHTML = "";
					$(res).empty();
					$(res).append(tableData);
					$(res).css('display','');
				}
				
				},
				error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError);
				}
				
               
            });
        }
    }

$(document).ready(function()
{
	$('#print_button').click(function(e){
		e.preventDefault();
		print_report();
	});
});

<?php $this->load->view("partial/footer"); ?>

