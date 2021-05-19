<?php

class Paycomet_Instantcredit extends Paycomet_APM
{
    // Setup our Gateway's id, description and other values
    public function __construct()
    {
        $this->id = 'paycomet_instantcredit';
        $this->icon = PAYTPV_PLUGIN_URL . 'images/apms/instantcredit.svg';
        $this->has_fields = false;
        $this->method_title = 'PAYCOMET - InstantCredit';
        $this->method_description = sprintf( __( 'PAYCOMET general data must be configured <a href="%s">here</a>.', 'wc_paytpv' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paytpv' ) );
        $this->methodId = 33;
        $this->title = __('Pay with Instant Credit', 'wc_paytpv' );
        $this->description = __('Pay with Instant Credit', 'wc_paytpv' );

        $this->supports = array(
            'refunds'
        );


        // Load the form fields
        $this->init_form_fields();
        $this->init_settings();

        $this->loadProp();

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));


        add_action( 'wp_enqueue_scripts', 'enqueue_scripts' );
        add_filter( 'script_loader_tag', 'add_id_to_script', 10, 3 );
        add_action(get_option('woocommerce_myinstantcredit_settings')['calculator_position'], 'show_instantcredit_calculator', get_option('woocommerce_myinstantcredit_settings')['priority_simulator']);
        add_filter( 'woocommerce_gateway_description', 'instantcredit_woocommerce_gateway_description', 10, 2 );        
    }

    function enqueue_scripts() {
        if (!is_product() && !is_checkout() &&!is_cart() ) return;

        $test = 'https://instantcredit.net/simulator/test/ic-simulator.js';
        wp_enqueue_script( 'ic-calculator', $test, array(), '1.0.3', true );
    }

    function add_id_to_script( $tag, $handle, $src ) {
        if ( 'ic-calculator' === $handle ) {
            $tag = '<script type="text/javascript" src="' . esc_url( $src ) . '" id="icCalculator" charset="UTF-8"></script>';
        }
     
        return $tag;
    }

    function show_instantcredit_calculator(){
        if (!is_product() && !is_checkout() && !is_cart() ) return;
        global $product;
        $id = $product->get_id();
        $cfg = get_option('woocommerce_myinstantcredit_settings');
    
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


    function instantcredit_woocommerce_gateway_description( $this_description, $this_id) { 
        global $woocommerce;
        if ('myinstantcredit' !== $this_id || !is_checkout()) {
            return $this_description;
        }
        if (defined('DOING_AJAX') && DOING_AJAX) {
            $result = $this_description;        
            $cart_total = $woocommerce->cart->get_total('');
            $cfg = get_option('woocommerce_myinstantcredit_settings');
    
            if ($cfg) {
                if ( ( $cart_total > $cfg['maxCost'] ) || ( $cart_total < $cfg['minCost'] ) ) return;
                $result.='<div class="ic-configuration" style="display:none;">'.$cfg['simulatorHash'].'</div>';
                $result.='<div class="ic-simulator" amount="'.$cart_total.'"></div>';
                $result.='<script>console.log("dentro");icSimulator = new ICSimulator();icSimulator.loadScript("https://code.jquery.com/jquery-1.12.0.min.js", function(){icSimulator.initialize();});;console.log(icSimulator);</script>';
            }
            
            return $result; 
        }
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
