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
      $("#password").focus();                   
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
				<div class="holder">
					
					<h1 class="heading">
						<?php echo img(
						array(
						'src' => $this->Appconfig->get_logo_image(),
						 'style' => 'width: auto;max-width: 180px',
						)); ?>
					</h1>
					<?php echo form_open('login/do_reset_password/'.$key,array('class'=>'form login-form')) ?>
							
						<?php if (isset($force_password_change) &&  $force_password_change){ 
							echo form_hidden('force_password_change',1);
						?>
							<p><?php echo lang('login_force_password_change'); ?></p>
						<?php } else {
							echo form_hidden('force_password_change',0);
						?>
							<p><?php echo lang('login_reset_password'); ?></p>
						<?php } ?>	
						
						<?php if (isset($error_message)) {?>
							<div class="alert alert-danger">
								<strong><?php echo json_encode(lang('common_error')); ?></strong>
								<?php echo $error_message; ?>
							</div>
						<?php } ?>

						<?php echo form_password(array(
							'name'=>'password', 
							'id' => 'password',
							'class'=>'form-control', 
							'placeholder'=>lang('login_password'), 
							'size'=>'20')); ?>				
						<?php echo form_password(array(
							'name'=>'confirm_password', 
							'class'=>'form-control', 
							'placeholder'=> lang('login_confirm_password'), 
							'size'=>'20')); ?>				
						<div class="bottom_info">
							<?php echo anchor('login', lang('login_login')); ?>
						</div>		
						<div class="clearfix"></div>
						<button type="submit" class="btn btn-primary btn-block"><?php echo lang('login_reset_password'); ?></button>
					<?php echo form_close() ?>	
					<div class="version">
                        <p>
                            <span class="badge bg-success"><?php echo APPLICATION_VERSION; ?></span> <?php echo lang('common_built_on'). ' '.BUILT_ON_DATE;?>
                        </p>
                    </div>			
				</div>
			</div>			
		</div>		
	</div>
			
</body>
</html>