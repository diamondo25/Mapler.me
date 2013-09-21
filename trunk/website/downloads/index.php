<?php
require_once __DIR__.'/../inc/header.php';

if (!$_loggedin):
?>
	<p class="lead alert-warn alert">You need to have an account before you can use the Mapler.me client.</p>
<?php
endif;
?>
	<div class="row">
		<div class="span6">
			<h1>Downloads and More</h1>
			<p>All official Mapler.me downloads including our client!</p>
			<br/>
			<div class="status">
			<h1>Mapler.me Client Installer</h1>
			<p>Simplistic, easy to use, and always there for you, the Mapler.me client is used to update your characters, items, and more.</p>
			<a href="http://cdn.mapler.me/installers/setup_2.0.2.2.exe" class="btn btn-success btn-large download-button">Download the latest client installer!</a>
			<blockquote>
			Requirements:<br />
			 - .NET Framework 4.0 (Client Profile) -> <a href="http://www.microsoft.com/en-us/download/details.aspx?id=24872">Download</a><br />
			 - WinPcap (will be installed with Mapler.me)<br />
			</blockquote>
			</div>
			
			<div class="status">
			<h1>Mapler.me Wordpress Plugin</h1>
			<p>Easily include your characters and their avatars in your posts, pages, and blog!</p>
			<a href="http://wordpress.org/plugins/maplerme/" class="btn btn-success btn-large download-button">View plugin on Wordpress.org!</a>
			<blockquote>You may also download the plugin through your Plugin Manager by searching for "Mapler.me"!</blockquote>
			</div>
		</div>
<?php
if ($_loggedin):
?>
		<div class="span4 offset2">
			<h1>Hello there!</h1>
			<p>If you have any questions or need assistance with our client, please let us know by filling a support ticket. (You will get a response in less then 48 hours)</p>
		</div>
<?php
endif;

require_once __DIR__.'/../inc/footer.php';
?>