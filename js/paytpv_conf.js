// Admin Plugin
jQuery(function($) {
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

    $('.payment_paycomet').on( 'change', function(e){
        checkPayment();
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


    window.checkPayment = function(){
        if ($(".payment_paycomet").val()==0) 
            $('.iframe_height').parents("tr").show();
        else
            $('.iframe_height').parents("tr").hide();

        if ($(".payment_paycomet").val()==2) 
            $('.jet_id').parents("tr").show();
        else
            $('.jet_id').parents("tr").hide();
    }

    window.checkaddTerminal = function (remove){
        max_terms = 3;

        if ($(".term").length< max_terms)
            $(".add.button").show()
        else
            $(".add.button").hide()
    }

    checkaddTerminal();
    checkPayment();

});



