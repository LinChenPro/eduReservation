<?php
$page_name = "booking_payment_result";

require_once('defines/environnement_head.php');

$uid = getCurrentUid();
$user = dbFindByKey("User", $uid);


if($sm_demande_session->session_statut==SESSION_STATUT_PAY_SUCCES){
?>
	<div class="result_info">
		your payment is accepted.
	</div>
	<a id="a_confirm" href="booking_confirm.php?uid=<?=$uid?>&<?=(SESSION_DEMANDE_ID_PARAM+"="+$sm_demande_session->session_id)?>" class="button">confirm</a>
<?php	
}else{
?>
	<div class="result_info">
		your payment is refused. <?=$_REQUEST["err_msg"]?> 
		<br><br>
		all the new lessons will be abandon.
		<br><br>
		do you want to apply the delete and move operation(the will be abandon too by default)?
		<br><br>
		<input type="checkbox" radio id="ck_delete" name="ck_delete"></input><label for="ck_delete">apply delete</label>
		&nbsp; &nbsp; &nbsp; &nbsp; 
		<input type="checkbox" id="ck_move" name="ck_move"></input><label for="ck_move">apply move</label>
	</div>
	<a id="a_confirm" href="booking_confirm.php?uid=<?=$uid?>&<?=(SESSION_DEMANDE_ID_PARAM+"="+$sm_demande_session->session_id)?>" class="button">confirm</a>
	<a href="booking_resume.php?uid=<?=$uid?>&<?=(SESSION_DEMANDE_ID_PARAM+"="+$sm_demande_session->session_id)?>" class="button">retry</a>
<?php	
}
?>

<script src="/js/jquery-3.2.1.min.js"></script>
<script type="text/javascript">
$("#a_confirm").click(function(){
	if(<?=($sm_demande_session->session_statut==SESSION_STATUT_PAY_SUCCES?"false":"true")?>){
		var do_delete = $("#ck_delete").is(':checked');
		var do_move = $("#ck_move").is(':checked');

		var href = this.href;
		href += "&do_delete="+(do_delete?1:0) + "&do_move="+(do_move?1:0);
		this.href = href;
	}

	return true;
});

</script>
<?php
include_once('defines/environnement_foot.php');
