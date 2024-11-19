<?php
/**
 * Plugin Name: PAYCOMET Woocommerce
 * Plugin URI: https://wordpress.org/plugins/paytpv-for-woocommerce/
 * Description: The PAYCOMET payment gateway for WooCommerce
 * Author: PAYCOMET
 * Author URI: https://www.paycomet.com
 * Version: 5.41
 * Tested up to: 6.7
 * WC tested up to: 9.3.3
 * Text Domain: wc_paytpv
 * Domain Path: /languages
 */


define( 'PAYTPV_VERSION', '5.41' );

define( 'PAYTPV_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PAYTPV_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

define( 'PAYTPV_PLUGIN', __FILE__ );
define( 'PAYTPV_PLUGIN_BASENAME', plugin_basename( PAYTPV_PLUGIN ) );


require_once PAYTPV_PLUGIN_DIR . 'paytpv.php';
require_once PAYTPV_PLUGIN_DIR . 'inc/dependencies.php';
require_once PAYTPV_PLUGIN_DIR . 'inc/upgrade.php';
require_once PAYTPV_PLUGIN_DIR . 'inc/thankyou.php';
require_once PAYTPV_PLUGIN_DIR . 'inc/PaytpvApi.php';
require_once PAYTPV_PLUGIN_DIR . 'inc/PaycometApiRest.php';

add_action( 'plugins_loaded', 'woocommerce_paytpv_init', 100 );
add_action( 'admin_enqueue_scripts', array( 'woocommerce_paytpv', 'load_resources_conf' ) );
add_action( 'wp_enqueue_scripts', array( 'woocommerce_paytpv', 'load_resources' ) );

add_action( 'woocommerce_before_my_account', array( 'woocommerce_paytpv', 'get_my_cards_template' ) );

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );
add_action( 'woocommerce_before_checkout_form', 'custom_display_checkout_error_message', 10 );


function woocommerce_paytpv_init() {

	/**
	 * Required functions
	 */
	if ( !class_exists( 'WC_Payment_Gateway' ) || !WC_PayTpv_Dependencies::woocommerce_active_check() )
		return;

	load_plugin_textdomain( 'wc_paytpv', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	add_filter( 'woocommerce_payment_gateways', 'add_paytpv_gateway' );

	register_activation_hook( __FILE__, 'paytpv_install' );

	require PAYTPV_PLUGIN_DIR . '/inc/woocommerce-paytpv.php';
	require PAYTPV_PLUGIN_DIR . '/inc/paycomet-apm.php';
	require PAYTPV_PLUGIN_DIR . '/inc/paycomet-bizum.php';
	require PAYTPV_PLUGIN_DIR . '/inc/paycomet-ideal.php';
	require PAYTPV_PLUGIN_DIR . '/inc/paycomet-klarna.php';
	require PAYTPV_PLUGIN_DIR . '/inc/paycomet-klarnapayments.php';
	require PAYTPV_PLUGIN_DIR . '/inc/paycomet-giropay.php';
	require PAYTPV_PLUGIN_DIR . '/inc/paycomet-mybank.php';
	require PAYTPV_PLUGIN_DIR . '/inc/paycomet-multibanco.php';
	require PAYTPV_PLUGIN_DIR . '/inc/paycomet-mbway.php';
	require PAYTPV_PLUGIN_DIR . '/inc/paycomet-trustly.php';
	require PAYTPV_PLUGIN_DIR . '/inc/paycomet-przelewy.php';
	require PAYTPV_PLUGIN_DIR . '/inc/paycomet-bancontact.php';
	require PAYTPV_PLUGIN_DIR . '/inc/paycomet-eps.php';
	//require PAYTPV_PLUGIN_DIR . '/inc/paycomet-tele2.php';
	require PAYTPV_PLUGIN_DIR . '/inc/paycomet-paypal.php';
	require PAYTPV_PLUGIN_DIR . '/inc/paycomet-paysera.php';
	require PAYTPV_PLUGIN_DIR . '/inc/paycomet-postfinance.php';
	require PAYTPV_PLUGIN_DIR . '/inc/paycomet-qiwi.php';
	//require PAYTPV_PLUGIN_DIR . '/inc/paycomet-yandex.php';
	//require PAYTPV_PLUGIN_DIR . '/inc/paycomet-mts.php';
	//require PAYTPV_PLUGIN_DIR . '/inc/paycomet-beeline.php';
	require PAYTPV_PLUGIN_DIR . '/inc/paycomet-paysafecard.php';
	require PAYTPV_PLUGIN_DIR . '/inc/paycomet-skrill.php';
	//require PAYTPV_PLUGIN_DIR . '/inc/paycomet-webmoney.php';
	require PAYTPV_PLUGIN_DIR . '/inc/paycomet-waylet.php';
	require PAYTPV_PLUGIN_DIR . '/inc/paycomet-instantcredit.php';
}



/**
 * Add the gateway to woocommerce
 * */
function add_paytpv_gateway( $methods ) {
	$methods[] = 'woocommerce_paytpv';
	
	// APMs
	$methods[] = 'Paycomet_Bizum';
	$methods[] = 'Paycomet_Klarna';
	$methods[] = 'Paycomet_Klarnapayments';
	$methods[] = 'Paycomet_Ideal';
	$methods[] = 'Paycomet_Giropay';
	$methods[] = 'Paycomet_Mybank';
	$methods[] = 'Paycomet_Multibanco';
	$methods[] = 'Paycomet_Mbway';
	$methods[] = 'Paycomet_Trustly';
	$methods[] = 'Paycomet_Przelewy';
	$methods[] = 'Paycomet_Bancontact';
	$methods[] = 'Paycomet_Eps';
	$methods[] = 'Paycomet_Tele2';
	$methods[] = 'Paycomet_Paypal';
	$methods[] = 'Paycomet_Paysera';
	$methods[] = 'Paycomet_Postfinance';
	$methods[] = 'Paycomet_Qiwi';
	$methods[] = 'Paycomet_Yandex';
	$methods[] = 'Paycomet_Mts';
	$methods[] = 'Paycomet_Beeline';
	$methods[] = 'Paycomet_Paysafecard';
	$methods[] = 'Paycomet_Skrill';
	$methods[] = 'Paycomet_Webmoney';
	$methods[] = 'Paycomet_Waylet';
	$methods[] = 'Paycomet_Instantcredit';

	return $methods;
}

add_action( 'admin_init', 'wppaytpv_upgrade' );

function getUserPaymentMethods($userTerminal, $apiKey)
{
	$methods = array();
	$apiRest = new PaycometApiRest($apiKey);
	$userPaymentMethods = $apiRest->getUserPaymentMethods($userTerminal);	
	try {
		if ($userPaymentMethods) {
			foreach ($userPaymentMethods as $apm) {
				$methods[] = preg_replace('/\s+/', '_', 'Paycomet_' . $apm->name);
			}
		}
	}catch (exception $e){}
	
	return $methods;
}

function wppaytpv_upgrade() {

	$old_ver = PayTPV::get_option( 'version', '0' );
	$new_ver = PAYTPV_VERSION;

	PayTPV::update_option( 'version', $old_ver );

	if ( $old_ver == $new_ver ) {
		return;
	}

	do_action( 'wppaytpv_upgrade_version', $new_ver, $old_ver );

	PayTPV::update_option( 'version', $new_ver );
}

/* Install and default settings */
/*
add_action( 'activate_' . PAYTPV_PLUGIN, 'wppaytpv_install' );

function wppaytpv_install() {
	if ( $opt = get_option( 'paytpv' ) )
		return;

	wppaytpv_upgrade();
}
*/


// Notice: Si tiene habilitado el plugin y no ha definido la API KEY.
if( (isset( get_option('woocommerce_paytpv_settings')['enabled'] ) && get_option('woocommerce_paytpv_settings')['enabled'] == "yes") && 
	(!isset(get_option('woocommerce_paytpv_settings')['apikey']) || empty( get_option('woocommerce_paytpv_settings')['apikey'] )) ) {
    add_action( 'admin_notices', 'my_update_notice' );
}

function my_update_notice() {
	$url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paytpv');
	?>
	<div class="notice notice-error">
	
	   <p><b><?php echo sprintf(__( 'PAYCOMET Error 1004: You must define the API Key <a href="%s">here</a>.', 'wc_paytpv'), $url);?></b></p>

	</div>
	<?php 
}

function custom_display_checkout_error_message() {
    if ( isset( $_GET['order'] )) {
        $order_id=$_GET['order'];
        $order = wc_get_order( $order_id );
    }

    if ( isset( $_GET['paycomet_error'] ) && $_GET['paycomet_error'] === 'payment' ) {
        if ($order->get_meta("ErrorID") == 1004) {
            $error_txt = __( 'Error: ', 'wc_paytpv' ) . $order->get_meta("ErrorID");
        }else{
            $error_txt = __( 'An error has occurred. Please verify the data entered and try again', 'wc_paytpv' );
        }
        wc_print_notice( $error_txt, 'error' );

		$error_code = $order->get_meta("ErrorID");
        $error_description = get_error_description($error_code);
		/**
		 * Formateamos un mensaje de error descriptivo para el usuario
		 */
        $error_txt = sprintf(
            __('Payment error: %s (%d)', 'wc_paytpv'),
            $error_description,
            $error_code
        );
		if ( true === WP_DEBUG ) {
			error_log($error_txt);
			//wc_get_logger()->log('error', $error_txt);
		}
		
    }
	
}

/**
 * Obtiene la descripción de un código de error extraida de: https://docs.paycomet.com/en/recursos/codigos-de-error
 *
 * @param int $error_code Código de error.
 * @return string Descripción del error.
 */
function get_error_description($error_code) {
	if(!$error_code || !is_numeric($error_code)) {
		return __('Unknown error', 'wc_paytpv');
	}

	$error_code = intval($error_code);

	$error_codes = [
		0 => __( 'No error', 'wc_paytpv' ),
		1 => __( 'Error', 'wc_paytpv' ),
		100 => __( 'Expired credit card', 'wc_paytpv' ),
		101 => __( 'Credit card blacklisted', 'wc_paytpv' ),
		102 => __( 'Operation not allowed for the credit card type', 'wc_paytpv' ),
		103 => __( 'Please, call the credit card issuer', 'wc_paytpv' ),
		104 => __( 'Unexpected error', 'wc_paytpv' ),
		105 => __( 'Insufficient funds', 'wc_paytpv' ),
		106 => __( 'Credit card not registered or not logged by the issuer', 'wc_paytpv' ),
		107 => __( 'Data error. Validation Code', 'wc_paytpv' ),
		108 => __( 'PAN Check Error', 'wc_paytpv' ),
		109 => __( 'Expiry date error', 'wc_paytpv' ),
		110 => __( 'Data error', 'wc_paytpv' ),
		111 => __( 'CVC2 block incorrect', 'wc_paytpv' ),
		112 => __( 'Please, call the credit card issuer', 'wc_paytpv' ),
		113 => __( 'Credit card not valid', 'wc_paytpv' ),
		114 => __( 'The credit card has credit restrictions', 'wc_paytpv' ),
		115 => __( 'Card issuer could not validate card owner', 'wc_paytpv' ),
		116 => __( 'Payment not allowed in off-line authorization', 'wc_paytpv' ),
		118 => __( 'Expired credit card. Please capture card', 'wc_paytpv' ),
		119 => __( 'Credit card blacklisted. Please capture card', 'wc_paytpv' ),
		120 => __( 'Credit card lost or stolen. Please capture card', 'wc_paytpv' ),
		121 => __( 'Error in CVC2. Please capture card', 'wc_paytpv' ),
		122 => __( 'Error en Pre-Transaction process. Try again later.', 'wc_paytpv' ),
		123 => __( 'Operation denied. Please capture card', 'wc_paytpv' ),
		124 => __( 'Closing with agreement', 'wc_paytpv' ),
		125 => __( 'Closing without agreement', 'wc_paytpv' ),
		126 => __( 'Cannot close right now', 'wc_paytpv' ),
		127 => __( 'Invalid parameter', 'wc_paytpv' ),
		128 => __( 'Transactions were not accomplished', 'wc_paytpv' ),
		129 => __( 'Duplicated internal reference', 'wc_paytpv' ),
		130 => __( 'Original operation not found. Could not refund', 'wc_paytpv' ),
		131 => __( 'Expired preauthorization', 'wc_paytpv' ),
		132 => __( 'Operation not valid with selected currency', 'wc_paytpv' ),
		133 => __( 'Error in message format', 'wc_paytpv' ),
		134 => __( 'Message not recognized by the system', 'wc_paytpv' ),
		135 => __( 'CVC2 block incorrect', 'wc_paytpv' ),
		137 => __( 'Credit card not valid', 'wc_paytpv' ),
		138 => __( 'Gateway message error', 'wc_paytpv' ),
		139 => __( 'Gateway format error', 'wc_paytpv' ),
		140 => __( 'Credit card does not exist', 'wc_paytpv' ),
		141 => __( 'Amount zero or not valid', 'wc_paytpv' ),
		142 => __( 'Operation canceled', 'wc_paytpv' ),
		143 => __( 'Authentification error', 'wc_paytpv' ),
		144 => __( 'Denegation by security level', 'wc_paytpv' ),
		145 => __( 'Error in PUC message. Please contact PayCOMET', 'wc_paytpv' ),
		146 => __( 'System error', 'wc_paytpv' ),
		147 => __( 'Duplicated transaction', 'wc_paytpv' ),
		148 => __( 'MAC error', 'wc_paytpv' ),
		149 => __( 'Settlement rejected', 'wc_paytpv' ),
		150 => __( 'System date/time not synchronized', 'wc_paytpv' ),
		151 => __( 'Invalid card expiration date', 'wc_paytpv' ),
		152 => __( 'Could not find any preauthorization with given data', 'wc_paytpv' ),
		153 => __( 'Cannot find requested data', 'wc_paytpv' ),
		154 => __( 'Cannot operate with given credit card', 'wc_paytpv' ),
		155 => __( 'This method requires activation of the VHASH protocol', 'wc_paytpv' ),
		172 => __( 'Operation denied. No repeat', 'wc_paytpv' ),
		173 => __( 'Denied operation for card data sent', 'wc_paytpv' ),
		174 => __( 'Operation denied. Do not repeat before 72 hours', 'wc_paytpv' ),
		184 => __( 'The authentication process was canceled', 'wc_paytpv' ),
		195 => __( 'Requires SCA authentication', 'wc_paytpv' ),
		500 => __( 'Unexpected error', 'wc_paytpv' ),
		501 => __( 'Unexpected error', 'wc_paytpv' ),
		502 => __( 'Unexpected error', 'wc_paytpv' ),
		504 => __( 'Transaction already cancelled', 'wc_paytpv' ),
		505 => __( 'Transaction originally denied', 'wc_paytpv' ),
		506 => __( 'Confirmation data not valid', 'wc_paytpv' ),
		507 => __( 'Unexpected error', 'wc_paytpv' ),
		508 => __( 'Transaction still in process', 'wc_paytpv' ),
		509 => __( 'Unexpected error', 'wc_paytpv' ),
		510 => __( 'Refund is not possible', 'wc_paytpv' ),
		511 => __( 'Unexpected error', 'wc_paytpv' ),
		512 => __( 'Card issuer not available right now. Please try again later', 'wc_paytpv' ),
		513 => __( 'Unexpected error', 'wc_paytpv' ),
		514 => __( 'Unexpected error', 'wc_paytpv' ),
		515 => __( 'Unexpected error', 'wc_paytpv' ),
		516 => __( 'Unexpected error', 'wc_paytpv' ),
		517 => __( 'Unexpected error', 'wc_paytpv' ),
		518 => __( 'Unexpected error', 'wc_paytpv' ),
		519 => __( 'Unexpected error', 'wc_paytpv' ),
		520 => __( 'Unexpected error', 'wc_paytpv' ),
		521 => __( 'Unexpected error', 'wc_paytpv' ),
		522 => __( 'Unexpected error', 'wc_paytpv' ),
		523 => __( 'Unexpected error', 'wc_paytpv' ),
		524 => __( 'Unexpected error', 'wc_paytpv' ),
		525 => __( 'Unexpected error', 'wc_paytpv' ),
		526 => __( 'Unexpected error', 'wc_paytpv' ),
		527 => __( 'TransactionType unknown', 'wc_paytpv' ),
		528 => __( 'Unexpected error', 'wc_paytpv' ),
		529 => __( 'Unexpected error', 'wc_paytpv' ),
		530 => __( 'Unexpected error', 'wc_paytpv' ),
		531 => __( 'Unexpected error', 'wc_paytpv' ),
		532 => __( 'Unexpected error', 'wc_paytpv' ),
		533 => __( 'Unexpected error', 'wc_paytpv' ),
		534 => __( 'Unexpected error', 'wc_paytpv' ),
		535 => __( 'Unexpected error', 'wc_paytpv' ),
		536 => __( 'Unexpected error', 'wc_paytpv' ),
		537 => __( 'Unexpected error', 'wc_paytpv' ),
		538 => __( 'Not cancelable operation', 'wc_paytpv' ),
		539 => __( 'Unexpected error', 'wc_paytpv' ),
		540 => __( 'Unexpected error', 'wc_paytpv' ),
		541 => __( 'Unexpected error', 'wc_paytpv' ),
		542 => __( 'Unexpected error', 'wc_paytpv' ),
		543 => __( 'Unexpected error', 'wc_paytpv' ),
		544 => __( 'Unexpected error', 'wc_paytpv' ),
		545 => __( 'Unexpected error', 'wc_paytpv' ),
		546 => __( 'Unexpected error', 'wc_paytpv' ),
		547 => __( 'Unexpected error', 'wc_paytpv' ),
		548 => __( 'Unexpected error', 'wc_paytpv' ),
		549 => __( 'Unexpected error', 'wc_paytpv' ),
		550 => __( 'Unexpected error', 'wc_paytpv' ),
		551 => __( 'Unexpected error', 'wc_paytpv' ),
		552 => __( 'Unexpected error', 'wc_paytpv' ),
		553 => __( 'Unexpected error', 'wc_paytpv' ),
		554 => __( 'Unexpected error', 'wc_paytpv' ),
		555 => __( 'Could not find the previous operation', 'wc_paytpv' ),
		556 => __( 'Data inconsistency in cancellation validation', 'wc_paytpv' ),
		557 => __( 'Delayed payment code does not exists', 'wc_paytpv' ),
		558 => __( 'Unexpected error', 'wc_paytpv' ),
		559 => __( 'Unexpected error', 'wc_paytpv' ),
		560 => __( 'Unexpected error', 'wc_paytpv' ),
		561 => __( 'Unexpected error', 'wc_paytpv' ),
		562 => __( 'Credit card does not allow preauthorizations', 'wc_paytpv' ),
		563 => __( 'Data inconsistency in confirmation', 'wc_paytpv' ),
		564 => __( 'Unexpected error', 'wc_paytpv' ),
		565 => __( 'Unexpected error', 'wc_paytpv' ),
		567 => __( 'Refund operation not correctly specified', 'wc_paytpv' ),
		568 => __( 'Online communication incorrect', 'wc_paytpv' ),
		569 => __( 'Denied operation', 'wc_paytpv' ),
		740 => __( 'AFT data validation failed. Wrong type of operation or sector of activity', 'wc_paytpv' ),
		741 => __( 'AFT data validation failed. The data sent does not meet the specifications', 'wc_paytpv' ),
		1000 => __( 'Account not found. Review your settings', 'wc_paytpv' ),
		1001 => __( 'User not found. Please contact your administrator', 'wc_paytpv' ),
		1002 => __( 'External provider signature error. Contact your service provider', 'wc_paytpv' ),
		1003 => __( 'Signature not valid. Please review your settings', 'wc_paytpv' ),
		1004 => __( 'Forbidden access', 'wc_paytpv' ),
		1005 => __( 'Invalid credit card format', 'wc_paytpv' ),
		1006 => __( 'Data error: Validation code', 'wc_paytpv' ),
		1007 => __( 'Data error: Expiration date', 'wc_paytpv' ),
		1008 => __( 'Preauthorization reference not found', 'wc_paytpv' ),
		1009 => __( 'Preauthorization data could not be found', 'wc_paytpv' ),
		1010 => __( 'Could not send cancellation. Please try again later', 'wc_paytpv' ),
		1011 => __( 'Could not connect to host', 'wc_paytpv' ),
		1012 => __( 'Could not resolve proxy address', 'wc_paytpv' ),
		1013 => __( 'Could not resolve host', 'wc_paytpv' ),
		1014 => __( 'Initialization failed', 'wc_paytpv' ),
		1015 => __( 'Could not find HTTP resource', 'wc_paytpv' ),
		1016 => __( 'The HTTP options range is not valid', 'wc_paytpv' ),
		1017 => __( 'The POST is not correctly built', 'wc_paytpv' ),
		1018 => __( 'The username is not correctly formatted', 'wc_paytpv' ),
		1019 => __( 'Operation timeout exceeded', 'wc_paytpv' ),
		1020 => __( 'Insufficient memory', 'wc_paytpv' ),
		1021 => __( 'Could not connect to SSL host', 'wc_paytpv' ),
		1022 => __( 'Protocol not supported', 'wc_paytpv' ),
		1023 => __( 'Given URL is not correctly formatted and cannot be used', 'wc_paytpv' ),
		1024 => __( 'URL user is not correctly formatted', 'wc_paytpv' ),
		1025 => __( 'Cannot register available resources to complete current operation', 'wc_paytpv' ),
		1026 => __( 'Duplicated external reference', 'wc_paytpv' ),
		1027 => __( 'Total refunds cannot exceed original payment', 'wc_paytpv' ),
		1028 => __( 'Account not active. Please contact PayCOMET', 'wc_paytpv' ),
		1029 => __( 'Account still not certified. Please contact PayCOMET', 'wc_paytpv' ),
		1030 => __( 'Product is marked for deletion and cannot be used', 'wc_paytpv' ),
		1031 => __( 'Insufficient rights', 'wc_paytpv' ),
		1032 => __( 'Product cannot be used under test environment', 'wc_paytpv' ),
		1033 => __( 'Product cannot be used under production environment', 'wc_paytpv' ),
		1034 => __( 'It was not possible to send the refund request', 'wc_paytpv' ),
		1035 => __( 'Error in field operation origin IP', 'wc_paytpv' ),
		1036 => __( 'Error in XML format', 'wc_paytpv' ),
		1037 => __( 'Root element is not correct', 'wc_paytpv' ),
		1038 => __( 'Field DS_MERCHANT_AMOUNT incorrect', 'wc_paytpv' ),
		1039 => __( 'Field DS_MERCHANT_ORDER incorrect', 'wc_paytpv' ),
		1040 => __( 'Field DS_MERCHANT_MERCHANTCODE incorrect', 'wc_paytpv' ),
		1041 => __( 'Field DS_MERCHANT_CURRENCY incorrect', 'wc_paytpv' ),
		1042 => __( 'Field DS_MERCHANT_PAN incorrect', 'wc_paytpv' ),
		1043 => __( 'Field DS_MERCHANT_CVV2 incorrect', 'wc_paytpv' ),
		1044 => __( 'Field DS_MERCHANT_TRANSACTIONTYPE incorrect', 'wc_paytpv' ),
		1045 => __( 'Field DS_MERCHANT_TERMINAL incorrect', 'wc_paytpv' ),
		1046 => __( 'Field DS_MERCHANT_EXPIRYDATE incorrect', 'wc_paytpv' ),
		1047 => __( 'Field DS_MERCHANT_MERCHANTSIGNATURE incorrect', 'wc_paytpv' ),
		1048 => __( 'Field DS_ORIGINAL_IP incorrect', 'wc_paytpv' ),
		1049 => __( 'Client not found', 'wc_paytpv' ),
		1050 => __( 'Preauthorization amount cannot be greater than previous preauthorization amount', 'wc_paytpv' ),
		1099 => __( 'Unexpected error', 'wc_paytpv' ),
		1100 => __( 'Card diary limit exceeds', 'wc_paytpv' ),
		1103 => __( 'ACCOUNT field error', 'wc_paytpv' ),
		1104 => __( 'USERCODE field error', 'wc_paytpv' ),
		1105 => __( 'TERMINAL field error', 'wc_paytpv' ),
		1106 => __( 'OPERATION field error', 'wc_paytpv' ),
		1107 => __( 'ORDER field error', 'wc_paytpv' ),
		1108 => __( 'AMOUNT field error', 'wc_paytpv' ),
		1109 => __( 'CURRENCY field error', 'wc_paytpv' ),
		1110 => __( 'SIGNATURE field error', 'wc_paytpv' ),
		1120 => __( 'Operation unavailable', 'wc_paytpv' ),
		1121 => __( 'Client not found', 'wc_paytpv' ),
		1122 => __( 'User not found. Contact PayCOMET', 'wc_paytpv' ),
		1123 => __( 'Invalid signature. Please check your configuration', 'wc_paytpv' ),
		1124 => __( 'Operation not available with the specified user', 'wc_paytpv' ),
		1125 => __( 'Invalid operation in a currency other than product currency', 'wc_paytpv' ),
		1127 => __( 'Quantity zero or invalid', 'wc_paytpv' ),
		1128 => __( 'Current currency conversion invalid', 'wc_paytpv' ),
		1129 => __( 'Invalid amount', 'wc_paytpv' ),
		1130 => __( 'Product not found', 'wc_paytpv' ),
		1131 => __( 'Invalid operation with the current currency', 'wc_paytpv' ),
		1132 => __( 'Invalid operation with a different currency than product currency', 'wc_paytpv' ),
		1133 => __( 'Info button corrupt', 'wc_paytpv' ),
		1134 => __( 'The subscription may not exceed the expiration date of the card', 'wc_paytpv' ),
		1135 => __( 'DS_EXECUTE can not be true if DS_SUBSCRIPTION_STARTDATE is different from today.', 'wc_paytpv' ),
		1136 => __( 'PAYTPV_OPERATIONS_MERCHANTCODE field error', 'wc_paytpv' ),
		1137 => __( 'PAYTPV_OPERATIONS_TERMINAL must be Array', 'wc_paytpv' ),
		1138 => __( 'PAYTPV_OPERATIONS_OPERATIONS must be Array', 'wc_paytpv' ),
		1139 => __( 'PAYTPV_OPERATIONS_SIGNATURE field error', 'wc_paytpv' ),
		1140 => __( 'Can not find any of the PAYTPV_OPERATIONS_TERMINAL', 'wc_paytpv' ),
		1141 => __( 'Error in the date range requested', 'wc_paytpv' ),
		1142 => __( 'The application can not have a length greater than 6 months', 'wc_paytpv' ),
		1143 => __( 'The operation state is incorrect', 'wc_paytpv' ),
		1144 => __( 'Error in the amounts of the search', 'wc_paytpv' ),
		1145 => __( 'The type of operation requested does not exist', 'wc_paytpv' ),
		1146 => __( 'Sort Order unrecognized', 'wc_paytpv' ),
		1147 => __( 'PAYTPV_OPERATIONS_SORTORDER unrecognized', 'wc_paytpv' ),
		1148 => __( 'Subscription start date wrong', 'wc_paytpv' ),
		1149 => __( 'Subscription end date wrong', 'wc_paytpv' ),
		1150 => __( 'Frequency error in the subscription', 'wc_paytpv' ),
		1151 => __( 'Invalid usuarioXML', 'wc_paytpv' ),
		1152 => __( 'Invalid codigoCliente', 'wc_paytpv' ),
		1153 => __( 'Invalid usuarios parameter', 'wc_paytpv' ),
		1154 => __( 'Invalid firma parameter', 'wc_paytpv' ),
		1155 => __( 'Invalid usuarios parameter format', 'wc_paytpv' ),
		1156 => __( 'Invalid type', 'wc_paytpv' ),
		1157 => __( 'Invalid name', 'wc_paytpv' ),
		1158 => __( 'Invalid surname', 'wc_paytpv' ),
		1159 => __( 'Invalid email', 'wc_paytpv' ),
		1160 => __( 'Invalid password', 'wc_paytpv' ),
		1161 => __( 'Invalid language', 'wc_paytpv' ),
		1162 => __( 'Invalid maxamount', 'wc_paytpv' ),
		1163 => __( 'Invalid multicurrency', 'wc_paytpv' ),
		1165 => __( 'Invalid permissions_specs. Format not allowed', 'wc_paytpv' ),
		1166 => __( 'Invalid permissions_products. Format not allowed', 'wc_paytpv' ),
		1167 => __( 'Invalid email. Format not allowed', 'wc_paytpv' ),
		1168 => __( 'Weak or invalid password', 'wc_paytpv' ),
		1169 => __( 'Invalid value for type parameter', 'wc_paytpv' ),
		1170 => __( 'Invalid value for language parameter', 'wc_paytpv' ),
		1171 => __( 'Invalid format for maxamount parameter', 'wc_paytpv' ),
		1172 => __( 'Invalid multicurrency. Format not allowed', 'wc_paytpv' ),
		1173 => __( 'Invalid permission_id – permissions_specs. Not allowed', 'wc_paytpv' ),
		1174 => __( 'Invalid user', 'wc_paytpv' ),
		1175 => __( 'Invalid credentials', 'wc_paytpv' ),
		1176 => __( 'Account not found', 'wc_paytpv' ),
		1177 => __( 'User not found', 'wc_paytpv' ),
		1178 => __( 'Invalid signature', 'wc_paytpv' ),
		1179 => __( 'Account without products', 'wc_paytpv' ),
		1180 => __( 'Invalid product_id - permissions_products. Not allowed', 'wc_paytpv' ),
		1181 => __( 'Invalid permission_id -permissions_products. Not allowed', 'wc_paytpv' ),
		1185 => __( 'Minimun limit not allowed', 'wc_paytpv' ),
		1186 => __( 'Maximun limit not allowed', 'wc_paytpv' ),
		1187 => __( 'Daily limit not allowed', 'wc_paytpv' ),
		1188 => __( 'Monthly limit not allowed', 'wc_paytpv' ),
		1189 => __( 'Max amount (same card / last 24 h.) not allowed', 'wc_paytpv' ),
		1190 => __( 'Max amount (same card / last 24 h. / same IP address) not allowed', 'wc_paytpv' ),
		1191 => __( 'Day / IP address limit (all cards) not allowed', 'wc_paytpv' ),
		1192 => __( 'Country (customer IP address) not allowed', 'wc_paytpv' ),
		1193 => __( 'Card type (credit / debit) not allowed', 'wc_paytpv' ),
		1194 => __( 'Card brand not allowed', 'wc_paytpv' ),
		1195 => __( 'Card Category not allowed', 'wc_paytpv' ),
		1196 => __( 'Authorization from different country than card issuer, not allowed', 'wc_paytpv' ),
		1197 => __( 'Denied. Filter: Card country issuer not allowed', 'wc_paytpv' ),
		1198 => __( 'Scoring limit exceeded', 'wc_paytpv' ),
		1199 => __( 'Week / IP address limit (all cards) not allowed', 'wc_paytpv' ),
		1200 => __( 'Denied. Filter: same card, different country last 24 h.', 'wc_paytpv' ),
		1201 => __( 'Number of erroneous consecutive attempts with the same card exceeded', 'wc_paytpv' ),
		1202 => __( 'Number of failed attempts (last 30 minutes) from the same ip address exceeded', 'wc_paytpv' ),
		1203 => __( 'Wrong or not configured credentials', 'wc_paytpv' ),
		1204 => __( 'Wrong token received', 'wc_paytpv' ),
		1205 => __( 'Can not perform the operation', 'wc_paytpv' ),
		1206 => __( 'ProviderID not available', 'wc_paytpv' ),
		1207 => __( 'Operations parameter missing or not in a correct format', 'wc_paytpv' ),
		1208 => __( 'PaycometMerchant parameter missing', 'wc_paytpv' ),
		1209 => __( 'MerchatID parameter missing', 'wc_paytpv' ),
		1210 => __( 'TerminalID parameter missing', 'wc_paytpv' ),
		1211 => __( 'TpvID parameter missing', 'wc_paytpv' ),
		1212 => __( 'OperationType parameter missing', 'wc_paytpv' ),
		1213 => __( 'OperationResult parameter missing', 'wc_paytpv' ),
		1214 => __( 'OperationAmount parameter missing', 'wc_paytpv' ),
		1215 => __( 'OperationCurrency parameter missing', 'wc_paytpv' ),
		1216 => __( 'OperationDatetime parameter missing', 'wc_paytpv' ),
		1217 => __( 'OriginalAmount parameter missing', 'wc_paytpv' ),
		1218 => __( 'Pan parameter missing', 'wc_paytpv' ),
		1219 => __( 'ExpiryDate parameter missing', 'wc_paytpv' ),
		1220 => __( 'Reference parameter missing', 'wc_paytpv' ),
		1221 => __( 'Signature parameter missing', 'wc_paytpv' ),
		1222 => __( 'OriginalIP parameter missing or not in a correct format', 'wc_paytpv' ),
		1223 => __( 'Authcode / errorCode parameter missing', 'wc_paytpv' ),
		1224 => __( 'Product of the operation missing', 'wc_paytpv' ),
		1225 => __( 'The type of operation is not supported', 'wc_paytpv' ),
		1226 => __( 'The result of the operation is not supported', 'wc_paytpv' ),
		1227 => __( 'The transaction currency is not supported', 'wc_paytpv' ),
		1228 => __( 'The date of the transaction is not in a correct format', 'wc_paytpv' ),
		1229 => __( 'The signature is not correct', 'wc_paytpv' ),
		1230 => __( 'Can not find the associated account information', 'wc_paytpv' ),
		1231 => __( 'Can not find the associated product information', 'wc_paytpv' ),
		1232 => __( 'Can not find the associated user information', 'wc_paytpv' ),
		1233 => __( 'The product is not set as multicurrency', 'wc_paytpv' ),
		1234 => __( 'The amount of the transaction is not in a correct format', 'wc_paytpv' ),
		1235 => __( 'The original amount of the transaction is not in a correct format', 'wc_paytpv' ),
		1236 => __( 'The card does not have the correct format', 'wc_paytpv' ),
		1237 => __( 'The expiry date of the card is not in a correct format', 'wc_paytpv' ),
		1238 => __( 'Can not initialize the service', 'wc_paytpv' ),
		1239 => __( 'Can not initialize the service', 'wc_paytpv' ),
		1240 => __( 'Method not implemented', 'wc_paytpv' ),
		1241 => __( 'Can not initialize the service', 'wc_paytpv' ),
		1242 => __( 'Service can not be completed', 'wc_paytpv' ),
		1243 => __( 'OperationCode parameter missing', 'wc_paytpv' ),
		1244 => __( 'bankName parameter missing', 'wc_paytpv' ),
		1245 => __( 'csb parameter missing', 'wc_paytpv' ),
		1246 => __( 'userReference parameter missing', 'wc_paytpv' ),
		1247 => __( 'Can not find the associated FUC', 'wc_paytpv' ),
		1248 => __( 'Duplicate xref. Pending operation.', 'wc_paytpv' ),
		1249 => __( '[DS_]AGENT_FEE parameter missing', 'wc_paytpv' ),
		1250 => __( '[DS_]AGENT_FEE parameter is not in a correct format', 'wc_paytpv' ),
		1251 => __( 'DS_AGENT_FEE parameter is not correct', 'wc_paytpv' ),
		1252 => __( 'CANCEL_URL parameter missing', 'wc_paytpv' ),
		1253 => __( 'CANCEL_URL parameter is not in a correct format', 'wc_paytpv' ),
		1254 => __( 'Commerce with secure cardholder and cardholder without secure purchase key', 'wc_paytpv' ),
		1255 => __( 'Call terminated by the client', 'wc_paytpv' ),
		1256 => __( 'Call terminated, incorrect attempts exceeded', 'wc_paytpv' ),
		1257 => __( 'Call terminated, operation attempts exceeded', 'wc_paytpv' ),
		1258 => __( 'stationID not available', 'wc_paytpv' ),
		1259 => __( 'It has not been possible to establish the IVR session', 'wc_paytpv' ),
		1260 => __( 'merchantCode parameter missing', 'wc_paytpv' ),
		1261 => __( 'The merchantCode parameter is incorrect', 'wc_paytpv' ),
		1262 => __( 'terminalIDDebtor parameter missing', 'wc_paytpv' ),
		1263 => __( 'terminalIDCreditor parameter missing', 'wc_paytpv' ),
		1264 => __( 'Authorisations for carrying out the operation not available', 'wc_paytpv' ),
		1265 => __( 'The Iban account (terminalIDDebtor) is invalid', 'wc_paytpv' ),
		1266 => __( 'The Iban account (terminalIDCreditor) is invalid', 'wc_paytpv' ),
		1267 => __( 'The BicCode of the Iban account (terminalIDDebtor) is invalid', 'wc_paytpv' ),
		1268 => __( 'The BicCode of the Iban account (terminalIDCreditor) is invalid', 'wc_paytpv' ),
		1269 => __( 'operationOrder parameter missing', 'wc_paytpv' ),
		1270 => __( 'The operationOrder parameter does not have the correct format', 'wc_paytpv' ),
		1271 => __( 'The operationAmount parameter does not have the correct format', 'wc_paytpv' ),
		1272 => __( 'The operationDatetime parameter does not have the correct format', 'wc_paytpv' ),
		1273 => __( 'The operationConcept parameter contains invalid characters or exceeds 140 characters', 'wc_paytpv' ),
		1274 => __( 'It has not been possible to record the SEPA operation', 'wc_paytpv' ),
		1275 => __( 'It has not been possible to record the SEPA operation', 'wc_paytpv' ),
		1276 => __( 'Can not create an operation token', 'wc_paytpv' ),
		1277 => __( 'Invalid scoring value', 'wc_paytpv' ),
		1278 => __( 'The language parameter is not in a correct format', 'wc_paytpv' ),
		1279 => __( 'The cardholder name is not in a correct format', 'wc_paytpv' ),
		1280 => __( 'The card does not have the correct format', 'wc_paytpv' ),
		1281 => __( 'The month does not have the correct format', 'wc_paytpv' ),
		1282 => __( 'The year does not have the correct format', 'wc_paytpv' ),
		1283 => __( 'The cvc2 does not have the correct format', 'wc_paytpv' ),
		1284 => __( 'The JETID parameter is not in a correct format', 'wc_paytpv' ),
		1288 => __( 'The splitId parameter is not valid', 'wc_paytpv' ),
		1289 => __( 'The splitId parameter is not allowed', 'wc_paytpv' ),
		1290 => __( "This terminal don't allow (split) transfers", 'wc_paytpv' ),
		1291 => __( 'It has not been possible to record the (split) transfer operation', 'wc_paytpv' ),
		1292 => __( "Original payment's date cannot exceed 90 days", 'wc_paytpv' ),
		1293 => __( 'Original (split) tansfer not found', 'wc_paytpv' ),
		1294 => __( 'Total reversal cannot exceed original (split) transfer', 'wc_paytpv' ),
		1295 => __( 'It has not been possible to record the (split) transfer reversal operation', 'wc_paytpv' ),
		1296 => __( 'uniqueIdCreditor parameter missing', 'wc_paytpv' ),
		1297 => __( 'Bank account still not certified.', 'wc_paytpv' ),
		1298 => __( 'companyNameCreditor parameter missing.', 'wc_paytpv' ),
		1299 => __( 'Invalid companyTypeCreditor parameter.', 'wc_paytpv' ),
		1300 => __( 'Invalid swiftCodeCreditor parameter.', 'wc_paytpv' ),
		1301 => __( 'The number of operations per request has been exceeded.', 'wc_paytpv' ),
		1302 => __( 'Denied. Filter same IP last 24 hours.', 'wc_paytpv' ),
		1303 => __( 'Denied. Max amount same IP last 24 hours.', 'wc_paytpv' ),
		1304 => __( "The account isn't set up correctly.", 'wc_paytpv' ),
		1305 => __( 'merchantCustomerId parameter missing.', 'wc_paytpv' ),
		1306 => __( 'Invalid merchantCustomerIban parameter.', 'wc_paytpv' ),
		1307 => __( 'fileContent parameter missing.', 'wc_paytpv' ),
		1308 => __( 'Invalid document extension.', 'wc_paytpv' ),
		1309 => __( 'The document exceeds the maximum file size.', 'wc_paytpv' ),
		1310 => __( 'Invalid document type.', 'wc_paytpv' ),
		1311 => __( 'Different IP / Reference limit not allowed', 'wc_paytpv' ),
		1312 => __( 'SEPA Credit Transfer denied', 'wc_paytpv' ),
		1313 => __( 'There is not payment_info', 'wc_paytpv' ),
		1314 => __( 'Bank account type is not IBAN', 'wc_paytpv' ),
		1315 => __( 'No documents found', 'wc_paytpv' ),
		1316 => __( 'Error uploading documents', 'wc_paytpv' ),
		1317 => __( 'Error downloading documents', 'wc_paytpv' ),
		1318 => __( 'Required documentation is not complete', 'wc_paytpv' ),
		1319 => __( 'Not allowed. Currency is not EUR', 'wc_paytpv' ),
		1320 => __( 'Invoice state is not COMPLETE', 'wc_paytpv' ),
		1321 => __( 'The exception sent is not enabled', 'wc_paytpv' ),
		1322 => __( 'Challenge required to complete the operation', 'wc_paytpv' ),
		1323 => __( 'The mandatory information of the MERCHANT_DATA has not been sent', 'wc_paytpv' ),
		1324 => __( 'The DS_USER_INTERACTION parameter is not valid', 'wc_paytpv' ),
		1325 => __( 'Challenge required and user not present', 'wc_paytpv' ),
		1326 => __( 'Denial by security controls on the processor', 'wc_paytpv' ),
		1327 => __( '3DS authentication process error', 'wc_paytpv' ),
		1328 => __( 'Parameter error: must be EMAIL or SMS', 'wc_paytpv' ),
		1329 => __( 'Email sending failed', 'wc_paytpv' ),
		1330 => __( 'SMS sending failed', 'wc_paytpv' ),
		1331 => __( 'Template not found', 'wc_paytpv' ),
		1332 => __( 'Maximum requests per minute reached', 'wc_paytpv' ),
		1333 => __( 'Phone number not configured for Sandbox SMS sending', 'wc_paytpv' ),
		1334 => __( 'Email address not configured for Sandbox email sending', 'wc_paytpv' ),
		1335 => __( 'DS_MERCHANT_IDENTIFIER parameter missing', 'wc_paytpv' ),
		1336 => __( 'DS_MERCHANT_IDENTIFIER parameter is not correct', 'wc_paytpv' ),
		1337 => __( 'Wrong or not configured notification URL', 'wc_paytpv' ),
		1338 => __( 'Notification URL does not have the correct response', 'wc_paytpv' ),
		1339 => __( 'Invalid terminal configuration', 'wc_paytpv' ),
		1340 => __( 'Payment method not available', 'wc_paytpv' ),
		1341 => __( 'Bizum. Authentication failed. Lock after three attempts.', 'wc_paytpv' ),
		1342 => __( 'Bizum. Transaction cancelled. The user does not wish to continue.', 'wc_paytpv' ),
		1343 => __( 'Bizum. Payment rejected by beneficiary.', 'wc_paytpv' ),
		1344 => __( 'Bizum. Charge rejected by originator.', 'wc_paytpv' ),
		1345 => __( 'Bizum. The processor rejects the operation.', 'wc_paytpv' ),
		1346 => __( 'Bizum. Insufficient available balance.', 'wc_paytpv' ),
		1351 => __( 'PSD2: BillAddrCountry field not included with supplied billAddrState', 'wc_paytpv' ),
		1352 => __( 'PSD2: ShipAddrCountry field not included with supplied shipAddrState', 'wc_paytpv' ),
		1353 => __( 'PSD2: BillAddrCountry field must be in ISO 3166-1 alpha-3 format', 'wc_paytpv' ),
		1354 => __( 'PSD2: BillAddrState field must be in ISO 3166-2 format', 'wc_paytpv' ),
		1355 => __( 'PSD2: ShipAddrCountry field must be in ISO 3166-1 alpha-3 format', 'wc_paytpv' ),
		1356 => __( 'PSD2: ShipAddrState field must be in ISO 3166-2 format', 'wc_paytpv' ),
		1360 => __( 'PSD2: MERCHANT_SCA_EXCEPTION field with incorrect value', 'wc_paytpv' ),
		1361 => __( 'PSD2: MERCHANT_TRX_TYPE not supplied required with MERCHANT_SCA_EXCEPTION = MIT', 'wc_paytpv' ),
		1362 => __( 'PSD2: MERCHANT_TRX_TYPE field with incorrect value', 'wc_paytpv' ),
		1363 => __( 'PSD2: Mandatory purchaseInstalData, recurringExpiry, and recurringFrequency fields in Instalment operation (MERCHANT_TRX_TYPE = I)', 'wc_paytpv' ),
		1364 => __( 'PSD2: Mandatory purchaseInstalData, recurringExpiry, and recurringFrequency fields in Recurring operation (MERCHANT_TRX_TYPE = R)', 'wc_paytpv' ),
		1365 => __( 'PSD2: The purchaseInstalData value must be greater than 1', 'wc_paytpv' ),
		1366 => __( 'PSD2: The recurringExpiry value must be in the format YYYYMMDD', 'wc_paytpv' ),
		1367 => __( 'When the same card has several payments in "flight" at the time that one is finalized, the rest are denied with this code. This restriction is for security.', 'wc_paytpv' ),
		1400 => __( 'Bizum could not authenticate user', 'wc_paytpv' ),
		1417 => __( 'PayPal - The instrument presented has been declined. Please select another one.', 'wc_paytpv' ),
		1418 => __( "PayPal - The sum of the amount of the cart items doesn't match the order total amount.", 'wc_paytpv' ),
		1419 => __( 'shoppingCart[].name parameter required', 'wc_paytpv' ),
		1420 => __( 'shoppingCart[].quantity parameter required.', 'wc_paytpv' ),
		1421 => __( 'shoppingCart[].unitPrice parameter required.', 'wc_paytpv' ),
		1422 => __( 'shipping.shipAddrCountry parameter required.', 'wc_paytpv' ),
		1423 => __( 'shipping.shipAddrCity parameter required.', 'wc_paytpv' ),
		1424 => __( 'shipping.shipAddrPostCode parameter required.', 'wc_paytpv' ),
		1425 => __( 'PayPal - Operation cancelled by user.', 'wc_paytpv' ),
		1450 => __( 'Refund card does not match the authorization one', 'wc_paytpv' ),
		1451 => __( 'paycometId parameter missing', 'wc_paytpv' ),
		1452 => __( 'Operations between sandbox and production environments are not allowed', 'wc_paytpv' ),
		1453 => __( 'Previous operation not found. Cancellation could not be executed', 'wc_paytpv' ),
		1454 => __( 'Previous operation already canceled', 'wc_paytpv' ),
		1455 => __( 'Wrong message format', 'wc_paytpv' ),
		1456 => __( 'System error', 'wc_paytpv' ),
		1457 => __( 'Resolver Center not available. Local authorization', 'wc_paytpv' ),
		1458 => __( 'Duplicate 1X2X message received (another message with the same RTS has already been processed)', 'wc_paytpv' ),
		1459 => __( 'Cancellation is not accepted. The HCP did not find the operation to be canceled', 'wc_paytpv' ),
		1460 => __( 'Collection rejected. Not found any of the original operations referenced', 'wc_paytpv' ),
		1461 => __( 'Wrong MAC', 'wc_paytpv' ),
		1462 => __( 'Received 1200 to an operation already canceled which was answered with 914 or 481', 'wc_paytpv' ),
		1463 => __( 'Session not allowed', 'wc_paytpv' ),
		1464 => __( 'Reception at the HCP of a communication message (1X2X) when a previous one is still being processed (1X2X/X)', 'wc_paytpv' ),
		1465 => __( 'Date and time not synchronized', 'wc_paytpv' ),
		1466 => __( 'Expiration Date of the card does not coincide with the one that the Issuer has on file', 'wc_paytpv' ),
		1467 => __( 'Violation of existing regulations to perform a local authorization', 'wc_paytpv' ),
		1468 => __( 'Existing rule violation to make a refund', 'wc_paytpv' ),
		1469 => __( 'Message response timed out', 'wc_paytpv' ),
		1470 => __( 'Timeout error when performing a communication test (1804)', 'wc_paytpv' ),
		1471 => __( 'A timeout has occurred in command execution', 'wc_paytpv' ),
		1472 => __( 'All messaging retries have failed', 'wc_paytpv' ),
		1473 => __( 'The specified EndPointWorker does not exist', 'wc_paytpv' ),
		1474 => __( 'Failed to stop worker', 'wc_paytpv' ),
		1475 => __( 'Number of PIN attempts exceeded', 'wc_paytpv' ),
		1476 => __( 'Mandatory PIN', 'wc_paytpv' ),
		1477 => __( 'Wrong PIN', 'wc_paytpv' ),
		1478 => __( 'Wrong PIN Block', 'wc_paytpv' ),
		1479 => __( 'Denied. Operation with clear card data in SNCP merchant', 'wc_paytpv' ),
		1480 => __( 'Card not supported by the system', 'wc_paytpv' ),
		1481 => __( 'Denied for various reasons', 'wc_paytpv' ),
		1482 => __( 'Denied. It is not possible to verify authentication by Reference', 'wc_paytpv' ),
		1483 => __( 'Denied. Authentication amount exceeded', 'wc_paytpv' ),
		1484 => __( 'Repeat operation with CHIP reading, using contacts, or double TAP plus PIN', 'wc_paytpv' ),
		1485 => __( 'Repeat operation with CHIP reading, using contacts, or request PIN only', 'wc_paytpv' ),
		1486 => __( 'Call the issuer', 'wc_paytpv' ),
		1487 => __( 'Transactional identifiers data not found', 'wc_paytpv' ),
		1488 => __( 'TransactionId do not match', 'wc_paytpv' ),
		1489 => __( 'Card on Black List or suspected of fraud, include in Black List file', 'wc_paytpv' ),
		1490 => __( 'Blacklisted or suspected fraud card', 'wc_paytpv' ),
		1495 => __( 'Cancellation not allowed. Authorization already refunded', 'wc_paytpv' )
	];
	return $error_codes[$error_code] ??	__( 'Unknown error', 'wc_paytpv' );

}
