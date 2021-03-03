<?php

class Paycomet_MyBank extends Paycomet_APM
{
    // Setup our Gateway's id, description and other values
    public function __construct()
    {
        $this->id = 'paycomet_mybank';
        $this->icon = PAYTPV_PLUGIN_URL . 'images/myBank.png';
        $this->has_fields = false;
        $this->method_title = 'PAYCOMET - MyBank';
        $this->method_description = __('Pay with MyBank. Configuration is on PAYCOMET main payment method.', 'wc_paytpv' );
        $this->methodId = 15;
        $this->title = __('Pay with MyBank', 'wc_paytpv' );
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
