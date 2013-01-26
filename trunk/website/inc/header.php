<?php
session_start();
include_once('functions.php');
include_once('domains.php');
include_once('database.php');
include_once('ranks.php');

// Initialize Login Data
$_loggedin = false;
if (isset($_SESSION['login_data'])) {
	$_logindata = $_SESSION['login_data'];
	$_loggedin = (strpos($_SERVER['REQUEST_URI'], '/logoff') === FALSE);
}

$__url_userdata = null;

if ($subdomain != "" && $subdomain != "www" && $subdomain != "direct" && $subdomain != "dev" && $subdomain != "social") {
	// Try to get userdata... Else: error!
	
	$username = $__database->real_escape_string($subdomain);
	$q = $__database->query("SELECT * FROM accounts WHERE username = '".$username."'");
	if ($q->num_rows > 0) {
		$__url_userdata = $q->fetch_assoc();
	}
	else {
		// User not found.
		header('Location: http://'.$domain.'/?error=user-not-found');
		die();
	}
	$q->free();
	
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<title>Mapler.me &middot; MapleStory Social Network</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="keywords" content="maplestory, maple, story, mmorpg, maple story, maplerme, mapler, me" />
	<meta name="description" content="Mapler.me is a MapleStory social network, with innovative features!" />
	
	<link href='http://fonts.googleapis.com/css?family=Muli:300,400,300italic,400italic' rel='stylesheet' type='text/css' />
	<link rel="stylesheet" href="//<?php echo $domain; ?>/inc/css/style.css" type="text/css" />
</head>

<body>

	<div class="navbar navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container">
				<a class="brand" href="//<?php echo $domain; ?>" style="margin-top: 6px;opacity: 1;color: #fff3e4;text-decoration: none;text-shadow: 0 1px 4px rgba(0,0,0,0.5);font-size:25px !important;"><img src="//<?php echo $domain; ?>/inc/img/logo.gif" style="float:left;position:relative;bottom:5px;right:5px;"/>Mapler.me</a>
				<div class="nav-collapse">
					<ul class="nav hidden-phone">
						 <li class="dropdown">
							<a data-toggle="dropdown" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="100" data-close-others="true" href="#"> Pages <b class="caret"></b></a>
							<ul class="dropdown-menu">
<?php
if (isset($__url_userdata)):
?>
								<li><a href="//<?php echo $subdomain.".".$domain; ?>/"><?php echo $__url_userdata['nickname']; ?></a></li>
								<li><a href="//<?php echo $subdomain.".".$domain; ?>/my-characters">Characters</a></li>
<?php
else:
?>
								<li><a href="//<?php echo $domain; ?>/intro/">About</a></li>
								<li><a href="//<?php echo $domain; ?>/todo">To-do</a></li>
								<li class="divider"></li>
								<li><a href="//<?php echo $domain; ?>/terms">Terms of Service</a></li>
<?php
endif;
?>
					 		</ul>
						</li>
					</ul>
				
					<!-- Login / Main Menu -->	
					<ul class="nav hidden-phone pull-right">
						<li class="dropdown">
<?php
if ($_loggedin):
?>
							<a data-toggle="dropdown" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="100" data-close-others="true" href="#"> Welcome back! <?php echo $_logindata['full_name']; ?> <b class="caret"></b></a>
							<ul class="dropdown-menu">
								<li><a href="//<?php echo $_logindata['username']; ?>.<?php echo $domain; ?>/">Profile</a></li>
								<li><a href="//<?php echo $_logindata['username']; ?>.<?php echo $domain; ?>/my-characters">My Characters</a></li>
								<li><a href="//<?php echo $_logindata['username']; ?>.<?php echo $domain; ?>/panel/">Settings</a></li>
						
<?php
if ($_logindata['account_rank'] == RANK_ADMIN):
?>
								<li class="divider"></li>
								<li id="fat-menu"><a href="//<?php echo $domain; ?>/actions/repo/">Update Website</a></li>
<?php
endif;
?>
								<li class="divider"></li>
								<li><a href="//<?php echo $domain; ?>/logoff">Log off</a></li>
							</ul>
<?php
else:
?>
							<a data-toggle="dropdown" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="100" data-close-others="true" href="#">Login <b class="caret"></b></a>
							<ul class="dropdown-menu">
								<form class="form-horizontal login" style="margin:10px;" action="//<?php echo $domain; ?>/login/" method="post">
									<div class="control-group">
										<div class="controls">
											<input type="text" id="inputUsername" name="username" placeholder="Username" style="width: 222px;"/>
										</div>
									</div>
									<div class="control-group">
										<div class="controls">
											<input type="password" id="inputPassword" name="password" placeholder="Password" style="width: 222px;"/>
										</div>
									</div>
									<div class="control-group">
										<div class="controls">
											<button type="submit" class="btn btn-success" style="margin-right:2px;width:220px;">Sign in</button>
											<button type="button" onclick="document.location = 'http://<?php echo $domain; ?>/register/'" class="btn pull-right" style="display:none;">Register?</button>
										</div>
									</div>
								</form>
							</ul>
<?php
endif;
	?>
						</li>
					</ul>
<?php
if (!$_loggedin):
?>	
					<ul class="nav pull-right hidden-phone">
						<li id="nav-signup-btn"><a href="/signup/">Request Invite</a></li>
					</ul>
<?php
endif;
?>

		
					<ul class="nav mobile pull-right">
						<li class="menu dropdown">
							<a data-toggle="dropdown" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="100" data-close-others="true" href="#"><span class="sprite more menu"></span></a>

							<ul class="dropdown-menu">
<?php
if ($_loggedin):
?>
								<li><a href="//<?php echo $_logindata['username']; ?>.<?php echo $domain; ?>/">Profile</a></li>
								<li><a href="//<?php echo $_logindata['username']; ?>.<?php echo $domain; ?>/my-characters">My Characters</a></li>
								<li><a href="//<?php echo $_logindata['username']; ?>.<?php echo $domain; ?>/panel/">Settings</a></li>
						
<?php
if ($_logindata['account_rank'] == RANK_ADMIN):
?>
								<li class="divider"></li>
								<li id="fat-menu"><a href="//<?php echo $domain; ?>/actions/repo/">Update Website</a></li>
<?php
endif;
?>
								<li class="divider"></li>
								<li><a href="//<?php echo $domain; ?>/logoff">Log off</a></li>
<?php
else:
?>
								<form class="form-horizontal login" style="margin:10px;" action="//<?php echo $domain; ?>/login/" method="post">
									<div class="control-group">
										<div class="controls">
											<input type="text" id="inputUsername" name="username" placeholder="Username" style="width: 222px;"/>
										</div>
									</div>
									<div class="control-group">
										<div class="controls">
											<input type="password" id="inputPassword" name="password" placeholder="Password" style="width: 222px;"/>
										</div>
									</div>
									<div class="control-group">
										<div class="controls">
											<button type="submit" class="btn btn-success" style="margin-right:2px;width:220px;">Sign in</button>
											<button type="button" onclick="document.location = 'http://<?php echo $domain; ?>/register/'" class="btn pull-right" style="display:none;">Register?</button>
										</div>
									</div>
								</form>
<?php
endif;
?>
					 			
					 		</ul>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>

	<div class="container">
<?php
if (!$_loggedin):
?>
	<p class="lead alert alert-error">&nbsp;Mapler.me is currently in private development. <b>Check back later!</b></p>
<?php
endif;
?>	
