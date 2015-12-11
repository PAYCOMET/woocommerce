function CheckForm(){
    jQuery("#clockwait").show();
    key = jQuery("#key").val();
    order = jQuery("#order").val();
    wc_api = jQuery("#wc_api").val();
    operation = jQuery("#Operation").val();
    // Check card Test values
    jQuery.post('?key='+key+'&order='+order+'&tpvLstr=checkcard&wc-api='+wc_api, jQuery( "#formulario" ).serialize(), function( data ) {
            if (data.checked==0){
                jQuery("#resp_error").show();
                jQuery("#form_pago").hide();
                jQuery("#clockwait").hide();
            }else{
                // Si es seguro y no es un add_user
                if (data.dsecure==1 && "107"!=operation){
                    parent.location= window.location + '&dsecure=1&MERCHAN_PAN='+jQuery("#merchan_pan").val();
                }else{
                    // Throw notification Test
                    jQuery.post( '?key='+key+'&order='+order+'&tpvLstr=notify&wc-api='+wc_api,jQuery( "#formulario" ).serialize(), function( data ) {
                        // Go to Url OK
                        parent.location=data.urlok;
                    }, "json");
                }
            }
    }, "json");

    return false;
}