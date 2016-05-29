<!DOCTYPE HTML>
<html>
	<head>
		<title>Anmeldung - deCMS</title>
		<meta charset="utf-8">
		<link rel="stylesheet" href="<?php echo site_url('css/admin.css'); ?>">
		<base href="<?php echo base_url(); ?>">
	</head>
	<body>
		<div id="loginform">
			<h1>Anmeldung</h1>
			<?php echo validation_errors(); ?>
			<?php if($loginError): ?>
				<div class="error">Benutzername und/oder Passwort falsch!</div>
			<?php endif; ?>
			<?php if(isset($_SESSION['Admin_InfoBox'])) echo '<div class="info">'.$_SESSION['Admin_InfoBox'].'</div>'; ?>
			<?php echo form_open('deCMS-admin/Login'); ?>
				<p>Benutzername:<br /><?php echo form_input('login_user', set_value('login_user', ''), 'class="field"'); ?></p>
				<p>Passwort:<br /><?php echo form_password('login_pass', '', 'class="field"'); ?></p>
				<p><?php echo form_submit('login_go', 'Anmelden'); ?></p>
			<?php echo form_close(); ?>
		</div>
	</body>
</html>