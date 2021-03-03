<?php

class Paycomet_Yandex_Money extends Paycomet_APM
{
    // Setup our Gateway's id, description and other values
    public function __construct()
    {
        $this->id = 'paycomet_yandex';
        $this->icon = PAYTPV_PLUGIN_URL . 'images/yandex.png';
        $this->has_fields = false;
        $this->method_title = 'PAYCOMET - Yandex Money';
        $this->method_description = __('Pay with Yandex Money. Configuration is on PAYCOMET main payment method.', 'wc_paytpv' );
        $this->methodId = 25;
        $this->title = __('Pay with Yandex Money', 'wc_paytpv' );

        // // Load the form fields
        $this->init_form_fields();
        $this->init_settings();
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
