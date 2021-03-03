<?php

class Paycomet_EPS extends Paycomet_APM
{
    // Setup our Gateway's id, description and other values
    public function __construct()
    {
        $this->id = 'paycomet_eps';
        $this->icon = PAYTPV_PLUGIN_URL . 'images/eps.png';
        $this->has_fields = false;
        $this->method_title = 'PAYCOMET - EPS';
        $this->method_description = __('Pay with EPS. Configuration is on PAYCOMET main payment method.', 'wc_paytpv' );
        $this->methodId = 20;
        $this->title = __('Pay with EPS', 'wc_paytpv' );
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
