<?php
$page_name = "booking_payment_result";

require_once('defines/environnement_head.php');

$uid = getCurrentUid();
$user = dbFindByKey("User", $uid);

// get payment result (simu) 
TODO("implement real params in payment module");
$payment_succes = $_REQUEST["succes"];
$payment_err_msg = $_REQUEST["err_msg"];

TODO("change session status to payment succes or payment failled");

if($payment_succes==1){
	TODO("change operations to reservation, add history");
}else{
	TODO("cancel the new lesson operations");
}


if($payment_succes){
?>
<div class="result_info">
	your payment is accepted.
</div>
<?php	
}else{
?>
<div class="result_info">
	your payment is refused. <?=$payment_err_msg?> 
	<br><br>
	all the new lessons will not be added
	<br><br>
	do you want to do the cancel and move operation(the will not be done by default)?
	<br><br>
	<input type="checkbox" radio id="ck_cancel" name="ck_cancel"></input><label for="ck_cancel">do cancel</label>
	&nbsp; &nbsp; &nbsp; &nbsp; 
	<input type="checkbox" id="ck_move" name="ck_move"></input><label for="ck_move">do move</label>
</div>
<?php	
}
?>
<a id="a_confirm" href="booking_confirm.php?uid=<?=$uid?>&succes=<?=$payment_succes?>" class="button">confirm</a>

<script src="/js/jquery-3.2.1.min.js"></script>
<script type="text/javascript">
$("#a_confirm").click(function(){
	if(0==<?=$payment_succes?>){
		var do_cancel = $("#ck_cancel").is(':checked');
		var do_move = $("#ck_move").is(':checked');

		var href = this.href;
		href += "&do_cancel="+(do_cancel?1:0) + "&do_move="+(do_move?1:0);
		this.href = href;
	}

	return true;
});

</script>
<?php
include_once('defines/environnement_foot.php');
