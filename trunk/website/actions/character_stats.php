<?php
include_once('../inc/database.php');
include_once('../inc/functions.php');
include_once('job_list.php');
include_once('caching.php');

$font = "arial.ttf";
$font_size = "9.25";

if (!isset($_GET['debug']))
	header('Content-Type: image/png');

$charname = isset($_GET['name']) ? $_GET['name'] : 'ixdiamondoz';

$len = strlen($charname);
if ($len < 4 || $len > 12) {
	$im = imagecreatetruecolor (192, 345);
	$bgc = imagecolorallocate ($im, 255, 255, 255);
	$tc = imagecolorallocate ($im, 0, 0, 0);

	imagefilledrectangle ($im, 0, 0, 192, 345, $bgc);

	/* Output an error message */
	imagestring ($im, 1, 5, 5, 'I AM ERROR', $tc);
	imagestring ($im, 1, 5, 20, "Incorrect Charname", $tc);
	imagepng($im);
	imagedestroy($im);
	die();
}

if (!isset($_GET['NO_CACHING']))
	ShowCachedImage($charname, 'stats');
$id = uniqid().rand(0, 9);
AddCacheImage($charname, 'stats', $id);

$q = $__database->query("SELECT * FROM characters WHERE name = '".$__database->real_escape_string($charname)."'");
if ($q->num_rows == 0) {
	$im = imagecreatetruecolor (192, 345);
	$bgc = imagecolorallocate ($im, 255, 255, 255);
	$tc = imagecolorallocate ($im, 0, 0, 0);

	imagefilledrectangle ($im, 0, 0, 192, 345, $bgc);

	/* Output an error message */
	imagestring ($im, 1, 5, 5, 'I AM ERROR', $tc);
	imagestring ($im, 1, 5, 20, "No data found", $tc);
	imagepng($im);
	imagedestroy($im);
	die();
}


$row = $q->fetch_assoc();

$row['guildname'] = '-';
$q2 = $__database->query("SELECT guild FROM character_views WHERE name = '".$__database->real_escape_string($charname)."'");
if ($q2->num_rows == 1) {
	// Try to fetch guildname
	$row2 = $q2->fetch_assoc();
	if ($row2['guild'] != null) {
		$row['guildname'] = $row2['guild'];
	}
}
$q2->free();


$stat_addition = GetCorrectStat($row['internal_id']);


$image = imagecreatetruecolor(192, 345);
imagealphablending($image, false);
imagesavealpha($image, true);

$bg_image = imagecreatefrompng("../inc/img/stat_window.png");
imagecopyresampled($image, $bg_image, 0, 0, 0, 0, 192, 345, 192, 345);
imagealphablending($image, true);

$base_x = 74;
$base_y = 38;
$step = 18;
$i = 0;
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['name']);
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, GetJobname($row['job']));
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['level']);
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['exp']);
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['honourlevel']);
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['honourexp']);
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['guildname']); // Guild
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['chp']." / ".($row["mhp"] + $stat_addition['mhp'])); // Seems strange, but MS updates MP/SP after logging in. -.-'
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['cmp']." / ".($row["mmp"] + $stat_addition['mmp']));
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['fame']);
$base_y += 23;
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['ap']);
$base_y += 10;
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, MakeStatAddition('str', $row['str'], $stat_addition));
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, MakeStatAddition('dex', $row['dex'], $stat_addition));
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, MakeStatAddition('int', $row['int'], $stat_addition));
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, MakeStatAddition('luk', $row['luk'], $stat_addition));


imagepng($image);


SaveCacheImage($charname, 'stats', $image, $id);

imagedestroy($image);
$q->free();
?>