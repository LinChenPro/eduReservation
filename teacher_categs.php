<?php
$page_name = "teacher_categs";

require_once('defines/environnement_head.php');

$uid = getCurrentUid();


$user = dbFindByKey("User", $uid);
$teacherCategs = TeacherCateg::getTeacherCategs($uid);
?>


<div> teacher : <?=$user->user_name?></div>

<form action="teacher_categs_update.php?uid=<?=$uid?>" method="post">

<table>
	<tr>
		<td></td>
		<td>categ</td>
		<td>current prise</td>
	</tr>
<?php 
foreach ($teacherCategs as $tc) { 
	$categ_id = $tc->categ->categ_id;
	$checked = $tc->in_cell ? "checked" : "";
	$prise = $tc->tp_prise == null ? "" : $tc->tp_prise/100;
?>

	<tr>
		<td><input type="checkbox" name="categ_id[]" value="<?=$categ_id?>" <?=$checked?>></td>
		<td><?=$tc->categ->categ_name?></td>
		<td><input name="categ_prise_<?=$categ_id?>" value="<?=$prise?>" size="6">Y/h</td>
	</tr>
<?php 
}
?>
</table>
<input type="submit" value="update">
</form>

<?php
include_once('defines/environnement_foot.php');

