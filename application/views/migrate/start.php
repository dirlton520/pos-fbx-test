<!DOCTYPE html>
<html class="<?php echo $this->config->item('language');?>">
<head>
	<meta charset="UTF-8" />
    <title>PHP Point Of Sale</title>
	<link rel="icon" href="<?php echo base_url();?>favicon.ico" type="image/x-icon"/>	
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/> <!--320-->
	<base href="<?php echo base_url();?>" />
	
	<link rel="icon" href="<?php echo base_url();?>favicon.ico" type="image/x-icon"/>
	
	<?php 
	$this->load->helper('assets');
	foreach(get_css_files() as $css_file) { ?>
		<link rel="stylesheet" type="text/css" href="<?php echo base_url().$css_file['path'].'?'.ASSET_TIMESTAMP;?>" />
	<?php } ?>
 		
	<script>
	var SITE_URL= "<?php echo site_url(); ?>";
	var BASE_URL= "<?php echo base_url(); ?>";
	</script>

  <script src="<?php echo base_url();?>assets/js/jquery.js?<?php echo ASSET_TIMESTAMP; ?>" type="text/javascript" language="javascript" charset="UTF-8"></script>
	
</head>
<body>
<div class="jumbotron">
	<div class="container">
		<div class="alert alert-warning" role="alert"><?php echo lang('migrate_warning'); ?>.</div>
	<div class="well">
  <p><?php echo $is_new ? lang('migrate_install_message') : lang('migrate_message'); ?></p>
		
  <p><a class="btn btn-primary btn-lg" href="javascript:void(0)" id="upgrade_database" role="button"><?php echo $is_new ? lang('migrate_install_database') : lang('migrate_upgrade_database');?></a></p>
	</div>
	<div class="progress" id="progress_container" style="display: none;">
	  <div class="progress-bar progress-bar-striped active" role="progressbar" id="progessbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
	    <span id="progress_percent">0</span>% <span id="progress_title"><?php echo lang('migrate_complete');?></span> <span id="progress_message"></span>
	  </div>
	</div>
	
  <a class="btn btn-default btn-lg pull-right" disabled href="<?php echo site_url('login'); ?>" id="login_to_pos" style="display:none;" role="button"><?php echo $is_new ? lang('migrate_login_to_new_pos') : lang('migrate_login_to_upgraded_pos');?> <span id="status_icon" class="glyphicon glyphicon-remove"></span></a>
	
</div>
<div id="all_messages_container" style="display: none;" class="text-center">
	<h4><?php echo lang('migrate_tasks_completed');?></h4>
<ul id="all_messages" class="list-group">
	
</ul>
</div>
</div>

</body>
</html>
<script type='text/javascript'>

var all_messages = [];

$("#upgrade_database").click(function()
{
	$(this).hide();
	set_progress(1,<?php echo json_encode(lang('migrate_starting')); ?>);
	migrate_one_step();
});

function migrate_one_step()
{
	$.getJSON(SITE_URL+'/migrate/migrate_one_step', function(response)
	{
		set_progress(response.percent_complete,response.message);
		all_messages.push(response.message);
		
		if (response.success && response.has_next_step)
		{
			migrate_one_step();
		}
		else
		{
			$("#login_to_pos").show();
			
			for(var k = 0;k< all_messages.length;k++)
			{
				$("#all_messages").append('<li class="list-group-item">'+all_messages[k]+'</li>');
			}
			
			$("#all_messages_container").show();
		}
	});
}

function set_progress(percent,message)
{
	$("#progress_container").show();
	$('#progessbar').attr('aria-valuenow', percent).css('width',percent+'%');
	$('#progress_percent').html(percent);
	if (message !='')
	{
		$("#progress_message").html('('+message+')');
	}
	else
	{
		$("#progress_message").html('');
	}
	
	if(percent == 100)
	{
		setTimeout(function(){ 
			$('.alert').slideToggle();
			$("#status_icon").removeClass("glyphicon-remove").addClass("glyphicon-ok");
			$("#login_to_pos").removeClass("btn-default").addClass("btn-success").removeAttr('disabled');
	},1000);
		
		
		
		
	}
}
</script>