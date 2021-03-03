<?php

class Paycomet_Paysera extends Paycomet_APM
{
    // Setup our Gateway's id, description and other values
    public function __construct()
    {
        $this->id = 'paycomet_paysera';
        $this->icon = PAYTPV_PLUGIN_URL . 'images/paysera.png';
        $this->has_fields = false;
        $this->method_title = 'PAYCOMET - Paysera';
        $this->method_description = __('Pay with Paysera. Configuration is on PAYCOMET main payment method.', 'wc_paytpv' );
        $this->methodId = 22;
        $this->title = __('Pay with Paysera', 'wc_paytpv' );
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
