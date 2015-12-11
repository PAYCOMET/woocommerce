<html>
<head>
<meta charset="UTF-8">
<?php


add_action('wp_print_styles', 'payment_test_styles', 100);
do_action('wp_head');


function payment_test_styles() {
    global $wp_styles;
    $wp_styles->queue = array();
    wp_register_style( '2100.css', PAYTPV_PLUGIN_URL . 'css/2100.css', PAYTPV_VERSION );
	wp_enqueue_style( '2100.css');

	wp_register_script( 'paytpv_test_mode.js', PAYTPV_PLUGIN_URL . 'js/paytpv_test_mode.js', array(),  PAYTPV_VERSION );
	wp_enqueue_script( 'paytpv_test_mode.js' );	
}



$key = (isset($_GET['key']))?$_GET['key']:"";
$order = (isset($_GET['order']))?$_GET['order']:"";
$wc_api = (isset($_GET['wc-api']))?$_GET['wc-api']:"";
$OPERATION = (isset($_GET['OPERATION']))?$_GET['OPERATION']:"";
$MERCHANT_ORDER = (isset($_GET['MERCHANT_ORDER']))?$_GET['MERCHANT_ORDER']:"";
$MERCHANT_AMOUNT = (isset($_GET['MERCHANT_AMOUNT']))?$_GET['MERCHANT_AMOUNT']:"";
$MERCHANT_MERCHANTSIGNATURE = (isset($_GET['MERCHANT_MERCHANTSIGNATURE']))?$_GET['MERCHANT_MERCHANTSIGNATURE']:"";
$MERCHANT_CURRENCY = (isset($_GET['MERCHANT_CURRENCY']))?$_GET['MERCHANT_CURRENCY']:"";
$URLKO = (isset($_GET['URLKO']))?$_GET['URLKO']:"";

?>
</head>

<body style="width:450px;">
<form name="formulario" id="formulario"  action="" method="post" onSubmit="return CheckForm();">
<div id="form_pago" style="background-color:#fff;width:400px !important;">
	<div>
		<span style="font-family: arial;font-size: 12px;color: #333;"><?php print __('Name on credit card', 'wc_paytpv' )?>:</span>
	</div>
	<div>
		<input type="text" style="border: 1px solid #ccc; border-radius: 4px; padding: 5px;color: #333; font-size: 12px; margin: 0px 6px 10px 0;" maxlength="50" id="nombre" name="nombre" value="" onClick="this.value='';" />
	</div>
	<div>
		<span style="font-family: arial;font-size: 12px;color: #333;"><?php print __('Credit card number', 'wc_paytpv' )?>:</span>
	</div>
	<div>
		<input type="text" style="border: 1px solid #ccc; border-radius: 4px; padding: 5px;color: #333; font-size: 12px; margin: 0px 6px 10px 0;" maxlength="16" id="merchan_pan" name="merchan_pan" value="" onClick="this.value='';" />
	</div>
	<div>
		<span style="font-family: arial;font-size: 12px;color: #333;"><?php print __('Expiration date', 'wc_paytpv' )?>:</span>
	</div>

	<div>
		<div style="display:inline">
			<select id="mm" name="mm" style="border: 1px solid #ccc;border-radius: 4px;padding: 5px;color: #333;font-size: 12px;margin: 0px 6px 10px 0; ">
			<option value="01">01</option>
			<option value="02">02</option>
			<option value="03">03</option>
			<option value="04">04</option>
			<option value="05">05</option>
			<option value="06">06</option>
			<option value="07">07</option>
			<option value="08">08</option>
			<option value="09">09</option>
			<option value="10">10</option>
			<option value="11">11</option>
			<option value="12">12</option>
			</select>        
		</div>
		<div style="display:inline">
			<select name="yy" id="yy" style="border: 1px solid #ccc;border-radius: 4px;padding: 5px;color: #333;font-size: 12px;margin: 0px 6px 10px 0; ">
			<option value=15>2015</option><option value=16>2016</option><option value=17>2017</option><option value=18>2018</option><option value=19>2019</option><option value=20>2020</option><option value=21>2021</option><option value=22>2022</option><option value=23>2023</option>
			</select>        
		</div>
	</div>
  
	<div><span style="font-family: arial;font-size: 12px;color: #333;"><?php print __('CVC2', 'wc_paytpv' )?>:</span></div>
  	<div style="width:14%;"><input type="text" style="width:90% !important;border: 1px solid #ccc; border-radius: 4px; padding: 5px;color: #333; font-size: 12px; margin: 0px 6px 10px 0;" maxlength="4" id="merchan_cvc2" name="merchan_cvc2" value="" onClick="this.value='';" /></div>
	
	<div style="width:14%;">
		<img src="<?php print PAYTPV_PLUGIN_URL?>images/cvv2.png" alt="CVC2" width="34" height="21" title="CVC2" style="margin-left:5px; float:left;" />
	</div>
  	<div style="width:70%;">
  		<span style="font-family: arial;font-size: 12px;color: #333;">(<?php print __('The last 3 digits', 'wc_paytpv' )?>)</span>
  	</div>
	
  	<br>
  
	<input type="submit" style="background-color: #0099e6;border: 1px solid #0099e6;color: #fff;font-weight: bold;border-radius: 3px;padding: 5px 10px;" value="Aceptar pago" class="boton" id="btnforg" />

	<div><img src="<?php print PAYTPV_PLUGIN_URL?>images/clockpayblue.gif" alt="<?php print __('Wait', 'wc_paytpv' )?>" width="41" height="30" id="clockwait" style="display:none; margin-top:5px;" /></div>
	
	<input type="hidden" name="TransactionType" value="<?php print $OPERATION;?>">
	<input type="hidden" name="Order" value="<?php print $MERCHANT_ORDER;?>">
	<input type="hidden" name="Amount" value="<?php print $MERCHANT_AMOUNT;?>">
	<input type="hidden" name="Response" value="OK">
	<input type="hidden" name="ExtendedSignature" value="<?php print $MERCHANT_MERCHANTSIGNATURE;?>">
	<input type="hidden" name="IdUser" value="0">
	<input type="hidden" name="TokenUser" value="TESTTOKEN">
	<input type="hidden" name="Currency" value="<?php print $MERCHANT_CURRENCY;?>">
	<input type="hidden" name="AuthCode" value="Test_mode">
	<input type="hidden" name="BankDateTime" value="">


	<input type="hidden" id="key" value="<?php print $key;?>">
	<input type="hidden" id="wc_api" value="<?php print $wc_api;?>">
	<input type="hidden" id="order" value="<?php print $order;?>">
	<input type="hidden" id="Operation" value="<?php print $OPERATION;?>">

</div>



<div id="resp_error" style="display:none;width:100%;" marginwidth="0" marginheight="0">
	<div style="margin-top:15px !important;background-color:#fff;width:400px !important;">
	<div style="float:left;width:98%;margin-top:13px;"><span style="font-family: arial;font-size: 12px;color: #333;"><?php print __('It not has been able to successfully perform the operation for the following reason', 'wc_paytpv' )?>:</span></div>
	<div style="float:left;width:98%;margin-top:13px;"><b><span style="font-family: arial;font-size: 12px;color: #333;"><?php print __('Error', 'wc_paytpv' )?><br><span class="bigger"><?php print __('Unexpected error. Check the data on the card and try again', 'wc_paytpv' )?>.</span></span></b></div>
	</div>
	<div style="margin-top:20px !important;background-color:#fff;width:400px !important;">
	<div style="float:left;width:30%;"><input type="button" value="Volver" style="background-color: #0099e6;border: 1px solid #0099e6;color: #fff;font-weight: bold;border-radius: 3px;padding: 5px 10px;" onclick="document.location=document.location"></div>
	 <div style="float:left;width:30%;"><input type="button" value="Finalizar" style="background-color: #0099e6;border: 1px solid #0099e6;color: #fff;font-weight: bold;border-radius: 3px;padding: 5px 10px;" onclick="window.location.href='<?php print $URLKO;?>'"></div>
</div>
</form>

</html>