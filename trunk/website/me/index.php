<?php
include_once('../inc/header.php');

?>

	<div class="row">
	<div class="span2">
	      <img src="//placehold.it/96&text=mapler.me" class="img-polaroid">
	</div>
	<div class="span10">
	<p class="lead"><?php echo $__url_userdata['full_name']; ?> <span class="muted">(<?php echo $__url_userdata['nickname']; ?>)</span></p>
	<?php echo $__url_userdata['bio']; ?>
	</div>
	</div>

<?php include_once('../inc/footer.php'); ?>