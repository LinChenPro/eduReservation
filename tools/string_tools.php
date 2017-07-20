<?php
function concat($firstSep, $sep, ...$values){
	$str = "";
	foreach ($values as $value) {
		if(!empty($value)){
			if($str == ""){
				$str .= $firstSep.$value;
			}else{
				$str .= $sep.$value;
			}
		}
	}
	return $str;
}
