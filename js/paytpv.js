// Plugin

jQuery(function($) {

    window.saveOrderInfoJQ = function(){
        paytpv_agree = $("#savecard").is(':checked')?1:0;

        $.ajax({
            url: $("#form_paytpv").attr("action"),
            type: "POST",
            data: {
                'paytpv_agree': paytpv_agree,
                'tpvLstr': 'savecard',
                'Order': $("#order_id").val(),
                'ajax': true
            },
            dataType:"json"
        })
    }

    $("#direct_pay").on("click", function(e){
        e.preventDefault();
        $("#clockwait").show();
        $(this).hide();
        $(this).attr("disabled", true); // evitar doble check
        $("#form_paytpv").submit();

    });


    window.checkCard = function(){

        if ($("#card").val()=="0" || $("#card").val()==""){
            $("#storingStep,#paytpv_iframe").removeClass("hidden").show();
            $("#direct_pay").hide();
        }else{
            $("#storingStep,#paytpv_iframe").hide();
            $("#direct_pay").show();
        }
    }

    setTimeout(checkCard, 0);

});



jQuery(function($) {

    function alert(msg) {
        lightcase.start({
          href: '#'+msg,
        });
    }

    window.vincularTarjeta = function(){

        $("#ifr-paytpv-container-acount").attr('src',url_paytpv);
        $('#savecard').attr("disabled", true);
        $('#close_vincular').show();
        if ($('#payment_paycomet').val()!=1) {
            $('#nueva_tarjeta').show();
        } else {
            window.open($('#ifr-paytpv-container-acount').attr('src'),'_self');
        }

        $('#open_vincular').hide();
    }

    window.close_vincularTarjeta = function (){
        $('#savecard').attr("disabled", false);
        $('#nueva_tarjeta').hide();
        $('#close_vincular').hide();
        $('#open_vincular').show();
        $('#aviso-tokenizacion').css("display", "none");
        $("#id_card").val("");
        $("#option").val("");
    }

    $(".tokenizacion").click(function(e){
        e.preventDefault();
        getUrlIframe($(this));
        $('#close_vincular').show();
        $("#id_card").val($(this).attr("id"));
        $("#option").val("tokenization");
        if ($('#payment_paycomet').val()!=1) {
            $('#aviso-tokenizacion').css("display", "block");
            $('#nueva_tarjeta').show();
        } 
        $('#open_vincular').hide();
        

    });

    $(".update").click(function(){
        $("#ifr-paytpv-container-acount").attr('src',url_paytpv);
        $('#close_vincular').show();
        $("#id_card").val($(this).attr("id"));
        $("#option").val("update");
        if ($('#payment_paycomet').val()!=1) {
            $('#aviso-tokenizacion').css("display", "none");
            $('#nueva_tarjeta').show();
        } else {
            window.open($('#ifr-paytpv-container-acount').attr('src'),'_self');
        }
        $('#open_vincular').hide();
        
    });

    $(".remove_card").on("click", function(e){
        e.preventDefault();
        element = $(this);
        id = $(this).attr("id");
        cc_iduser = $("#cc_"+$(this).attr("id")).val()
        if (confirm($(this).html() + ": " + cc_iduser)) {
            removeCard(element);
        };
    });

    $(".save_desc").on("click", function(e){
        e.preventDefault();
        card_desc = $("#card_desc_"+$(this).attr("id")).val()
        saveDescriptionCard($(this));
    });


    window.saveDescriptionCard = function(element)
    {

        car_desc = $("#card_desc_"+element.attr("id")).val();
        $.ajax({
            url: element.attr("href"),
            type: "POST",
            data: {
                'card_desc': car_desc,
                'ajax': true
            },
            success: function(result)
            {
                if (result.resp == '0')
                {
                   alert("#msg_descriptionsaved")

                }
            },
            dataType:"json"
        });

    };


    window.removeCard = function(element)
    {
       
        $.ajax({
            url: element.attr("href"),
            type: "POST",
            data: {
                'ajax': true
            },
            success: function(result)
            {
                if (result.resp == '0')
                {
                   $("#card_"+id).fadeOut(1000);
                }
            },
            dataType:"json"
        });
        window.location.reload();
    };


    window.getUrlIframe = function(element)
    {
  
        $.ajax({
            url: element.attr("href"),
            type: "POST",
            data: {
                'tpvLstr': 'getUrlIframe',
                'card_id': element.attr("id"),
                'ajax': true
            },
            success: function(result)
            {
                if (result.resp == '0')
                {
                    $("#ifr-paytpv-container-acount").attr('src',result.url);
                    if ($('#payment_paycomet').val()==1) {
                        window.open($('#ifr-paytpv-container-acount').attr('src'),'_self');
                    }
                }
            },
            dataType:"json"
        })
    }


});

