<?php

add_action( 'woocommerce_order_details_after_order_table', 'hwn_add_thankyou_custom_text', $order_id);
add_action( 'woocommerce_email_after_order_table', 'hwn_add_thankyou_custom_text', $order_id);
add_action( 'woocommerce_order_details_before_order_table', 'hwn_add_thankyou_mbway_confirm', $order_id);

function hwn_add_thankyou_custom_text($order_id) {
    if ($order_id->meta_data[1]->value->entityNumber != '' && $order_id->meta_data[1]->value->referenceNumber != '') { 
    ?>
        <section id="order-paycomet" class="box" style="display: {$display}">
            <p>Información de pago de multibanco:</p>
            <ul style="list-style-type: none;">
                <li><strong>Entidad: </strong> <?php echo $order_id->meta_data[1]->value->entityNumber;?></li>
                <li><strong>Referencia: </strong> <?php echo $order_id->meta_data[1]->value->referenceNumber;?></li>
            </ul>
        </section>
    <?php 
    }  
 }
 function hwn_add_thankyou_mbway_confirm($order_id) {
    if ($order_id->payment_method == "paycomet_mbway"){
        ?>
            <p><strong><?php echo sprintf(__( 'You must confirm the purchase on MB WAY, through the notice or in the activity area', 'wc_paytpv'));?></strong></p>  
        <?php
    }
 }
?>