<?php
class ExtendedMysqli extends mysqli {
	public $last_query = "";
	public $queries = array();
	
	public function query($pQuery) {
		$this->last_query = $pQuery;
		$this->queries[] = $pQuery;
		
		$result = parent::query($pQuery) or die($this->get_debug_info());
		return $result;
	}
	
	public function get_debug_info() {
		$error_msg = <<<NO_END
<h2>Oh noes!</h2>
The server made a boo-boo! Our technical Coolie Zombies are after this problem. For now, <a href="/">please return to the landing page of Mapler.me</a>!
<!--
{ERROR_DATA_HERE}
-->
NO_END;

		$error_msg = str_replace("{ERROR_DATA_HERE}", $this->error." (errno. ".$this->errno.")\r\n".$this->last_query, $error_msg);
		return $error_msg;
	}
	
	public function GetRanQueries() {
?>
<pre>
<?php
foreach ($this->queries as $query) {
	echo $query."\r\n";
}
?>
</pre>
<?php
	}
	
	public function QueriesRan() {
		return count($this->queries);
	}
	
	public static function GetAllRows($query, $how = 'assoc') {
		$rows = array();
		while (true) {
			$row = null;
			if ($how == 'assoc') $row = $query->fetch_assoc();
			elseif ($how == 'row') $row = $query->fetch_row();
			elseif ($how == 'array') $row = $query->fetch_array();
			if ($row == null) break;
			$rows[] = $row;
		}
		return $rows;
	}
}

// Connect to the database
$__database = new ExtendedMysqli((strpos($_SERVER["DOCUMENT_ROOT"], "/var/www/maplestats_svn/") !== FALSE ? "127.0.0.1" : "mc.craftnet.nl"), "maplestats", "maplederp", "maplestats");
if ($__database->connect_errno != 0) {
	die("<strong>Mapler.me is experiencing some inconstancies. Try reloading your page!</strong>");
}

?>