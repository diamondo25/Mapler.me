<?php
require_once __DIR__.'/../../inc/functions.php';
require_once __DIR__.'/../../inc/functions.ajax.php';
require_once __DIR__.'/../../inc/classes/statuses.php';
require_once __DIR__.'/../../inc/job_list.php';

CheckSupportedTypes('info');


if ($request_type == 'info') {
	$res = array();
	$res['time'] = (int)$__server_time;
	
	$_client_time = isset($_POST['client-time']) ? intval($_POST['client-time']) : time() - 10000;

	
	$res['loggedin'] = $_loggedin;
	$res['notifications'] = $_loggedin ? (int)GetNotification() : 0;

	if ($_loggedin) {
		$res['membername'] = $_loginaccount->GetUsername();
		$__database->query("UPDATE accounts SET last_login = NOW(), last_ip = '".$_SERVER['REMOTE_ADDR']."' WHERE id = ".$_loginaccount->GetID());
		
		$statuscount = $__database->query("SELECT COUNT(*) FROM social_statuses WHERE account_id = ".$_loginaccount->GetID());
		$row = $statuscount->fetch_row();
		$res['memberstatuses'] = (int)$row[0];
	}
	
	
	$url = isset($_POST['url']) ? $_POST['url'] : null;
	$parsed_url = $url == null ? null : parse_url($url);
	$is_ok_url = $url != null && strpos($parsed_url['host'], $domain) !== false;
	
	
	// Check server status
	
	$res['server_status'] = GetMaplerServerInfo();
	
	$status_info = array();
	if (isset($_POST['shown-statuses'])) {
		// Check status info
		$correctids = array();
		foreach ($_POST['shown-statuses'] as $oriid) {
			$id = intval($oriid);
			if ($id == 0) {
				continue;
			}
			$correctids[] = $id;
		}
		$correctids = array_unique($correctids);
		if (count($correctids) > 0) {
			$tmp = "
SELECT
	s.id,
	(SELECT COUNT(s2.id) FROM social_statuses s2 WHERE s2.reply_to = s.id) AS `reply_count`
FROM
	social_statuses s
WHERE
	s.id IN (".implode(',', $correctids).")";
			$q = $__database->query($tmp);
			if ($q->num_rows == 0) {
				// All deleted
				foreach ($_POST['shown-statuses'] as $oriid)
					$status_info['deleted'][] = $oriid;
			}
			else {
				while ($row = $q->fetch_row()) {
					$status_info['reply_count'][$row[0]] = (int)$row[1];
					if (($key = array_search($row[0], $correctids)) !== false) {
						unset($correctids[$key]);
					}
				}
				foreach ($correctids as $oriid)
					$status_info['deleted'][] = (int)$oriid;
			}
			$q->free();
		}
		
	}
	$res['status_info'] = $status_info;
	
	
	if ($is_ok_url && isset($_POST['has-statuses']) && $_POST['has-statuses'] != 0) {
		$subdomain = trim(substr($parsed_url['host'], 0, strpos($parsed_url['host'], $domain)), '.');
		$is_maindomain = $subdomain == '' || $subdomain == 'www' || $subdomain == 'ems' || $subdomain == 'gms' || $subdomain == 'kms';
		$is_discover = strpos($url, '/'.$domain.'/discover/') !== false;
		
		// NOTE FOR SELF
		// Get 10 statuses. Then, use the oldest time as 'AND TIME > OLDEST TIME' for timeline. woop
		
		$whereq = '> FROM_UNIXTIME('.$_client_time.')';
		if (isset($_POST['older-than']) && $_client_time > 0) {
			$whereq = '< FROM_UNIXTIME('.$_client_time.')';
		}
		$whereq_1 = '`when` '.$whereq;
		$whereq_2 = '`ss`.`timestamp` '.$whereq;
		
		
		
		// Get statuses
		
		$q = "
SELECT 
	UNIX_TIMESTAMP(`ss`.`timestamp`),
	'status' AS `type`,
	CONVERT(CONCAT(ss.`id`, X'02', 
	ss.`account_id`, X'02', 
	ss.`nickname`, X'02', 
	ss.`character`, X'02', 
	ss.`blog`, X'02', 
	ss.`override`, X'02', 
	IF(ss.`reply_to` IS NULL, '-', `ss`.`reply_to`), X'02',(
	SELECT
		COUNT(s_inner.id)
	FROM
		social_statuses s_inner
	WHERE
		s_inner.reply_to = ss.id
	), X'02',
	ss.`using_face`, X'02'
	
	) USING utf8) AS `col1`,
	CONVERT(`ss`.`content` USING utf8) AS `col2`,
	a.username,
	a.nickname,
	a.account_rank,
	ss.`id`
FROM
	`social_statuses` ss
LEFT JOIN
	accounts a
	ON
		a.id = `ss`.`account_id`
LEFT JOIN
	`social_statuses` ss_reply
	ON
		`ss_reply`.`id` = `ss`.`reply_to`
WHERE
	".$whereq_2;
		if ($_loggedin && $is_maindomain) { // Main screen
			$q .= "
	AND
	(
		".($is_discover ? ' 1 OR' : '')."
		".DB_ACCOUNTS.".`FriendStatus`(`ss`.`account_id`, ".$_loginaccount->GetID().") IN ('FRIENDS', 'FOREVER_ALONE')
		OR
		`ss`.override = 1
	)
	AND
	(
		".($is_discover ? ' 1 OR ' : '')."
		IF(
			`ss_reply`.`id` IS NOT NULL,
			".DB_ACCOUNTS.".`FriendStatus`(`ss_reply`.`account_id`, ".$_loginaccount->GetID().") IN ('FRIENDS', 'FOREVER_ALONE'),
			1
		)
	
	)";
		}
		if (!$is_maindomain) {
			$q .= ' AND ';
			$q .= "a.username = '".$__database->real_escape_string($subdomain)."'";
		}

		$q .= "
ORDER BY
	`ss`.`id` DESC
LIMIT
	15
";
		$q = $__database->query($q);
		
		
		$found_rows = array();
		$oldest_time = 10000000000;
		$res['oldesttimes'] = array();
		while ($row = $q->fetch_row()) {
			$found_rows[] = $row;
			if ($row[0] < $oldest_time)
				$oldest_time = $row[0];
			$res['oldesttimes'][] = (int)$row[0];
		}
		$q->free();
		
		// Use oldest_time for where query
		if (count($found_rows) > 0)
			$whereq_1 .= ' AND `when` > FROM_UNIXTIME('.$oldest_time.')';
		
		
		// Get timeline stuff
		
		$query = "
SELECT 
	UNIX_TIMESTAMP(`when`),
	'timeline_row' AS `type`,
	CONVERT(CONCAT(c.`name`, X'02', `type`) USING utf8) AS `col1`,
	CONVERT(`data` USING utf8) AS `col2`,
	a.username,
	a.nickname,
	a.account_rank,
	`timeline`.`id`
FROM
	`timeline`
LEFT JOIN
	characters c
	ON
		c.internal_id = character_id
LEFT JOIN
	".DB_ACCOUNTS.".accounts a
	ON
		a.id = `account_id`
WHERE
	(`type` = 'levelup' OR `type` = 'jobup')
	AND
	".$whereq_1."
";
		if ($_loggedin && $is_maindomain) { // Main screen
			$query .= "
	AND
	(
		".($is_discover ? ' 1 OR' : '')."
		".DB_ACCOUNTS.".`FriendStatus`(`account_id`, ".$_loginaccount->GetID().") IN ('FRIENDS', 'FOREVER_ALONE')
	)
	";
		}
		if (!$is_maindomain) {
			$query .= ' AND ';
			$query .= "a.username = '".$__database->real_escape_string($subdomain)."'";
		}
	
		$query .= "
ORDER BY
	`timeline`.`id` DESC
LIMIT
	15
";

		foreach ($_supported_locales as $locale) {
			$_db = ConnectCharacterDatabase($locale);
			$q = $_db->query($query);
			
			while ($row = $q->fetch_row()) {
				$row['locale'] = $locale;
				$found_rows[] = $row;
			}
			$q->free();
		}
		
		$stream = array();
		$timestamp = $__server_time;
		$lowest_date = 0;
		foreach ($found_rows as $row) {
			$timestamp = (int)$row[0];
			$type = $row[1];
			$content = explode(chr(0x02), $row[2]);
			$info = $row[3];
			$username = $row[4];
			$nickname = $row[5];
			$account_rank = $row[6];
			$object_id = $row[7];
			$seconds_since = $__server_time - $timestamp;
			$uniqueid = ($timestamp * 1000) + ($object_id % 1000);
			if (count($stream) == 0)
				$lowest_date = $timestamp;
			ob_start();
			if ($type == 'timeline_row') {
				if ($content[1] == 'levelup') {
?>
			<div class="status">
				<p style="margin:0px;"><i class="icon-check-sign"></i>
					<a href="//<?php echo $username; ?>.<?php echo $domain; ?>/">@<?php echo $username; ?></a>'s character 
					<a href="//<?php echo $row['locale']; ?>.<?php echo $domain; ?>/character/<?php echo $content[0]; ?>"><?php echo $content[0]; ?></a> reached Level <span style="font-size: 13px"><?php echo $info; ?>!</span>
					<span status-post-time="<?php echo $timestamp; ?>" style="float:right;"><?php echo time_elapsed_string($seconds_since); ?> ago - Auto</span>
				</p>
			</div>
<?php
				}
				elseif ($content[1] == 'jobup') {
?>
			<div class="status">
				<p style="margin:0px;"><i class="icon-check-sign"></i>
					<a href="//<?php echo $username; ?>.<?php echo $domain; ?>/">@<?php echo $username; ?></a>'s character 
					<a href="//<?php echo $row['locale']; ?>.<?php echo $domain; ?>/character/<?php echo $content[0]; ?>"><?php echo $content[0]; ?></a> advanced to a '<span style="font-size: 13px"><?php echo GetJobname($info); ?>' (<?php echo MakeOrdinalNumberSuffix(GetJobRank($info)); ?> job)!</span>
					<span status-post-time="<?php echo $timestamp; ?>" style="float:right;"><?php echo time_elapsed_string($seconds_since); ?> ago - Auto</span>
				</p>
			</div>
<?php
				}
				// hurr
			}
			elseif ($type == 'status') {
				$status = new Status(array(
					'id' => $content[0],
					'account_id' => $content[1],
					'nickname' => $content[2],
					'character' => $content[3],
					'blog' => $content[4],
					'override' => $content[5],
					'reply_to' => $content[6],
					'reply_count' => $content[7],
					'using_face' => $content[8],
					'content' => $info,
					'timestamp' => $timestamp
				));
				
				// If maindomain and loggedin and status is not from self
				if ($is_maindomain && !$is_discover && $_loggedin && $status->account_id != $_loginaccount->GetID()) {
					// Check if mention is at someone who isn't a friend of $_loginaccount
					if (strpos($info, '@') == 0 && count($status->mention_list) > 0) { // Check if it's really a mention
						// Check if is friend...
						$mentioning = $status->mention_list[0];
						$q_temp = $__database->query("
SELECT
	".DB_ACCOUNTS.".`FriendStatus`(`id`, ".$_loginaccount->GetID().") IN ('FRIENDS', 'FOREVER_ALONE')
FROM
	".DB_ACCOUNTS.".accounts
WHERE
	username = '".$__database->real_escape_string($mentioning)."'");
						if ($q_temp->num_rows > 0) {
							// And? Is it a friend?
							$row = $q_temp->fetch_row();
							$q_temp->free();
							if ($row[0] == 0) {
								// Nope.
								unset($status);
								continue;
							}
						}
						else {
							$q_temp->free();
						}
					}
				}
				
				$status->PrintAsHTML('');
				unset($status);
			}
			$level_info = ob_get_clean();
			$stream[] = array((int)$timestamp, $level_info, $uniqueid);
		}
		$highest_date = $timestamp;

		$res['statuses'] = $stream;
		$res['oldest_status'] = (int)$lowest_date;
		$res['newest_status'] = (int)$highest_date;

	}
	
	JSONAnswer($res);
}
?>