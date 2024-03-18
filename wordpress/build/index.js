/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/***/ ((module) => {

module.exports = window["React"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);


//Array of payment methods
let methods = ["paytpv_data", "paycomet_bancontact_data", "paycomet_bizum_data", "Paycomet_Eps_data", "paycomet_giropay_data", "paycomet_ideal_data", "paycomet_instantcredit_data", "paycomet_klarna_data", "paycomet_klarnapayments_data", "paycomet_multibanco_data", "paycomet_mybank_data", "paycomet_paypal_data", "paycomet_paysafecard_data", "paycomet_paysera_data", "paycomet_postfinance_data", "paycomet_przelewy_data", "paycomet_qiwi_data", "paycomet_skrill_data", "paycomet_trustly_data", "paycomet_waylet_data"];

//Array of years for expiration date
const UltimosAniosComponente = () => {
  const anioActual = new Date().getFullYear();
  const ultimosAnios = 15;
  const anios = [];
  for (let i = 0; i < ultimosAnios; i++) {
    const anio = anioActual + i;
    anios.push(anio);
  }
  return anios;
};
const proximosAnios = UltimosAniosComponente();
for (let i = 0; i < methods.length; i++) {
  const payment_data_paytpv = Object(window.wc.wcSettings.getSetting(methods[i], {}));
  const payment_content_paytpv = () => {
    return window.wp.htmlEntities.decodeEntities(payment_data_paytpv.description);
  };
  const getIcons = () => {
    return Object.entries(payment_data_paytpv.icons).map(([id, {
      src,
      alt
    }]) => {
      return {
        id,
        src,
        alt
      };
    });
  };
  const icon = getIcons();
  const Contenido = ({
    eventRegistration
  }) => {
    //onSubmit(
    //() => 
    eventRegistration.onPaymentSetup(() => console.log('onPaymentSetup'));
    //eventRegistration.onPaymentSetup(() => console.log('onPaymentSetup'))
    // );  

    let content;
    if (payment_data_paytpv.name == 'paytpv' && payment_data_paytpv.jetiframe == 2) {
      content = (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(react__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, Object(window.wp.element.createElement)(payment_content_paytpv, null), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
        type: "hidden",
        id: "jetiframe-token",
        name: "jetiframe-token"
      }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
        type: "checkbox",
        id: "savecard_jetiframe",
        name: "savecard_jetiframe",
        style: {
          display: 'none'
        }
      }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
        type: "text",
        id: "hiddenCardField",
        name: "hiddenCardField",
        style: {
          display: 'none'
        }
      }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        id: "saved_cards",
        style: {
          display: payment_data_paytpv.store_card
        }
      }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        className: "form-group"
      }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
        htmlFor: "card"
      }, payment_data_paytpv.text.Card, " "), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
        name: "jet_iframe_card",
        id: "jet_iframe_card",
        onChange: checkSelectedCard,
        className: "form-group"
      }, payment_data_paytpv.saved_cards.map((card, index) => (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
        key: index,
        value: card.id
      }, card.paytpv_cc, card.card_desc != null && card.card_desc != "" ? " - " + card.card_desc : "")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
        value: "0"
      }, payment_data_paytpv.text.NewCard)))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        id: "toHide",
        style: {
          display: 'none'
        }
      }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
        type: "hidden",
        "data-paycomet": "jetID",
        value: payment_data_paytpv.jet_id
      }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
        type: "hidden",
        className: "form-control",
        name: "username",
        "data-paycomet": "cardHolderName",
        placeholder: "",
        value: "NONAME",
        style: {
          height: '30px',
          width: '290px'
        }
      }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        className: "row"
      }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        className: "form-group"
      }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
        htmlFor: "cardNumber"
      }, payment_data_paytpv.text.CardNumber), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        className: "input-group"
      }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        id: "paycomet-pan",
        style: {
          height: '34px',
          width: '290px',
          padding: '0px',
          border: '1px solid #dcd7ca'
        }
      }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
        style: {
          height: '30px',
          fontSize: '18px',
          paddingtop: '2px',
          border: '0px'
        },
        "paycomet-name": "pan"
      }))))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        className: "row"
      }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        className: "col-xs-12 col-md-9",
        style: {
          paddingleft: '0px'
        }
      }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        className: "form-group"
      }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
        className: "hidden-xs"
      }, payment_data_paytpv.text.ExpirationDate), " "), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        className: "form-inline"
      }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
        className: "form-control",
        style: {
          height: '34px',
          width: '142px',
          border: '1px solid #dcd7ca',
          fontSize: '18px',
          padding: '0 0 0 10px'
        },
        "data-paycomet": "dateMonth"
      }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", null, payment_data_paytpv.text.Month), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
        value: "01"
      }, payment_data_paytpv.text.January), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
        value: "02"
      }, payment_data_paytpv.text.February), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
        value: "03"
      }, payment_data_paytpv.text.March), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
        value: "04"
      }, payment_data_paytpv.text.April), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
        value: "05"
      }, payment_data_paytpv.text.May), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
        value: "06"
      }, payment_data_paytpv.text.June), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
        value: "07"
      }, payment_data_paytpv.text.July), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
        value: "08"
      }, payment_data_paytpv.text.August), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
        value: "09"
      }, payment_data_paytpv.text.September), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
        value: "10"
      }, payment_data_paytpv.text.October), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
        value: "11"
      }, payment_data_paytpv.text.November), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
        value: "12"
      }, payment_data_paytpv.text.December)), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
        className: "form-control",
        style: {
          height: '34px',
          width: '142px',
          border: '1px solid #dcd7ca',
          fontSize: '18px',
          padding: '0 0 0 10px'
        },
        "data-paycomet": "dateYear"
      }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", null, payment_data_paytpv.text.Year), proximosAnios.map(anio => (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
        key: anio,
        value: anio
      }, anio)))))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        className: "col-xs-12 col-md-3",
        style: {
          paddingleft: '0px'
        }
      }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        className: "form-group"
      }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
        "data-toggle": "tooltip",
        title: "",
        "data-original-title": "3 digits code on back side of the card"
      }, "CVV ", (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
        className: "fa fa-question-circle"
      })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        id: "paycomet-cvc2",
        style: {
          height: '34px',
          padding: '0px'
        }
      }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
        "paycomet-name": "cvc2",
        maxLength: 4,
        style: {
          height: '30px',
          width: '60px',
          fontsize: '18px',
          paddingleft: '7px',
          border: '1px solid #dcd7ca'
        },
        className: "form-control",
        required: "",
        type: "text"
      })))))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        id: "storingStep",
        className: "box",
        style: {
          display: 'none'
        }
      }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
        className: "checkbox"
      }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
        type: "checkbox",
        name: "jetiframe_savecard",
        id: "jetiframe_savecard"
      }), " ", payment_data_paytpv.text.SaveCard, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
        className: "paytpv-pci"
      }, " ", payment_data_paytpv.text.Pci, " "))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
        type: "submit",
        style: {
          width: '290px',
          display: 'none'
        },
        name: "jetiframe-button",
        id: "jetiframe-button",
        value: payment_data_paytpv.text.MakePayment
      }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        id: "paymentErrorMsg",
        style: {
          color: '#fff',
          background: '#b22222',
          margintop: '10px',
          textalign: 'center'
        }
      }));
    } else {
      content = Object(window.wp.element.createElement)(payment_content_paytpv, null);
    }
    return content;
  };
  const Paytpv = {
    name: payment_data_paytpv.name,
    label: (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(react__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", null, payment_data_paytpv.title, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("img", {
      src: icon[0].src,
      alt: icon[0].alt,
      style: {
        paddingLeft: '10px'
      }
    }))),
    content: (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(Contenido, null),
    edit: (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(Contenido, null),
    canMakePayment: () => true,
    placeOrderButtonLabel: window.wp.i18n.__('Continue', payment_data_paytpv.name),
    ariaLabel: window.wp.htmlEntities.decodeEntities(payment_data_paytpv.title),
    supports: {}
  };
  window.wc.wcBlocksRegistry.registerPaymentMethod(Paytpv);
}
function checkSelectedCard() {
  if (document.getElementById('jet_iframe_card').value != 0) {
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
}
;
})();

/******/ })()
;
//# sourceMappingURL=index.js.map