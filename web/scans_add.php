<?php
require_once('common.php');
?>
<?php
//	check login first
if (!already_login()) {
	die();
}

$type = check_sql(trim($_REQUEST['type']));
// echo $type . '<br>';
//	start a scan task
if ($type == 'start') {

	$url = check_sql(trim($_REQUEST['url']));
	// echo $url . '<br>';
	$startTime = $_SERVER['REQUEST_TIME'] ;	//自 PHP 5.1 起在 $_SERVER['REQUEST_TIME'] 中保存了发起该请求时刻的时间戳。
	// echo $startTime . '<br>';
	$args = check_sql(trim($_REQUEST['args']));
	// echo $args . '<br>';
	$userid = get_userid();
	// echo $userid . '<br>';

	$query = "INSERT INTO Scan(Url,Start_Time,Arguments,User_ID) VALUES('$url',$startTime,'$args',$userid)";
	// echo $query . '<br>';
	$result = mysql_query($query);
	if ($row = mysql_fetch_array($result)) {
		// echo $row;
	}
	$query = "SELECT ID FROM Scan WHERE Start_Time='$startTime' AND Url='$url' AND User_ID='$userid' limit 1";
	// echo $query . '<br>';
	$result = mysql_query($query);
	if ($row = mysql_fetch_array($result)) {
		$scanID = (int)$row[0];
		// echo $scanID . '<br>';
	}

	// return start time
	$ret = array('id'=>$scanID,'startTime' => $startTime);
	echo json_encode($ret);
}
//	end a scan task
elseif ($type == 'end') {
	$ipurl = check_sql(trim($_REQUEST['ipurl']));
	// echo $ipurl . '<br>';
	$scanid = (int)($_REQUEST['id']);
	// echo $ipurl . '<br>';
	$userid = get_userid();
	// echo $userid . '<br>';
	$retinfo = json_decode(trim($_REQUEST['retinfo']),true);
	// var_dump($retinfo);
	
	$level=array('info' => 1,'low' => 2,'medium' => 3,'high' => '4');
	// $scanLevel = 'info';
	// $scanLevelInt=(int)$level[$scanLevel];
	// $scanLevelInt = 0;
	$endTime = $_SERVER['REQUEST_TIME'];

	//	check userid and scanid
	$query = "SELECT Level FROM Scan WHERE  ID='$scanid' AND User_ID='$userid'";
	echo $query . '<br>';
	$result = mysql_query($query);
	if ($row = mysql_fetch_array($result)) {
		$scanLevelInt = (int)$row[0];
	}
	else{
		die();
	}

	foreach ($retinfo as $key => $eachVuln) {
		// print_r($eachVuln);
		$vulnType = check_sql($eachVuln['type']);
		$vulnLevel = check_sql($eachVuln['level']);
		if (array_key_exists($vulnLevel,$level)) {
			$vulnLevelInt = (int)$level[$vulnLevel];
		}
		else $vulnLevelInt = 0;
		$scanLevelInt = $scanLevelInt>$vulnLevelInt?$scanLevelInt:$vulnLevelInt;
		// echo '$scanLevelInt='.$scanLevelInt.'<br>';
		$vulnContent = $eachVuln['content'];
		
		if(is_array($vulnContent)){
			$vulnContent = json_encode($vulnContent);
		}
		$vulnContent = check_sql($vulnContent);

		//	get plugin id
		$query = "SELECT ID FROM Plugin WHERE Name='$vulnType' limit 1";
		echo $query . '<br>';
		$result = mysql_query($query);
		if ($row = mysql_fetch_array($result)) {
			$pluginID = $row[0];
			echo $pluginID . '<br>';
		}

		//
		$query = "INSERT INTO Vuln(Scan_ID,IP_URL,Plugin_ID,Vuln_Info,Level) VALUES('$scanid','$ipurl','$pluginID','$vulnContent','$vulnLevelInt')";
		echo $query . '<br>';
		$result = mysql_query($query);
		if ($row = mysql_fetch_array($result)) {
			// echo $row;
		}
	}

	//	update Scan
	$query = "UPDATE Scan SET End_Time=$endTime, Level='$scanLevelInt' WHERE ID=$scanid";
	echo $query . '<br>';
	$result = mysql_query($query);
	if ($row = mysql_fetch_array($result)) {
		// echo $row;
	}
}

?>