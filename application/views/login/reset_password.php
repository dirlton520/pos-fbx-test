<!DOCTYPE html>
<html>
<head>
	 <title><?php 
	$this->load->helper('demo');	 
	  echo $this->config->item('company').' -- '.lang('common_powered_by').' PHP Point Of Sale' ?></title>
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
    <style type="text/css">
        body
        {
            padding: 5px;
        }
    </style>   
	<script type="text/javascript">
		$(document).ready(function()
		{
				//If we have an empty username focus
				if ($("#username_or_email").val() == '')
				{
					$("#username_or_email").focus();					
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
                <div class="holder">
                                        
                    <h1 class="heading">
                        <?php echo img(
                        array(
                            'src' => $this->Appconfig->get_logo_image(),
									 'style' => 'width: auto;max-width: 180px',
                            )); ?>
                    </h1> 
                    <?php echo form_open('login/do_reset_password_notify',array('class'=>'form login-form')); ?>
                        <p><?php echo lang('login_reset_password'); ?></p>
                        <?php if (isset($error)) {?>
			                <div class="alert alert-danger">
			                    <strong><?php echo lang('common_error'); ?></strong>
			                    <?php echo $error; ?>
			                </div>
			            <?php } else if(isset($success)){ ?>
			                <div class="alert alert-success">
			                    <strong><?php echo lang('common_success'); ?></strong> 
			                    <?php echo $success; ?>
			                </div>
		                <?php } ?>
                        <?php echo form_input(array(
                            'name'=>'username_or_email', 
									 'id' =>'username_or_email',
                            'class'=>'form-control', 
                            'placeholder'=>lang('login_username'), 
                            'size'=>'20')); 
                        ?>
                
                        <div class="bottom_info">
                            <a href="<?php echo site_url('login') ?>" class="flip-link to-recover"><?php echo lang('login_login'); ?></a>
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