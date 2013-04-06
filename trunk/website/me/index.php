<?php
require_once __DIR__.'/../inc/header.php';
require_once __DIR__.'/../inc/me.header.template.php';


$q = $__database->query("
SELECT
	*,
	TIMESTAMPDIFF(SECOND, timestamp, NOW()) AS `secs_since`
FROM
	social_statuses
LEFT JOIN
	accounts
	ON
		social_statuses.account_id = accounts.id
WHERE
	account_id = '".$__database->real_escape_string($__url_useraccount->GetID())."'
ORDER BY
	secs_since ASC
");

$social_cache = array();
while ($row = $q->fetch_assoc()) {
	$social_cache[] = $row;
}

$q->free();



if (count($social_cache) == 0) {
?>
	<center>
		<img src="//<?php echo $domain; ?>/inc/img/no-character.gif"/>
		<p><?php echo $__url_useraccount->GetNickName(); ?> hasn't posted anything yet!</p>
	</center>
<?php
}
?>
<div class="span9">
<?php
// printing table rows

foreach ($social_cache as $row) {
$content = $row['content'];
//@replies
$content1 = preg_replace('/(^|[^a-z0-9_])@([a-z0-9_]+)/i', '$1<a href="http://$2.mapler.me/">@$2</a>', $content);
//#hashtags (no search for the moment)
$content2 = preg_replace('/(^|[^a-z0-9_])#([a-z0-9_]+)/i', '$1<a href="#">#$2</a>', $content1);
?>
			<div class="status <?php if ($row['override'] == 1): ?> notification<?php endif; ?>">
				<div class="header" style="background: url('http://mapler.me/avatar/<?php echo $row['character']; ?>') no-repeat right -30px #FFF;">
					<?php echo $row['nickname'];?> said:
				</div>
				<br />
				<?php $parser->parse($content2); echo $parser->getAsHtml(); ?>
				<div class="status-extra">
					<?php if ($row['account_id'] !== 2): ?><a href="#" class="mention-<?php echo $row['id']; ?>" mentioned="<?php echo $row['username']; ?>"><i class="icon-share-alt"></i></a>
					<script type="text/javascript">
							$('.mention-<?php echo $row['id']; ?>').click(function() {
								var value = $(".mention-<?php echo $row['id']; ?>").attr('mentioned');
								var input = $('#post-status');
								input.val(input.val() + '@' + value + ' ');
								$(".poster").addClass("in");
								$('.poster').css("height","auto");
								return false;
							});
					</script>
					<?php endif; ?>
					<a href="//<?php echo $domain; ?>/stream/status/<?php echo $row['id']; ?>"><?php echo time_elapsed_string($row['secs_since']); ?> ago</a>

<?php
	if ($_loggedin) {
		if (IsOwnAccount()) {
?>
						- <a href="#" onclick="RemoveStatus(<?php echo $row['id']; ?>);">delete?</a>
<?php
		}
		else {
			// Report button
?>
						- <a href="#"></a>
<?php
		}
	}
?>
				</div>
			</div>
			
<?php
}
?>
</div>
</div>
<?php require_once __DIR__.'/../inc/footer.php'; ?>