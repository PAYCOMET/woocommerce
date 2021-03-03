<?php

class Paycomet_WebMoney extends Paycomet_APM
{
    // Setup our Gateway's id, description and other values
    public function __construct()
    {
        $this->id = 'paycomet_webmoney';
        $this->icon = PAYTPV_PLUGIN_URL . 'images/webMoney.png';
        $this->has_fields = false;
        $this->method_title = 'PAYCOMET - WebMoney';
        $this->method_description = __('Pay with WebMoney. Configuration is on PAYCOMET main payment method.', 'wc_paytpv' );
        $this->methodId = 30;
        $this->title = __('Pay with WebMoney', 'wc_paytpv' );
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
