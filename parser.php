<?php
set_time_limit(500);
function parsing_dir($dir, $srch){
	$array = array_values(preg_grep('/^([^.])/', scandir($dir)));
	foreach($array as $value){
		if(is_dir($dir . "\\" . $value)){
			parsing_dir($dir . "\\" . $value, $srch);
		}
		else{
			if(stripos($value, '.conf') !== false or stripos($value, '.agi') !== false or stripos($value, '.php') !== false or stripos($value, '.sh') !== false){
				$file = file($dir . "\\" . $value);
				foreach($file as $string){
					if(strpos($string, $srch) !== false)
						echo($dir . "\\" . $value . " (" . $string . ")</br>");
				}
			
			}
		}
	}
}
parsing_dir($_GET['postvar1'], $_GET['postvar2']);	
?>