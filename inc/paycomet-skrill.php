<?php

class Paycomet_Skrill extends Paycomet_APM
{
    // Setup our Gateway's id, description and other values
    public function __construct()
    {
        $this->id = 'paycomet_skrill';
        $this->icon = PAYTPV_PLUGIN_URL . 'images/skrill.png';
        $this->has_fields = false;
        $this->method_title = 'PAYCOMET - Skrill';
        $this->method_description = __('Pay with Skrill. Configuration is on PAYCOMET main payment method.', 'wc_paytpv' );
        $this->methodId = 29;
        $this->title = __('Pay with Skrill', 'wc_paytpv' );
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
