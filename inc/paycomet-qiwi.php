<?php

class Paycomet_QIWI_Wallet extends Paycomet_APM
{
    // Setup our Gateway's id, description and other values
    public function __construct()
    {
        $this->id = 'paycomet_qiwi';
        $this->icon = PAYTPV_PLUGIN_URL . 'images/qiwi.png';
        $this->has_fields = false;
        $this->method_title = 'PAYCOMET - QIWI Wallet';
        $this->method_description = __('Pay with QIWI Wallet. Configuration is on PAYCOMET main payment method.', 'wc_paytpv' );
        $this->methodId = 24;
        $this->title = __('Pay with QIWI Wallet', 'wc_paytpv' );
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
