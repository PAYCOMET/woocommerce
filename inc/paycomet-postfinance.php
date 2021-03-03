<?php

class Paycomet_PostFinance extends Paycomet_APM
{
    // Setup our Gateway's id, description and other values
    public function __construct()
    {
        $this->id = 'paycomet_postfinance';
        $this->icon = PAYTPV_PLUGIN_URL . 'images/postfinance.png';
        $this->has_fields = false;
        $this->method_title = 'PAYCOMET - PostFinance';
        $this->method_description = __('Pay with PostFinance. Configuration is on PAYCOMET main payment method.', 'wc_paytpv' );
        $this->methodId = 23;
        $this->title = __('Pay with PostFinance', 'wc_paytpv' );
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
