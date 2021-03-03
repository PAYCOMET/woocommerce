<?php

class Paycomet_Paysafecard extends Paycomet_APM
{
    // Setup our Gateway's id, description and other values
    public function __construct()
    {
        $this->id = 'paycomet_paysafecard';
        $this->icon = PAYTPV_PLUGIN_URL . 'images/paysafecard.png';
        $this->has_fields = false;
        $this->method_title = 'PAYCOMET - PaysafeCard';
        $this->method_description = __('Pay with PaySafeCard. Configuration is on PAYCOMET main payment method.', 'wc_paytpv' );
        $this->methodId = 28;
        $this->title = __('Pay with PaySafeCard', 'wc_paytpv' );
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
