<?php $this->load->view("partial/header"); ?>
<?php if($redirect) { ?>
<div class="manage_buttons">
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 margin-top-10">
			<div class="buttons-list">
				<div class="pull-right-btn">
					<?php echo anchor(site_url($redirect), ' ' . lang('common_done'), array('class'=>'btn btn-primary btn-lg ion-android-exit', 'title'=>'')); ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>

<div class="modal fade" id="step2done" tabindex="-1" role="dialog" aria-labelledby="categoryData" aria-hidden="true">
    <div class="modal-dialog customer-recent-sales">
      	<div class="modal-content">
	        <div class="modal-header">
	          	<button type="button" class="close" data-dismiss="modal" aria-label=<?php echo json_encode(lang('common_close')); ?>><span aria-hidden="true">&times;</span></button>
	          	<h4 class="modal-title" id="categoryModalDialogTitle"><?php echo lang('common_spreadsheet_column_mapping_complete'); ?></h4>
	        </div>
	        <div class="modal-body">
						<?php echo lang('common_go_to_step_3_dialog'); ?>
	        </div>
					<div class="modal-footer">
						<button type="button" id="no" class="btn btn-default" data-dismiss="modal"><?php echo lang('common_no'); ?></button>
						<button type="button" id="yes" class="btn btn-primary" data-dismiss="modal"><?php echo lang('common_yes'); ?></button>
					</div>
    	</div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="row <?php echo $redirect ? 'm-top-20' : '' ?>"  id="form">
	<div class="col-md-12">
		<div class="panel panel-piluku">
			<div class="panel-heading">
				<?php echo lang('common_mass_import_from_excel'); ?>
			</div>
			<div class="panel-body">
					<div class="row form-group">
				        <div class="col-xs-12">
				            <ul class="nav nav-pills nav-justified thumbnail setup-panel">
				                <li class="active"><a href="#step-1">
				                    <h4 class="list-group-item-heading"><?php echo lang('common_step_1'); ?></h4>
				                    <p class="list-group-item-text"><?php echo lang('common_upload_file'); ?></p>
				                </a></li>
				                <li class="disabled"><a href="#step-2">
				                    <h4 class="list-group-item-heading"><?php echo lang('common_step_2'); ?></h4>
				                    <p class="list-group-item-text"><?php echo lang('common_match_spreadsheet_to_database'); ?></p>
				                </a></li>
				                <li class="disabled"><a href="#step-3">
				                    <h4 class="list-group-item-heading"><?php echo lang('common_step_3'); ?></h4>
				                    <p class="list-group-item-text"><?php echo lang('common_validate_data_and_import'); ?></p>
				                </a></li>
				            </ul>
				        </div>
					</div>
				    <div class="row setup-content" id="step-1">
			        <div class="col-xs-12">
								
			            <div class="col-md-12 well text-center">
		                	<h4 class="text-center"><?php echo lang('common_download_spreadsheet_template'); ?></h4>
		               	 	<p class="text-center"><?php echo lang('common_download_spreadsheet_template_description'); ?></p>
											<?php if ($this->Location->count_all() > 1) { ?>
											
											<p class="text-center">
												<?php echo form_label(lang('items_include_location_specific_columns').':', 'location_specific_columns',array('class'=>'control-label ')); ?>
													<?php echo form_checkbox('location_specific_columns', '1', null,'id="location_specific_columns"');?>
													<label for="location_specific_columns"><span></span></label>
											</p>
											<?php } ?>
											<a href="<?php echo site_url('items/excel_export/0') ?>" class="btn btn-primary template_url"><?php echo lang('items_update_items_import'); ?></a>
											<a href="<?php echo site_url('items/excel/0') ?>" class="btn btn-primary template_url"><?php echo lang('items_new_items_import'); ?></a>
			            </div>
			        </div>
				        <div class="col-xs-12">
				            <div class="col-md-12 well">
											<div class="spinner" id="grid-loader" style="display:none">
												  <div class="rect1"></div>
												  <div class="rect2"></div>
												  <div class="rect3"></div>
											</div>
			                	
				                <h4 class="text-center"><?php echo lang('common_upload_file'); ?></h4>
				                <p class="text-center"><?php echo lang('common_upload_template'); ?>:</p>
												<?php echo form_open_multipart('items/do_excel_upload/',array('id'=>'item_form','class'=>'form-horizontal')); ?>
												
												<div class="form-group">
													<?php echo form_label(lang('common_file_path').':', 'file_path',array('class'=>'col-sm-3 col-md-3 col-lg-2 control-label')); ?>
													<div class="col-sm-9 col-md-9 col-lg-10">
														<input id="file" type="file" accept=".csv, text/csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" name="file" class="filestyle" data-icon="false" >
													</div>
												<?php echo form_close() ?>							
												</div>
				            </div>
										<nav id="Step1_nav" aria-label="...">
											<ul class="list-inline">
					              <li class="pull-right"><button id="step1_next_button" style="display:none" type="button" class="btn btn-primary next-step disabled"><?php echo lang('common_next_step') ?> <span aria-hidden="true">&rarr;</span></button></li>
					            </ul>
										</nav>
				        </div>
				    </div>
				    <div class="row setup-content" id="step-2">
				        <div class="col-xs-12">
				            <div class="col-md-12 well">
												<div class="spinner" id="grid-loader2" style="display:none">
													  <div class="rect1"></div>
													  <div class="rect2"></div>
													  <div class="rect3"></div>
												</div>
				                <h4 class="text-center"><?php echo lang('common_match_spreadsheet_columns_to_the_database'); ?></h4>
				                <p class="text-center"><?php echo lang('common_match_spreadsheet_columns_to_the_database_description'); ?></p>
												<br>   
												<div id="grid"></div>
				            </div>
										<nav id="Step2_nav" aria-label="...">
											<ul class="list-inline">
					              <li class="pull-left"><button id="step2_previous_button" type="button" class="btn btn-default prev-step"><span aria-hidden="true">&larr;</span> <?php echo lang('common_previous_step') ?></button></li>
					              <li class="pull-right"><button id="step2_next_button" type="button" class="btn btn-primary next-step disabled"><?php echo lang('common_next_step') ?> <span aria-hidden="true">&rarr;</span></button></li>
					            </ul>
										</nav>
				        </div>
				    </div>
				    <div class="row setup-content" id="step-3">
				        <div class="col-xs-12">
				            <div id="Complete" class="col-md-12 well text-center">
												<div class="spinner" id="grid-loader3" style="display:none">
													  <div class="rect1"></div>
													  <div class="rect2"></div>
													  <div class="rect3"></div>
												</div>
												<h4 class="text-center"><?php echo lang('common_complete_excel_import') ?></h4>
				                <p class="text-center">
													<?php echo lang('common_complete_excel_import_directions') ?>
												</p>
												<button id="complete_import" class="btn btn-primary" action="<?php echo site_url('items/complete_excel_import');?>"><?php echo lang('common_complete_excel_import') ?></button>
												
				            </div>
				            <div id="Errors" class="col-md-12 well text-center" style="display:none">
												<div id="success" class="alert alert-success" style="display:none">
												  <strong>Import Success!</strong> Your Items were imported without any errors.
												</div>
												<div id="warning" class="alert alert-warning" style="display:none">
												  <strong>Import Warning!</strong> Below is a list of warnings that occured while importing your Items.
												</div>
												<div id="error" class="alert alert-danger" style="display:none">
												  <strong>Import Failed!</strong> Below is a list of Errors that need to be corrected in your Spreadsheet before you can import your Items.
												</div>
												
												<div id="grid2"></div>
				            </div>
										<nav id="Step3_nav" aria-label="...">
											<ul class="list-inline">
							              <li class="pull-left"><button id="step3_previous_button" type="button" class="btn btn-default prev-step"><span aria-hidden="true">&larr;</span> <?php echo lang('common_previous_step') ?></button></li>
					            </ul>
										</nav>
				        </div>
				    </div>
			</div><!-- /panel-body -->
		</div>
	</div>
</div>
</div>

<script type='text/javascript'>
//multipart
$(document).ready(function() {
    
    var navListItems = $('ul.setup-panel li a'),
        allWells = $('.setup-content');

    allWells.hide();

    navListItems.click(function(e)
    {
        e.preventDefault();
        var $target = $($(this).attr('href')),
            $item = $(this).closest('li');
        
        if (!$item.hasClass('disabled')) {
            navListItems.closest('li').removeClass('active');
            $item.addClass('active');
            allWells.hide();
            $target.show();
        }
    });
    
    $('ul.setup-panel li.active a').trigger('click');
    
		$('#file').on('click', function(){
			//reset form
			var $el = $(this);
			$el.closest('form').get(0).reset();
			
			$('ul.setup-panel li:eq(1)').addClass('disabled');
			$('ul.setup-panel li:eq(2)').addClass('disabled');
			$('#step1_next_button').addClass('disabled');
			$('#step2_next_button').addClass('disabled');
			$('#step1_next_button').hide();
			
			//step 3 cleanup
			$("#Complete").show()
	    $('#Errors').hide();
			$("#grid2").empty();
			$('#success').hide();
			$('#error').hide();
			$('#warning').hide();
			
			
		});
		
		$('#file').on('change', function(){
			$('#grid-loader').show();
			
			$("#item_form").ajaxSubmit({
						success:function(response)
						{
							
							$('#grid-loader').hide();
			        
							if(!response.success)
							{ 
								show_feedback('error', response.message, <?php echo json_encode(lang('common_error')); ?>);
							}
							else
							{
								$("#grid").jsGrid("loadData");		
				        $('ul.setup-panel li:eq(1)').removeClass('disabled');
								
				        $('ul.setup-panel li a[href="#step-2"]').trigger('click');
								$('#step1_next_button').removeClass('disabled');
								$('#step1_next_button').show();
							}							
						},
						dataType:'json',
						resetForm: false
					});
		});
});

var databaseFields = [];
var columns;
var column_map = {};

//grid-js
jsGrid.validators.unique = {
    message: <?php echo json_encode(lang('common_database_field_mappings_must_be_unique')); ?>,
    validator: function(value, item) {
			if(value === -1)
			{
				//-1 means it will not be imported
				return true;
			}
			for (var x in columns) {
				if(columns[x]["Database Field"] === value)
				{
					if(item["Spreadsheet Column"] !== columns[x]["Spreadsheet Column"])
					{
						return false;
					}
				}
			}

			return true;
    }
}

$.ajax({
    url: <?php echo json_encode(site_url('items/get_database_fields_for_import')); ?>,
    dataType: "json"
}).done(function(response) {
		//remove spinner
    databaseFields = response;
		
		$("#grid").jsGrid({
		    width: "100%",
		    height: "auto",

		    inserting: false,
		    editing: true,
		    sorting: false,
				pageSize: 30,
		    paging: false,
				updateOnResize: true,
				autoload: false,
				controller: {
		        loadData: function() {
		            var d = $.Deferred();
								//add load indicator
		            $.ajax({
		                url: <?php echo json_encode(site_url('items/get_uploaded_excel_columns')); ?>,
		                dataType: "json"
		            }).done(function(response) {
									$.get(<?php echo json_encode(site_url('items/do_excel_import_map')); ?>);
										//remove spinner
		                d.resolve(response);
			
		            });

		            return d.promise();
		        },
						updateItem: function(column) {
								column_map[column["Spreadsheet Column"]] = column;
		        },
		    },
				onDataLoaded: function(args) {
					columns = args.data;
					//check to see if completely done
					var done = function(){
						var complete = true;
						for (var x in columns) 
						{
							if('Database Field' in columns[x])
							{
								//enable step 3
								$('ul.setup-panel li:eq(2)').removeClass('disabled');
								$('#step2_next_button').removeClass('disabled');
								if(columns[x]['Database Field'] === -1)
								{
									complete = false;
								}
							}
							else
							{
								complete = false;
							}
						}
						return complete;
					}
			
					if(done())
					{
						$("#step2done").modal('show');	
					}
				},
				onItemUpdated: function(args) {
			
					//check to see if completely done
					var done = function(){
						for (var x in columns) {
							if(columns[x]['Database Field'] === undefined || columns[x]['Database Field'] === -1)
							{
								return false;
							}
						}
						return true;
					}
			
					if(done())
					{
						$("#step2done").modal('show');	
					}
			
					//enable step 3
					$('ul.setup-panel li:eq(2)').removeClass('disabled');
					$('#step2_next_button').removeClass('disabled');
				
			
					if(args.previousItem["Database Field"] === 2 && args.item["Database Field"] !== 2)
					{
						// dissable step 3
						$('ul.setup-panel li:eq(2)').addClass('disabled');
						$('#step2_next_button').addClass('disabled');
						
					}
			
				},
				invalidNotify: function(args) {
			        var messages = $.map(args.errors, function(error) {
			            return error.message;
			        });

			         show_feedback('error', messages, <?php echo json_encode(lang('common_error')); ?>);
			  },
		    fields: [
		        { title: <?php echo json_encode(lang('common_spreadsheet_column')); ?>, name: "Spreadsheet Column", align: "left", width: 100},
		        { title: <?php echo json_encode(lang('common_database_field')); ?>, name: "Database Field", type: "select", align: "left", items: databaseFields, valueField: "Id", textField: "Name", valueType: "number", selectedIndex: -1, validate: "unique"},
		    ]
		});
});

//select change event listener		
$("#grid").on('change', ".jsgrid-cell select",function() {
	$("#grid").jsGrid("updateItem");
});

$(".panel input[type=checkbox]").on("click", function() {
        var $cb = $(this);
				$("#grid").jsGrid("fieldOption", "Column Preview", "visible", $cb.is(":checked"));
});

$('#step1_next_button').on("click", function(e){
	if (!$(this).hasClass('disabled')) {
		$('ul.setup-panel li a[href="#step-2"]').trigger('click');
		$(window).scrollTop(0);
	}
});

$("#step2done .btn#no").on("click", function(e) {
		$(window).scrollTop(0)
  	$("#myModal").modal('hide');     // dismiss the dialog
});
	
$("#step2done .btn#yes, #step2_next_button").on("click", function(e) {
	if (!$(this).hasClass('disabled')) {
		$('ul.setup-panel li a[href="#step-3"]').trigger('click');
		$(window).scrollTop(0);
		$("#myModal").modal('hide');     // dismiss the dialog if shown
	}
  	
});

$('#step2_previous_button').on("click", function(e){
	if (!$(this).hasClass('disabled')) {
		$('ul.setup-panel li a[href="#step-1"]').trigger('click');
		$(window).scrollTop(0);
	}
});

$('#step3_previous_button').on("click", function(e){
	if (!$(this).hasClass('disabled')) {
		$('ul.setup-panel li a[href="#step-2"]').trigger('click');
		$(window).scrollTop(0);
	}
});

$("#complete_import").on("click", function(e){
	$('#grid-loader3').show();
	
  $.ajax({
      url: <?php echo json_encode(site_url('items/set_excel_columns_map')); ?>,
			data: {mapKeys: JSON.stringify($.map(column_map, function(v) { return v; }))},
			method: 'POST'
  }).done(function(response1) {
    $.ajax({
        url: <?php echo json_encode(site_url('items/dedup_excel_import_data')); ?>,
				dataType: "json"
		}).done(function(response2){	
			
			show_feedback(response2.type, response2.message, response2.title);
			if(response2.type == 'error')
			{
				$('#grid-loader3').hide();
				display_import_errors(response2.type);
				return;
			}
			
	    $.ajax({
	        url: <?php echo json_encode(site_url('items/complete_excel_import')); ?>,
					dataType: "json"
	    }).done(function(response3) {
				$('#grid-loader3').hide();
				show_feedback(response3.type, response3.message, response3.title);
				display_import_errors(response3.type);
	    });
		});
	});
});

function display_import_errors(type)
{
	
  $.ajax({
      url: <?php echo json_encode(site_url('items/get_import_errors')); ?>,
			dataType: "json"
  }).done(function(errors) {
		$("#Complete").slideUp("slow", function() {
    	$('#Errors').slideDown("slow", function(){
				$("#"+type).toggle("slide");
				if(type !== 'success')
				{
					$("#grid2").jsGrid({
					    width: "100%",
					    height: "400px",

					    inserting: false,
					    editing: false,
					    sorting: true,
					    paging: false,

					    data: errors,

					    fields: [
					        { title:"Spreadsheet Row", name:"row", type:"number", align: "center", width: 25},
					        { title:"Error Message", name: "message", align: "center", width: 100 },
									{ title:"Type", name: "type", align: "center", width: 25, cellRenderer: function(value, item) {
										if(value == 'Warning')
										{
											return $("<td>").append($('<div>').addClass('alert alert-warning').append(value));
										}
										return $("<td>").append($('<div>').addClass('alert alert-danger').append(value));
			        }},
					    ]
					});
				}
			});
  	});
		
  });
}

$(".template_url").click(function()
{
	var replace_with = $("#location_specific_columns").prop('checked') ? "1" : "0";
	var new_href = $(this).attr('href').replace(/.$/,replace_with);
	$(this).attr('href',new_href);
});

</script>
<?php $this->load->view('partial/footer'); ?>