<?php
$page_name = "booking_resume";

require_once('defines/environnement_head.php');

$uid = getCurrentUid();
$user = dbFindByKey("User", $uid);

TODO("calcul resume, show resume, show selection of mode and type of payment");

?>

<pre>
(simu)
you have xxx on your compte.
you have the follow purchases:
date teacher categ prise total consume rest

---------------------

after the cancel of the follow lessons:
date teacher categ n lessons, h hours, rembourse to purchase 
date teacher categ n lessons, h hours, rembourse to your compte

you will have xxx on your compte.
you have the follow purchases:
date teacher categ prise total consume rest

----------------------

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
