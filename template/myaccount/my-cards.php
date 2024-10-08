<?php
// jetIFrame

if (isset($_POST["paytpvToken"])) {

    $token = $_POST["paytpvToken"];
    $user_id = get_current_user_id();

    $id_card= $_POST["id_card"];
    $option= $_POST["option"];

    if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
        //check ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
        //to check ip is pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    $error = false;
    if ($token && strlen($token) == 64) {

        if ($apiKey != '') {

            $notify = 2;

            $apiRest = new PaycometApiRest($apiKey);

            $addUserResponse = $apiRest->addUser(
                $term,
                $token,
                '',
                '',
                'ES',
                $notify
            );

            if ($addUserResponse->errorCode==0) {

                $idUser = $addUserResponse->idUser;
                $tokenUser = $addUserResponse->tokenUser;

                $infoUserResponse = $apiRest->infoUser(
                    $idUser,
                    $tokenUser,
                    $term
                );

                if ($infoUserResponse->errorCode==0) {
                    $result['DS_MERCHANT_PAN'] = $infoUserResponse->pan;
                    $result['DS_CARD_BRAND'] = $infoUserResponse->cardBrand;
                    $result['DS_CARD_EXPIRYDATE'] = $infoUserResponse->expiryDate;
                    $result['DS_TOKENCOF'] = $infoUserResponse->tokenCOF;
                }

                if ($id_card!="" && $option=="tokenization"){
                    

                    $url_mi_cuenta = get_permalink( get_option('woocommerce_myaccount_page_id') );

                    $executePurchaseResponse = $apiRest->executePurchase(
                        $term,
                        $id_card ."_" . rand() . "_tokenization",
                        '50',
                        'EUR',
                        1,
                        $ip,
                        1,
                        $idUser,
                        $tokenUser,
                        $url_mi_cuenta,
                        $url_mi_cuenta,
                        0,
                        '',
                        '',
                        1,
                        [],
                        '',
                        '',
                        1
                    );


                    if ($executePurchaseResponse->errorCode==0) {
                        $salida = $executePurchaseResponse->challengeUrl;
                        if ( ! headers_sent() ) {
                            header('Location: '. $salida);
                            exit;
                        }else{
                            echo '<script type="text/javascript">';
                            echo 'window.location.href="' . $salida . '";';
                            echo '</script>';
                            echo '<noscript>';
                            echo '<meta http-equiv="refresh" content="0;url=' . $salida . '" />';
                            echo '</noscript>';
                            exit;
                        }
                    }else{
                        $error = true;
                    }                    
                }

                if ($id_card!="" && $option=="update"){
                    Paytpv::removeCard($id_card);
                }

            } else {
                $error = true;
            }

        } else {
            $error = true;
        }

        if (!$error) {
            PayTPV::saveCard($user_id, $idUser, $tokenUser, $result['DS_MERCHANT_PAN'], $result['DS_CARD_BRAND'], $result['DS_CARD_EXPIRYDATE'], $result['DS_TOKENCOF']);
            $_POST["paytpvToken"] = '';
            echo "<meta http-equiv='refresh' content='0'>";
        } else {
            print '<div id="paymentErrorMsg" style="color: #fff; background: #b22222; margin-top: 10px; text-align: center; width: 100%; font-size: 20px; padding: 10px;">No se ha podido guardar la tarjeta, por favor inténtelo de nuevo</div>';
        }
    } else {
        print '<div id="paymentErrorMsg" style="color: #fff; background: #b22222; margin-top: 10px; text-align: center; width: 100%; font-size: 20px; padding: 10px;">No se ha podido guardar la tarjeta, por favor inténtelo de nuevo</div>';
    }
}
?>

<?php if ($disable_offer_savecard == 0 ) :?>
<div class="woocommerce_paytpv_cards">

    <h2><?php _e( 'My Cards', 'wc_paytpv' ); ?></h2>

    <div class="span6" id="div_tarjetas">

    <script type="text/javascript">
        // Pasar variable a paytpv.js
        var url_paytpv = "<?php echo $url_paytpv; ?>";

        // formSubmit
        jQuery( function( $ ) {
            if (typeof $.fn.select2 !== 'undefined') {
                $('#paycomet_card_month, #paycomet_card_year').select2();
            }
        });

    </script>

    <?php
        // Procesamos la tarjetas para ver si están asociadas a una suscripcion y agruparlas en "Tarjetas de suscripción"
        $apiRest = new PaycometApiRest($apiKey); $popup = 0;
        foreach ($saved_cards["valid"] as $cardInd => $card) {
            $subscriptions = PayTPV::subscriptionsWithCard($card["paytpv_iduser"]);

            if (count($subscriptions) > 0) {
                $card["expired"] = 0; // Tarjeta no Caducada
                // Si es una tarjeta de suscripcion la mostraremos en el gurpo de tarjetas de suscripcion
                $saved_cards["suscription"][] = $card;

                // Eliminamos la tarjeta de las Validas
                unset($saved_cards["valid"][$cardInd]);
                continue;
            }
        }

        foreach ($saved_cards["invalid"] as $cardInd => $card) {
            $subscriptions = PayTPV::subscriptionsWithCard($card["paytpv_iduser"]);

            if (count($subscriptions) > 0) {
                $card["expired"] = 1; // Tarjeta Caducada
                // Si es una tarjeta de suscripcion la mostraremos en el gurpo de tarjetas de suscripcion
                $saved_cards["suscription"][] = $card;

                // Eliminamos la tarjeta de las Validas
                unset($saved_cards["invalid"][$cardInd]);
                continue;
            }
        }
    ?>


    <table class="my_account_orders woocommerce-orders-table woocommerce-MyAccount-subscriptions shop_table shop_table_responsive">

        <thead>
            <?php if(count($saved_cards["valid"])>0 || count($saved_cards["invalid"])>0 || count($saved_cards["suscription"])>0) { ?>
                <tr>
                    <th class="class="order-number woocommerce-orders-table__header woocommerce-orders-table__header-order-number"><?php print __('Card', 'wc_paytpv');?></th>
                    <th class="class="order-number woocommerce-orders-table__header woocommerce-orders-table__header-order-number"><?php print __('Brand', 'wc_paytpv');?></th>
                    <th class="class="order-number woocommerce-orders-table__header woocommerce-orders-table__header-order-number"><?php print __('Expiry Date', 'wc_paytpv');?></th>
                    <th class="class="order-number woocommerce-orders-table__header woocommerce-orders-table__header-order-number"><?php print __('Description', 'wc_paytpv');?></th>
                    <th class="class="order-number woocommerce-orders-table__header woocommerce-orders-table__header-order-number"></th>
                </tr>
            <?php }else{ ?>
            <p class="no_cards"><?php printf( __( 'You have no active cards.', 'wc_paytpv' ), '<a href="' . apply_filters( 'woocommerce_subscriptions_message_store_url', get_permalink( wc_get_page_id( 'shop' ) ) ) . '">', '</a>' ); ?></p>
            <?php } ?>
        </thead>

        <tbody>

            <?php if(count($saved_cards["valid"])>0) : ?>

                <tr><td colspan="5"><strong><?php _e( 'Available cards', 'wc_paytpv' ); ?></strong></td></tr>

                <?php foreach ($saved_cards["valid"] as $card) :  ?>
                    <tr class="woocommerce-orders-table__row">
                        <td class="woocommerce-orders-table__cell"><?php print $card["paytpv_cc"]?></td>
                        <td class="woocommerce-orders-table__cell"><?php print $card["paytpv_brand"]?></td>
                        <td class="woocommerce-orders-table__cell"><?php print $card["paytpv_expirydate"]?></td>
                        <td class="woocommerce-orders-table__cell">
                            <input type="text" class="card_desc" maxlength="32" id="card_desc_<?php print $card["id"]?>" name="card_desc_<?php print $card["id"]?>" value="<?php print $card["card_desc"]?>" placeholder="<?php print __("Add a description", 'wc_paytpv')?>">
                        </td>

                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-actions">

                            <a href="<?php print add_query_arg( array('tpvLstr'=>'saveDesc','id'=>$card["id"],'wc-api'=>'woocommerce_paytpv'), home_url( '/' )  );?>" id="<?php print $card["id"]?>" class="save_desc woocommerce-button wp-element-button button  renew "><?php print __('Save Description', 'wc_paytpv');?></a>
                            <a href="<?php print add_query_arg( array('tpvLstr'=>'removeCard','id'=>$card["id"],'wc-api'=>'woocommerce_paytpv'), home_url( '/' )  );?>" id="<?php print $card["id"]?>" class="remove_card woocommerce-button wp-element-button button  renew"><?php print __('Remove', 'wc_paytpv');?></a>
                            <input type="hidden" name="cc_<?php print $card["id"]?>" id="cc_<?php print $card["id"]?>" value="<?php print $card["paytpv_cc"]?>">
                        </td>
                    </tr>

                <?php endforeach; ?>
            <?php endif; ?>

            <?php if(count($saved_cards["invalid"])>0) : ?>
                <tr><td colspan="5"><strong><?php _e( 'Inactive cards', 'wc_paytpv' ); ?></strong></td></tr>
                <?php foreach ($saved_cards["invalid"] as $card) : ?>

                    <tr class="woocommerce-orders-table__row">
                        <td class="woocommerce-orders-table__cell"><?php print $card["paytpv_cc"]?></td>
                        <td class="woocommerce-orders-table__cell"><?php print $card["paytpv_brand"]?></td>
                        <td class="woocommerce-orders-table__cell"><?php print $card["paytpv_expirydate"]?></td>
                        <td class="woocommerce-orders-table__cell">
                            <input type="text" class="card_desc" maxlength="32"  id="card_desc_<?php print $card["id"]?>" name="card_desc_<?php print $card["id"]?>" value="<?php print $card["card_desc"]?>" placeholder="<?php print __("Add a description", 'wc_paytpv')?>">
                        </td>

                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-actions">
                        <a href="<?php print add_query_arg( array('tpvLstr'=>'saveDesc','id'=>$card["id"],'wc-api'=>'woocommerce_paytpv'), home_url( '/' )  );?>" id="<?php print $card["id"]?>" class="save_desc woocommerce-button wp-element-button button  renew"><?php print __('Save Description', 'wc_paytpv');?></a>
                            <a href="<?php print add_query_arg( array('tpvLstr'=>'removeCard','id'=>$card["id"],'wc-api'=>'woocommerce_paytpv'), home_url( '/' )  );?>" id="<?php print $card["id"]?>" class="remove_card woocommerce-button wp-element-button button  renew"><?php print __('Remove', 'wc_paytpv');?></a>
                            <a href="<?php print add_query_arg( array('tpvLstr'=>'getUrlIframeExpired','id'=>$card["id"],'wc-api'=>'woocommerce_paytpv'), home_url( '/' )  );?>" class="update woocommerce-button wp-element-button button " id="<?php print __($card["id"])?>"  title="<?php print __('Update', 'wc_paytpv');?>">
                                <span><?php print __('Update', 'wc_paytpv');?><i></i></span>
                            </a>
                            <input type="hidden" name="cc_<?php print $card["id"]?>" id="cc_<?php print $card["id"]?>" value="<?php print $card["paytpv_cc"]?>">
                        </td>
                    </tr>

                <?php endforeach; ?>
            <?php endif; ?>

            <?php if(count($saved_cards["suscription"])>0) : ?>
                <tr><td colspan="5"><strong><?php _e( 'Subscriptions cards', 'wc_paytpv' ); ?></strong></td></tr>
                <?php foreach ($saved_cards["suscription"] as $card) : ?>

                    <?php
                        $subscriptions = PayTPV::subscriptionsWithCard($card["paytpv_iduser"]);

                        $cof=PayTPV::existsCOF($card["paytpv_iduser"],$card["paytpv_tokenuser"]);
                        // Si la tarjeta de suscripción y no tiene COF verifico en PA
                        if( $cof["tokenCOF"] != 1){
                            $infoUserResponse = $apiRest->infoUser(
                                    $card["paytpv_iduser"],
                                    $card["paytpv_tokenuser"],
                                    $term
                            );

                            if ($infoUserResponse->errorCode==0) {
                                $result['DS_TOKENCOF'] = $infoUserResponse->tokenCOF;
                                Paytpv::saveCOF($result['DS_TOKENCOF'],$card["paytpv_iduser"], $card["paytpv_tokenuser"]);
                                $cof["tokenCOF"] = $result['DS_TOKENCOF'];
                            }
                        }else{
                            $result['DS_TOKENCOF'] = $cof["tokenCOF"];
                        }
                    ?>

                    <tr class="woocommerce-orders-table__row">

                        <td class="woocommerce-orders-table__cell"><?php print $card["paytpv_cc"]?></td>
                        <td class="woocommerce-orders-table__cell"><?php print $card["paytpv_brand"]?></td>
                        <td class="woocommerce-orders-table__cell"><?php print $card["paytpv_expirydate"]?></td>
                        <td class="woocommerce-orders-table__cell">
                            <input type="text" class="card_desc" maxlength="32"  id="card_desc_<?php print $card["id"]?>" name="card_desc_<?php print $card["id"]?>" value="<?php print $card["card_desc"]?>" placeholder="<?php print __("Add a description", 'wc_paytpv')?>">
                        </td>

                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-actions">
                        <a href="<?php print add_query_arg( array('tpvLstr'=>'saveDesc','id'=>$card["id"],'wc-api'=>'woocommerce_paytpv'), home_url( '/' )  );?>" id="<?php print $card["id"]?>" class="save_desc woocommerce-button wp-element-button button renew"><?php print __('Save Description', 'wc_paytpv');?></a>
                            <a href="<?php print add_query_arg( array('tpvLstr'=>'removeCard','id'=>$card["id"],'wc-api'=>'woocommerce_paytpv'), home_url( '/' )  );?>" id="<?php print $card["id"]?>" class="remove_card woocommerce-button wp-element-button button renew"><?php print __('Remove', 'wc_paytpv');?></a>
                            <?php
                            if($card["expired"] || $cof["tokenCOF"]== "0"){ ?>

                                <a href="<?php print add_query_arg( array('tpvLstr'=>'getUrlIframe','id'=>$card["id"],'wc-api'=>'woocommerce_paytpv'), home_url( '/' )  );?>" class="tokenizacion woocommerce-button wp-element-button button" id="<?php print __($card["id"])?>"  title="<?php print __('Update', 'wc_paytpv');?>">
                                    <span><?php print __('Update', 'wc_paytpv');?><i></i></span>
                                </a>
                                <?php $popup = 1; ?>
                            <?php } ?>
                            <input type="hidden" name="cc_<?php print $card["id"]?>" id="cc_<?php print $card["id"]?>" value="<?php print $card["paytpv_cc"]?>">
                        </td>
                    </tr>

                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if($popup == 1) {?>
        <div class="woocommerce-notices-wrapper">
            <div id="popup-informativo" class="woocommerce-message" role="alert"
            style="display: none; position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 1000;">
            <?php print __('You have cards associated with a subscription that need to be updated. Press Update on the marked card.', 'wc_paytpv'); ?><br>
            <button class="woocommerce-button wp-element-button button" id="cerrar-popup"><?php print __('Close', 'wc_paytpv'); ?></button>
        </div></div>
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                var popup = document.getElementById('popup-informativo');
                var cerrarPopup = document.getElementById('cerrar-popup');

                // Mostrar el popup después de 1 segundo
                setTimeout(function() {
                    popup.style.display = 'block';
                }, 1000);

                // Cerrar el popup al hacer clic en el botón
                cerrarPopup.addEventListener('click', function() {
                    popup.style.display = 'none';
                });
            });
        </script>
    <?php } ?>

</div>

    <div id="storingStepUser" class="box">
        <p>
            <a href="javascript:void(0);" onclick="vincularTarjeta();" title="<?php print __('Link card', 'wc_paytpv');?>" id="open_vincular" class="button button-small btn btn-default">
                <span><?php print __('Link card', 'wc_paytpv');?><i class="icon-chevron-right right"></i></span>
            </a>
            <a href="javascript:void(0);" onclick="close_vincularTarjeta();" title="<?php print __('Cancel', 'wc_paytpv');?>" class="button button-small btn btn-default" id="close_vincular" style="display:none">
                <span><?php print __('Cancel', 'wc_paytpv');?><i class="icon-chevron-right right"></i></span>
            </a>
            <label>
                <span class="paytpv-pci"><?php print __('Card data is protected by the Payment Card Industry Data Security Standard (PCI DSS)', 'wc_paytpv' );?></span>
            </label>
            <label style="display:none" id="aviso-tokenizacion">
                </br>
                <span><?php print __('You will be charged 0.50 cents that will be returned immediately.', 'wc_paytpv' );?></span>
            </label>
        </p>

        <p id="msg_accept" style="display:none"><?php print __('You must accept save card to continue', 'wc_paytpv');?></p>
        <p id="msg_descriptionsaved" style="display:none"><?php print __('Description stored successfully', 'wc_paytpv');?></p>


        <div class="payment_module paytpv_iframe" id="nueva_tarjeta" style="display:none">
            <?php if ($isJetIframeActive == 0 ) :?>
                <iframe id="ifr-paytpv-container-acount" src="<?php print $url_paytpv?>" name="paytpv" style="min-width: 320px; border-top-width: 0px; border-right-width: 0px; border-bottom-width: 0px; border-left-width: 0px; border-style: initial; border-color: initial; border-image: initial; height: 360px; " marginheight="0" marginwidth="0" scrolling="no" sandbox="allow-top-navigation allow-scripts allow-same-origin allow-forms"></iframe>
            <?php else : ?>
                <form role="form" name="paycometPaymentForm" id="paycometPaymentForm" action="" method="POST">

                    <input type="hidden" name="id_card" id="id_card" >
                    <input type="hidden" name="option" id="option" >
                    <input type="hidden" data-paycomet="jetID" value="<?php print $jet_id ?>">

                    <input type="hidden" class="form-control" name="username" data-paycomet="cardHolderName" placeholder="" value="NONAME" style="height:30px; width: 290px">

                    <div class="form-group">
                        <label for="cardNumber"><?php print __('Card number', 'wc_paytpv');?></label>
                        <div class="input-group">
                            <div id="paycomet-pan" style="<?php print $pan_div_style ?>"></div>
                            <input paycomet-style="<?php print $pan_input_style ?>" paycomet-name="pan">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xs-12 col-md-9">
                            <div class="form-group">
                                <label><span class="hidden-xs"><?php print __('Expiration date', 'wc_paytpv');?></span> </label>
                                <div class="form-inline">

                                    <select id="paycomet_card_month" class="form-control select2" style="height:auto; width: 142px;" data-paycomet="dateMonth">
                                        <option><?php print __('Month', 'wc_paytpv');?></option>
                                        <option value="01"><?php print __('01 - January', 'wc_paytpv');?></option>
                                        <option value="02"><?php print __('02 - February', 'wc_paytpv');?></option>
                                        <option value="03"><?php print __('03 - March', 'wc_paytpv');?></option>
                                        <option value="04"><?php print __('04 - April', 'wc_paytpv');?></option>
                                        <option value="05"><?php print __('05 - May', 'wc_paytpv');?></option>
                                        <option value="06"><?php print __('06 - June', 'wc_paytpv');?></option>
                                        <option value="07"><?php print __('07 - July', 'wc_paytpv');?></option>
                                        <option value="08"><?php print __('08 - August', 'wc_paytpv');?></option>
                                        <option value="09"><?php print __('09 - September', 'wc_paytpv');?></option>
                                        <option value="10"><?php print __('10 - October', 'wc_paytpv');?></option>
                                        <option value="11"><?php print __('11 - November', 'wc_paytpv');?></option>
                                        <option value="12"><?php print __('12 - December', 'wc_paytpv');?></option>
                                    </select>

                                    <select id="paycomet_card_year" class="form-control  select2" style="height:auto; width: 142px;" data-paycomet="dateYear">
                                        <option><?php print __('Year', 'wc_paytpv');?></option>

                                        <?php
                                            $firstYear = (int) date('Y');
                                            for($i = 0; $i <= 8; $i++) { ?>
                                            <option value="<?= substr($firstYear, 2, 2) ?>"><?= $firstYear?></option>
                                        <?php
                                                $firstYear++;
                                            }
                                        ?>
                                    </select>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-md-3">

                            <div class="form-group">

                                <label data-toggle="tooltip" title=""
                                    data-original-title="3 digits code on back side of the card">
                                    CVV <i class="fa fa-question-circle"></i>
                                </label>

                                <div id="paycomet-cvc2" style="<?php print $cvc2_div_style ?>"></div>
                                <input paycomet-name="cvc2" paycomet-style="<?php print $cvc2_input_style ?>" class="form-control" required="" type="text">

                            </div>
                        </div>

                    </div>

                    <br/>

                    <button style="width: 290px;" class="subscribe btn btn-primary btn-block" type="submit" id="jetiframe-button"><?php print __('Save card', 'wc_paytpv');?></button>
                    <script src="https://api.paycomet.com/gateway/paycomet.jetiframe.js?lang=es"></script>
                </form>
            <?php endif; ?>
        </div>
        <div id="paymentErrorMsg" style="color: #fff; background: #b22222; margin-top: 10px; text-align: center; width: 290px; font-size: 20px;"></div>
        <input type="hidden" name="payment_paycomet" id="payment_paycomet" value="<?=$payment_paycomet?>">
    </div>

    <?php
        wc_get_template( 'myaccount/conditions.php', array( ), '', PAYTPV_PLUGIN_DIR . 'template/' );
    ?>

</div>

<div id="alert" style="display:none">
    <p class="title"></p>
</div>

<?php endif; ?>

