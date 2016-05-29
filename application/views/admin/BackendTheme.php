<!DOCTYPE HTML>
<html>
	<head>
		<title><?php echo $PageTitle; ?> - deCMS</title>
		<meta charset="utf-8">
		<link rel="stylesheet" href="<?php echo site_url('css/admin.css'); ?>">
		<base href="<?php echo base_url(); ?>">
	</head>
	<body>
		<div id="sidebar">
			<div id="sidebarTitle"><h1 class="nospace">deCMS</h1></div>
			<div id="sidebarMenu">
				<?php echo $_SESSION['Admin_SidebarCache']; ?>
			</div>
		</div>
		<div id="main">
			<div id="mainTitle">
				<span>Angemeldet als: <?php echo $_SESSION['Admin_Username']; ?>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<a href="<?php echo site_url('deCMS-admin/Logout'); ?>">Abmelden</a></span>
				<h1 class="nospace"><?php echo $PageTitle; ?></h1>
			</div>
			<div id="content">
				<?php echo $Content; ?>
			</div>
		</div>
	</body>
</html>