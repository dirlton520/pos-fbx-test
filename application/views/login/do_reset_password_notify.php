<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title><?php echo lang('login_reset_password'); ?></title>
<?php
$this->load->helper('demo');
if (is_on_demo_host()) { ?>		
	<script src="//phppointofsale.com/js/iframeResizer.contentWindow.min.js"></script>
<?php } ?>		
	</head>
	<body>
		<?php echo lang('login_password_reset_has_been_sent'); ?>
	</body>
</html>