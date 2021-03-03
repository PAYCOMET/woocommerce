<?php

abstract class Paycomet_APM extends WC_Payment_Gateway
{
    public function __construct()
    {
        $this->supports = array(
            'refunds'
        );

        $this->init_form_fields();
        $this->init_settings();
    }

    public function init_form_fields()
    {
        //
    }

    public function payment_fields() 
    {
        //
    }

    public function payWithAlternativeMethod($order_id, $methodId)
    {
        $paytpvBase = new woocommerce_paytpv();
        
        if($paytpvBase->settings['apikey']) {
            $apiRest = new PaycometApiRest($paytpvBase->settings['apikey']);

            $order = new WC_Order($order_id);
            $terminal = $paytpvBase->paytpv_terminals[0]['term'];
            $amount = number_format($order->get_total() * 100, 0, '.', '');
            $currency = $paytpvBase->paytpv_terminals[0]['moneda'];
            $ip = $_SERVER['REMOTE_ADDR'];

            $URLOK = $this->get_return_url($order);
            $URLKO = $order->get_cancel_order_url_raw();

            $orderId = str_pad($order_id, 8, "0", STR_PAD_LEFT);

            $executePurchaseResponse = $apiRest->executePurchase(
                $terminal,
                $orderId,
                $amount,
                $currency,
                $methodId,
                $ip,
                1,
                '',
                '',
                $URLOK,
                $URLKO,
                '0',
                '',
                '',
                1,
                [],
                '',
                '',
                $paytpvBase->getMerchantData($order),
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

    public function process_payment($order_id)
    {
        //
    }

    public function canRefundOrder($methodId)
    {
        $paytpvBase = new woocommerce_paytpv();
        $apiKey = $paytpvBase->settings['apikey'];
        $userTerminal = $paytpvBase->paytpv_terminals[0]['term'];

        if($apiKey == '') {
            return false;
        }

        $apiRest = new PaycometApiRest($apiKey);
	    $userPaymentMethods = $apiRest->getUserPaymentMethods($userTerminal);

        foreach ($userPaymentMethods as $paymentMethod) {
            if($paymentMethod->id == $this->methodId) {
                return (bool) $paymentMethod->allowAPIRefunds;
            }
        }

        return false;
    }

    /**
     * Process a refund if supported
     * @param  int $order_id
     * @param  float $amount
     * @param  string $reason
     * @return  boolean True or false based on success, or a WP_Error object
     */
    public function process_refund($order_id, $amount = null, $reason = '')
    {
        $paytpvBase = new woocommerce_paytpv();
        $apiKey = $paytpvBase->settings['apikey'];
        $order = wc_get_order( $order_id );
        $ip = $_SERVER['REMOTE_ADDR'];
        $userTerminal = $paytpvBase->paytpv_terminals[0]['term'];
        $currency = $paytpvBase->paytpv_terminals[0]['moneda'] ?? 'EUR';
        $importe = number_format($amount * 100, 0, '.', '');
        $orderReference = get_post_meta((int) $order->get_id(), 'PayTPV_Referencia', true);
        $transaction_id = $order->get_transaction_id();
        $notifyDirectPayment = 2; // No notificar HTTP

        $apiRest = new PayCometApiRest($apiKey);
        $executeRefundReponse = $apiRest->executeRefund(
            $orderReference,
            $userTerminal,
            $importe,
            $currency,
            $transaction_id,
            $ip,
            $notifyDirectPayment
        );

        $result["DS_RESPONSE"] = ($executeRefundReponse->errorCode > 0) ? 0 : 1;
        $result["DS_ERROR_ID"] = $executeRefundReponse->errorCode;

        if ($executeRefundReponse->errorCode == 0) {
            $result['DS_MERCHANT_AUTHCODE'] = $executeRefundReponse->authCode;
        }

        if ((int) $result['DS_RESPONSE'] != 1) {
            $order->add_order_note('Refund Failed. Error: ' . $result['DS_ERROR_ID']);

            return false;
        } else {
            $order->add_order_note( sprintf( __('Refunded %s - Refund ID: %s', 'woocommerce'), $amount, $result['DS_MERCHANT_AUTHCODE']));

            return true;
        }
    }
}
