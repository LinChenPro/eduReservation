<?php
$page_name = "booking_resume";

require_once('defines/environnement_head.php');

$uid = getCurrentUid();
$user = dbFindByKey("User", $uid);
$session = getExistSession($uid);

$userBalance = fieldQuery("select sb_amount from student_balance where sb_sid=$uid", "sb_amount", 0);
$userPurchases = getSessionRelativePurchases($session);

$userOperations = getOperationsBySessionId($session->session_id);
$userLessonsToDelete = array();
$userLessonsToCreate = array();
$userLessonsToMove = array();

$userBalanceAfterRefund = $userBalance;
$userPurchasesAfterRefund = array();
foreach ($userPurchases as $purchase) {
	$userPurchasesAfterRefund[$purchase->pur_id] = $purchase->clone();
}


foreach($userOperations as $operation){
	if($operation->statut == OPE_STATUT_TODELETE){
		array_push($userLessonsToDelete, $operation);
	}else if($operation->statut == OPE_STATUT_TOCREATE){
		array_push($userLessonsToCreate, $operation);
	}else if($operation->statut == OPE_STATUT_TOMOVE){
		array_push($userLessonsToMove, $operation);
	}
}

// js 
// rembourse abandon

TODO("calcul resume, show resume, show selection of mode and type of payment");


// actuel resume
?>
you have <?=$userBalance/100?> on your compte.
</br>
<?php
if(!empty($userPurchases)){
?>



	you have the follow purchases who has the same teachers and categories in ypur current choise:
	<table>
		<tr>
			<td>date</td><td>teacher</td><td>categ</td><td>total</td><td>consume</td><td>rest</td><td>prise/h</td><td>total prise</td>
		</tr>
<?php
	foreach($userPurchases as $userPurchase) {
?>
		<tr>
			<td><?=$userPurchase->create_time?></td>
			<td><?=$userPurchase->t_name?></td>
			<td><?=$userPurchase->categ_name?></td>
			<td><?=$userPurchase->hour_total?></td>
			<td><?=($userPurchase->hour_total - $userPurchase->hour_rest)?></td>
			<td><?=$userPurchase->hour_rest?></td>
			<td><?=$userPurchase->tp_prise/100?></td>
			<td><?=$userPurchase->tp_prise*$userPurchase->hour_total/100?></td>
		</tr>
<?php
	}
?>

	</table>
	<hr>  
<?php
}




// operation move
if(!empty($userLessonsToMove)){
?>
	you want to move the lessons to new place as follow:
	<table>
		<tr>
			<td>teacher</td><td>categ</td><td>current time</td><td>move to</td>
		</tr>
<?php
	foreach($userLessonsToMove as $operation) {
?>
		<tr>
			<td><?=$operation->t_name?></td>
			<td><?=$operation->categ_name?></td>
			<td><?=dayNbToStr($operation->res_day_nb)?> &nbsp; <?=hNbToCreneax($operation->res_begin_nb, $operation->res_end_nb)?></td>
			<td><?=dayNbToStr($operation->day_nb)?> &nbsp; <?=hNbToCreneax($operation->begin_nb, $operation->end_nb)?></td>
		</tr>
<?php
	}
?>

	</table>
	<hr>  
<?php
}



// operation move
if(!empty($userLessonsToDelete)){
?>
	you want to delete the follow lessons:
	<table>
		<tr>
			<td>teacher</td><td>categ</td><td>current time</td><td>pirse/h</td><td>total pirse</td><td>is purchase</td>
		</tr>
<?php
	foreach($userLessonsToDelete as $operation) {
		$totalPrise_i = getTotalPrise($operation->tp_prise, $operation->res_begin_nb, $operation->res_end_nb);
		if(empty($operation->pur_id)){
			$userBalanceAfterRefund += $totalPrise_i;
		}else{
			$demihour_total = $operation->res_end_nb-$operation->res_begin_nb+1;
			$hour_total = ($demihour_total)/2;
			$purchase = $userPurchasesAfterRefund[$operation->pur_id];
			$purchase->hour_rest += $hour_total;
		}
?>
		<tr>
			<td><?=$operation->t_name?></td>
			<td><?=$operation->categ_name?></td>
			<td><?=dayNbToStr($operation->res_day_nb)?> &nbsp; <?=hNbToCreneax($operation->res_begin_nb, $operation->res_end_nb)?></td>
			<td><?=$operation->tp_prise/100?></td>
			<td><?=$totalPrise_i/100?></td>
			<td><?=(empty($operation->pur_id)?"no":"yes")?></td>


		</tr>
<?php
	}
?>

	</table>
	<br>
	after these delete operations, you will have <?=$userBalanceAfterRefund/100?> on your compte
	<br>
	and the situations of your purchases will be
	<table>
		<tr>
			<td>date</td><td>teacher</td><td>categ</td><td>total</td><td>consume</td><td>rest</td><td>prise/h</td><td>total prise</td>
		</tr>
<?php
	foreach($userPurchasesAfterRefund as $userPurchase) {
?>
		<tr>
			<td><?=$userPurchase->create_time?></td>
			<td><?=$userPurchase->t_name?></td>
			<td><?=$userPurchase->categ_name?></td>
			<td><?=$userPurchase->hour_total?></td>
			<td><?=($userPurchase->hour_total - $userPurchase->hour_rest)?></td>
			<td><?=$userPurchase->hour_rest?></td>
			<td><?=$userPurchase->tp_prise/100?></td>
			<td><?=$userPurchase->tp_prise*$userPurchase->hour_total/100?></td>
		</tr>
<?php
	}
?>

	</table>

	<hr>  

<?php
}
?>




<pre>
you have selected the new lessons as follow, you may select the payment mode for them:
teacher categ n lesson, h hours :  type_purchase(h hours, prise)		compte solde(rest)		payment rest
teacher categ n lesson, h hours :  type_purchase(h hours, prise)		compte solde(rest)		payment rest
teacher categ n lesson, h hours :  type_purchase(h hours, prise)		compte solde(rest)		payment rest
teacher categ n lesson, h hours :  type_purchase(h hours, prise)		compte solde(rest)		payment rest

-----------------------

you need to pay : xxxx  select payment type: wechat/card 

</pre>
<a href="student_booking.php?uid=<?=$uid?>" class="button">change my choises</a>

<a href="booking_payment.php?uid=<?=$uid?>" class="button">payment</a>

<?php
include_once('defines/environnement_foot.php');
