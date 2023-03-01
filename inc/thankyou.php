<?php


add_action( 'woocommerce_order_details_after_order_table', 'hwn_add_thankyou_custom_text');
add_action( 'woocommerce_email_after_order_table', 'hwn_add_thankyou_custom_text');
add_action( 'woocommerce_order_details_before_order_table', 'hwn_add_thankyou_mbway_confirm');


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
?>