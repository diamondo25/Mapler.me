<?php

require_once __DIR__.'/../inc/functions.php';

$success = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm'])) {
	$query = $__database->query("SELECT password, salt FROM accounts WHERE id = ".$_loginaccount->GetId());
	if ($query->num_rows == 1) {
		$row = $query->fetch_assoc();
		
		$encrypted = GetPasswordHash($_POST['confirm'], $row['salt']);
		if ($encrypted === $row['password']) {
			$success = 2;
		}
		else {
			$success = 1;
		}
	}

	if ($success == 2) {
		$wastheirid = $_loginaccount->GetId();
        $username = $_loginaccount->GetUsername();
        $ip = $_loginaccount->GetLastIP();
        
		$statement = $__database->prepare('INSERT INTO account_deletion_log (id, username, ip, at) VALUES
			(?,?,?,NOW())');
			
		$statement->bind_param('sss', $wastheirid, $username, $ip);
		$statement->execute();

		$finish = $__database->query("DELETE FROM accounts WHERE id = ".$_loginaccount->GetId());
		$finish->free();

		unset($_SESSION['username']);
		session_destroy();
		SetMaplerCookie('login_session', '', -100);
			
		require_once __DIR__.'/../inc/header.php';
		if ($statement->affected_rows == 1) {
		
?>
		<p class="lead alert alert-info"><i class="icon-exlamation-sign"></i> Your account has been removed.</p>
		<ol>
			<li>Your account itself, and all related information has been removed successfully.</li>
			<li>Al characters has been removed successfully.</li>
			<li>All status messages have been removed successfully.</li>
			<li>All other matters handled successfully.</li>
		</ol>
		<p>If you have any feedback for our team, send us an email at support@mapler.me! :)</p>
		<p><b>We wish you a great day, and happy mapling!</b></p>
<?php
	    }
	    else {
?>
        <p class="lead alert alert-info"><i class="icon-exlamation-sign"></i> Something went wrong while deleting your account. Please contact support.</p>
<?php
	    }
	}
	elseif ($success == 1) {
		require_once __DIR__.'/../inc/header.php';
?>
	<p class="lead alert alert-danger"><i class="icon-exlamation-sign"></i> You typed an incorrect password. Please try again.</p>
	<div class="row">
		<div class="span12">
			<form method="post">
				<input type="password" name="confirm" placeholder="Password"/>
				<input type="submit" class="btn btn-danger" value="Delete my account"/>
			</form>
		</div>
	</div>
<?php
	}
}
else {
	require_once __DIR__.'/../inc/header.php';
?>

<div class="row">
	<div class="span12">
<?php if($_loggedin): ?>
		<p class="lead alert alert-info"><i class="icon-question-sign"></i> We're sorry to see you go! To protect your account please type your password below:</p>
		<form method="post">
			<input type="password" name="confirm" placeholder="Password"/>
			<input type="submit" class="btn btn-danger" value="Delete my account"/>
		</form>
<?php
else:
	DisplayError(1);
endif;
?>
	</div>
</div>
<?php
}
require_once __DIR__.'/../inc/footer.php';
?>