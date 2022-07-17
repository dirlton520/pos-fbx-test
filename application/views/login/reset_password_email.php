<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title><?php echo lang('login_reset_password'); ?></title>
	</head>
	<body>
		<?php echo lang('login_reset_password_message'); ?><br /><br />
		<?php echo anchor('login/reset_password_enter_password/'.$reset_key, lang('login_reset_password')); ?>
	</body>
</html>