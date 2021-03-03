<?php

class Paycomet_Tele2 extends Paycomet_APM
{
    // Setup our Gateway's id, description and other values
    public function __construct()
    {
        $this->id = 'paycomet_tele2';
        $this->icon = PAYTPV_PLUGIN_URL . 'images/tele2.png';
        $this->has_fields = false;
        $this->method_title = 'PAYCOMET - Tele2';
        $this->method_description = __('Pay with Tele2. Configuration is on PAYCOMET main payment method.', 'wc_paytpv' );
        $this->methodId = 21;
        $this->title = __('Pay with Tele2', 'wc_paytpv' );
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
