<?php
function concat($firstSep, $sep, ...$values){
	$str = "";
	foreach ($values as $value) {
		if(!empty($value) || $value===0){
			if($str == ""){
				$str .= $firstSep.$value;
			}else{
				$str .= $sep.$value;
			}
		}
	}
	return $str;
}

function echoln($s=""){
	echo $s;
	echo "<br>\n";
}


function TODO($command){
//	echoln($command);
}