<?php
require_once __DIR__.'/functions.php';

if (!IsLoggedin() || !isset($_GET['page'], $_GET['type'])) {
	header('Location: http://'.$domain.'/');
}

$page = '../settings/'.($_GET['type'] == '' ? '' : stripslashes($_GET['type']).'/').stripslashes($_GET['page']).'.php';
if (!file_exists($page)) {
	header('Location: http://'.$domain.'/');
	die();
}

require_once __DIR__.'/header.template.php';
if ($_GET['type'] == '') {
	require_once __DIR__.'/additional.menu.php';
}
?>
		<div class="row">
			<div class="span12">
<?php require_once $page; ?>
			</div>
		</div>
<?php 
require_once __DIR__.'/footer.php';
?>