<?php 
require_once '../inc/header.php';
$statusid = htmlentities($_GET['id']);
?>

<script type="text/javascript">
function RemoveStatus(id) {
	if (confirm("Are you sure you want to delete this status?")) {
		document.location.href = '?removeid=' + id;
	}
}
</script>
<?php

// Preventing spamming of form.

// If antispam passes, push status
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['removeid'])) {
	// Removing status
	
	$id = $__database->real_escape_string($_GET['removeid']);
	
	$__database->query("DELETE FROM social_statuses WHERE id = '".$id."' AND account_id = ".$_loginaccount->GetId());
?>
<p class="lead alert-info alert">The status was successfully deleted.</p>
<?php
}

?>
    <?php
    
$q = $__database->query("
SELECT
	*,
	TIMESTAMPDIFF(SECOND, timestamp, NOW()) AS `secs_since`
FROM
	social_statuses
WHERE
	id = '".$statusid."'
	
ORDER BY
secs_since ASC
");
	
$fixugh = '0';
	
$cache = array();
while ($row = $q->fetch_assoc()) {
	if (isset($fixugh)) {
		if ($fixugh == 2) { // Always hide... :)
			continue;
		}
	}
	$cache[] = $row;
}

$q->free();

function time_elapsed_string($etime) {
   if ($etime < 1) {
       return '0 seconds';
   }
   
   $a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
               30 * 24 * 60 * 60       =>  'month',
               24 * 60 * 60            =>  'day',
               60 * 60                 =>  'hour',
               60                      =>  'minute',
               1                       =>  'second'
               );
   
   foreach ($a as $secs => $str) {
       $d = $etime / $secs;
       if ($d >= 1) {
           $r = round($d);
           return $r . ' ' . $str . ($r > 1 ? 's' : '');
       }
   }
}

?>
	<div class="row">
	<div class="span12">

<?php
if (count($cache) == 0) {
echo '<p class="lead alert-info alert">404! Status not found. (The status was deleted or removed)</p>';
}
?>
	
	<?php

// printing table rows

foreach ($cache as $row) {

?>
			<div class="status">
			<div class="header">
			<?php
			echo $row['nickname'];
			$playerid = $row['account_id'];
			$bb = $row['content'];
			?> said: <span class="pull-right">
				<a href="//<?php echo $domain; ?>/stream/status/<?php echo $statusid; ?>"><?php echo time_elapsed_string($row['secs_since']); ?> ago</a> 
				
				<?php
				if ($_loggedin) {
				if ($playerid == $_loginaccount->GetId()) { ?>
					- <a href="#" onclick="RemoveStatus('<?php echo $statusid; ?>')">
					delete?
				</a>
				<?php } 
					
				else {
					echo '<a href="#"></a>'; //will be report button
				}
				}
				?>
				
				
			</span></div>
				<br/><img src="http://mapler.me/avatar/<?php echo $row['character']; ?>" class="pull-right"/>
					<?php echo bb_parse($bb); ?>
			</div>
        
<?php       
}
?>
	</div>
	</div>
<?php
require_once '../inc/footer.php';
?>