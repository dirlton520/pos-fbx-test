<!DOCTYPE html>
<html>
<head>
	 <title><?php echo $this->config->item('company').' -- '.lang('common_powered_by').' PHP Point Of Sale' ?></title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <base href="<?php echo base_url();?>" />
    <link rel="icon" href="<?php echo base_url();?>favicon.ico" type="image/x-icon"/>
		<?php 
		$this->load->helper('assets');
		foreach(get_css_files() as $css_file) { ?>
 			<link rel="stylesheet" type="text/css" href="<?php echo base_url().$css_file['path'].'?'.ASSET_TIMESTAMP;?>" />
 		<?php } ?>

    <script src="<?php echo base_url();?>assets/js/jquery.js?<?php echo ASSET_TIMESTAMP; ?>" type="text/javascript" language="javascript" charset="UTF-8"></script>

	<script type="text/javascript">
		$(document).ready(function()
		{
				//If we have an empty username focus
				if ($("#login_form input:first").val() == '')
				{
					$("#login_form input:first").focus();					
				}
				else
				{
					$("#login_form input:last").focus();
				}
			});
	</script>
<?php
$this->load->helper('demo');
if (is_on_demo_host()) { ?>		
	<script src="//phppointofsale.com/js/iframeResizer.contentWindow.min.js"></script>
<?php } ?>

</head>
<body>

	<div class="flip-container">
        <div class="flipper">
            <div class="front">
                <!-- front content -->
                <div class="holder text-center">
                  <div id="logo">
					<?php echo img(
						array(
							'src' => $this->Appconfig->get_logo_image(),
						 	'style' => 'width: auto;max-width: 180px',
							
						)); ?>
				  </div>
				
				<div id="loginbox">            
			
				<p><?php echo lang('login_password_has_been_reset'); ?></p>
				<p><?php echo anchor('login', lang('login_login')); ?></p>
			</div>
					
                </div>
            </div>          
        </div>      
    </div>


</body>
</html>
