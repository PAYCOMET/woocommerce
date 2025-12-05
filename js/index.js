const { createElement, Fragment } = window.wp.element;
const { __ } = window.wp.i18n;

// Lista de métodos Paycomet
const PAYMENT_METHODS = [
    'paytpv_data',
    'paycomet_bancontact_data',
    'paycomet_bizum_data',
    'paycomet_eps_data',
    'paycomet_giropay_data',
    'paycomet_ideal_data',
    'paycomet_instantcredit_data',
    'paycomet_klarna_data',
    'paycomet_klarnapayments_data',
    'paycomet_mbway_data',
    'paycomet_multibanco_data',
    'paycomet_mybank_data',
    'paycomet_paypal_data',
    'paycomet_paysafecard_data',
    'paycomet_paysera_data',
    'paycomet_postfinance_data',
    'paycomet_przelewy_data',
    'paycomet_qiwi_data',
    'paycomet_skrill_data',
    'paycomet_trustly_data',
    'paycomet_waylet_data'
];

/**
 * Función para registrar todos los métodos de pago en WooCommerce Blocks.
 */
function registerAllPaycometMethods() {

    PAYMENT_METHODS.forEach((key, index) => {
        const settings = window.wc.wcSettings.getSetting(key, {});

        const label = createElement(
            Fragment,
            null,
            settings.title || __(key, 'wc_paytpv'),
            settings.icons?.[Object.keys(settings.icons)[0]]?.src
                ? createElement('img', {
                      src: settings.icons[Object.keys(settings.icons)[0]].src,
                      alt: settings.icons[Object.keys(settings.icons)[0]].alt || key,
                      style: { paddingLeft: '10px', verticalAlign: 'middle', maxHeight: 24 },
                  })
                : null
        );
      
        let Content = settings.description || __('You will be redirected to payment', 'wc_paytpv');

        //JET-IFRAME
        if (key === 'paytpv_data' && settings.payment_paycomet == 2) {
            // Este div es el portal donde se insertará el formulario jet-iframe.
            Content = createElement(
                Fragment,
                null,
                createElement('div', null, settings.description || __('You will be redirected to payment', 'wc_paytpv')),
                createElement('br'),
                createElement('div', { id: 'paycometFormPortal' })
            );
        }
        
        const contentElement = createElement(
            Fragment,
            null,
            createElement('div', null, Content)
        );

        const method = {
            name: settings.name || key,
            label: label || key,
            content: contentElement,
            edit: contentElement,
            canMakePayment: () => true,
            ariaLabel: settings.title || key,
            ...(key === 'paytpv_data' ? { 
                supports: { features: settings.supports, },
            } : {}),
            isSelected: index === 0
        };

        window.wc.wcBlocksRegistry.registerPaymentMethod(method);
   
    });
}

/**
 * Función para crear el formulario JET-IFRAME.
 */
function createPaycometForm(portal) {
    if (portal.hasChildNodes()) {
        return; 
    }

    const settings = window.wc.wcSettings.getSetting('paytpv_data', {});

    let hiddenContainer = document.getElementById('paycometHiddenFieldsContainer');

    if (!hiddenContainer) {
        hiddenContainer = document.createElement('div');
        hiddenContainer.id = 'paycometHiddenFieldsContainer';
        hiddenContainer.innerHTML = `
            <input type="hidden" id="jetiframe-token" name="jetiframe-token">
            <input type="checkbox" id="savecard_jetiframe" name="savecard_jetiframe" style="display:none">
            <input type="hidden" id="hiddenCardField" name="hiddenCardField" value="" data-field="paycomet_universal_message" >
        `;
        document.body.appendChild(hiddenContainer);
    }
    
    portal.appendChild(hiddenContainer);

    const form = document.createElement('form');
    form.id = 'paycometPaymentForm';
    form.name = 'paycometPaymentForm';
    form.action = 'javascript:jetIframeValidated()';
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" data-paycomet="jetID" value="${settings.jet_id}" />
        <input type="hidden" class="form-control" name="username" data-paycomet="cardHolderName" placeholder="" value="NONAME" style="height:30px; width: 290px">

        ${settings.saved_cards && settings.saved_cards.length > 0 ? `
            <div id="saved_cards" style="display:${settings.store_card}">
                <div class="form-group">
                    <label for="jet_iframe_card">${settings.text.Card}</label>
                    <select name="jet_iframe_card" id="jet_iframe_card" class="form-control" style="width:100%;" onChange="checkSelectedCard()">
                        ${settings.saved_cards.map(card => {
                            const desc = card.card_desc ? ` - ${card.card_desc}` : '';
                            return `<option value="${card.id}">${card.paytpv_cc}${desc}</option>`;
                        }).join('')}
                        <option value="0">${settings.text.NewCard}</option>
                    </select>
                </div>
            </div>
        ` : ''}

        <div id="toHide" style="display:none;">
            <div class="row">
                <div class="form-group">
                    <label>${settings.text.CardNumber}</label>
                    <div class="input-group">
                        <div id="paycomet-pan" style="${settings.pan_div_style}"></div>
                        <input paycomet-style="${settings.pan_input_style}" paycomet-name="pan">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12 col-md-9" style="padding-left:0">
                    <div class="form-group">
                        <label><span class="hidden-xs">${settings.text.ExpirationDate}</span></label>
                        <div class="form-inline">
                            <select id="paycomet_card_month" class="form-control" aria-hidden="true" data-paycomet="dateMonth" style="width:142px;border:1px solid #dcd7ca;font-size:18px;padding:0 0 0 10px">
                                <option value="" selected disabled>${settings.text.Month}</option>
                                ${(() => {
                                    const monthKeys = [
                                        'January', 'February', 'March', 'April', 'May', 'June',
                                        'July', 'August', 'September', 'October', 'November', 'December'
                                    ];
                                    return monthKeys.map((key, i) => {
                                        const val = String(i + 1).padStart(2, '0');
                                        const label = settings.text[key] || `${val} - ${key}`;
                                        return `<option value="${val}">${label}</option>`;
                                    }).join('');
                                })()}
                            </select>

                            <select id="paycomet_card_year" class="form-control" aria-hidden="true" data-paycomet="dateYear" style="width:142px;border:1px solid #dcd7ca;font-size:18px;padding:0 0 0 10px">
                                <option value="" selected disabled>${settings.text.Year}</option>
                                ${Array.from({length:9}, (_,i)=>`<option value="${String(new Date().getFullYear()+i).slice(2)}">${new Date().getFullYear()+i}</option>`).join('')}
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12 col-md-3" style="padding-left:0">
                    <div class="form-group">
                        <label data-toggle="tooltip" data-original-title="3 digits code on back side of the card">CVV <i class="fa fa-question-circle"></i></label>
                        <div id="paycomet-cvc2" style="${settings.cvc2_div_style}"></div>
                        <input paycomet-name="cvc2" paycomet-style="${settings.cvc2_input_style}" class="form-control" required="" type="text">
                    </div>
                </div>
            </div>

        </div>
        
        ${settings.disable_offer_savecard ? `
            <div id="storingStep" class="box" style="display:none">
                <label class="checkbox"><input type="checkbox" name="jetiframe_savecard" id="jetiframe_savecard">${settings.text.SaveCard}<span class="paytpv-pci"> ${settings.text.Pci}</span></label>
            </div>
        ` : ''}

        <input type="submit" id="jetiframe-button" name="jetiframe-button" value="Make payment" style="width:290px;display:none;" />
        
    `;

    portal.appendChild(form);

    const errorDiv = document.createElement('div');
    errorDiv.id = 'paymentErrorMsg';
    errorDiv.style.cssText = 'color: #fff; background: #b22222; margin-top: 10px; text-align: center;';
    portal.appendChild(errorDiv);

    //Actualizar hiddenCardField al inicio
    const cardSelect = document.getElementById('jet_iframe_card');
    const hiddenField = document.getElementById('hiddenCardField');

    if (cardSelect && hiddenField) {
        if (hiddenField.value === '') {
            hiddenField.value = cardSelect.value; 
        }
    }

    //Si no hay tarjetas guardadas mostramos el formulario de pago
    const toHide = document.getElementById('toHide');
    const storingStep = document.getElementById('storingStep');
    if(!cardSelect){
        if(toHide){
            toHide.style.display = 'block';
        }
        if(storingStep){
            storingStep.style.display = 'block';
        }
    }
    
}

/**
 * Función cuando se valida el formulario de JetIframe correctamente
 */
function jetIframeValidated(){

    if (document.getElementById("jetiframe_savecard") != null) {
        document.getElementById("savecard_jetiframe").checked = document.getElementById("jetiframe_savecard").checked;
    }

    document.getElementById("jetiframe-token").value = document.getElementsByName("paytpvToken")[0].value;

    if (jQuery("#jetiframe-token").val() !== "") {

        const placeOrderBtn = document.querySelector(
            '.wc-block-components-button.wp-element-button.wc-block-components-checkout-place-order-button.contained'
        );

        if (placeOrderBtn) {
            placeOrderBtn.click();
        }
    }

}

/**
 * Función cuando se cambia el selector de tarjetas guardadas
 */
function checkSelectedCard() {

    if (document.getElementById('jet_iframe_card').value != 0){
        document.getElementById('toHide').style.display = "none";
        if (document.getElementById('storingStep')) {
            document.getElementById('storingStep').style.display = "none";
        }
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

document.addEventListener('DOMContentLoaded', () => {

    setTimeout(registerAllPaycometMethods, 50);

    //JET-IFRAME (GENERACIÓN FORM Y EJECUCIÓN DE SCRIPT)
 
    const settings = window.wc.wcSettings.getSetting('paytpv_data', {});

    const targetNode = document.body;
    const config = { childList: true, subtree: true };

    const callback = () => {
        const portal = document.getElementById('paycometFormPortal');
        
        if (portal && !portal.hasChildNodes()) {
            createPaycometForm(portal);

            const scriptLoader = setInterval(() => {
                const form = document.getElementById('paycometPaymentForm');
                if (form) {
                    if (typeof window.PAYCOMETIFRAME === 'undefined' && typeof jQuery !== 'undefined') {
                        jQuery.getScript(`https://api.paycomet.com/gateway/paycomet.jetiframe.js?lang=${settings.getLanguage}`);
                    }
                    clearInterval(scriptLoader);
                }
            }, 100);
        }
    };

    const observer = new MutationObserver(callback);
    observer.observe(targetNode, config);
});


//JET-IFRAME (CONTROLAMOS SUBMIT)
document.addEventListener('DOMContentLoaded', () => {

    const observer = new MutationObserver(() => {

        const jetBtn = document.getElementById('jetiframe-button');

        if (jetBtn) {
            observer.disconnect();

            const placeOrderBtn = document.querySelector(
                '.wc-block-components-button.wp-element-button.wc-block-components-checkout-place-order-button.contained'
            );

            if (placeOrderBtn) {
               
                placeOrderBtn.onclick = function(e) {

                    if (!e.isTrusted) {
                        const originalFetch = window.fetch.bind(window);

                        window.fetch = async function (resource, init = {}) {
                        const url =
                            typeof resource === "string"
                            ? resource
                            : resource && resource.url
                            ? resource.url
                            : "";

                        if (url.includes("/wc/store/checkout") || url.includes("/wc/store/v1/checkout")) {
                            if (init && typeof init.body === "string" && init.body.trim().startsWith("{")) {
                                // Parseamos el JSON original
                                const jsonBody = JSON.parse(init.body);

                                // Añadimos campos
                                jsonBody.hiddenCardField = document.getElementById('jet_iframe_card')?.value ?? 0;
                                jsonBody.jetToken = document.getElementById("jetiframe-token").value;
                                jsonBody.saveCard = document.getElementById("savecard_jetiframe").checked ? 1 : 0; 

                                // Reconstruimos el body para enviar
                                init.body = JSON.stringify(jsonBody);

                                // Asegura Content-Type JSON
                                init.headers = init.headers || {};
                                if (init.headers instanceof Headers) {
                                    init.headers.set("Content-Type", "application/json");
                                } else {
                                    init.headers["Content-Type"] = "application/json";
                                }
                            }
                        }

                        // Ejecutamos el fetch real con el body modificado
                        const response = await originalFetch(resource, init);

                        return response;
                        };

                    }else{    
                        const jetForm = document.getElementById('paycometPaymentForm');
                        if(jetForm){
                            e.preventDefault();
                            e.stopImmediatePropagation();   
    
                            if ((document.getElementById('jet_iframe_card')?.value ?? 0) == 0) {
                                jetBtn.click();
                            }else{
                                placeOrderBtn.click();
                            }
                        }
                      
                    }
                };

            }
        }
    });

    observer.observe(document.body, { childList: true, subtree: true });
});

