<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class WC_MyGateway_Blocks extends AbstractPaymentMethodType {

	private $gateway;
	protected $name = 'Bizum';

	public function initialize() {
		$this->settings = get_option( 'woocommerce_dummy_settings', [] );
		$this->gateway = new Paycomet_Bizum();
		//print_r($this->get_setting( 'description' ));
	}

	public function is_active() {
		return true;
	}

	public function get_payment_method_script_handles() {
		wp_register_script(
			'wc-mygateway-blocks-integration',
			PAYTPV_PLUGIN_URL . 'js/blocks.js',
			[],
			null,
			true
		);

		return [ 'wc-mygateway-blocks-integration' ];
	}

	public function get_payment_method_data() {
		return [
			'title'       => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),
		];
	}

}