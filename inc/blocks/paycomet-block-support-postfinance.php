<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class Paycomet_Block_Support_Postfinance extends AbstractPaymentMethodType {

	private $gateway;
	protected $name = 'paycomet_postfinance';

	public function initialize() {
		$this->settings = get_option( 'woocommerce_paycomet_postfinance_settings', array() );
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
			'name' => 'paycomet_postfinance'
		];
	}

	private function get_icons() {
		$icon_src = [
			'postfinance'       => [
				'src' => PAYTPV_PLUGIN_URL . 'images/apms/postfinance.svg',
				'alt' => __( 'Pay with PostFinance', 'wc_paytpv' ),
			],
		];
		return $icon_src;
	}
}
