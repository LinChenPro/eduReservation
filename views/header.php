<?php
//include_once('outside_includes/page_header.php');

?>
<html>
	<head>
		<title><?=getCrtPageTitle()?></title>
<?php
$css_arr = getCrtPageCss();
if(!empty($css_arr)){
	foreach ($css_arr as $css_link) {
?>
		<link rel="stylesheet" type="text/css" href="<?=$css_link?><?=("?".date('Y-m-d_H:i:s'))?>" />
<?php
	}
}
?>


	</head>
	<body>

	<div style="position:absolute;right: 50px;z-index: 200;">
		<?=showAllPageLinks()?>
	</div>
