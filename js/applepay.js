(function ($) {
    'use strict';

    function portal() {
        return document.getElementById('paycometApplePayPortal');
    }

    function ensure$() {
        if ($) {
            window.$ = $;
        }
    }

    function wireClick() {
        const el = portal();
        if (!el) {
            return;
        }
        const btn = el.querySelector('apple-pay-button');
        if (!btn || btn.dataset.wired) {
            return;
        }
        btn.dataset.wired = '1';
        btn.removeAttribute('onclick');
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            ensure$();
            if (typeof window.onApplePayButtonClicked === 'function') {
                try {
                    window.onApplePayButtonClicked();
                } catch (err) {
                    console.error('Paycomet Apple Pay:', err);
                }
            }
        });
    }

    function injectHtml(el, html) {
        const tmp = document.createElement('div');
        tmp.innerHTML = html;
        const scripts = [];
        const nodes = [];

        tmp.childNodes.forEach(function (n) {
            if (n.nodeName === 'SCRIPT') {
                if (n.src) {
                    scripts.push({ src: n.src });
                } else if (n.textContent.trim()) {
                    scripts.push({ text: n.textContent });
                }
            } else {
                nodes.push(n);
            }
        });

        el.innerHTML = '';
        const sdk = scripts.find(function (s) {
            return s.src && s.src.indexOf('apple-pay-sdk') !== -1;
        });

        function finish() {
            ensure$();
            scripts.forEach(function (s) {
                if (s.text) {
                    $.globalEval(s.text);
                }
            });
            nodes.forEach(function (n) {
                el.appendChild(n.cloneNode(true));
            });
            wireClick();
        }

        if (sdk && sdk.src) {
            if (!document.querySelector('script[src="' + sdk.src + '"]')) {
                const tag = document.createElement('script');
                tag.src = sdk.src;
                tag.onload = finish;
                tag.onerror = finish;
                document.head.appendChild(tag);
            } else {
                finish();
            }
            return;
        }

        finish();
    }

    function loadStored() {
        if (typeof window.paycometApplePayStoredHtml !== 'string' || !window.paycometApplePayStoredHtml) {
            return;
        }
        const el = portal();
        if (!el) {
            return;
        }
        const html = window.paycometApplePayStoredHtml;
        delete window.paycometApplePayStoredHtml;
        $(el).find('.paycomet-applepay-loading').remove();
        injectHtml(el, html);
    }

    function init() {
        ensure$();
        loadStored();
    }

    $(function () {
        init();
    });
})(window.jQuery);
