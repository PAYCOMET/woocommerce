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

let methods = ["paytpv_data", "paycomet_bancontact_data", "paycomet_bizum_data", "Paycomet_Eps_data", "paycomet_giropay_data", "paycomet_ideal_data", "paycomet_instantcredit_data", "paycomet_klarna_data", "paycomet_klarnapayments_data", "paycomet_multibanco_data", "paycomet_mybank_data", "paycomet_paypal_data", "paycomet_paysafecard_data", "paycomet_paysera_data", "paycomet_postfinance_data", "paycomet_przelewy_data", "paycomet_qiwi_data", "paycomet_skrill_data", "paycomet_trustly_data", "paycomet_waylet_data"];
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
  let contenido = Object(window.wp.element.createElement)(payment_content_paytpv, null);
  console.log(payment_data_paytpv.jet_id);
  if (payment_data_paytpv.name == 'paytpv' && payment_data_paytpv.jetiframe == 2) {
    contenido = (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(react__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, Object(window.wp.element.createElement)(payment_content_paytpv, null), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
      style: {
        color: "red"
      }
    }, "Incompatible Blocks"));
  }
  const Paytpv = {
    name: payment_data_paytpv.name,
    label: (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(react__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", null, payment_data_paytpv.title, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("img", {
      src: icon[0].src,
      alt: icon[0].alt,
      style: {
        paddingLeft: '10px'
      }
    }))),
    content: contenido,
    edit: Object(window.wp.element.createElement)(payment_content_paytpv, null),
    canMakePayment: () => true,
    placeOrderButtonLabel: window.wp.i18n.__('Continue', payment_data_paytpv.name),
    ariaLabel: window.wp.htmlEntities.decodeEntities(payment_data_paytpv.title),
    supports: {}
  };
  window.wc.wcBlocksRegistry.registerPaymentMethod(Paytpv);
}
})();

/******/ })()
;
//# sourceMappingURL=index.js.map