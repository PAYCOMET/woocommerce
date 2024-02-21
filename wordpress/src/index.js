let methods = [
"paytpv_data", 
"paycomet_bancontact_data", 
"paycomet_bizum_data",
"Paycomet_Eps_data",
"paycomet_giropay_data",
"paycomet_ideal_data",
"paycomet_instantcredit_data",
"paycomet_klarna_data",
"paycomet_klarnapayments_data",
"paycomet_multibanco_data",
"paycomet_mybank_data",
"paycomet_paypal_data",
"paycomet_paysafecard_data",
"paycomet_paysera_data",
"paycomet_postfinance_data",
"paycomet_przelewy_data",
"paycomet_qiwi_data",
"paycomet_skrill_data",
"paycomet_trustly_data",
"paycomet_waylet_data"
];



for (let i = 0; i < methods.length; i++) {

    const payment_data_paytpv = Object(window.wc.wcSettings.getSetting( methods[i], {} ));


    const payment_content_paytpv = () => {
        return window.wp.htmlEntities.decodeEntities( payment_data_paytpv.description );
    };
    
    const getIcons = () => {
        return Object.entries(payment_data_paytpv.icons ).map(
            ( [ id, { src, alt } ] ) => {
                return {
                    id,
                    src,
                    alt,
                };
            }
        );
    };
    
    const icon = getIcons();
   
    let contenido= Object( window.wp.element.createElement )(payment_content_paytpv,null);
console.log(payment_data_paytpv.jet_id);
    if(payment_data_paytpv.name=='paytpv' && payment_data_paytpv.jetiframe==2){
        contenido=  (
            <>
                { Object( window.wp.element.createElement )(payment_content_paytpv,null)}
                {<p style={{color:"red"}}>Incompatible Blocks</p>}            
            </>
        )
    }

    const Paytpv = {
        name: payment_data_paytpv.name,
        label: (	
        <>
            <span>
                { payment_data_paytpv.title } 
                <img src={ icon[0].src } alt={ icon[0].alt }  style={{paddingLeft: '10px'}}/>
            </span>
        </>
        ),
        content: contenido,
        edit: Object( window.wp.element.createElement )( payment_content_paytpv, null ),
        canMakePayment: () => true,
        placeOrderButtonLabel: window.wp.i18n.__( 'Continue', payment_data_paytpv.name),
        ariaLabel: window.wp.htmlEntities.decodeEntities( payment_data_paytpv.title ),
        supports: {},
    };
    
    window.wc.wcBlocksRegistry.registerPaymentMethod( Paytpv );
    
}









