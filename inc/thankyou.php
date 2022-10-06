<?php

if (isset($order_id)) {
    add_action( 'woocommerce_order_details_after_order_table', 'hwn_add_thankyou_custom_text', $order_id);
    add_action( 'woocommerce_email_after_order_table', 'hwn_add_thankyou_custom_text', $order_id);
}

function hwn_add_thankyou_custom_text($order_id) {
    // $order_id = wc_get_order_id_by_order_key( $_GET[ 'key' ] );
 
     //$PayTPV_referenceNumber = get_post_meta((int) $order->get_id(), 'PayTPV_referenceNumber', true);
    
     ?>
     <?php if ($order_id->meta_data[1]->value->entityNumber != '' && $order_id->meta_data[1]->value->referenceNumber != '') { ?>
     <section id="order-paycomet" class="box" style="display: {$display}">
         <p>Información de pago de multibanco:</p>
         <ul style="list-style-type: none;">
             <li><strong>Entidad: </strong> <?php echo $order_id->meta_data[1]->value->entityNumber;?></li>
             <li><strong>Referencia: </strong> <?php echo $order_id->meta_data[1]->value->referenceNumber;?></li>
         </ul>
     </section>
     <?php } 
 }

?>