<!DOCTYPE html>
<html>
<head>
    <title><?php 
		$this->load->helper('demo');
		echo !is_on_demo_host() ?  $this->config->item('company').' -- '.lang('common_powered_by').' PHP Point Of Sale' : 'Demo - PHP Point Of Sale | Easy to use Online POS Software' ?></title>
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
            if ($("#username").val() == '')
            {
                $("#username").focus();                   
            }
            else
            {
                $("#password").focus();
            }
				
				$(".checkForUpdate").click(function(event)
				{
					event.preventDefault();
					$('#spin').removeClass('hidden');
		
					$.getJSON($(this).attr('href'), function(update_available) 
					{
						$('#spin').addClass('hidden');
						if(update_available)
						{
							$(".checkForUpdate").parent().html(<?php echo json_encode(lang('common_update_available').' <a href="http://phppointofsale.com/downloads.php" target="_blank">'.lang('common_download_now').'</a>');?>);
						}
						else
						{
							$(".checkForUpdate").parent().html(<?php echo json_encode(lang('common_not_update_available')); ?>);
						}
					});
		
				});
		});
		
		
    </script>
<?php
$this->load->helper('demo');
if (is_on_demo_host()) { ?>		
	<script src="//phppointofsale.com/js/iframeResizer.contentWindow.min.js"></script>
<?php } ?>		
</head>
<body>	
	
		<?php
		if(isset($announcement))
		{
		?>
     <div class="text-center">
				<?php echo $announcement; ?>
			</div>
		<?php
		}
		?>
    
    <div class="flip-container">
        <div class="flipper">
            <div class="front">
                <!-- front content -->
                <div class="holder">
                    <?php if ($ie_browser_warning) { ?>
		                 <div class="holder">
                        <div class="alert alert-danger">
                           <strong><?php echo lang('login_unsupported_browser');?></strong>
                        </div>
								<br />
								<br />
							</div>
                    <?php
                    } ?>
                              

                    <div class="heading login-logo">
                        <?php echo img(array('src' => $this->Appconfig->get_logo_image())); ?>
                    </div> 
                    <?php echo form_open('login?continue='.rawurlencode($this->input->get('continue')), array('class' => 'form login-form', 'id'=>'loginform', 'autocomplete'=> 'off')) ?>            
                        <p>
									<?php 
									echo lang('login_welcome_message'); 
									
							       if (is_on_demo_host()) 
									 {
							           echo '<h2 class="text-center">'.lang('login_press_login_to_continue').'</h2>';
									 }
									?>
								</p>
                        <?php if (validation_errors()) {?>
                        <div class="alert alert-danger">
                            <strong><?php echo lang('common_error'); ?></strong>
                            <?php echo validation_errors(); ?>
                        </div>
                        <?php } ?>
                        <?php echo form_input(array(
                            'name'=>'username', 
                            'id'=>'username', 
                            'value'=> $username,
                            'class'=> 'form-control',
                            'placeholder'=> lang('login_username'),
                            'size'=>'20')); 
                        ?>

                        <?php echo form_password(array(
                            'name'=>'password', 
                            'id' => 'password',
                            'value'=>$password,
                            'class'=>'form-control',
                            'placeholder'=> lang('login_password'),
                            'size'=>'20')); 
                        ?>
                
                        <div class="bottom_info">
                            <a href="<?php echo site_url('login/reset_password') ?>" class="pull-right flip-link to-recover"><?php echo lang('login_reset_password').'?'; ?></a>
                            
                            <?php 
									 $this->load->helper('update');
									 if (!is_on_phppos_host()) {?>
                                <span><?php echo anchor('login/is_update_available', lang('common_check_for_update'), array('class' => 'checkForUpdate pull-left')); ?></span>&nbsp;
                                <span id="spin" class="hidden">
                                    <i class="ion ion-load-d ion-spin"></i>
                                </span>
                            <?php } ?>
                        </div>      
                        <div class="clearfix"></div>
                        <button type="submit" class="btn btn-primary btn-block"><?php echo lang('login_login'); ?></button>
                    <?php echo form_close() ?>  
                    <div class="version">
                        <p>
                            <span class="badge bg-success"><?php echo APPLICATION_VERSION; ?></span> <?php echo lang('common_built_on'). ' '.BUILT_ON_DATE;?>
                        </p>
							
                        <?php if (isset($trial_on) && $trial_on === true && !isset($trial_over)) { ?>
                           <div class="alert alert-success">
                            <?php echo lang('login_trail_info'). ' '.date(get_date_format(), strtotime($cloud_customer_info['trial_end_date'])).'. '.lang('login_trial_info_2'); ?>
                            </div>
                            <a class="btn btn-block btn-success" href="https://phppointofsale.com/update_billing.php?store_username=<?php echo $cloud_customer_info['username'];?>" target="_blank"><?php echo lang('common_update_billing_info');?></a>
                        <?php } ?>
						
							
                        <?php if (isset($subscription_payment_failed) && $subscription_payment_failed === true) { ?>
                           <div class="alert alert-danger">
                                <?php echo lang('login_payment_failed_text'); ?>
                            </div>
                            <a class="btn btn-block btn-success" href="https://phppointofsale.com/update_billing.php?store_username=<?php echo $cloud_customer_info['username'];?>" target="_blank"><?php echo lang('common_update_billing_info');?></a>
                        <?php } ?>
								
                        <?php if (isset($subscription_cancelled_within_5_days) && $subscription_cancelled_within_5_days === true) { ?>
                            <div class="alert alert-danger">
                                <?php echo lang('login_resign_text'); ?>
                            </div>
                        
                            <ul class="list-inline">
                            <li>
                                <a class="btn btn-block btn-sm btn-success" href="https://phppointofsale.com/update_billing.php?store_username=<?php echo $cloud_customer_info['username'];?>" target="_blank"><?php echo lang('login_resignup');?></a>
                            </li>
						</ul>

                        <?php } ?>
                    </div>                
                </div>
            </div>          
        </div>      
    </div>
</body>
<?php if ($this->input->get('demologin')) { ?>
<script>	
	$("#loginform").submit();
</script>
	<?php } ?>
</html>