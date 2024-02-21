<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class Paycomet_Block_Support_Paytpv extends AbstractPaymentMethodType {

	private $gateway;
	protected $name = 'paytpv';

	public function initialize() {
		$this->settings = get_option( 'woocommerce_paytpv_settings', array() );
	}

	public function is_active() {
		return true;
	}

	public function get_payment_method_script_handles() {
		
		wp_register_script(
			'wc-mygateway-blocks-integration',
			PAYTPV_PLUGIN_URL . 'wordpress/build/index.js',
			array(),
			1,
			true
		);
		return [ 'wc-mygateway-blocks-integration' ];
	}

	public function get_payment_method_data() {

		$saved_cards = Paytpv::savedActiveCards(get_current_user_id());
    	$store_card = (sizeof($saved_cards) == 0) ? "none" : "";

		return [
			'title'       => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),
			'icons'       => $this->get_icons(),
			'supports'    => array(
				'products',
				'refunds',
				'subscriptions',
				'subscription_cancellation',
				'subscription_suspension',
				'subscription_reactivation',
				'subscription_amount_changes',
				'subscription_date_changes'
			),
			'name' => 'paytpv',
			'jetiframe' => $this->get_setting( 'payment_paycomet' ),
			'saved_cards' => $saved_cards,
			'store_card' => $store_card,
			'jet_id' => $this->get_setting( 'jet_id' )
		];
	}

	private function get_icons() {
		$icon_src = [
			'visa'       => [
				'src' => PAYTPV_PLUGIN_URL . 'images/apms/paycomet.svg',
				'alt' => __( 'Pay with card', 'wc_paytpv' ),
			],
		];
		return $icon_src;
	}
}
