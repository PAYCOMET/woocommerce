<?php

// Verify if enabled
if (!isset(get_option('woocommerce_paycomet_instantcredit_settings')['enabled']) || get_option('woocommerce_paycomet_instantcredit_settings')['enabled']!="yes") {
    return;
}

add_action( 'wp_enqueue_scripts', 'woocommerce_paycomet_instantcredit_enqueue_scripts' );

function woocommerce_paycomet_instantcredit_enqueue_scripts() {
    if (!is_product() && !is_checkout() && !is_cart() ) return;
    if (
        isset(get_option('woocommerce_paycomet_instantcredit_settings')['simulatorEnvironment']) && 
        get_option('woocommerce_paycomet_instantcredit_settings')['simulatorEnvironment'] == "test"
    ){
        $ic_simulator = 'https://instantcredit.net/simulator/test/ic-simulator.js';      // Test
    } else {
        $ic_simulator = 'https://instantcredit.net/simulator/ic-simulator.js';      // Prod
    }
    wp_enqueue_script( 'woocommerce_paycomet_instantcredit-ic-calculator', $ic_simulator, array(), '1.1.1', true );
}

add_filter( 'script_loader_tag', 'woocommerce_paycomet_instantcredit_add_id_to_script', 10, 3 );
function woocommerce_paycomet_instantcredit_add_id_to_script( $tag, $handle, $src ) {
    if ( 'woocommerce_paycomet_instantcredit-ic-calculator' === $handle ) {
        $tag = '<script type="text/javascript" src="' . esc_url( $src ) . '" id="icCalculator" charset="UTF-8"></script>';
    }

    return $tag;
}

add_filter( 'woocommerce_available_payment_gateways', 'available_paycomet_myinstantcredit_gateway' );

function available_paycomet_myinstantcredit_gateway( $available_gateways ) {
    $cfg = get_option('woocommerce_paycomet_instantcredit_settings');

    if ( (isset($cfg['maxCost']) && isset(WC()->cart) && WC()->cart->total > $cfg['maxCost'] ) || 
         (isset($cfg['minCost']) && isset(WC()->cart) && WC()->cart->total < $cfg['minCost'])
    ) {
        unset( $available_gateways['paycomet_instantcredit'] );
    }
    return $available_gateways;
}

add_action(get_option('woocommerce_paycomet_instantcredit_settings')['calculator_position'], 'woocommerce_paycomet_instantcredit_show_instantcredit_calculator', get_option('woocommerce_paycomet_instantcredit_settings')['priority_simulator']);

function woocommerce_paycomet_instantcredit_show_instantcredit_calculator(){

    if (!is_product() && !is_checkout() && !is_cart() ) return;
    global $product;
    $id = $product->get_id();
    $cfg = get_option('woocommerce_paycomet_instantcredit_settings');

    // 1. Variable products
    if( $product->is_type('variable') ){
        // Searching for the default variation
        $default_attributes = $product->get_default_attributes();
        // Loop through available variations
        foreach($product->get_available_variations() as $variation){
            $found = true; // Initializing
            // Loop through variation attributes
            foreach( $variation['attributes'] as $key => $value ){
                $taxonomy = str_replace( 'attribute_', '', $key );
                // Searching for a matching variation as default
                if( isset($default_attributes[$taxonomy]) && $default_attributes[$taxonomy] != $value ){
                    $found = false;
                    break;
                }
            }
            // When it's found we set it and we stop the main loop
            if( $found ) {
                $default_variaton = $variation;
                break;
            } // If not we continue
            else {
                continue;
            }
        }
        // Get the default variation prices or if not set the variable product min prices
        $regular_price = isset($default_variaton) ? $default_variaton['display_price']: $product->get_variation_regular_price( 'min', true );
        $sale_price = isset($default_variaton) ? $default_variaton['display_regular_price']: $product->get_variation_sale_price( 'min', true );

    } else {
        $regular_price = $product->get_price();

    }

    if ( ( $product->get_price() > $cfg['maxCost'] ) || ( $product->get_price() < $cfg['minCost'] ) ) return;
    ?>

    <div class="ic-configuration" style="display:none;"><?php echo $cfg['simulatorHash'];?></div>
    <div class="ic-simulator" amount="<?php echo $regular_price;?>"></div>
    <script type="text/javascript">
        jQuery(function($) {
            $( ".single_variation_wrap" ).on( "show_variation", function ( event, variation ) {
                var id_product = $(event.delegateTarget).find('input[name="product_id"]').val();
                if (id_product == <?php echo $product->get_id();?>){
                    $('.ic-simulator').attr("amount", variation.display_price);
                    icSimulator.initialize();
                }
            } );
        });
    </script>
    <?php
}


class Paycomet_Instantcredit extends Paycomet_APM
{
    // Setup our Gateway's id, description and other values
    public function __construct()
    {
        $this->id = 'paycomet_instantcredit';
        $this->icon = PAYTPV_PLUGIN_URL . 'images/apms/instantcredit02.svg';
        $this->has_fields = false;
        $this->method_title = 'PAYCOMET - Instant Credit';
        $this->method_description = sprintf( __( 'PAYCOMET general data must be configured <a href="%s">here</a>.', 'wc_paytpv' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paytpv' ) );
        $this->methodId = 33;
        $this->title = __('Instant installment payment', 'wc_paytpv' );
        $this->description = __('Quick and paperless process with the confidence of Banco Sabadell. Have your ID at hand to process the financing.', 'wc_paytpv' );

        $this->supports = array(
            'refunds'
        );


        // Load the form fields
        $this->init_form_fields();
        $this->init_settings();

        $this->loadProp();

        if ($this->title == "Pago a plazos instantáneo") {
            $this->title = __( 'Instant installment payment', 'wc_paytpv' );
        }
        if ($this->description == "Proceso rápido y sin papeles con la confianza de Banco Sabadell. Ten a mano tu DNI para tramitar la financiación.") {
            $this->description = __( 'Quick and paperless process with the confidence of Banco Sabadell. Have your ID at hand to process the financing.', 'wc_paytpv' );
        }

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

    }

    function payment_fields(){
        global $woocommerce;
        if (defined('DOING_AJAX') && DOING_AJAX) {
            $result = wpautop( wptexturize( $this->description ) );
            print $result;

            $cart_total = $woocommerce->cart->get_total('');
            $cfg = get_option('woocommerce_paycomet_instantcredit_settings');
            if ($cfg) {

                if ( !isset($cfg['maxCost']) || ( $cart_total > $cfg['maxCost'] ) || !isset($cfg['minCost']) || ( $cart_total < $cfg['minCost'] ) ) return;

                $result ='<div class="ic-configuration" style="display:none;">'.$cfg['simulatorHash'].'</div>';
                $result.='<div class="ic-simulator" amount="'.$cart_total.'"></div>';
                $result.='<script>console.log("dentro");icSimulator = new ICSimulator();icSimulator.loadScript("https://code.jquery.com/jquery-1.12.0.min.js", function(){icSimulator.initialize();});;console.log(icSimulator);</script>';

            }
            print $result;
        }
    }


    function init_form_fields(){

        parent::init_form_fields();

        $this->form_fields_instantcredit = array(

            'simulatorEnvironment' => array(
                'title' => __( 'Simulator Environment', 'wc_paytpv' ),
                'type' => 'select',
                'options'     => array(
                    'production' => __('Production', 'wc_paytpv' ),
                    'test' => __('Test', 'wc_paytpv' )
                ),
                'default' => 'production'
            ),
            'simulatorHash' => array(
                'title' => __( 'Simulator Hash', 'wc_paytpv' ),
                'type' => 'password',
                'description' => __( 'Please enter your Instant Credit Simulator Hash.', 'wc_paytpv' ),
                'default' => ''
            ),
            'calculator_position' => array(
                    'title' => __( 'Simulator position', 'wc_paytpv' ),
                    'type' => 'select',
                    'options'     => array(
                        'woocommerce_before_add_to_cart_quantity' => __('before_add_to_cart_quantity', 'wc_paytpv' ),
                        'woocommerce_after_add_to_cart_quantity' => __('after_add_to_cart_quantity', 'wc_paytpv' ),
                        'woocommerce_after_add_to_cart_button' => __('after_add_to_cart_button', 'wc_paytpv' ),
                        'woocommerce_before_add_to_cart_form' => __('before_add_to_cart_form', 'wc_paytpv' ),
                        'woocommerce_before_single_product_summary' => __('before_single_product_summary', 'wc_paytpv' ),
                        'woocommerce_after_single_product_summary' => __('after_single_product_summary', 'wc_paytpv' ),
                        'woocommerce_before_single_product' => __('before_single_product', 'wc_paytpv' ),
                        'woocommerce_single_product_summary' => __('single_product_summary', 'wc_paytpv' ),
                        'woocommerce_single_variation' => __('single_variation', 'wc_paytpv' ),
                        'woocommerce_before_variations_form' => __('before_variations_form', 'wc_paytpv' ),
                        'woocommerce_before_single_variation' => __('before_single_variation', 'wc_paytpv' )
                    ),
                    'description' => __( 'Select position to show Instant Credit calculator','wc_paytpv'),
                    'desc_tip'    => true,
                    'default'     => 'woocommerce_before_add_to_cart_quantity'
            ),
            'priority_simulator' => array(
                    'title' => __( 'Priority simulator', 'wc_paytpv' ),
                    'type' => 'select',
                    'options'     => array(
                        '1' => __('1', 'wc_paytpv' ),
                        '2' => __('2', 'wc_paytpv' ),
                        '3' => __('3', 'wc_paytpv' ),
                        '4' => __('4', 'wc_paytpv' ),
                        '5' => __('5', 'wc_paytpv' ),
                        '6' => __('6', 'wc_paytpv' ),
                        '7' => __('7', 'wc_paytpv' ),
                        '8' => __('8', 'wc_paytpv' ),
                        '9' => __('9', 'wc_paytpv' ),
                        '10' => __('10', 'wc_paytpv' ),
                        '11' => __('11', 'wc_paytpv' ),
                        '12' => __('12', 'wc_paytpv' ),
                        '13' => __('13', 'wc_paytpv' ),
                        '14' => __('14', 'wc_paytpv' ),
                        '15' => __('15', 'wc_paytpv' ),
                        '16' => __('16', 'wc_paytpv' ),
                        '17' => __('17', 'wc_paytpv' ),
                        '18' => __('18', 'wc_paytpv' ),
                        '19' => __('19', 'wc_paytpv' ),
                        '20' => __('20', 'wc_paytpv' ),
                    ),
                    'description' => __( 'Select priority of Instant Credit calculator','wc_paytpv'),
                    'desc_tip'    => true,
                    'default'     => '10'
            ),
            'minCost' => array(
                'title'       => __( 'Min. cart price', 'wc_paytpv' ),
                'type'        => 'text',
                'description' => __( 'Minimun purchase value.', 'wc_paytpv' ),
                'default'     => '28',
                'desc_tip'    => true,
            ),
            'maxCost' => array(
                'title'       => __( 'Max. product price', 'wc_paytpv' ),
                'type'        => 'text',
                'description' => __( 'Maximun purchase value.', 'wc_paytpv' ),
                'default'     => '5000000',
                'desc_tip'    => true,
            ),
        );

        $this->form_fields = array_merge($this->form_fields,$this->form_fields_instantcredit);
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

