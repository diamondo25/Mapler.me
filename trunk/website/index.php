<?php
require_once __DIR__.'/inc/header.php';
	
$s = $__database->query("
SELECT
	(SELECT COUNT(*) FROM accounts),
	(SELECT COUNT(*) FROM characters),
	(SELECT COUNT(*) FROM items)
");
$tmp = $s->fetch_row(); 
$s->free();

if ($_loggedin) {
?>
<meta http-equiv="refresh" content="0;URL='/stream/'" />
<?php
	die;
}
?>
<style>
.woah {
	font-size:30px;
}
</style>
<div class="row">
	<div class="span12">
		<center>
		<p class="lead woah">Start the next chapter of your Maple 'Story'!</p>
		<a href="//<?php echo $domain; ?>/login/" class="btn btn-success btn-large">Login or register!</a>
		</center>
		<br/>
	</div>
</div>
<div class="row">
	<div class="span8 hidemobile">
		<p class="lead">Mapler.me is a brand new social network dedicated to MapleStory that allows you to stay connected with your buddies, keep track of your characters' growth, and much more.</p>
		<p class="lead">Ever feel disconnected from your friends, guild, or alliance whenever you're not on MapleStory? With Mapler.me, you can stay connected with <strong>over <?php echo $tmp[0]; ?> maplers</strong> already apart of Mapler.me.</p>
		<p class="lead">Have a question? Check out our <a href="//<?php echo $domain; ?>/faq/">FAQ</a>.</p>
		<p>
			Stay up to date on the latest news by following us on <a href="http://twitter.com/maplerme">Twitter</a>, or like us on <a href="http://facebook.com/MaplerMe">Facebook</a>!<br />
			<div class="fb-like" data-href="http://facebook.com/MaplerMe" data-send="false" data-layout="button_count" data-width="450" data-show-faces="true"></div> <a href="https://twitter.com/maplerme" class="twitter-follow-button" data-show-count="false">Follow @maplerme</a>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
		</p>
	</div>
	<div class="span4 hidemobile">
		<div class="lead status">
<?php
	$q = $__database->query("
SELECT
	name
FROM
	characters 
WHERE
	level > 30
	AND
	NOT job BETWEEN 800 AND 1000
ORDER BY
	rand()
	LIMIT 1
");
	$row = $q->fetch_row();
	
	MakePlayerAvatar($row[0], array('face' => 'smile', 'size' => 'small'));
	$q->free();
?>
			Mapler.me currently handles <strong><?php echo $tmp[1]; ?> characters</strong> with over <strong><?php echo $tmp[2]; ?> items</strong> in total!
		<div>
	</div>

</div>