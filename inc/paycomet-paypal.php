<?php

class Paycomet_Paypal extends Paycomet_APM
{
    // Setup our Gateway's id, description and other values
    public function __construct()
    {
        $this->id = 'paycomet_paypal';
        $this->icon = PAYTPV_PLUGIN_URL . 'images/paypal.png';
        $this->has_fields = false;
        $this->method_title = 'PAYCOMET - Paypal';
        $this->method_description = __('Pay with Paypal. Configuration is on PAYCOMET main payment method.', 'wc_paytpv' );
        $this->methodId = 10;
        $this->title = __('Pay with Paypal', 'wc_paytpv' );
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
