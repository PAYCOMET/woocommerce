<?php

class Paycomet_Instant_Credit extends Paycomet_APM
{
    // Setup our Gateway's id, description and other values
    public function __construct()
    {
        $this->id = 'paycomet_instant_credit';
        $this->icon = PAYTPV_PLUGIN_URL . 'images/instantCredit.png';
        $this->has_fields = false;
        $this->method_title = 'PAYCOMET - InstantCredit';
        $this->method_description = __('Pay with InstantCredit. Configuration is on PAYCOMET main payment method.', 'wc_paytpv' );
        $this->methodId = 33;
        $this->title = __('Pay with InstantCredit', 'wc_paytpv' );

        // // Load the form fields
        $this->init_form_fields();
        $this->init_settings();
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
