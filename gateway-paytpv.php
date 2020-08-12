<?php
/**
 * Plugin Name: PAYCOMET Woocommerce
 * Plugin URI: https://wordpress.org/plugins/paytpv-for-woocommerce/
 * Description: The PAYCOMET payment gateway for WooCommerce
 * Author: PAYCOMET 
 * Author URI: https://www.paycomet.com
 * Version: 4.20
 * Tested up to: 5.4
 * WC tested up to: 4.2
 * Text Domain: wc_paytpv
 * Domain Path: /languages
 */


define( 'PAYTPV_VERSION', '4.20' );

define( 'PAYTPV_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PAYTPV_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

define( 'PAYTPV_PLUGIN', __FILE__ );
define( 'PAYTPV_PLUGIN_BASENAME', plugin_basename( PAYTPV_PLUGIN ) );


require_once PAYTPV_PLUGIN_DIR . 'paytpv.php';
require_once PAYTPV_PLUGIN_DIR . 'inc/dependencies.php';
require_once PAYTPV_PLUGIN_DIR . 'inc/upgrade.php';
require_once PAYTPV_PLUGIN_DIR . 'inc/PaytpvApi.php';
require_once PAYTPV_PLUGIN_DIR . 'inc/PaycometApiRest.php';

add_action( 'plugins_loaded', 'woocommerce_paytpv_init', 100 );
add_action( 'admin_enqueue_scripts', array( 'woocommerce_paytpv', 'load_resources_conf' ) );
add_action( 'wp_enqueue_scripts', array( 'woocommerce_paytpv', 'load_resources' ) );

add_action( 'woocommerce_before_my_account', array( 'woocommerce_paytpv', 'get_my_cards_template' ) );

function woocommerce_paytpv_init() {

	/**
	 * Required functions
	 */
	if ( !class_exists( 'WC_Payment_Gateway' ) || !WC_PayTpv_Dependencies::woocommerce_active_check() )
		return;

	load_plugin_textdomain( 'wc_paytpv', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	add_filter( 'woocommerce_payment_gateways', 'add_paytpv_gateway' );

	/**
	 * Add the gateway to woocommerce
	 * */
	function add_paytpv_gateway( $methods ) {
		$methods[ ] = 'woocommerce_paytpv';
		return $methods;
	}

	register_activation_hook( __FILE__, 'paytpv_install' );

	require PAYTPV_PLUGIN_DIR . '/inc/woocommerce-paytpv.php';
	
}


add_action( 'admin_init', 'wppaytpv_upgrade' );

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