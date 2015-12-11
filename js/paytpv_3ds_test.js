function checkform() {
    if (jQuery("#demopin").val() == "1234") {
        key = jQuery("#key").val();
        order = jQuery("#order").val();
        wc_api = jQuery("#wc_api").val();

        // Throw notification Test
        jQuery.post( '?key='+key+'&order='+order+'&tpvLstr=notify&wc-api='+wc_api, jQuery( "#formulario" ).serialize(), function( data ) {
            parent.location=data.urlok;
        }, "json");
    } else {
        jQuery("#showerror").show();
    }
    return false;
}