<?php

class Paycomet_Klarna_Payments extends Paycomet_APM
{
    // Setup our Gateway's id, description and other values
    public function __construct()
    {
        $this->id = 'paycomet_klarna';
        $this->icon = PAYTPV_PLUGIN_URL . 'images/klarna.png';
        $this->has_fields = false;
        $this->method_title = 'PAYCOMET - Klarna';
        $this->method_description = __('Pay with Klarna. Configuration is on PAYCOMET main payment method.', 'wc_paytpv' );
        $this->methodId = 13;
        $this->title = __('Pay with Klarna', 'wc_paytpv' );
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
