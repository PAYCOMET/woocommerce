<form role="form" id="paycometPaymentForm" action="javascript:customFuncion()" method="POST">
    <input type="hidden" data-paycomet="jetID" value="<?php print $jet_id ?>">

    <div class="form-group">
        <label for="username"><?php print __('Full name (on the card)', 'wc_paytpv');?></label>
        <div class="input-group">
            <input type="text" class="form-control" name="username" data-paycomet="cardHolderName" placeholder="" required="" style="height:30px">
        </div>
    </div>

    <div class="form-group">
        <label for="cardNumber"><?php print __('Card number', 'wc_paytpv');?></label>
        <div class="input-group">
            <div id="paycomet-pan" style="padding:0px; height:34px; border: 1px solid #dcd7ca"></div>
            <input paycomet-style="width: 91%; height: 24px; font-size:14px; padding-left:7px; padding-top:8px; border:0px;" paycomet-name="pan">
        </div>
    </div>

    <div class="row">
        <div class="col-sm-8">
            <div class="form-group">
                <label><span class="hidden-xs"><?php print __('Expiration date', 'wc_paytpv');?></span> </label>
                <div class="form-inline">

                    <select class="form-control" style="width:50%; border: 1px solid #dcd7ca;" data-paycomet="dateMonth">
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

                    <select class="form-control" style="width:49%; border: 1px solid #dcd7ca;" data-paycomet="dateYear">
                        <option><?php print __('Year', 'wc_paytpv');?></option>
                        <option value="18">2018</option>
                        <option value="19">2019</option>
                        <option value="20">2020</option>
                        <option value="21">2021</option>
                        <option value="22">2022</option>
                        <option value="23">2023</option>
                        <option value="24">2024</option>
                        <option value="25">205</option>
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
                <input paycomet-name="cvc2" paycomet-style="border:0px; width: 98%; height: 30px; font-size:12px; padding-left:7px; padding-tap:8px; border: 1px solid #dcd7ca;" class="form-control" required="" type="text">
            </div>
        </div>

    </div>
    <button class="subscribe btn btn-primary btn-block" type="submit" id="jetiframe-button"><?php print __('Make payment', 'wc_paytpv');?></button>
</form>

<div id="paymentErrorMsg" style="color: #fff; background: #b22222; margin-top: 10px; text-align: center;">

</div>

<script>
function customFuncion(){
    document.getElementById("jetiframefield").value = document.getElementsByName("paytpvToken")[0].value;
    document.getElementById('place_order').click();
    document.getElementById('jetiframe-button').disabled = false;
}
</script>

<script src="https://api.paycomet.com/gateway/paycomet.jetiframe.js?lang=es"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.8/css/all.css">


