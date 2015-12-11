<?php
/**
 * My Cards
 */
?>

<div class="woocommerce_paytpv_cards">

	<h2><?php _e( 'My Cards', 'wc_paytpv' ); ?></h2>

	<?php if ( ! empty( $saved_cards ) ) : ?>
		<div class="span6" id="div_tarjetas">
            
            <?php foreach ($saved_cards as $card) :?>  
                <div class="bankstoreCard" id="card_<?php print $card["id"];?>">  
                	<span class="cc"><?php print $card["paytpv_cc"] ." (" . $card["paytpv_brand"].")"?></span>
                    <input type="text" class="card_desc" maxlength="32"  id="card_desc_<?php print $card["id"]?>" name="card_desc_<?php print $card["id"]?>" value="<?php print $card["card_desc"]?>" placeholder="<?php print __("Add a description", 'wc_paytpv')?>">
                    <label class="button_del">
                        <a href="<?php print add_query_arg( array('tpvLstr'=>'saveDesc','id'=>$card["id"],'wc-api'=>'woocommerce_paytpv'), home_url( '/' )  );?>" id="<?php print $card["id"]?>" class="save_desc button  button-smal renew"><?php print __('Save Description', 'wc_paytpv');?></a>  
                        <a href="<?php print add_query_arg( array('tpvLstr'=>'removeCard','id'=>$card["id"],'wc-api'=>'woocommerce_paytpv'), home_url( '/' )  );?>" id="<?php print $card["id"]?>" class="remove_card button renew"><?php print __('Remove', 'wc_paytpv');?></a>
                       
                        <input type="hidden" name="cc_<?php print $card["id"]?>" id="cc_<?php print $card["id"]?>" value="<?php print $card["paytpv_cc"]?>">
                    </label>
                    <HR/>
                </div>
            <?php endforeach; ?>
        </div>

	<?php else : ?>

		<p class="no_cards"><?php printf( __( 'You have no active cars.', 'wc_paytpv' ), '<a href="' . apply_filters( 'woocommerce_subscriptions_message_store_url', get_permalink( woocommerce_get_page_id( 'shop' ) ) ) . '">', '</a>' ); ?></p>

	<?php endif; ?>

	<div id="storingStepUser" class="box">
        <h4><?php print __('STREAMLINE YOUR FUTURE PURCHASES', 'wc_paytpv');?></h4>
        <p><?php print __('Link a card to your account to be able to make all procedures easily and quickly.', 'wc_paytpv');?></p>

        <p class="checkbox">
            <span class="checked"><input type="checkbox" name="savecard" id="savecard"></span>
            <label for="savecard"><?php print __('By linking a card you accept the ', 'wc_paytpv');?><a id="open_conditions" href="#conditions" class="link"><strong><?php print __('terms and conditions of the service', 'wc_paytpv');?></strong></a></label>
        </p>
        <p>
            <a href="javascript:void(0);" onclick="vincularTarjeta();" title="<?php print __('Link card', 'wc_paytpv');?>" id="open_vincular" class="button button-small btn btn-default">
                <span><?php print __('Link card', 'wc_paytpv');?><i class="icon-chevron-right right"></i></span>
            </a>
            <a href="javascript:void(0);" onclick="close_vincularTarjeta();" title="<?php print __('Cancel', 'wc_paytpv');?>" class="button button-small btn btn-default" id="close_vincular" style="display:none">
                <span><?php print __('Cancel', 'wc_paytpv');?><i class="icon-chevron-right right"></i></span>
            </a>
        </p>

        <p id="msg_accept" style="display:none"><?php print __('You must accept the terms and conditions of service', 'wc_paytpv');?></p>
        <p id="msg_descriptionsaved" style="display:none"><?php print __('Description stored successfully', 'wc_paytpv');?></p>

        <p class="payment_module paytpv_iframe" id="nueva_tarjeta" style="display:none">
            <iframe src="<?php print $url_paytpv?>" name="paytpv" style="width: 670px; border-top-width: 0px; border-right-width: 0px; border-bottom-width: 0px; border-left-width: 0px; border-style: initial; border-color: initial; border-image: initial; height: 322px; " marginheight="0" marginwidth="0" scrolling="no"></iframe>
        </p>
    </div>



	<?php
	wc_get_template( 'myaccount/conditions.php', array( ), '', PAYTPV_PLUGIN_DIR . 'template/' );
	?>
	

</div>

<div id="alert" style="display:none">
    <p class="title"></p>
</div>