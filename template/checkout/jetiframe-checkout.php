<?php
    $saved_cards = Paytpv::savedCards(get_current_user_id());
?>

<form role="form" id="paycometPaymentForm" action="javascript:jetIframeValidated()" method="POST">

<div id="saved_cards">
    <div class="form-group">
        <label for="card"><?php print __('Card', 'wc_paytpv'); ?></label>
        <select name="jet_iframe_card" id="jet_iframe_card" onChange="checkSelectedCard()" class="form-control">
            <?php
                foreach ($saved_cards as $card){
                    $card_desc = ($card["card_desc"] != "") ? (" - " . $card["card_desc"]) : "";
            ?>
                <option value="<?= $card['id'] ?>"><?= $card['paytpv_cc'] . $card_desc ?></option>
            <?php
                }
            ?>
                <option value="0"><?php print __('NEW CARD', 'wc_paytpv') ?></option>
        </select>
    </div>
</div>


<div id="toHide" style="display:none;">
    <input type="hidden" data-paycomet="jetID" value="<?php print $jet_id ?>">

    <input type="hidden" class="form-control" name="username" data-paycomet="cardHolderName" placeholder="" value="NONAME" style="height:30px; width: 290px">
    
    <div class="form-group">
        <label for="cardNumber"><?php print __('Card number', 'wc_paytpv');?></label>
        <div class="input-group">
            <div id="paycomet-pan" style="width: 290px; padding:0px; height:34px; border: 1px solid #dcd7ca"></div>
            <input paycomet-style="height: 24px; font-size:14px; padding-left:7px; padding-top:8px; border:0px;" paycomet-name="pan">
        </div>
    </div>

    <div class="row">
        <div class="col-sm-8">
            <div class="form-group">
                <label><span class="hidden-xs"><?php print __('Expiration date', 'wc_paytpv');?></span> </label>
                <div class="form-inline">

                    <select class="form-control" style="width: 142px; border: 1px solid #dcd7ca; font-size: 17px;" data-paycomet="dateMonth">
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

                    <select class="form-control" style="width: 142px; border: 1px solid #dcd7ca; font-size: 17px;" data-paycomet="dateYear">
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

        <div class="col-sm-4">

            <div class="form-group">

                <label data-toggle="tooltip" title=""
                    data-original-title="3 digits code on back side of the card">
                    CVV <i class="fa fa-question-circle"></i>
                </label>

                <div id="paycomet-cvc2" style="height: 45px; padding:0px;"></div>

                <input paycomet-name="cvc2" paycomet-style="border:0px; width: 130px; height: 30px; font-size:12px; padding-left:7px; padding-tap:8px; border: 1px solid #dcd7ca;" class="form-control" required="" type="text">

            </div>
        </div>
    </div>
</div>

    <?php if(get_current_user_id() > 0 && $disable_offer_savecard == 0) { ?>
        <div id="storingStep" class="box" style="display:none;">
            <label class="checkbox"><input type="checkbox" name="jetiframe_savecard" id="jetiframe_savecard"> <?php print __('Save card for future purchases', 'wc_paytpv' ) ?><span class="paytpv-pci"> <?php print __('Card data is protected by the Payment Card Industry Data Security Standard (PCI DSS)', 'wc_paytpv' ) ?></span></label>
        </div>
    <?php
        }
    ?>

    <button style="width: 290px; display:none;" class="subscribe btn btn-primary btn-block" type="submit" id="jetiframe-button"><?php print __('Make payment', 'wc_paytpv');?></button>

</form>

<div id="paymentErrorMsg" style="color: #fff; background: #b22222; margin-top: 10px; text-align: center;">

</div>

<script>
    
//Oculta o muestra el formulario si hay una tarjeta guardada seleccionada
function checkSelectedCard() {
    if (document.getElementById('jet_iframe_card').value != 0){
        document.getElementById('toHide').style.display = "none";
        document.getElementById('storingStep').style.display = "none";
    } else {
        document.getElementById('toHide').style.display = "block";
        document.getElementById('storingStep').style.display = "block";
    }

    document.getElementById('hiddenCardField').value = document.getElementById('jet_iframe_card').value;
};



//Comportamiento cuando se valida el formulario de JetIframe correctamente
function jetIframeValidated(){
    
    if (document.getElementById("jetiframe_savecard") != null) {
        document.getElementById("savecard_jetiframe").checked = document.getElementById("jetiframe_savecard").checked;
    }
    document.getElementById("jetiframe-token").value = document.getElementsByName("paytpvToken")[0].value;

    document.getElementById('place_order').click();
    document.getElementById('jetiframe-button').disabled = false;
}

// formSubmit
jQuery( function( $ ) {    
    $( "#place_order").on('click',function( event ) {
        if ($( '#payment_method_paytpv' ).is( ':checked' )) {
            event.preventDefault();

            new_card = (document.getElementById('jet_iframe_card').value == 0)?true:false;
        
            // New Card
            if (new_card) {
                // jetIframe action
                $("#jetiframe-button").click();            
            }
            if ($( "#jetiframe-token" ).val() != "" || !new_card){
                $('#place_order').submit();
            }
        }
    });

    setTimeout(() => {  checkSelectedCard() }, 100);
});



</script>

<script src="https://api.paycomet.com/gateway/paycomet.jetiframe.js?lang=es"></script>