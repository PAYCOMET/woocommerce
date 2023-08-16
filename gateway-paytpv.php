<?php
/**
 * Plugin Name: PAYCOMET Woocommerce
 * Plugin URI: https://wordpress.org/plugins/paytpv-for-woocommerce/
 * Description: The PAYCOMET payment gateway for WooCommerce
 * Author: PAYCOMET
 * Author URI: https://www.paycomet.com
 * Version: 5.28
 * Tested up to: 6.1.1
 * WC tested up to: 7.8
 * Text Domain: wc_paytpv
 * Domain Path: /languages
 */


define( 'PAYTPV_VERSION', '5.28' );

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
	//require PAYTPV_PLUGIN_DIR . '/inc/paycomet-mbway.php';
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
