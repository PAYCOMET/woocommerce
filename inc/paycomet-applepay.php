<?php

use Automattic\WooCommerce\Utilities\OrderUtil;

class Paycomet_Applepay extends Paycomet_APM
{
    public $id;
    public $icon;
    public $has_fields;
    public $method_title;
    public $method_description;
    public $methodId;
    public $title;
    public $description;
    public $supports;

    public function __construct()
    {
        $this->id = 'paycomet_applepay';
        $this->icon = PAYTPV_PLUGIN_URL . 'images/apms/applepay.svg';
        $this->has_fields = false;
        $this->method_title = 'PAYCOMET - Apple Pay';
        $this->method_description = sprintf( __( 'PAYCOMET general data must be configured <a href="%s">here</a>.', 'wc_paytpv' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paytpv' ) );
        $this->methodId = 1;
        $this->title = __( 'Pay with Apple Pay', 'wc_paytpv' );
        $this->description = __( 'You will be redirected to Apple Pay', 'wc_paytpv' );

        $this->supports = array( 'refunds' );

        $this->init_form_fields();
        $this->init_settings();
        $this->loadProp();

        if ( $this->title === 'Paga con Apple Pay' ) {
            $this->title = __( 'Pay with Apple Pay', 'wc_paytpv' );
        }
        if ( $this->description === 'Se te redirigirá a Apple Pay' ) {
            $this->description = __( 'You will be redirected to Apple Pay', 'wc_paytpv' );
        }

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
        add_filter( 'woocommerce_payment_successful_result', array( $this, 'forcePayOrderRedirect' ), 10, 2 );
    }

    public function init_form_fields()
    {
        parent::init_form_fields();

        $this->form_fields['button_width'] = array(
            'title'   => __( 'Button width (%)', 'wc_paytpv' ),
            'type'    => 'text',
            'default' => '15',
        );
        $this->form_fields['button_height'] = array(
            'title'   => __( 'Button height', 'wc_paytpv' ),
            'type'    => 'text',
            'default' => '44',
        );
        $this->form_fields['button_color'] = array(
            'title'   => __( 'Button color', 'wc_paytpv' ),
            'type'    => 'select',
            'default' => 'White outline',
            'options' => array(
                'black'         => __( 'Black', 'wc_paytpv' ),
                'white'         => __( 'White', 'wc_paytpv' ),
                'white-outline' => __( 'White outline', 'wc_paytpv' ),
            ),
        );
    }

    public function receipt_page( $order_id )
    {
        $order = wc_get_order( (int) $order_id );

        if ( ! $order || $order->get_payment_method() !== $this->id ) {
            return;
        }

        $stored_html = $this->generate_paytpv_form( $order_id );       

        wp_enqueue_style( 'paytpv.css', PAYTPV_PLUGIN_URL . 'css/paytpv.css', array(), PAYTPV_VERSION );
        wp_enqueue_script( 'jquery' );
        wp_add_inline_script( 'jquery', 'window.$ = window.jQuery;', 'after' );
        wp_enqueue_script(
            'paycomet-applepay',
            PAYTPV_PLUGIN_URL . 'js/applepay.js',
            array( 'jquery' ),
            PAYTPV_VERSION,
            true
        );

        if ( $stored_html ) {
            wp_add_inline_script(
                'paycomet-applepay',
                'window.paycometApplePayStoredHtml=' . wp_json_encode( $stored_html ) . ';',
                'before'
            );
        }

        echo '<div id="paycometApplePayPortal" class="paycomet-applepay-portal">';
        if ( $stored_html ) {
            echo '<p class="paycomet-applepay-loading">' . esc_html__( 'Loading Apple Pay…', 'wc_paytpv' ) . '</p>';
        }
        echo '</div>';
    }

    public function forcePayOrderRedirect( $result, $order_id )
    {
        if ( empty( $result['result'] ) || $result['result'] !== 'success' ) {
            return $result;
        }

        $order = wc_get_order( $order_id );
        if ( $order && $order->get_payment_method() === $this->id && $order->needs_payment() ) {
            $result['redirect'] = $order->get_checkout_payment_url( true );
        }

        return $result;
    }
    

    /**
     * Generate the paytpv button link
     * */
    function generate_paytpv_form($order_id)
    {

        list( $width, $height, $color ) = $this->buttonOptions();

        $paytpvBase = new woocommerce_paytpv( false );

        if ( ! $paytpvBase->settings['apikey'] ) {
            return array(
                'result'   => 'fail',
                'redirect' => '',
            );
        }

        $order = wc_get_order( $order_id );
        $html  = $this->requestApplePayButton(
            $paytpvBase,
            $this->methodId,
            str_pad( $order_id, 8, '0', STR_PAD_LEFT ),
            (int) round( $order->get_total() * 100 ),
            $paytpvBase->paytpv_terminals[0]['moneda'],
            '',
            $width,
            $height,
            $color,
            $order
        );        

        return $html;
    }

    function process_payment($order_id)
    {
        $order = new WC_Order($order_id);

        $result = "success";        

        return array(
            'result' => $result,
            'redirect'	=> $order->get_checkout_payment_url( true )
        );
    }
    
    
    public function can_refund_order( $order )
    {
        return parent::canRefundOrder( $this->methodId );
    }

    private function buttonOptions()
    {
        return array(
            (int) ( $this->settings['button_width'] ?? 15 ),
            (int) ( $this->settings['button_height'] ?? 44 ),
            $this->settings['button_color'] ?? 'white',
        );
    }

    private function requestApplePayButton( $paytpvBase, $methodId, $order_ref, $amount, $currency, $product_description, $width, $height, $color, $order )
    {
        $terminal = (int) $paytpvBase->paytpv_terminals[0]['term'];

        $urlOk = $this->get_return_url($order);
        $paramsUrl = array(
            'order' => $order->get_id(),
            'paycomet_error' => 'payment'
        );
        $urlKo = add_query_arg( $paramsUrl, wc_get_checkout_url() );

        $payment = array(
            'terminal'           => $terminal,
            'methodId'           => (int) $methodId,
            'originalIp'         => (string) $paytpvBase->getIp(),
            'amount'             => (int) $amount,
            'order'              => (string) $order_ref,
            'currency'           => (string) $currency,
            'secure'             => 1,
            'productDescription' => (string) $product_description,
            'userInteraction'    => 1,
            'urlOk'              => (string) $urlOk,
            'urlKo'              => (string) $urlKo,
            'width'              => (int) $width,
            'height'             => (int) $height,
            'color'              => (string) $color,
        );        

        $apiRest     = new PaycometApiRest( $paytpvBase->settings['apikey'] );
        $apiResponse = $apiRest->applePayButton( $terminal, 'ES', 1, $payment );
        $data        = isset( $apiResponse->data ) ? (string) $apiResponse->data : '';

        if ( isset( $apiResponse->success ) && $apiResponse->success === true && isset( $apiResponse->error ) && (int) $apiResponse->error === 0 && $data !== '' ) {
            return $data;
        }

        $error_code = isset( $apiResponse->error ) ? $apiResponse->error : '';
        if ( class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
            $order->update_meta_data( 'ErrorID', $error_code );
            $order->save();
        } else {
            update_post_meta( (int) $order->get_id(), 'ErrorID', $error_code );
        }

        return '';
    }
    
}
