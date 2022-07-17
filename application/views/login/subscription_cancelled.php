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
					<p>
						<?php echo lang('login_resign_text'); ?>
					    <a class="btn btn-block btn-sm btn-danger" href="https://phppointofsale.com/update_billing.php?store_username=<?php echo $cloud_customer_info['username'];?>" target="_blank"><?php echo lang('login_resignup');?></a>
					</p>
				</div>
			</div>
		</div>
	</div>
</body>
</html>