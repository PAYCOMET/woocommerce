<?php

use Automattic\WooCommerce\Utilities\OrderUtil;
class Paycomet_APM extends WC_Payment_Gateway
{
    public function __construct()
    {
    }

    public function loadProp() {
        $this->enabled = $this->settings['enabled'];
        $this->title = $this->settings['title'];
        $this->description = $this->settings['description'];

        // Para habiltar el APM tienen que estar definidos los campos obligatorios de paycomet
        $paytpv_settings = get_option('woocommerce_paytpv_settings');
        $paytpv_terminals = get_option('woocommerce_paytpv_terminals');
        if ($paytpv_settings && $paytpv_terminals){
            if ($paytpv_settings["clientcode"] == "" || $paytpv_terminals[0]["term"] == "" || $paytpv_terminals[0]["pass"] == "" ) {
                $this->enabled = false;
            }
        } else {
            $this->enabled = false;
        }
    }


    public function init_form_fields()
    {

        $this->form_fields = array(

            'activation'  => array(
                'description' => __( 'Must be activated from your Paycomet Control Panel <a href="https://dashboard.paycomet.com/cp_control/" target="_blank">here</a>', 'wc_paytpv' ),
                'type'        => 'title',
            ),

            'enabled' => array(
                'title' => __( 'Enable/Disable', 'wc_paytpv' ),
                'label' => __( 'Enable', 'wc_paytpv' ) . " " . $this->method_title,
                'type' => 'checkbox',
                'description' => '',
                'default' => 'no'
            ),
            'title' => array(
                'title' => __( 'Title', 'wc_paytpv' ),
                'type' => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.', 'wc_paytpv' ),
                'default' => $this->title,
                'desc_tip'    => true
            ),
            'description' => array(
                'title' => __( 'Description', 'wc_paytpv' ),
                'type' => 'textarea',
                'class' => 'description',
                'description' => __( 'This controls the description which the user sees during checkout.', 'wc_paytpv' ),
                'default' => $this->description,
                'desc_tip'    => true
            )
        );

    }

    public function payment_fields()
    {
        if ( $this->description)
            echo wpautop( wptexturize( $this->description ) );
    }


    public function payWithAlternativeMethod($order_id, $methodId)
    {
        $paytpvBase = new woocommerce_paytpv(false);

        if($paytpvBase->settings['apikey']) {
            $apiRest = new PaycometApiRest($paytpvBase->settings['apikey']);

            $order = new WC_Order($order_id);
            $terminal = $paytpvBase->paytpv_terminals[0]['term'];
            $amount = number_format($order->get_total() * 100, 0, '.', '');
            $currency = $paytpvBase->paytpv_terminals[0]['moneda'];
            $userInteraction = 1;
            $secure_pay = 1;

            $URLOK = $this->get_return_url($order);
            $URLKO = $order->get_cancel_order_url_raw();

            $orderId = str_pad($order_id, 8, "0", STR_PAD_LEFT);
            $ip = $paytpvBase->getIp();

            $apiResponse = $apiRest->executePurchase(
                $terminal,
                $orderId,
                $amount,
                $currency,
                $methodId,
                $ip,
                (int) $secure_pay,
                '',
                '',
                $URLOK,
                $URLKO,
                '',
                '',
                '',
                (int) $userInteraction,
                [],
                '',
                '',
                $paytpvBase->getMerchantData($order),
                1
            );

            if($apiResponse->errorCode == '0') {
                if ( class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
                    $order->add_meta_data('PayTPV_methodData', $apiResponse->methodData);
                    $order->save();
                } else {
                    update_post_meta( ( int ) $order->get_id(), 'PayTPV_methodData', $apiResponse->methodData);
                }


                // Multibanco mostrarmos en el pedido los datos
                if (isset($apiResponse->methodData->entityNumber) && isset($apiResponse->methodData->referenceNumber)) {
                    if ( class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
                        $order->add_meta_data('entityNumber', $apiResponse->methodData->entityNumber);
                        $order->add_meta_data('referenceNumber', $apiResponse->methodData->referenceNumber);
                        $order->save();
                    } else {
                        update_post_meta( ( int ) $order->get_id(), 'entityNumber', $apiResponse->methodData->entityNumber);
                        update_post_meta( ( int ) $order->get_id(), 'referenceNumber', $apiResponse->methodData->referenceNumber);
                    }
                }
                
                return array(
                    'result' => 'success',
                    'redirect'	=> $apiResponse->challengeUrl
                );
            } else {
                if ($apiResponse->errorCode==1004) {
                    wc_add_notice(__( 'An error has occurred: ', 'wc_paytpv' ) . $apiResponse->errorCode, 'error' );
                } else {
				    wc_add_notice(__( 'An error has occurred. Please verify the data entered and try again', 'wc_paytpv' ));
                }
                $paytpvBase->write_log('Error ' . $apiResponse->errorCode . " en APM executePurchase");
                return;
            }
        } else {
            $error_txt = __( 'Error: ', 'wc_paytpv' ) . "1004";
            wc_add_notice($error_txt, 'error');
            return;
        }
    }

    public function process_payment($order_id)
    {
        //
    }

    public function canRefundOrder($methodId)
    {
        return (in_array('refunds',$this->supports))?true:false;
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
        $paytpvBase = new woocommerce_paytpv(false);
        $apiKey = $paytpvBase->settings['apikey'];
        $order = wc_get_order( $order_id );
        $ip = $_SERVER['REMOTE_ADDR'];
        $userTerminal = $paytpvBase->paytpv_terminals[0]['term'];
        $currency = $paytpvBase->paytpv_terminals[0]['moneda'] ?? 'EUR';
        $importe = number_format($amount * 100, 0, '.', '');
        if ( class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
            $orderReference = $order->get_meta('PayTPV_Referencia', true);
        } else {
            $orderReference = get_post_meta((int) $order->get_id(), 'PayTPV_Referencia', true);
        }
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
