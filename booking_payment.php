<?php
$page_name = "booking_payment";

require_once('defines/environnement_head.php');

$uid = getCurrentUid();
$user = dbFindByKey("User", $uid);

/************* this is the entry of payment. *************/

TODO("change current session statut"); 

/**** functions to implement in module payment: ****/
// get necessary payment parameters
// go into payment procedules

?>

<div class="result_info" style="padding:50px;">

	(in payment procedure)

</div>
( payment result simulation buttons )<br>
<a href="booking_payment_result.php?uid=<?=$uid?>&succes=1" class="button">payment succes</a>
<a href="booking_payment_result.php?uid=<?=$uid?>&succes=0&err_msg=payment_refused_by_xxxx_raison" class="button">payment failed</a>


<?php
include_once('defines/environnement_foot.php');
