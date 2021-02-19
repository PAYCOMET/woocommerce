<?php

class Paycomet_Bizum extends WC_Payment_Gateway
{
    // Setup our Gateway's id, description and other values
    public function __construct()
    {
        $this->id = 'bizum';
        $this->icon = 'https://www.bankia.es/estaticos/Portal-unico/Particulares/Servicios/Servicios/Digital/Adjuntos/Pago_entre_amigos_Bizum/logo_bizum.jpg';
        $this->has_fields = false;
        $this->method_title = 'PAYCOMET - BIZUM';
        $this->method_description = __('Pay with Bizum. Configuration is on PAYCOMET main payment method.', 'wc_paytpv' );
        $this->methodId = 11;

        // Load the form fields
        $this->init_form_fields();
        $this->init_settings();

        $this->paytpvBase = new woocommerce_paytpv();

        $this->title = __('Pay with Bizum', 'wc_paytpv' ); 
        $this->description = 'Paga a través de Bizum';

        $apiRest = new PaycometApiRest($this->paytpvBase->settings['apikey']);

        // $order = new WC_Order($order_id);
        // $terminal = $this->paytpvBase->paytpv_terminals[0]['term'];
        // $amount = number_format($order->get_total() * 100, 0, '.', '');
        // $currency = $this->paytpvBase->paytpv_terminals[0]['moneda'];
        // $ip = $_SERVER['REMOTE_ADDR'];

        // $orderId = str_pad( $order_id, 8, "0", STR_PAD_LEFT );

        // $executePurchaseResponse = $apiRest->executePurchase(
        //     $terminal,
        //     $orderId,
        //     $amount,
        //     $currency,
        //     $this->methodId,
        //     $ip,
        //     1,
        //     '',
        //     '',
        //     'https://www.marca.com',
        //     'https://www.elpais.com',
        //     '0',
        //     '',
        //     '',
        //     1,
        //     [],
        //     '',
        //     '',
        //     //Añadir merchant data aqui debajo
        //     [],
        //     1
        // );

        // if($executePurchaseResponse->errorCode == '0') {
        //     return array(
        //         'result' => 'success',
        //         'redirect'	=> $executePurchaseResponse->challengeUrl
        //     );
        // } else {
        //     wc_add_notice('Se ha producido un error: ' . $executePurchaseResponse->errorCode, 'error' );
        //     return;
        // }
    }

    public function init_form_fields()
    {
        //
    }

    public function payment_fields() 
    {
        //
    }

    public function process_payment($order_id)
    {
        ////FUNCION PARA CHECKEAR QUE TODOS LOS PARÁMETROS DE LA LLAMADA ESTAN BIEN
        ////$this->checkParamsForPaymentMethods();
        if($this->paytpvBase->settings['apikey']) {
            $apiRest = new PaycometApiRest($this->paytpvBase->settings['apikey']);

            $order = new WC_Order($order_id);
            $terminal = $this->paytpvBase->paytpv_terminals[0]['term'];
            $amount = number_format($order->get_total() * 100, 0, '.', '');
            $currency = $this->paytpvBase->paytpv_terminals[0]['moneda'];
            $ip = $_SERVER['REMOTE_ADDR'];

            $orderId = str_pad( $order_id, 8, "0", STR_PAD_LEFT );

            $executePurchaseResponse = $apiRest->executePurchase(
                $terminal,
                $orderId,
                $amount,
                $currency,
                $this->methodId,
                $ip,
                1,
                '',
                '',
                'https://www.marca.com',
                'https://www.elpais.com',
                '0',
                '',
                '',
                1,
                [],
                '',
                '',
                //Añadir merchant data aqui debajo
                [],
                1
            );

            if($executePurchaseResponse->errorCode == '0') {
                return array(
                    'result' => 'success',
                    'redirect'	=> $executePurchaseResponse->challengeUrl
                );
            } else {
                wc_add_notice('Se ha producido un error: ' . $executePurchaseResponse->errorCode, 'error' );
                return;
            }

            return array(
                'result' => 'success',
                'redirect'	=> 'url de redirección que corresponda'
            );
        }
    }
}
