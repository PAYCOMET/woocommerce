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
			PAYTPV_PLUGIN_URL . 'js/index.js',
			['wp-hooks'],
			1,
			true
		);
		return [ 'wc-mygateway-blocks-integration' ];
	}

	public function get_payment_method_data() {

		$saved_cards = Paytpv::savedActiveCards(get_current_user_id());
    	$store_card = (sizeof($saved_cards) == 0) ? "none" : "";
		$disable_offer_savecard = ($this->get_setting( 'disable_offer_savecard' ) == 0 && get_current_user_id() > 0)? true : false;

		$paytpvBase = new woocommerce_paytpv(false); 

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
			'payment_paycomet' => $this->get_setting( 'payment_paycomet' ),
			'getLanguage' => strtolower($paytpvBase->_getLanguange("EN")),
			'saved_cards' => $saved_cards,
			'store_card' => $store_card,
			'disable_offer_savecard' => $disable_offer_savecard,
			'jet_id' => $this->get_setting( 'jet_id' ),
			'text' => array(
				'Card' => __('Card', 'wc_paytpv'),
				'NewCard' => __('NEW CARD', 'wc_paytpv'),
				'ExpirationDate' => __('Expiration date', 'wc_paytpv'),
				'CardNumber' => __('Card number', 'wc_paytpv'),
				'Month' => __('Month', 'wc_paytpv'),
                'January' => __('01 - January', 'wc_paytpv'),
				'February' => __('02 - February', 'wc_paytpv'),
				'March' => __('03 - March', 'wc_paytpv'),
				'April' => __('04 - April', 'wc_paytpv'),
				'May' => __('05 - May', 'wc_paytpv'),
				'June' => __('06 - June', 'wc_paytpv'),
				'July' => __('07 - July', 'wc_paytpv'),
				'August' => __('08 - August', 'wc_paytpv'),
				'September' => __('09 - September', 'wc_paytpv'),
				'October' => __('10 - October', 'wc_paytpv'),
				'November' => __('11 - November', 'wc_paytpv'),
				'December' => __('12 - December', 'wc_paytpv'),
				'Year' => __('Year', 'wc_paytpv'),
				'Pci' => __('Card data is protected by the Payment Card Industry Data Security Standard (PCI DSS)', 'wc_paytpv'),
				'SaveCard' => __('Save card for future purchases', 'wc_paytpv'),
				'MakePayment' => __('Make payment', 'wc_paytpv')
		
            ),
			'pan_div_style' => $this->get_setting('pan_div_style'),
			'pan_input_style' => $this->get_setting('pan_input_style'),
			'cvc2_div_style' => $this->get_setting('cvc2_div_style'),
			'cvc2_input_style' => $this->get_setting('cvc2_input_style')
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
