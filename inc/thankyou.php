<?php
//add_action( 'woocommerce_checkout_order_processed', 'wpdesk_set_completed_for_paid_orders' );
add_action( 'woocommerce_before_thankyou', 'hwn_add_thankyou_error');
add_action( 'woocommerce_order_details_after_order_table', 'hwn_add_thankyou_custom_text');
add_action( 'woocommerce_email_after_order_table', 'hwn_add_thankyou_custom_text');
add_action( 'woocommerce_order_details_before_order_table', 'hwn_add_thankyou_mbway_confirm');

add_action('woocommerce_endpoint_order-received_title',
	function( $title ) {
		global $wp;
		$order_id  = isset( $wp->query_vars['order-received'] ) ? absint( $wp->query_vars['order-received'] ) : 0;
		$order     = wc_get_order( $order_id );

		if ( $order && $order->has_status( 'failed' ) ) {
			return 'Pedido no recibido';
		}

		return $title;
	}
);

function hwn_add_thankyou_custom_text($order) {

    if($order->get_payment_method() == "paycomet_multibanco"){
        if ($order->get_meta("PayTPV_methodData")) {
            $datos = $order->get_meta("PayTPV_methodData");
            if ($datos->entityNumber != '' && $datos->referenceNumber != '') {
                ?>
                    <section id="order-paycomet" class="box">
                        <p>Información de pago de multibanco:</p>
                        <ul style="list-style-type: none;">
                            <li><strong>Entidad: </strong> <?php echo $datos->entityNumber;?></li>
                            <li><strong>Referencia: </strong> <?php echo $datos->referenceNumber;?></li>
                        </ul>
                    </section>
                <?php 
            }
        }
    }
}

function hwn_add_thankyou_mbway_confirm($order) {

    if ($order->get_payment_method() == "paycomet_mbway"){
        ?>
            <p><strong><?php echo sprintf(__( 'You must confirm the purchase on MB WAY, through the notice or in the activity area', 'wc_paytpv'));?></strong></p>  
        <?php
    }
}

/*
function wpdesk_set_completed_for_paid_orders( $order_id ) {

    $order = wc_get_order( $order_id );
    $order->update_status( 'failed' );
    
}
*/

function hwn_add_thankyou_error($order_id) {
    $order = wc_get_order( $order_id );
    // Si tiene asociado error
    if ($order->get_meta("ErrorID") && $order->get_meta("ErrorID") > 0) {
        // Error por defecto
        $error_txt = __( 'An error has occurred. Please verify the data entered and try again', 'wc_paytpv' );
        // Si es 1004 lo mostramos
        if ($order->get_meta("ErrorID") == 1004) {
            $error_txt = __( 'Error: ', 'wc_paytpv' ) . $order->get_meta("ErrorID");
        }
        ?><p><?php echo $error_txt?></p></strong><?php
    }
}



?>