import React, { useRef, useEffect } from 'react';
import { generateNextYears } from './generateNextYears';

//Array of payment methods
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
    
    const nextYears = generateNextYears();
    
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

        const Contenido= ({eventRegistration}) => {

            const jetiframeButton = useRef(null);
            const jetiframeInput = useRef(null);
            
            // Función para simular clic en el input de tipo submit
            const handleClickSubmit = () => {
                const new_card = (document.getElementById('jet_iframe_card').value == 0)?true:false;

                if (jetiframeButton.current && new_card) {
                    console.log(document.getElementsByName("paytpvToken")[0]);
                    jetiframeButton.current.click();
                  
                }
            
                
                jetiframeInput.current.value = "Texto de ejemplo";
                setInputValue(jetiframeInput.current.value);
               
            };
            useEffect(() => {
                $.getScript("https://api.paycomet.com/gateway/paycomet.jetiframe.js?lang=es"); 
                eventRegistration.onCheckoutValidation(() => {
                    handleClickSubmit();
                } );
                
            },[])
            
            let content;
            if(payment_data_paytpv.name=='paytpv' && payment_data_paytpv.jetiframe==2){
            content=
            <>
                <form name="paycometPaymentForm" id="paycometPaymentForm"  method="POST">
                    { Object( window.wp.element.createElement )(payment_content_paytpv,null)}
                    {<p/>}   
                    <input type="text" id="jetiframe-token" name="jetiframe-token" ref={jetiframeInput} style={{display:'none'}}/>
                    <input type="checkbox" id="savecard_jetiframe" name="savecard_jetiframe" style={{display:'none'}}/>
                    <input type="text" id="hiddenCardField" name="hiddenCardField" defaultValue={payment_data_paytpv.saved_cards.length} style={{display:'none'}}/>

                    <div id="saved_cards" style={{ display: payment_data_paytpv.store_card }}>
                        <div className="form-group">
                            <label htmlFor="card">{payment_data_paytpv.text.Card }{" "}</label>
                            <select name="jet_iframe_card" id="jet_iframe_card" onChange={checkSelectedCard} className="form-group">
                            {payment_data_paytpv.saved_cards.map((card, index) => (
                                <option key={index} value={card.id}> 
                                    {card.paytpv_cc}{(card.card_desc != null && card.card_desc !="") ? (" - " + card.card_desc) : ""} 
                                </option>
                    
                            ))}
                                <option value="0">{payment_data_paytpv.text.NewCard}</option>

                            </select>
                        </div>
                    </div>
                
                    <div id="toHide" style={{display:'none'}}>
                        <input type="hidden" data-paycomet="jetID" value={payment_data_paytpv.jet_id}/>

                        <input type="hidden" className="form-control" name="username" data-paycomet="cardHolderName" placeholder="" value="NONAME" style={{height:'30px', width: '290px'}}/>

                        <div className="row">
                            <div className="form-group">
                                <label htmlFor="cardNumber">{payment_data_paytpv.text.CardNumber}</label>
                                <div className="input-group">
                                    <div id="paycomet-pan" style={{height:'34px', width: '290px', padding:'0px', border: '1px solid #dcd7ca'}}>  
                                    <input paycomet-style="height: 30px; font-size:18px; padding-top:2px; border:0px;" paycomet-name="pan"/>
                                    </div> 
                                </div>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-xs-12 col-md-9" style={{paddingleft:'0px'}}>
                                <div className="form-group">
                                    <label><span className="hidden-xs">{payment_data_paytpv.text.ExpirationDate}</span> </label>
                                    <div className="form-inline">
                                        <select className="form-control" style={{height:'34px', width: '142px', border: '1px solid #dcd7ca', fontSize: '18px', padding: '0 0 0 10px'}} data-paycomet="dateMonth">
                                            <option>{payment_data_paytpv.text.Month}</option>
                                            <option value="01">{payment_data_paytpv.text.January}</option>
                                            <option value="02">{payment_data_paytpv.text.February}</option>
                                            <option value="03">{payment_data_paytpv.text.March}</option>
                                            <option value="04">{payment_data_paytpv.text.April}</option>
                                            <option value="05">{payment_data_paytpv.text.May}</option>
                                            <option value="06">{payment_data_paytpv.text.June}</option>
                                            <option value="07">{payment_data_paytpv.text.July}</option>
                                            <option value="08">{payment_data_paytpv.text.August}</option>
                                            <option value="09">{payment_data_paytpv.text.September}</option>
                                            <option value="10">{payment_data_paytpv.text.October}</option>
                                            <option value="11">{payment_data_paytpv.text.November}</option>
                                            <option value="12">{payment_data_paytpv.text.December}</option>
                                        </select>
                                        <select className="form-control" style={{height:'34px', width: '142px', border: '1px solid #dcd7ca', fontSize: '18px', padding: '0 0 0 10px'}} data-paycomet="dateYear">
                                            <option>{payment_data_paytpv.text.Year}</option>
                                
                                        {nextYears.map((year) => (
                                            <option key={year} value={year.toString().substring(2, 4)}>
                                                {year}
                                            </option>
                                        ))}

                                        </select>
                                    </div> 
                                </div>
                            </div>  
                            <div className="col-xs-12 col-md-3" style={{paddingleft:'0px'}}>
                                <div className="form-group">
                                    <label data-toggle="tooltip" title=""
                                        data-original-title="3 digits code on back side of the card">
                                        CVV <i className="fa fa-question-circle"></i>
                                    </label>
                                    <div id="paycomet-cvc2" style={{height: '34px', padding:'0px'}}>
                                    <input paycomet-name="cvc2" maxLength={4} paycomet-style="height: 30px; width: 60px; font-size:18px; padding-left:7px; border: 1px solid #dcd7ca;" className="form-control" required="" type="text"/>
                                    </div>
                                </div>
                            </div>  
                        </div>
                    </div> 
                                      
                    <div id="storingStep" className="box" style={{display:'none'}}>
                        <label className="checkbox" style={{ display: payment_data_paytpv.disable_offer_savecard }}>
                            <input type="checkbox" name="jetiframe_savecard" id="jetiframe_savecard"/> {payment_data_paytpv.text.SaveCard}
                            <span className="paytpv-pci"> {payment_data_paytpv.text.Pci} </span>
                        </label>
                    </div>  

                    <input type="submit" style={{width: '290px',display:'none'}} name="jetiframe-button" id="jetiframe-button" ref={jetiframeButton} value={payment_data_paytpv.text.MakePayment} ></input>
        
                    <div id="paymentErrorMsg" style={{color: '#fff', background: '#b22222', margintop: '10px', textalign: 'center'}}></div>
                </form>
            </>
        
            }else{
                content= Object( window.wp.element.createElement )(payment_content_paytpv,null);
            }
        return(content);
        }
        
        const Paytpv ={
            name: payment_data_paytpv.name,
            label: (    
            <>
                <span>
                    { payment_data_paytpv.title } 
                    <img src={ icon[0].src } alt={ icon[0].alt }  style={{paddingLeft: '10px'}}/>
                </span>
            </>
            ),
            content: <Contenido/>,
            edit: <Contenido/>,
            canMakePayment: () => true,
            placeOrderButtonLabel: window.wp.i18n.__( 'Continue', payment_data_paytpv.name),
            ariaLabel: window.wp.htmlEntities.decodeEntities( payment_data_paytpv.title ),
            supports: {},
        };
        
        window.wc.wcBlocksRegistry.registerPaymentMethod( Paytpv );

    }
    
    function checkSelectedCard() {
        if (document.getElementById('jet_iframe_card').value != 0){
            document.getElementById('toHide').style.display = "none";
            document.getElementById('storingStep').style.display = "none";
        } else {
            if (document.getElementById('toHide')) {
                document.getElementById('toHide').style.display = "block";
            }
            if (document.getElementById('storingStep')) {
                document.getElementById('storingStep').style.display = "block";
            }
        }
        document.getElementById('hiddenCardField').value = document.getElementById('jet_iframe_card').value;
    };
    
  
 

    