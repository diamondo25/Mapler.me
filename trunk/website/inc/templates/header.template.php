<?php
$title = 'Mapler.me &middot; MapleStory Social Network';
if (isset($__url_useraccount)) {
	$title = $__url_useraccount->GetNickname().' &middot; '.$title;
}
elseif (strpos($_SERVER['REQUEST_URI'], '/character/') !== FALSE) {
	$title = urldecode(substr($_SERVER['REQUEST_URI'], strrpos($_SERVER['REQUEST_URI'], '/') + 1)).' &middot; '.$title;
}

$locale_domain = $domain;
if (GMS) $locale_domain = 'gms.'.$locale_domain;
elseif (EMS) $locale_domain = 'ems.'.$locale_domain;
elseif (KMS) $locale_domain = 'kms.'.$locale_domain;

if (strpos($_SERVER['REQUEST_URI'], '/player/') !== FALSE) {
$character_name = $_GET['name'];
header('Location: http://'.$locale_domain.'/character/'.$character_name.'');
die;
}

function IsActive($name) {
	echo strpos($_SERVER['REQUEST_URI'], $name) !== false ? ' class="active"' : '';
}

/*
elseif (strpos($_SERVER['REQUEST_URI'], '/guild/') !== FALSE) {
	$title = $__url_useraccount->GetNickname().' &middot; '.$title;
}
*/
if ($_loggedin) {
	$rank = $_loginaccount->GetAccountRank();
}


function _AddHeaderLink($what, $filename) {
	global $domain;
	switch ($what) {
		case 'css':
			$dirname = 'css';
			$extension = 'css';
			$type = 'css';
		break;
		case 'js':
			$dirname = 'js';
			$extension = 'js';
			$type = 'javascript';
		break;
	}

	$modificationTime = filemtime(__DIR__.'/../'.$dirname.'/'.$filename.'.'.$extension);
	if ($what == 'css') {
?>
<link rel="stylesheet" href="//<?php echo $domain; ?>/inc/<?php echo $dirname; ?>/<?php echo $filename.'.'.$modificationTime.'.'.$extension; ?>" type="text/<?php echo $type; ?>" />
<?php
	}
	elseif ($what == 'js') {
?>
<script type="text/javascript" src="//<?php echo $domain; ?>/inc/<?php echo $dirname; ?>/<?php echo $filename.'.'.$modificationTime.'.'.$extension; ?>"></script>
<?php
	}
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

	<title><?php echo $title; ?></title>

	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
	<meta name="keywords" content="maplestory, maple, story, mmorpg, maple story, maplerme, mapler, me, Mapler Me, Mapler.me, Nexon, Nexon America,
	henesys, leafre, southperry, maplestory rankings, maplestory, realtime updates, Maplestory items, MapleStory skills, guild, alliance, GMS, KMS, EMS, <?php
	if (isset($__url_useraccount)):
		echo $__url_useraccount->GetNickname().', '.$__url_useraccount->GetNickname()."'s Mapler.me";
	endif;
	?>" />
	<meta name="description" content="Mapler.me is a MapleStory social network and service providing innovative features to enhance your gaming experience!" />

	<link href="http://fonts.googleapis.com/css?family=Muli:300,400,300italic,400italic" rel="stylesheet" type="text/css" />
    <link href="http://<?php echo $domain; ?>/inc/css/themes/light.css" rel="stylesheet" type="text/css" />
<?php
_AddHeaderLink('css', 'style');
_AddHeaderLink('css', 'animate.min');
_AddHeaderLink('css', 'font-awesome.min');
if (strpos($_SERVER['REQUEST_URI'], '/character/') !== FALSE ||
	strpos($_SERVER['REQUEST_URI'], '/guild/') !== FALSE) {
	_AddHeaderLink('css', 'style.player');
}

if (strpos($_SERVER['REQUEST_URI'], '/settings/') !== FALSE ||
	strpos($_SERVER['REQUEST_URI'], '/manage/') !== FALSE) {
	_AddHeaderLink('css', 'settings.style');
}
?>

	<link rel="shortcut icon" href="//<?php echo $domain; ?>/inc/img/favicon.ico" />
	<link rel="icon" href="//<?php echo $domain; ?>/inc/img/favicon.ico" type="image/x-icon" />

	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js" type="text/javascript"></script>
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js" type="text/javascript"></script>
	
<?php
_AddHeaderLink('js', 'scripts');
if (strpos($_SERVER['REQUEST_URI'], '/character/') !== FALSE) {
	_AddHeaderLink('js', 'script.player');
}
_AddHeaderLink('js', 'jquery.isotope.min');
_AddHeaderLink('js', 'maplerme');
_AddHeaderLink('js', 'keypress');
?>

	<script>
	$(function() {
		$( ".draggable" ).draggable({ containment: "html", scroll: false });
	});
  	</script>
</head>

<body>

<header>
    <div class="sticky-nav stuck span12">
        <nav id="rightmenu">
        	<ul id="menu-rightnav">
				<li class="dropdown">
					<a id="goUp" data-toggle="dropdown" class="dropdown-toggle hidden-phone" data-toggle="dropdown" data-hover="dropdown" data-delay="100" data-close-others="true" href="#"><img src="//<?php echo $domain; ?>/inc/img/logo.celebration.png" style="width:35px;position:relative;top:10px;"/> <b>mapler</b>.me
						<?php if ($_loggedin && GetNotification() != '0'): ?>
							(<?php echo GetNotification(); ?><i class="icon-bell-alt icon-white"></i>)
						<?php endif; ?> <i class="icon-chevron-down"></i>
					</a>

					<a id="goUp" data-toggle="dropdown" class="dropdown-toggle showmobile" data-toggle="dropdown" data-hover="dropdown" data-delay="100" data-close-others="true" href="#"><img src="//<?php echo $domain; ?>/inc/img/logo.celebration.png" class="showmobile" style="width:35px;position:relative;top:10px;"/>
					</a>

					<ul class="dropdown-menu">
					<?php if ($_loggedin && GetNotification() != 0): ?>
						<li><a href="//<?php echo $domain; ?>/settings/friends/"><?php echo GetNotification(); ?> Notifications</a></li>
						<li class="divider"></li>
					<?php endif; ?>
<?php
// Display subdomain pages related to the user
if (isset($__url_useraccount)):
?>

						<li><a href="//<?php echo $subdomain.".".$domain; ?>/">Profile</a></li>
						<li><a href="//<?php echo $subdomain.".".$domain; ?>/characters/">Characters</a></li>
						<li><a href="//<?php echo $subdomain.".".$domain; ?>/friends/">Friends</a></li>
						<li class="divider"></li>
						<li style="font-weight:500;"><a href="<?php if ($_loggedin): ?>//<?php echo $domain; ?>/stream/">Back to Stream<?php else: ?>//<?php echo $domain; ?>">Back to Home<?php endif; ?></a></li>

<?php
// Display normal pages if not a subdomain
else:
?>
						<li>
							<form method="post" action="http://<?php echo $domain; ?>/search/" style="margin:0 !important;">
								<input type="text" name="search" class="search-query searchbar flat" style="margin-left: 8px;" placeholder="Find a character?" />
								<input type="hidden" name="type" value="character" />
							</form>
						</li>
						<li class="divider"></li>
						<li style="font-weight:500;"><a href="<?php if ($_loggedin): ?>//<?php echo $domain; ?>/stream/">Stream<?php else: ?>//<?php echo $domain; ?>">Home<?php endif; ?></a></li>
						<li class="divider"></li>
						<li><a href="//<?php echo $domain; ?>/rankings/">Rankings</a></li>
						<li><a href="//blog.mapler.me/">Blog</a></li>
<?php if ($_loggedin): ?>
						<li><a href="//<?php echo $domain; ?>/guide/">Guide</a></li>
						<li><a href="//<?php echo $domain; ?>/downloads/">Downloads</a></li>
						<li><a href="//<?php echo $domain; ?>/cdn/">CDN</a></li>
<?php endif; ?>
						<li class="divider"></li>
						<li><a href="//<?php echo $domain; ?>/team/">Our Team</a></li>
<?php
endif;
?>
					</ul>
				</li>
			</ul>
		</nav>
<?php
if ($_loggedin):
?>
        <nav id="rightmenu">
        	<ul id="menu-rightnav">
        		<li class="dropdown">

					<a data-toggle="dropdown" class="dropdown-toggle" style="z-index:1;overflow:hidden;" data-toggle="dropdown" data-hover="dropdown" data-delay="100" data-close-others="true" href="#">
						<span>@<?php echo $_loginaccount->GetUsername(); ?></span>
						<i class="icon-chevron-down"></i>
					</a>
					<ul class="dropdown-menu" style="margin-right: 9px;">
						<li><a href="//<?php echo $_loginaccount->GetUsername(); ?>.<?php echo $domain; ?>/">Profile</a></li>
						<li class="divider"></li>
						<li><a href="//<?php echo $_loginaccount->GetUsername(); ?>.<?php echo $domain; ?>/characters/">Characters</a></li>
						<li><a href="//<?php echo $_loginaccount->GetUsername(); ?>.<?php echo $domain; ?>/friends/">Friends</a></li>
						<li class="dropdown-submenu">
							<a tabindex="-1" href="//<?php echo $domain; ?>/settings/profile/">Settings</a>
							<ul class="dropdown-menu">
								<li><a href="//<?php echo $domain; ?>/settings/general/">General</a></li>
								<li><a href="//<?php echo $domain; ?>/settings/characters/">Characters</a></li>
								<li><a href="//<?php echo $domain; ?>/settings/friends/">Friend Requests</a></li>
								<li><a href="//<?php echo $domain; ?>/settings/connections/">Connections</a></li>
							</ul>
						</li>

<?php
if ($_loginaccount->GetAccountRank() >= RANK_ADMIN):
?>
						<li class="divider"></li>
						<li class="dropdown-submenu">
							<a tabindex="-1" href="//<?php echo $domain; ?>/manage/general/">Manage</a>
							<ul class="dropdown-menu">
								<li><a href="//<?php echo $domain; ?>/manage/general/">General</a></li>
								<li><a href="//<?php echo $domain; ?>/manage/statuses/">Statuses</a></li>
								<li><a href="//<?php echo $domain; ?>/manage/revisions/">Revisions</a></li>
								<li><a href="//<?php echo $domain; ?>/manage/statistics/">Statistics</a></li>
								<li><a href="//<?php echo $domain; ?>/manage/serverlog/">Log</a></li>
								<li><a href="//<?php echo $domain; ?>/manage/findstring/">Search</a></li>
							</ul>
						</li>
<?php
endif;
?>
						<li class="divider"></li>
						<li><a href="//<?php echo $domain; ?>/logoff/">Sign Out</a></li>
					</ul>
				</li>
			</ul>
		</nav>
<?php
else:
?>
		<nav id="menu">
			<ul id="menu-nav">
				<li><a href="//<?php echo $domain; ?>/login/"><i class="icon-check"></i> Login</a></li>
			</ul>
		</nav>
<?php
endif;
?>

<?php if ($_loggedin): ?>
		<nav id="menu">
			<ul id="menu-nav" class="extra-nav">
				<li><a href="//<?php echo $domain; ?>/stream/" <?php IsActive('/stream/'); ?>><i class="icon-reorder"></i> <span class="hidden-tablet hidemobile">Stream</span></a></li>
				<li><a href="//<?php echo $domain; ?>/discover/" <?php IsActive('/discover/'); ?>><i class="icon-question-sign"></i> <span class="hidden-tablet hidemobile">Discover</span></a></li>
				<li><a href="//<?php echo $domain; ?>/mentions/" <?php IsActive('/mentions/'); ?>><i class="icon-comments"></i> <span class="hidden-tablet hidemobile">Mentions</span></a></li>
				
<?php
	if ($_loggedin) {
		// Shouldn't be here...
		$__login_main_character = $_loginaccount->GetMainCharacterName();

		if (!$_loginaccount->IsMuted()){
?>
				<li>
					<a href="#post" role="button" data-toggle="modal"><i class="icon-plus"></i></a>
				</li>

<?php
		}
	}
?>
			</ul>
		</nav>
<?php endif; ?>
    </div>
</header>

<div class="container main" style="padding: 20px; border-radius: 5px; margin-top: 90px; margin-bottom: 30px;">

<?php
if ($_loggedin) {
	if ($_loginaccount->GetAccountRank() <= RANK_AWAITING_ACTIVATION) {
		if (strpos($_SERVER['REQUEST_URI'], '/support/') === FALSE) {
			DisplayError(5);
			require_once __DIR__.'/../../inc/footer.php';
			die;
		}
	}

	if (!$_loginaccount->IsMuted()) {
		require_once 'social.php';
	}

	if ($_loginaccount->IsRankOrHigher(RANK_ADMIN)) {
		require_once 'banhammer.php';
	}

	if ($_loginaccount->IsMuted()) {
		DisplayError(4);
	}
}
?>