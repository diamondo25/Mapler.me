<?php
if (isset($_GET['debugsite'])) {
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
}
else {
	set_time_limit(60);
	error_reporting(0);
}
// Default set to Pacific Time (MapleStory Time)
// Note that our database still uses the GMT + 1 time (Holland)
date_default_timezone_set('America/Los_Angeles');
ini_set('display_errors', 0);
//mb_internal_encoding('UTF-8');


require_once __DIR__.'/classes/database.php';



// IP ban check right away.
$__incoming_ip = $_SERVER['REMOTE_ADDR'];
$q = $__database->query("SELECT 1 FROM ip_ban WHERE '".$__database->real_escape_string($__incoming_ip)."' LIKE ip");
if ($q->num_rows != 0) {
	$__database->query("INSERT INTO ip_ban_trigger_log VALUES (NULL, '".$__database->real_escape_string($__incoming_ip)."', NOW())");
?>
<html>
<head>
	<title>Banned.</title>
</head>
<body>
	<div style="background: url('http://puu.sh/3ADi8.gif') top center no-repeat;margin: 0 auto; width:500px;height:400px;">
	<center style="position:relative;top:200px;">
		<img src="http://puu.sh/3ADER.png" style="z-index:10;"/><br />
		<a href="mailto:support@mapler.me" style="color:black;text-decoration:none;font-size:20px;border-bottom: 1px dotted black;">Appeal / Report Unintentional Ban</a>
	</center>
	</div>
</body>
</html>
<?php
	die();
}

require_once __DIR__.'/classes/form.php';
require_once __DIR__.'/classes/account.php';
require_once __DIR__.'/classes/inventory.php';
require_once __DIR__.'/classes/statuses.php';
require_once __DIR__.'/domains.php';
require_once __DIR__.'/ranks.php';
require_once __DIR__.'/functions.datastorage.php';
require_once __DIR__.'/bbcode.php';
require_once __DIR__.'/server_info.php';

function GetUniqueID() {
	return substr(uniqid(), -5);
}

function CheckArrayOf($from, $arrayValues, &$errorList) {
	$errorList = array();
	foreach ($arrayValues as $name) {
		if (empty($from[$name])) $errorList[$name] = true;
	}
	return count($errorList) == 0;
}

// $vals: array(array("herp", 0, 12))
function IsInBetween($vals) {
	foreach ($vals as $val) {
		if ($val[1] == -1 || strlen($val[0]) >= $val[1]) {
			if ($val[2] == -1 || strlen($val[0]) <= $val[2]) {
				continue;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}
	return true;
}

// Password = 28 characters in DB, but uses MD5 (32) characters to confuse hackers. And has a salt aswell.
function GetPasswordHash($password, $salt) {
	return substr(md5($salt.$password), 0, 28);
}

function time_elapsed_string($etime) {
	if ($etime < 1) {
		return 'moments';
	}

	$a = array(
		12 * 30 * 24 * 60 * 60  =>  'year',
		30 * 24 * 60 * 60       =>  'month',
		24 * 60 * 60            =>  'day',
		60 * 60                 =>  'hour',
		60                      =>  'minute',
		1                       =>  'second'
	);

	foreach ($a as $secs => $str) {
		$d = $etime / $secs;
		if ($d > 1) {
			$r = round($d);
			return $r . ' ' . $str . ($r > 1 ? 's' : '');
		}
	}
	return '0 seconds';
}

function AddCommas($number) {
	echo number_format($number);
}

function Explode2($seperator, $subseperator, $value) {
	$result = array();
	foreach (explode($seperator, $value) as $chunk) {
		$pos = strpos($chunk, $subseperator);
		$key = substr($chunk, 0, $pos);
		$value = substr($chunk, $pos + 1);
		$result[$key] = $value;
	}
	return $result;
}

function IGTextToWeb($data, $extraOptions = array()) {
	// Escape quotes
	$data = str_replace(array('"', "'"), array('&quot;', '&#39;'), $data); // Single quote = &#39; -.-

	// Fix newlines
	$data = str_replace('\\\\', '\\', $data); // Triple slashes
	$data = str_replace('\\\\', '\\', $data); // Double slashes
	$data = str_replace(array('\r', '\n'), array("\r", "\n"), $data);
	// Replace all newlines to <br />'s
	$data = nl2br($data);
	// Remove newlines
	$data = str_replace(array("\r", "\n"), array('', ''), $data);

	// Ingame things

	// For 'extra' options, like #incrDAM and such
	if (count($extraOptions) > 0) {
		$_from = array();
		$_to = array();
		foreach ($extraOptions as $from => $to) {
			$_from[] = $form;
			$_to[] = $to;
		}
		$data = str_replace($_from, $_to, $data);
	}

	$endTag = '';
	$result = '';
	$datalen = strlen($data);
	$end = false;
	for ($i = 0; $i < $datalen; $i++) {
		$end = ($i + 1 == $datalen);
		$c = $data[$i];
		$preobj = $i > 0 ? $data[$i - 1] : '-';
		if ($c == '#' && $preobj != '&') {
			if ($end) continue;
			$nc = $data[$i + 1];
			$i++;
			if ($nc == 'w') { // no typewriting; ignore
				// CutoutText($data, $i, 2);
			}
			elseif ($nc == 'e') { // Bold
				if ($endTag != '') {
					$result .= $endTag;
				}
				$result .= '<strong>';
				$endTag = '</strong>';
			}
			elseif ($nc == 'r') { // Red
				if ($endTag != '') {
					$result .= $endTag;
				}
				$result .= '<span style="color: red;">';
				$endTag = '</span>';
			}
			elseif ($nc == 'b') { // Blue
				if ($endTag != '') {
					$result .= $endTag;
				}
				$result .= '<span style="color: lightblue;">';
				$endTag = '</span>';
			}
			elseif ($nc == 'g') { // Green
				if ($endTag != '') {
					$result .= $endTag;
				}
				$result .= '<span style="color: green;">';
				$endTag = '</span>';
			}
			elseif ($nc == 'k') { // Black
				if ($endTag != '') {
					$result .= $endTag;
				}
				$result .= '<span style="color: black;">';
				$endTag = '</span>';
			}
			elseif ($nc == 'c') { // Orange?
				if ($endTag != '') {
					$result .= $endTag;
				}
				$result .= '<span style="color: darkorange;">';
				$endTag = '</span>';
			}
			elseif ($nc == '*') { // Orange? | * == Nebulite info -.-
				if ($endTag != '') {
					$result .= $endTag;
				}
				$result .= '<span style="color: darkorange;">';
				$result .= '*';
				$endTag = '</span>';
			}
			elseif ($nc == 'd') { // Purple
				if ($endTag != '') {
					$result .= $endTag;
				}
				$result .= '<span style="color: purple;">';
				$endTag = '</span>';
			}
			else {
				// Break current one!
				if ($endTag != '') {
					$result .= $endTag;
					$endTag = '';
				}
				else {
					$result .= $c;
				}
				$i--;
			}
		}
		else {
			$result .= $c;
		}
	}
	
	if ($end && $endTag != '') {
		$result .= $endTag;
	}

	return $result;
}

function GetInventoryName($id) {
	switch ($id) {
		case 0: return 'Equipment';
		case 1: return 'Usage';
		case 2: return 'Set-Up';
		case 3: return 'Etc';
		case 4: return 'Cash';
	}
}

function GetSystemTimeFromFileTime($time) {
	if ($time == 3439756800)
		return '';
	if ($time == 3439670400)
		return 'Permanent';
	return date('Y-m-d h:i:s', $time);
}


function GetCorrectStat($internal_id, $locale) {
	$db = ConnectCharacterDatabase($locale);

	// Item buffs
	$tmp = NULL;
	$q = $db->query("SELECT SUM(`str`) AS `str`, SUM(`dex`) AS `dex`, SUM(`int`) AS `int`, SUM(`luk`) AS `luk`, SUM(`maxhp`) AS `mhp`, SUM(`maxmp`) AS `mmp` FROM `items` WHERE `character_id` = ".intval($internal_id)." AND slot < 0");
	if ($q->num_rows >= 1) {
		$tmp = $q->fetch_assoc();
	}
	$q->free();

	return $tmp;
}


function CalculateSkillValue($what, $x) {
	$what = str_replace(
		array("u", "d", "x"), // u(x/2) = ceil(x/2)
		array("ceil", "floor", $x), // d(x/2) = floor(x/2)
		$what
	);

	eval('$value = intval('.$what.');'); // ohboy...
	return $value;
}

function GetSkillBuffs($internal_id, $locale) {
	$db = ConnectCharacterDatabase($locale);

	$q = $db->query("
SELECT
	s.skillid,
	s.level,
	st.value
FROM
	`skills` s
LEFT JOIN
	`strings` st
		ON
	st.objectid = s.skillid
		AND
	st.key = 'buff'
WHERE
	s.`character_id` = ".intval($internal_id)."
		AND
	st.value IS NOT NULL
");
	$temp = array();
	while ($row = $q->fetch_assoc()) {
		$temp[$row['skillid']] = array('level' => $row['level'], 'data' => Explode2(';', '=', $row['value']));
	}
	$q->free();
	return $temp;
}


function GetCharacterName($id, $locale) {
	$db = ConnectCharacterDatabase($locale);

	$q = $db->query("SELECT name FROM characters WHERE id = ".intval($id));
	if ($q->num_rows >= 1) {
		$tmp = $q->fetch_row();
		$q->free();
		return $tmp[0];
	}
	$q->free();
	return 'Unknown Character';
}

function GetCharacterAccountId($id, $locale) {
	$db = ConnectCharacterDatabase($locale);

	$q = $db->query("SELECT GetCharacterAccountId(".intval($id).")");
	$tmp = $q->fetch_row();
	$q->free();
	return $tmp[0];
}

function GetCharacterStatus($id, $locale, $account = NULL) {
	global $__database;
	
	$accountLoaded = $account !== NULL;
	if (!$accountLoaded) {
		$account = Account::Load(intval(GetCharacterAccountId($id, $locale)));
	}
	
	$name = GetCharacterName($id, $locale);
	
	$value = $account->GetCharacterDisplayValue($name);
	
	if (!$accountLoaded) { // Clear data
		unset($account);
	}
	
	return $value;
}

function GetFriendStatus($you, $it) {
	global $__database;

	$q = $__database->query("SELECT FriendStatus(".intval($you).", ".intval($it).")");
	$tmp = $q->fetch_row();
	$q->free();
	return $tmp[0];
}

function AccountExists($name) {
	global $__database;

	$q = $__database->query("SELECT 1 FROM accounts WHERE username = '".$__database->real_escape_string($name)."'");
	$found = $q->num_rows == 1;
	$q->free();
	return $found;
}

function GetAccountID($name) {
	global $__database;

	$q = $__database->query("SELECT id FROM accounts WHERE username = '".$__database->real_escape_string($name)."'");
	if ($q->num_rows == 0) {
		$q->free();
		return NULL;
	}
	$tmp = $q->fetch_row();
	$q->free();
	return $tmp[0];
}

function Logging($type, $person, $action, $extra) {
    global $__database;
    	
	if ($type == 'admin') {
        $statement = $__database->prepare('INSERT INTO admin_log (id, username, action, extra_info, at) VALUES (NULL,?,?,?,NOW())');
        
        $statement->bind_param('sss', $person, $action, $extra);
        
        $statement->execute();
	}
	
	if ($type == 'characterdeletion') {
		$db = ConnectCharacterDatabase(CURRENT_LOCALE);
        $statement = $db->prepare('INSERT INTO character_delete_queue (id, name, requested_by, requested_at) VALUES (NULL,?,?,NOW())');
        
        $statement->bind_param('ss', $person, $action);
        
        $statement->execute();
	}
	
}

// only notifications will be friend requests for now.
function GetNotification() {
	global $__database, $_loginaccount;

	$q = $__database->query("SELECT COUNT(*) FROM friend_list WHERE friend_id = ".$_loginaccount->GetId()." AND accepted_on IS NULL");
	$tmp = $q->fetch_row();
	$q->free();
	return $tmp[0];
}

function GetMapname($id, $locale, $full = true) {
	$map = GetMapleStoryString('map', $id, 'name', $locale);
	if ($full) {
		$subname = GetMapleStoryString('map', $id, 'street', $locale);
		if ($subname != NULL) {
			$map = $subname.' - '.$map;
		}
	}
	if ($map == '') {
		$map = '???';
	}
	return $map;
}

function MakeStatAddition($name, $value, $statarray) {
	$add = $statarray[$name];
	if ($add > 0) {
		return ($value + $add);
	}
	else {
		return $value;
	}
}

function GetItemType($id) {
	return floor($id / 10000);
}

function GetItemInventory($id) {
	return floor($id / 1000000);
}

function GetWZItemTypeName($id) {
	$tmp = GetItemType($id);
	
	if ($id < 10000) {
		return str_pad($id, 8, '0', STR_PAD_LEFT).'.img';
	}

	switch ($tmp) {
		case 100: return 'Cap';
		case 104: return 'Coat';
		case 105: return 'Longcoat';
		case 106: return 'Pants';
		case 107: return 'Shoes';
		case 108: return 'Glove';
		case 109: return 'Shield';
		case 110: return 'Cape';
		case 111: return 'Ring';
		case 117: return 'MonsterBook';
		case 120: return 'Totem';


		case 101:
		case 102:
		case 103:
		case 112:
		case 113:
		case 114:
		case 115:
		case 116:
		case 118:
		case 119:
			return 'Accessory';


		case 121:
		case 122:
		case 123:
		case 124:
		case 130:
		case 131:
		case 132:
		case 133:
		case 134:
		case 135:
		case 136:
		case 137:
		case 138:
		case 139: // FISTFIGHT!!! (sfx: barehands, only 1 item: 1392000)
		case 140:
		case 141:
		case 142:
		case 143:
		case 144:
		case 145:
		case 146:
		case 147:
		case 148:
		case 149:
		case 150:
		case 151:
		case 152:
		case 153:
		case 154: // 1542061 is the only wep, 1532061 is missing... NEXON
		case 155: // Fans of the wall, oh wait
		case 160:
		case 170:
			return 'Weapon';

		case 161:
		case 162:
		case 163:
		case 164:
		case 165:
			return 'Mechanic';

		case 168:
			return 'Bits';

		case 180:
		case 181:
			return 'PetEquip';

		case 184:
		case 185:
		case 186:
		case 187:
		case 188:
		case 189:
			return 'MonsterBattle';

		case 190:
		case 191:
		case 192:
		case 193:
		case 198:
		case 199:
			return 'TamingMob';

		case 194:
		case 195:
		case 196:
		case 197:
			return 'Dragon';


		case 166:
		case 167:
			return 'Android';

		case 996: return 'Familiar';
	}
}

function GetItemIconID($id, $locale) {
	$type = GetItemType($id, $locale);
	if ($type != 306) {
		
		$iteminfo = GetItemWZInfo($id, $locale);
		if ($iteminfo['info'] === null)	return $id;
		if ($iteminfo['info']->IsUOL('icon')) {
			$id = $iteminfo['info']['icon']['..']['..']['ITEMID']; // Hell yea. Get the UOL object, then go back to get the Item ID
		}

		return $id;
	}

	$nebtype = ($id / 1000) % 5;
	$main_id = 3800274;

	return $main_id + $nebtype;
}

function GetItemDataLocation($location, $id) {
	$inv = GetItemInventory($id);
	$type = GetItemType($id);

	if ($type == 996) {
		$url = $location.'Character/Familiar/'.str_pad($id, 7, '0', STR_PAD_LEFT).'.img/';
	}
	elseif ($type < 5) {
		switch ($type) {
			case 0:
			case 1:
				$url = $location.'Character/'.str_pad($id, 8, '0', STR_PAD_LEFT).'.img/';
				break;
			case 2:
				$url = $location.'Character/Face/'.str_pad($id, 8, '0', STR_PAD_LEFT).'.img/';
				break;
			case 3:
				$url = $location.'Character/Hair/'.str_pad($id, 8, '0', STR_PAD_LEFT).'.img/';
				break;
		}
	}
	elseif ($inv == 1) {
		$name = GetWZItemTypeName($id);
		$url = $location.'Character/'.$name.'/'.str_pad($id, 8, '0', STR_PAD_LEFT).'.img/';
	}
	else {
		if ($type == 500) {
			$url = $location.'Inventory/Pet/'.$id.'.img/';
		}
		else {
			$typeid = str_pad($type, 4, '0', STR_PAD_LEFT).'.img';
			$typename = '';
			switch (floor($type / 100)) {
				case 2: $typename = 'Consume'; break;
				case 3: $typename = 'Install'; break;
				case 4: $typename = 'Etc'; break;
				case 5: $typename = 'Cash'; break;
			}
			$url = $location.'Inventory/'.$typename.'/'.$typeid.'/'.str_pad($id, 8, '0', STR_PAD_LEFT).'/';
		}
	}
	return $url;
}

function GetItemIcon($id, $locale, $addition = '') {
	global $subdomain;
	$data_domain = '';
	if ($locale == 'ems') $data_domain = 'EMS/';
	//elseif ($locale == 'kms') $data_domain = 'KMS/';
	else $data_domain = '';
	
	$domain = '//static_images.mapler.me/'.$data_domain;
	//$id = GetItemIconID($id);
	return GetItemDataLocation($domain, $id).'info.icon'.$addition.'.png';
}

function ValueOrDefault($what, $default) {
	return isset($what) ? $what : $default;
}

function GetAllianceWorldID($worldid, $locale) {
	if ($locale == 'gms') {
		switch ($worldid) {
			case 6:
			case 7:
			case 8:
			case 14: return 100; // CMYK

			case 9:
			case 10:
			case 11:
			case 12:
			case 13: return 101; // GAZED
				
			case 5:
			case 15: return 102; // Bellonova

			default:
				return $worldid;
		}
	}
	return $worldid;
}

function GetAlliancedWorldName($worldid, $locale) {
	$realid = GetAllianceWorldID($worldid, $locale);
	$db = ConnectCharacterDatabase($locale);
	$q = $db->query('SELECT world_name FROM world_data WHERE world_id = '.intval($realid));
	$row = $q->fetch_row();
	$q->free();
	return $row[0];
}

function SetMaplerCookie($name, $value, $expiresInDays = 35600) {
	global $domain;
	setcookie(
		'mplr'.$name,
		$value,
		time() + ($expiresInDays * 24 * 60 * 60),
		'/',
		'.'.$domain,
		!empty($_SERVER['HTTPS']),
		true // XSS attack thingy, only in PHP 5.2 >
	);
}

function GetMaplerCookie($name) {
	return isset($_COOKIE['mplr'.$name]) ? $_COOKIE['mplr'.$name] : null;
}

function MakePlayerAvatar($name, $locale, $options = array()) {
	global $domain, $subdomain;
	$size = isset($options['size']) ? $options['size'] : 'small';
	$styleappend = isset($options['styleappend']) ? $options['styleappend'] : '';
	$face = !empty($options['face']) ? '&face='.$options['face'] : '';
	$type = isset($options['ign']) && $options['ign'] == true ? 'ignavatar' : 'avatar';
	$flip = isset($options['flip']) && $options['flip'] == true;
	$tamingmob = isset($options['tamingmob']) ? '&tamingmob='.$options['tamingmob'] : '';
	
	$notfound = $name === null || $name == '';
	$image = '//mapler.me/inc/img/no-character.gif';
	$y_offset = '-15px';
	
	if (!$notfound) {
		$y_offset = '-2px';
		$image = '//'.$locale.'.mapler.me/'.$type.'/'.$name.'?size='.$size.$face.($flip ? '&flip' : '').$tamingmob;
	}
	
	if (isset($options['onlyurl'])) {
		echo $image;
		return;
	}
?>
	<div <?php if (!$notfound): ?>onclick="document.location.href = '//<?php echo $locale; ?>.<?php echo $domain; ?>/character/<?php echo $name; ?>'"<?php endif; ?> style="background: url('<?php echo $image; ?>') no-repeat center <?php echo $y_offset; ?>;<?php if (!$notfound): ?> cursor: pointer;<?php endif; ?><?php echo $styleappend; ?>" class="character"></div>
<?php
}


function GetMaplerServerInfo() {
	global $maplerme_servers;
	$result = array();
	
	if (IsCachedObject('server_info', 'all')) return GetCachedObject('server_info', 'all');
	
	foreach ($maplerme_servers as $servername => $data) {
		$socket = @fsockopen($data[0], $data[1], $errno, $errstr, 5);
		$data = array('state' => 'offline', 'locale' => '?', 'version' => '?', 'players' => 0);
		if ($socket) {
			$size = fread($socket, 1);
			for ($i = 0; strlen($size) < 1 && $i < 10; $i++) {
				$size = fread($socket, 1);
			}
			if (strlen($size) == 1) {
				$size = ord($size[0]);
				$data = fread($socket, $size);
				for ($i = 0; strlen($data) < $size && $i < 10; $i++) {
					$data .= fread($socket, $size - strlen($data));
				}
				if (strlen($data) == $size) {
					$data = unpack('vversion/clocale/Vplayers', $data);
					$data['state'] = 'online';
					
					$cutversion = substr($data['version'], 0, -2).'.'.substr($data['version'], -2);
					
					switch ($data['locale']) {
						case 2: $data['locale'] = 'Korea'; $data['version'] = '1.'.$cutversion; break;
						case 7: $data['locale'] = 'SEA'; $data['version'] = $cutversion; break;
						case 8: $data['locale'] = 'Global'; $data['version'] = $cutversion; break;
						case 9: $data['locale'] = 'Europe'; $data['version'] = $cutversion; break;
					}
				}
			}
			fclose($socket);
		}
		$result[$servername] = $data;
	}
	
	SetCachedObject('server_info', $result, 'all', 3);
	return $result;
}



require_once __DIR__.'/functions.loginaccount.php';

function DisplayError($type) {
    if ($type == '1' || $type == 'notloggedin') {
        //Not logged in.
        echo '<p class="lead alert alert-info"><i class="icon-question-sign"></i> You must be logged in to view this page.</p>';
    }
    elseif ($type == '2' || $type == 'nopermission') {
        //Not of a certain rank, or admin only.
        echo '<p class="lead alert alert-danger"><i class="icon-exclamation-sign"></i> You do not have permission to view this page.</p>';
    }
    elseif ($type == '3' || $type == 'alreadyloggedin') {
        //Already logged in error, for pages like login and password reset.
        echo '<p class="lead alert alert-info"><i class="icon-question-sign"></i> You are already logged in. Redirecting shortly...</p>';
    }
    elseif ($type == '4' || $type == 'muted') {
        //Muted
        echo '<p class="lead alert alert-danger"><i class="icon-exclamation-sign"></i> You are currently muted. Posting statuses and sending friend requests disabled.</p>';
    }
    elseif ($type == '5' || $type == 'banned') {
        //Banned (not IP Banned)
        echo '<p class="lead alert alert-danger"><i class="icon-exclamation-sign"></i> You are currently restricted from using Mapler.me. <a href="http://mapler.me/support/">Request support?</a></p>';
    }
    elseif ($type == '6' || $type == 'notpermissionaction') {
        //Banned (not IP Banned)
        echo '<p class="lead alert alert-danger"><i class="icon-exclamation-sign"></i> You do not have permission to complete that action.</p>';
    }
}

// Set to null by default
$__url_useraccount = null;

if ($subdomain != '' && $subdomain != 'www' && $subdomain != 'direct' && $subdomain != 'dev' && $subdomain != 'cdn' && $subdomain != 'status' && $subdomain != 'i' && 
	$subdomain != 'ems' && $subdomain != 'gms' && $subdomain != 'kms' 
	) {
	// Tries to receive userdata for the subdomain. If it fails, results in a 404.

	$__url_useraccount = Account::Load($subdomain);
	if ($__url_useraccount == null) {
		// User Not Found Results In 404
		header('HTTP/1.1 404 File Not Found', 404);
		header('Location: http://'.$domain.'/');
		exit;
	}
}