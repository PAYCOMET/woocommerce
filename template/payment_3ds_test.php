<html>
<head>
<meta charset="UTF-8">
<?php

add_action('wp_print_styles', 'payment_3ds_styles', 100);
do_action('wp_head');


function payment_3ds_styles() {
    global $wp_styles;
    $wp_styles->queue = array();
    wp_register_style( '2100.css', PAYTPV_PLUGIN_URL . 'css/2100.css', PAYTPV_VERSION );
	wp_enqueue_style( '2100.css');

	wp_register_script( 'paytpv_3ds_test.js', PAYTPV_PLUGIN_URL . 'js/paytpv_3ds_test.js', array(),  PAYTPV_VERSION );
	wp_enqueue_script( 'paytpv_3ds_test.js' );	
}


$key = (isset($_GET['key']))?$_GET['key']:"";
$order = (isset($_GET['order']))?$_GET['order']:"";
$wc_api = (isset($_GET['wc-api']))?$_GET['wc-api']:"";
$OPERATION = (isset($_GET['OPERATION']))?$_GET['OPERATION']:"";
$MERCHANT_ORDER = (isset($_GET['MERCHANT_ORDER']))?$_GET['MERCHANT_ORDER']:"";
$MERCHANT_AMOUNT = (isset($_GET['MERCHANT_AMOUNT']))?$_GET['MERCHANT_AMOUNT']:"";
$MERCHANT_MERCHANTSIGNATURE = (isset($_GET['MERCHANT_MERCHANTSIGNATURE']))?$_GET['MERCHANT_MERCHANTSIGNATURE']:"";
$MERCHANT_CURRENCY = (isset($_GET['MERCHANT_CURRENCY']))?$_GET['MERCHANT_CURRENCY']:"";
$MERCHAN_PAN = (isset($_GET['MERCHAN_PAN']))?$_GET['MERCHAN_PAN']:"";

$IDUSER = (isset($_GET['IDUSER']))?$_GET['IDUSER']:0;
$TOKEN_USER = (isset($_GET['TOKEN_USER']))?$_GET['TOKEN_USER']:"TESTTOKEN";

$URLKO = (isset($_GET['URLKO']))?$_GET['URLKO']:"";

?>
</head>
<body>
	<table width="370" cellspacing="0" cellpadding="0">
		<tbody>
			<tr>
				<td align="center" colspan="2">
					<h3><?php print __('Authentication Secure Electronic Commerce', 'wc_paytpv' )?></h3>
				</td>
			</tr>
			<tr>
				<td width="185px" align="center">
					<img border="0" alt="VISA" src="<?php print PAYTPV_PLUGIN_URL?>images/VerifiedByVisa.jpg"></td>
				<td width="185px" align="center"></td>
			</tr>
			<tr>
				<td colspan="2">
					<hr></td>
			</tr>
		</tbody>
	</table>
	<div id="capaPantalla1" style="position: absolute; visibility: visible; left: 5px;">
		<div style="position:relative;margin:0px auto;display:block;top: -10px;">
			<h4 class="uno"><?php print __('Check the details of your operation', 'wc_paytpv' )?></h4>
		</div>
		<form onsubmit="javascript:checkform();" id="formulario" name="formulario" method="formulario">
		<table width="374" cellspacing="0" cellpadding="0" border="0" style="BORDER-COLLAPSE: collapse">
			<tbody>
				<tr>
					<td style="BORDER-TOP: 0px ; BORDER-LEFT-WIDTH: 0px; BORDER-BOTTOM: 0px ; BORDER-RIGHT-WIDTH: 0px"> <font class="titulotabla">Importe:</font>
					</td>
					<td style="BORDER-TOP: 0px ; BORDER-LEFT-WIDTH: 0px; BORDER-BOTTOM: 0px ; BORDER-RIGHT-WIDTH: 0px"> <font class="detalletabla"><?php print ($MERCHANT_AMOUNT/100);?> €</font>
					</td>
				</tr>
				<tr>
					<td style="BORDER-TOP: 0px ; BORDER-LEFT-WIDTH: 0px; BORDER-BOTTOM: 0px ; BORDER-RIGHT-WIDTH: 0px">
						<font class="titulotabla"><?php print __('Commerce', 'wc_paytpv' )?>:</font>
					</td>
					<td style="BORDER-TOP: 0px ; BORDER-LEFT-WIDTH: 0px; BORDER-BOTTOM: 0px ; BORDER-RIGHT-WIDTH: 0px">
						<font class="detalletabla"><?php wp_title( '|', true, 'right' );?></font>
					</td>
				</tr>
				<tr>
					<td style="BORDER-TOP: 0px ; BORDER-LEFT-WIDTH: 0px; BORDER-BOTTOM: 0px ; BORDER-RIGHT-WIDTH: 0px">
						<font class="titulotabla"><?php print __('Date', 'wc_paytpv' )?>:</font>
					</td>
					<td style="BORDER-TOP: 0px ; BORDER-LEFT-WIDTH: 0px; BORDER-BOTTOM: 0px ; BORDER-RIGHT-WIDTH: 0px">
						<font class="detalletabla"><?php print date("d/m/Y")?></font>
					</td>
				</tr>
				<tr>
					<td style="BORDER-TOP: 0px ; BORDER-LEFT-WIDTH: 0px; BORDER-BOTTOM: 0px ; BORDER-RIGHT-WIDTH: 0px">
						<font class="titulotabla"><?php print __('Hour', 'wc_paytpv' )?>:</font>
					</td>
					<td style="BORDER-TOP: 0px ; BORDER-LEFT-WIDTH: 0px; BORDER-BOTTOM: 0px ; BORDER-RIGHT-WIDTH: 0px">
						<font class="detalletabla"><?php print date("H:i:s")?></font>
					</td>
				</tr>
				
				<tr>
					<td width="362" height="1" colspan="2">
						<div style="margin:0px auto;padding:0px auto;">
							<hr></div>
						<div style="margin:0px auto;display:block;top: -10px;">
							<h4 class="dos">
								<?php print __('Enter the PIN of 4 digits of your credit / debit card', 'wc_paytpv' )?>:
							</h4>
						</div>
					</td>
				</tr>
				<tr>
					<td width="363" align="center" class="letra" colspan="2">
						<table align="center">
							<tbody>
								<tr>
									<td width="40%" align="right">
										<font style="font-family: Arial;font-size: 11px;font-weight:bold;color:#858585;">PIN:</font>
										&nbsp;
									</td>
									<td width="60%" align="left">
										<input type="text" size="6" maxlength="6" id="demopin" name="pin" class="formulario">
										&nbsp;
										<img border="0" align="middle" alt="CaixaProtect" src="<?php print PAYTPV_PLUGIN_URL?>images/2100candau256.png"></td>
								</tr>
								<tr>
									<td width="100%" align="center" colspan="2">
										<div id="showerror" class="error_text" style="display:none"><?php print __('The PIN entered is incorrect', 'wc_paytpv' )?></div>
										<br></td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>

				<tr>
					<td width="362" height="7" style="BORDER-TOP: 0px ; BORDER-LEFT-WIDTH: 0px; BORDER-BOTTOM: 0px ; BORDER-RIGHT-WIDTH: 0px" colspan="2"></td>
				</tr>
				<tr>
					<td width="362" align="right" height="30" style="valign:center;BORDER-TOP: 0px ; BORDER-LEFT-WIDTH: 0px; BORDER-BOTTOM: 0px ; BORDER-RIGHT-WIDTH: 0px" colspan="2">
						<div style="align: right;">
							<a class="boton aceptar" href="#" onclick="checkform();">
								<span><?php print __('Confirm purchase', 'wc_paytpv' )?></span>
							</a>
							<a class="boton cancelar" href="#" onclick="window.location.href='<?php print $URLKO?>'">
								<span><?php print __('Cancel', 'wc_paytpv' )?></span>
							</a>
						</div>
					</td>
				</tr>

				<input type="hidden" name="TransactionType" value="<?php print $OPERATION;?>">
				<input type="hidden" name="Order" value="<?php print $MERCHANT_ORDER;?>">
				<input type="hidden" name="Amount" value="<?php print $MERCHANT_AMOUNT;?>">
				<input type="hidden" name="Response" value="OK">
				<input type="hidden" name="ExtendedSignature" value="<?php print $MERCHANT_MERCHANTSIGNATURE;?>">
				<input type="hidden" name="IdUser" value="<?php print $IDUSER;?>">
				<input type="hidden" name="TokenUser" value="<?php print $TOKEN_USER;?>">
				<input type="hidden" name="Currency" value="<?php print $MERCHANT_CURRENCY;?>">
				<input type="hidden" name="merchan_pan" value="<?php print $MERCHAN_PAN;?>">
				<input type="hidden" name="AuthCode" value="Test_mode">
				<input type="hidden" name="BankDateTime" value="">

				<input type="hidden" id="key" value="<?php print $key;?>">
				<input type="hidden" id="wc_api" value="<?php print $wc_api;?>">
				<input type="hidden" id="order" value="<?php print $order;?>">

			</tbody>
		</table>
		</form>
	</div>
</body>

</html>