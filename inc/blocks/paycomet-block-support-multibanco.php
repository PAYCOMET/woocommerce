<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class Paycomet_Block_Support_Multibanco extends AbstractPaymentMethodType {

	private $gateway;
	protected $name = 'paycomet_multibanco';

	public function initialize() {
		$this->settings = get_option( 'woocommerce_paycomet_multibanco_settings', array() );
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
			'supports'    => array(),
			'name' => 'paycomet_multibanco'
		];
	}

	private function get_icons() {
		$icon_src = [
			'multibanco'       => [
				'src' => PAYTPV_PLUGIN_URL . 'images/apms/multibanco.svg',
				'alt' => __( 'Pay with Multibanco', 'wc_paytpv' ),
			],
		];
		return $icon_src;
	}
}
