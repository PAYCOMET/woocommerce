<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class Paycomet_Block_Support_Paysera extends AbstractPaymentMethodType {

	private $gateway;
	protected $name = 'paycomet_paysera';

	public function initialize() {
		$this->settings = get_option( 'woocommerce_paycomet_paysera_settings', array() );
	}
	
	public function is_active() {
		return true;
	}

	public function get_payment_method_script_handles() {
		wp_register_script(
			'wc-mygateway-blocks-integration',
			PAYTPV_PLUGIN_URL . 'js/index.js',
			array(),
			1,
			true
		);

		return [ 'wc-mygateway-blocks-integration' ];
	}

	public function get_payment_method_data() {
		return [
			'title'       => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),
			'icons'       => $this->get_icons(),
			'supports'    => array(
				'refunds',
			),
			'name' => 'paycomet_paysera'
		];
	}
	private function get_icons() {
		$icon_src = [
			'paysera'       => [
				'src' => PAYTPV_PLUGIN_URL . 'images/apms/paysera.svg',
				'alt' => __( 'Pay with Paysera', 'wc_paytpv' ),
			],
		];
		return $icon_src;
	}

}
