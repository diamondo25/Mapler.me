<?php
require_once __DIR__.'/../database.php';
require_once __DIR__.'/account.php';
require_once __DIR__.'/../functions.php'; // For a lot of functions


class Statusses {
	public $data;
	
	public function FeedData($query) {
		$this->data = array();
		while ($row = $query->fetch_assoc()) {
			$this->data[] = new Status($row);
		}
	}
	
	public function Count() {
		return count($this->data);
	}
}

class Status {
	public $id, $account, $nickname, $character, $content, $blog, $timestamp, $override, $mention_list, $reply_to;
	
	public function __construct($row) {
		$this->id = (int)$row['id'];
		$this->account_id = (int)$row['account_id'];
		$this->account = Account::Load($this->account_id);
		$this->nickname = $row['nickname'];
		$this->character = $row['character'];
		$this->content = $row['content'];
		$this->blog = (int)$row['blog'];
		$this->timestamp = $row['timestamp'];
		$this->override = (int)$row['override'];
		$this->seconds_since = (int)$row['secs_since'];
		$this->reply_to = (int)$row['reply_to'];
		
		$this->ParseContent();
	}
	
	public static function GetReplyInfo($id) {
		global $__database;
		
		$q = $__database->query("
SELECT
	id,
	nickname,
	timestamp,
	TIMESTAMPDIFF(SECOND, timestamp, NOW()) AS `secs_since`
FROM
	social_statuses
WHERE
	id = ".$id);
		if ($q->num_rows == 0) {
			$q->free();
			return NULL;
		}
		$row = $q->fetch_assoc();
		$q->free();
		return $row;
	}
	
	public function GetReplyToCount() {
		global $__database;
		
		$q = $__database->query("
SELECT
	COUNT(*)
FROM
	social_statuses
WHERE
	reply_to = ".$this->id);
		$row = $q->fetch_row();
		$q->free();
		return $row[0];
	}
	
	public function ParseContent() {
		global $domain;
		
		preg_match_all('/@([a-z0-9_]+)/i', $this->content, $matches);
		// $matches[1] Contains a list of found mentions
		// Remove dupes
		$matches[1] = array_unique($matches[1]);
		
		$this->mention_list = array_values($matches[1]); // Push all values to mention_list
		
		$this->content = preg_replace('/(http|https|ftp|mailto)\:\/\/([^\<\s\t]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $this->content);
		
		//@replies
		$this->content = preg_replace('/(^| )@([a-z0-9_]+)/i', '$1<a href="http://$2.'.$domain.'/">@$2</a>', $this->content);
		//#hashtags (no search for the moment)
		// $this->content = preg_replace('/(^| )#([a-z0-9_]+)/i', '$1<a href="//'.$domain.'/search/tag/$2">#$2</a>', $this->content);
		//^images (workaround for the moment)
		$this->content = preg_replace('/(^| )!([a-z0-9_]+)/i', '$1<a href="http://cdn.mapler.me/media/$2"><img src="http://cdn.mapler.me/media/$2" class="status-picture" onerror="this.src=\'http://mapler.me/inc/img/no-character.gif\'" /></a>', $this->content);
	}
	
	public function PrintAsHTML($style_addition = '') {
		global $parser, $_loggedin, $domain, $_loginaccount;
		$parser->parse($this->content);
		
		$username = $this->account->GetUsername();
		$own_post = $_loggedin && ($this->account_id == $_loginaccount->GetID() || $_loginaccount->IsRankOrHigher(RANK_MODERATOR));
		
		$reply_info = $this->reply_to == NULL ? NULL : $this->GetReplyInfo($this->reply_to);
		
		$object_id = GetUniqueID();

	$main_char = $this->character;
	if ($main_char == null)
		$main_char = 'inc/img/no-character.gif';
	else
		$main_char = 'avatar/'.$main_char;
?>

<script type="text/javascript">
	$("#status-more-<?php echo $this->id;?>").popover();
	$('#status-more-<?php echo $this->id;?>').popover(
    {
      placement: 'right',
      offset: 15,
      trigger: 'hover',
      delay: { show: 350, hide: 100 },
      html: true,
    }
  );
    var timer,
        popover_parent;
    function hidePopover(elem) {
        $(elem).popover('hide');
    }
    $('#status-more-<?php echo $this->id;?>').hover(
        function() {
          var self = this;
          clearTimeout(timer);
          $('.popover').hide(); //Hide any open popovers on other elements.
          popover_parent = self
          $(self).popover('show');            
        }, 
        function() {
          var self = this;
          timer = setTimeout(function(){hidePopover(self)},300);                 
    });
    $('.popover').live({
      mouseover: function() {
        clearTimeout(timer);
      },
      mouseleave: function() {
        var self = this;
        timer = setTimeout(function(){hidePopover(popover_parent)},300); 
      }
    });
});
	
</script>
			<div class="status<?php echo ($this->override == 1) ? ' notification' : ''; ?><?php echo $style_addition; ?>" status-id="<?php echo $this->id; ?>" unique-id="<?php echo $object_id; ?>">
				<div class="header" style="background: url('http://mapler.me/<?php echo $main_char; ?>') no-repeat center -17px #FFF;"><a href="#" id="status-more-<?php echo $this->id;?>" rel="popover" data-content="<i class='icon-share'></i> <a href='//<?php echo $username; ?>.mapler.me/'>Profile</a>" data-original-title="
					<?php echo $this->nickname;?> (@<?php echo $username; ?>)" data-trigger="hover">
					<img src="#" style="opacity:0;width:50px;height:50px;"/>
				</div></a>
				<br />
				<div class="status-contents">
				<?php echo $parser->getAsHtml(); ?>
				</div>
				<div class="status-extra" style="clear:both;">
<?php if ($reply_info != NULL): ?>
					<a href="//mapler.me/stream/status/<?php echo $reply_info['id']; ?>" style="float: left;">In reply to <?php echo $reply_info['nickname']; ?></a>
<?php endif; ?>
<?php if ($this->account_id !== 2): ?>
					<a href="#" class="mention" status-id="<?php echo $this->id; ?>" poster="<?php echo $username; ?>" mentions="<?php echo implode(';', $this->mention_list); ?>"><i class="icon-share-alt"></i> (<?php echo $this->GetReplyToCount(); ?>)</a>
<?php endif; ?>
					<a href="//<?php echo $domain; ?>/stream/status/<?php echo $this->id; ?>"><?php echo time_elapsed_string($this->seconds_since); ?> ago</a>
<?php
	if ($_loggedin) {
		if ($own_post) {
?>
						- <a href="#" onclick="RemoveStatus(<?php echo $this->id; ?>);">delete?</a>
<?php
		}
		elseif (false) {
			// Report button
?>
						- <a href="#"></a>
<?php
		}
	}
?>
				</div>
			</div>
			<div class="reply-list span6" status-id="<?php echo $this->id; ?>" unique-id="<?php echo $object_id; ?>"></div>
<?php    
	}
}
?>