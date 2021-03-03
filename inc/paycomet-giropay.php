<?php

class Paycomet_Giropay extends Paycomet_APM
{
    // Setup our Gateway's id, description and other values
    public function __construct()
    {
        $this->id = 'paycomet_giropay';
        $this->icon = PAYTPV_PLUGIN_URL . 'images/giropay.png';
        $this->has_fields = false;
        $this->method_title = 'PAYCOMET - Giropay';
        $this->method_description = __('Pay with Giropay. Configuration is on PAYCOMET main payment method.', 'wc_paytpv' );
        $this->methodId = 14;
        $this->title = __('Pay with Giropay', 'wc_paytpv' );
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
