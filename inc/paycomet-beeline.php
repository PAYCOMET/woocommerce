<?php

class Paycomet_Beeline extends Paycomet_APM
{
    // Setup our Gateway's id, description and other values
    public function __construct()
    {
        $this->id = 'paycomet_beeline';
        $this->icon = PAYTPV_PLUGIN_URL . 'images/beeline.png';
        $this->has_fields = false;
        $this->method_title = 'PAYCOMET - Beeline';
        $this->method_description = __('Pay with Beeline. Configuration is on PAYCOMET main payment method.', 'wc_paytpv' );
        $this->methodId = 27;
        $this->title = __('Pay with Beeline', 'wc_paytpv' );
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
