<?php

class Paycomet_Bancontact extends Paycomet_APM
{
    // Setup our Gateway's id, description and other values
    public $id;
    public $icon;
    public $has_fields;
    public $method_title;
    public $method_description;
    public $methodId;
    public $title;
    public $description;
    public $supports;
    
    public function __construct()
    {
        $this->id = 'paycomet_bancontact';
        $this->icon = PAYTPV_PLUGIN_URL . 'images/apms/bancontact.svg';
        $this->has_fields = false;
        $this->method_title = 'PAYCOMET - Bancontact';
        $this->method_description = sprintf( __( 'PAYCOMET general data must be configured <a href="%s">here</a>.', 'wc_paytpv' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paytpv' ) );
        $this->methodId = 19;
        $this->title = __('Pay with Bancontact', 'wc_paytpv' );
        $this->description = __('You will be redirected to Bancontact', 'wc_paytpv' );

        $this->supports = array();


        // Load the form fields
        $this->init_form_fields();
        $this->init_settings();

        $this->loadProp();

        if ($this->title == "Paga con Bancontact") {
            $this->title = __( 'Pay with Bancontact', 'wc_paytpv' );
        }
        if ($this->description == "Se te redirigirá a Bancontact") {
            $this->description = __( 'You will be redirected to Bancontact', 'wc_paytpv' );
        }

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    public function process_payment($order_id)
    {
        return parent::payWithAlternativeMethod($order_id, $this->methodId);
    }

    public function can_refund_order($order)
    {
        return parent::canRefundOrder($this->methodId);
    }
}
