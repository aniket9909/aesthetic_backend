<?php 
// if(!isset($_POST['firstname'])){header("location: index.php");}
// Change the Merchant key here as provided by Payumoney
$MERCHANT_KEY = "gRMRwlJD";

// Change the Merchant Salt as provided by Payumoney
$SALT = "MVF8Zroh4k";
header('Access-Control-Allow-Origin: *'); 

error_log($_GET['data']); /* To see data on console */

$data = array();

// $firstname =$_GET['firstname'];
// $email =$_GET['email'];
// $phone =$_GET['phone'];
// $amount =$_GET['amount'];


$exportdata = explode('~', $_GET['data']); /* Split into an array, Implode is an opposite function */
$firstname =$exportdata[0];
$email =$exportdata[1];
$phone =$exportdata[2];
$amount = $exportdata[3];
$txnid = $exportdata[4];
$service_provider ='payu_paisa';
// $txnid = time().rand(1000,99999);
$productinfo ="Order Veera da dhaba";
$surl ='https://store.veeradadhaba.in/payumoney/success.php';
$furl ='https://store.veeradadhaba.in/payumoney/fail.php';
	// $firstname =$_POST['firstname'];
	// $email =$_POST['email'];
	// $phone =$_POST['phone'];
	// $productinfo =$_POST['productinfo'];
	// $service_provider =$_POST['service_provider'];
	// $amount =$_POST['amount'];
	// $txnid =$_POST['txnid'];
	// $productinfo =$_POST['productinfo'];
	// $surl =$_POST['surl'];
	// $furl =$_POST['furl'];
	

	//$ =$_POST[''];

	$hashseq=$MERCHANT_KEY.'|'.$txnid.'|'.$amount.'|'.$productinfo.'|'.$firstname.'|'.$email.'|||||||||||'.$SALT;
	$hash =strtolower(hash("sha512", $hashseq)); 
	

?>


<!DOCTYPE html>
<html>
<head>

<title>Payment Processing</title>
	<script>
    function submitForm() {
      var postForm = document.forms.postForm;
     // postForm.submit();
    }
</script>
</head>
<body onload="submitForm();">

<div>
<p>Please be patient. this process might take some time,<br />please do not hit refresh or back button or close this window</p>
</div>

<div>
	<form name="postForm" action="https://secure.payu.in/_payment" method="POST" >
		<input type="text" name="key" value="<?php echo $MERCHANT_KEY; ?>" />
		<input type="text" name="hash" value="<?php echo $hash;  ?>"/>
    	<input type="text" name="service_provider" value="payu_paisa" />
		<input type="text" name="txnid" value="<?php echo $txnid;  ?>" />
		<input type="text" name="amount" value="<?php echo $amount;  ?>" />
		<input type="text" name="firstname" value="<?php echo $firstname;  ?>" />
		<input type="text" name="email" value="<?php echo $email;  ?>" />
		<input type="text" name="phone" value="<?php echo $phone;  ?>" />
		<input type="text" name="productinfo" value="<?php echo $productinfo;  ?>" />
		<input type="text" name="service_provider" value="payu_paisa" size="64" />
		<input type="text" name="surl" value="<?php echo $surl;  ?>" />
		<input type="text" name="furl" value="<?php echo $furl;  ?>" />
	</form>
</div>
</body>
</html>