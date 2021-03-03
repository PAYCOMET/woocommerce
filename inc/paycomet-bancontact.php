<?php

class Paycomet_Bancontact extends Paycomet_APM
{
    // Setup our Gateway's id, description and other values
    public function __construct()
    {
        $this->id = 'paycomet_bancontact';
        $this->icon = PAYTPV_PLUGIN_URL . 'images/bancontact.png';
        $this->has_fields = false;
        $this->method_title = 'PAYCOMET - Bancontact';
        $this->method_description = __('Pay with Bancontact. Configuration is on PAYCOMET main payment method.', 'wc_paytpv' );
        $this->methodId = 19;
        $this->title = __('Pay with Bancontact', 'wc_paytpv' );
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
