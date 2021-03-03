<?php

class Paycomet_Przelewy24 extends Paycomet_APM
{
    // Setup our Gateway's id, description and other values
    public function __construct()
    {
        $this->id = 'paycomet_przelewy';
        $this->icon = PAYTPV_PLUGIN_URL . 'images/przelewy24.png';
        $this->has_fields = false;
        $this->method_title = 'PAYCOMET - Przelewy24';
        $this->method_description = __('Pay with Przelewy24. Configuration is on PAYCOMET main payment method.', 'wc_paytpv' );
        $this->methodId = 18;
        $this->title = __('Pay with Przelewy24', 'wc_paytpv' );
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
