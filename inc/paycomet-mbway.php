<?php

class Paycomet_Mbway extends Paycomet_APM
{
    // Setup our Gateway's id, description and other values
    public function __construct()
    {
        $this->id = 'paycomet_mbway';
        $this->icon = PAYTPV_PLUGIN_URL . 'images/apms/mbway.svg';
        $this->has_fields = false;
        $this->method_title = 'PAYCOMET - MBway';
        $this->method_description = sprintf( __( 'PAYCOMET general data must be configured <a href="%s">here</a>.', 'wc_paytpv' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paytpv' ) );
        $this->methodId = 38;
        $this->title = __('Pay with MB Way', 'wc_paytpv' );
        $this->description = __('You can approve the payment request in the app to complete the order.', 'wc_paytpv' );

        $this->supports = array();
        

        // Load the form fields
        $this->init_form_fields();
        $this->init_settings();

        $this->loadProp();

        if ($this->title == "Paga con MB Way") {
            $this->title = __( 'Pay with MB Way', 'wc_paytpv' );
        }
        if ($this->description == "Podréis aprobar la solicitud de pago en la app para completar el pedido.") {
            $this->description = __( 'You can approve the payment request in the app to complete the order.', 'wc_paytpv' );
        }

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
      
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
