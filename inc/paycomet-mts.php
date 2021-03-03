<?php

class Paycomet_MTS extends Paycomet_APM
{
    // Setup our Gateway's id, description and other values
    public function __construct()
    {
        $this->id = 'paycomet_mts';
        $this->icon = PAYTPV_PLUGIN_URL . 'images/mts.png';
        $this->has_fields = false;
        $this->method_title = 'PAYCOMET - MTS';
        $this->method_description = __('Pay with MTS. Configuration is on PAYCOMET main payment method.', 'wc_paytpv' );
        $this->methodId = 26;
        $this->title = __('Pay with MTS', 'wc_paytpv' );
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
