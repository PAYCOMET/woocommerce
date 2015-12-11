// Admin Plugin
jQuery(function($) {
    $("#woocommerce_paytpv_environment").on('change',function(){
        checkenvironment();
    })

    $('#paytpv_terminals').on( 'click', 'a.add', function(){
        var size = $('#paytpv_terminals').find('tbody .account').size();
        var lasttr=$('.tblterminals tbody tr:first').clone();
        lasttr.find('select').val(0);
        lasttr.find('input').val("");
        $('.tblterminals tbody').append(lasttr);         

        checkaddTerminal();

        return false;
    });

    $('.remove_rows.button').on( 'click', function(e){
        checkaddTerminal();   

    });

    $('.wc_input_table .remove_term').click( function() {
        if ($(".term").length==1){
            alert($("#msg_1terminal").html());
            return false;
        }else{

            var $tbody = $( this ).closest( '.wc_input_table' ).find( 'tbody' );
            if ( $tbody.find( 'tr.current' ).size() > 0 ) {
                var $current = $tbody.find( 'tr.current' );
                $current.each( function() {
                    $( this ).remove();
                });
            }
            checkaddTerminal();
            return false;
        }
    });

    var previous;

    $('#paytpv_terminals').on('focus', '.moneda', function () {
        // Store the current value on focus and on change
        previous = $(this).val();
        moneda = $(this);
    }).on('change',function() {
        
        $('#paytpv_terminals .moneda').not(moneda).each(function() {
            if ($(this)!=moneda && $(this).val()==$(moneda).val()){
                alert($("#msg_moneda_terminal").html());
                $(moneda).val(previous);
            }
        });
    });
   
    

    window.checkenvironment = function(){
        if ($("#woocommerce_paytpv_environment").val()==1){
            $(".clientcode").closest("tr").hide()
            $("#woocommerce_paytpv_environment").closest("fieldset").find(".description").show()
        }else{
            $("#woocommerce_paytpv_environment").closest("fieldset").find(".description").hide()
            $(".clientcode").closest("tr").show()
            
        }
    }

    window.checkAllTerminales = function(){
        $( ".term" ).each(function() {
            checkterminales($(this));
        });
    }

    window.checkterminales = function (element){
        // Si solo tiene terminal seguro o tiene los dos la primera compra va por seguro
        // Seguro
        switch ($(element).val()){
            case "0": // SEGURO
                $(element).closest("tr").find('.dsecure').val(1);
                $(element).closest("tr").find('.dsecure option:not(:selected)').prop( "disabled", true );
                $(element).closest("tr").find('.tdmin').hide();
                
                break;
            case "1": // NO SEGURO
                $(element).closest("tr").find('.dsecure').val(0);
                $(element).closest("tr").find('.dsecure option:not(:selected)').prop( "disabled", true );
                $(element).closest("tr").find('.tdmin').hide();
                
                break;
            case "2": // AMBOS
                $(element).closest("tr").find('.dsecure option:not(:selected)').prop( "disabled", false );
                $(element).closest("tr").find('.tdmin').show();
                
                break;
        }
    }

    window.checkaddTerminal = function (remove){
        max_terms = 3;
        
        if ($(".term").length< max_terms)
            $(".add.button").show()
        else
            $(".add.button").hide()
    }


    checkenvironment();
    checkAllTerminales();
    checkaddTerminal();

});



