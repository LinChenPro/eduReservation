<?php
$page_name = "booking_resume";

require_once('defines/environnement_head.php');
function addLessonToItem(&$arr, $ope){
	$key = $ope->categ_id."_".$ope->tid;
	$item = $arr[$key];	
	if($item==null){
		$item = array();
		$item["categ_id"] = $ope->categ_id;
		$item["categ_name"] = $ope->categ_name;
		$item["tid"] = $ope->tid;
		$item["t_name"] = $ope->t_name;
		$item["prise"] = $ope->tp_prise;
		$item["hours"] = ($ope->end_nb - $ope->begin_nb +1)/2;
		$item["prise_total"] = $ope->tp_prise * ($ope->end_nb - $ope->begin_nb +1)/2;
		$item["lessons"] = array();
		$item["purchases"] = array();
		$item["lessons"][$ope->ope_id] = $ope;
		$arr[$key] = $item;
	}else{
		$arr[$key]["hours"] = $item["hours"] + ($ope->end_nb - $ope->begin_nb +1)/2;
		$arr[$key]["prise_total"] += $ope->tp_prise * ($ope->end_nb - $ope->begin_nb +1)/2;
		$arr[$key]["lessons"][$ope->ope_id] = $ope;
	}
}

function addPurchaseToItem(&$arr, $purchase){
	if($purchase->hour_rest>0){
		$key = $purchase->categ_id."_".$purchase->tid;
		$item = $arr[$key];
		if($item!=null){
			$arr[$key]["purchases"][$purchase->pur_id] = $purchase;
		}
	}
}



$uid = getCurrentUid();
$user = dbFindByKey("User", $uid);

$userBalance = fieldQuery("select sb_amount from student_balance where sb_sid=$uid", "sb_amount", 0);
$userPurchases = getSessionRelativePurchases($sm_demande_session);
$userFinalPaymentAmount = 0;

$userOperations = getOperationsBySessionId($sm_demande_session->session_id);

$userLessonsToDelete = array();
$userLessonsToCreate = array();
$userLessonsToMove = array();

$userBalanceAfterRefund = $userBalance;
$userPurchasesAfterRefund = array();

$userPaymentItems = array();

foreach($userOperations as $operation){
	if($operation->statut == OPE_STATUT_TODELETE){
		array_push($userLessonsToDelete, $operation);
	}else if($operation->statut == OPE_STATUT_TOCREATE){
		array_push($userLessonsToCreate, $operation);
		addLessonToItem($userPaymentItems, $operation);
	}else if($operation->statut == OPE_STATUT_TOMOVE){
		array_push($userLessonsToMove, $operation);
	}
}

foreach ($userPurchases as $purchase) {
	$userPurchasesAfterRefund[$purchase->pur_id] = $purchase->cloneMe();
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



// add purchase to pay items
foreach ($userPurchases as $purchase) {
	$userPurchasesAfterRefund[$purchase->pur_id] = $purchase->cloneMe();
	addPurchaseToItem($userPaymentItems, $purchase);
}

?>

	<br>
	you have selected the new lessons as follow, you may select the payment mode for them:
	<table>
		<tr>
			<td>categ</td><td>t_name</td><td>h_total</td><td>prise/h</td><td>prise total</td><td>pay mode</td><td>rest amount</td>
		</tr>
<?php
	foreach($userPaymentItems as $paymentItem) {
		$userFinalPaymentAmount += $paymentItem["prise_total"];
?>
		<tr>
			<td><?=$paymentItem["categ_name"]?></td>
			<td><?=$paymentItem["t_name"]?></td>
			<td><?=$paymentItem["hours"]?></td>
			<td><?=$paymentItem["prise"]/100?></td>
			<td><?=$paymentItem["prise_total"]/100?></td>
			<td>
<?php
		foreach($paymentItem["purchases"] as $purchase) {
?>
			<input type="checkbox"/> 
				purchase 
				total:<?=$purchase->hour_total?>h,
				rest:<?=$purchase->hour_rest?>h,
				after pay:<?=$purchase->hour_rest?>h
				<br>
<?php
		}
?>
			</td>
			<td><?=$paymentItem["prise_total"]/100?></td>

		</tr>
<?php
	}
?>

	</table>

you need to pay : <?=$userFinalPaymentAmount/100?>  
select payment type: 
<br><input type="radio" name="payment_type"/>wechat 
<br><input type="radio" name="payment_type"/>credit card 

<br>
<a href="student_booking.php?uid=<?=$uid?>&<?=(SESSION_DEMANDE_ID_PARAM."=".$sm_demande_session->session_id)?>" class="button">change my choises</a>

<a href="booking_payment.php?uid=<?=$uid?>&<?=(SESSION_DEMANDE_ID_PARAM."=".$sm_demande_session->session_id)?>" class="button">payment</a>

<?php
include_once('defines/environnement_foot.php');
