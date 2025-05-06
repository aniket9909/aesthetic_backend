<?php 
$MERCHANT_KEY = $data['key'];
$firstname =$data['firstname'];
$email =$data['email'];
$phone =$data['phone'];
$amount = $data['amount'];
$txnid = $data['txnid'];
$service_provider =$data['service_provider'];
$productinfo =$data['productinfo'];
$surl =$data['surl'];
$furl =$data['furl'];
$udf2 =$data['udf2'];
$baseurl=$data['baseurl'];
$hash=$data['hash'];
?>


<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.6/css/bootstrap.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.6/js/bootstrap.min.js"></script>
    
<title>Payment Processing</title>
<style>
@import url(https://fonts.googleapis.com/css?family=Roboto:300,400);

/** SPINNER CREATION **/

.loader {
  position: relative;
  text-align: center;
  margin: 15px auto 35px auto;
  z-index: 9999;
  display: block;
  width: 80px;
  height: 80px;
  border: 10px solid rgba(0, 0, 0, .3);
  border-radius: 50%;
  border-top-color: #000;
  animation: spin 1s ease-in-out infinite;
  -webkit-animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
  to {
    -webkit-transform: rotate(360deg);
  }
}

@-webkit-keyframes spin {
  to {
    -webkit-transform: rotate(360deg);
  }
}


/** MODAL STYLING **/

.modal-content {
  border-radius: 0px;
  box-shadow: 0 0 20px 8px rgba(0, 0, 0, 0.7);
}

.modal-backdrop.show {
  opacity: 0.75;
}

.loader-txt p {
    font-size: 13px;
    color: #666;
}
            </style>
	<script>
    function submitForm() {
      var postForm = document.forms.postForm;
      postForm.submit();
    }
            $(document).ready(function() {
            $("#loadMe").modal({
                  backdrop: "static", //remove ability to close modal with click
                  keyboard: false, //remove option to close with keyboard
                  show: true //Display loader!
                });
                       })
</script>
</head>
<body onload="submitForm()">
	<form name="postForm" action="<?= $baseurl ?>" method="POST" >
		<input type="hidden" name="key" value="<?php echo $MERCHANT_KEY; ?>" />
		<input type="hidden" name="hash" value="<?php echo $hash;  ?>"/>
                <input type="hidden" name="txnid" value="<?php echo $txnid;  ?>" />
		<input type="hidden" name="amount" value="<?php echo $amount;  ?>" />
		<input type="hidden" name="firstname" value="<?php echo $firstname;  ?>" />
		<input type="hidden" name="email" value="<?php echo $email;  ?>" />
		<input type="hidden" name="phone" value="<?php echo $phone;  ?>" />
    <input type="hidden" name="udf2" value="<?php echo $udf2;  ?>" />
		<input type="hidden" name="productinfo" value="<?php echo $productinfo;  ?>" />
		<input type="hidden" name="service_provider" value="payu_paisa" size="64" />
		<input type="hidden" name="surl" value="<?php echo $surl;  ?>" />
		<input type="hidden" name="furl" value="<?php echo $furl;  ?>" />
	</form>
            <!-- Modal -->
            <div class="modal fade" id="loadMe" tabindex="-1" role="dialog" aria-labelledby="loadMeLabel">
              <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                  <div class="modal-body text-center">
                    <div class="loader"></div>
                    <div clas="loader-txt">
            <p>Please be patient. this process might take some time,<br />Do not hit refresh or back button or close this window</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
</body>
</html>
