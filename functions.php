<?php

function get_data_array($sql_text, $params, $work = false){

	$hierarchy_1lvl = array();
	$hierarchy_2lvl = array();

	If($work == true){
		
		defined('WorkBARS') or define('WorkBARS', '(DESCRIPTION=(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 192.168.110.125)(PORT = 1521)))(CONNECT_DATA=(SID=MSCH123)))');
		
		$conn = oci_connect('dev', ',deve1ope8', WorkBARS, 'AL32UTF8');
	
	}else{
		
		defined('TestBARS') or define('TestBARS', '(DESCRIPTION=(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 192.168.101.251)(PORT = 1521)))(CONNECT_DATA=(SID=MSCH123)))');
		
		$conn = oci_connect('dev', 'def', TestBARS, 'AL32UTF8');
	
	}

	$stmt = oci_parse($conn, $sql_text);

	if(!count($params) == 0){

		foreach($params as $key => $value){
			oci_bind_by_name($stmt, ":" . $key, $params[$key]);
		}

	}

	oci_execute($stmt);

	while(($row = oci_fetch_assoc($stmt)) != false){

		foreach ($row as $key => $value){
			$hierarchy_2lvl[$key] = $value;
		}
		$hierarchy_1lvl[] = $hierarchy_2lvl;
	}

	oci_free_statement($stmt);

	oci_close($conn);

	return $hierarchy_1lvl;	

}

function send_http_request($url, $post){

	$ch = curl_init($url);

	curl_setopt($ch, CURLOPT_USERPWD, 'Пехтерев А. К.:2468513790');	
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$body = curl_exec($ch);

	$info = curl_getinfo($ch);

	curl_close($ch);

	return $resp = ['body' => $body, 'info' => $info];

}

?>