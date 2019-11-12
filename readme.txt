=== PAYCOMET for WooCommerce ===
Contributors: PAYCOMET
Tags: woocommerce, payment, payment gateway, pasarela de pago, suscripciones, pago, tarjeta, multibanco, moneda, ecommerce, e-commerce
Requires at least: 3.0.1
Tested up to: 5.3
Stable tag: 4.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Módulo de pago PAYCOMET para WooCommerce. Permite realizar pagos con tarjeta de crédito.

PAYCOMET - Pasarela de pagos PCI-DSS Nivel 1 Multiplataforma

== Description ==

This is a payment gateway for WooCommerce to accept credit card payments using merchant accounts from https://www.paycomet.com

Módulo de pago para WooCommerce que permite el pago de los pedidos mediante tarjeta de crédito usando el servicio de tpv virtual de https://www.paycomet.com

Funcionalidades del módulo:

* Tokenización de tarjetas: El usuario podrá almacenar la tarjeta para futuros pagos.
* Devoluciones: Admite las Devoluciones Totales o Parciales en línea.
* Subscriptions: Compatible con WooCommerce Subscriptions.
* Multimoneda: Se puede asociar un terminal por cada moneda.
* Multibanco: PAYCOMET permite operar con diferentes bancos y procesar las operaciones con el banco que se desee.

== Installation ==

1. Suba el directorio paytpv-for-woocommerce a la carpeta de plugins de wordpresss `/wp-content/plugins/`
2. Active el plugin desde el apartado de Plugins de WordPress
3. Acceda a WooCommerce -> Setting -> Payment Gateways -> PAYCOMET link y configure los datos. Estos los podrá obtener en su cuenta dentro de https://www.paycomet.com account.

== Frequently Asked Questions ==



== Screenshots ==

1. Pantalla de configuración

== Changelog ==

= 4.2 =
Mejora en traducciones

= 4.1 =
Se añade la validación de los datos a la hora de guardar la configuración del Módulo para verificar que la cuenta está bien configurada en PAYCOMET para poder procesar.

= 4.0 =
Cambio a PAYCOMET, calculo de firmas y depuración de código

= 3.3 =
Mejoras tokenizacion de tarjetas area de usuario

= 3.2 =
Mejoras del Módulo de pago

= 3.0 =
Release date: Dec 10st, 2015

* Operativa PAYTPV Bankstore.
* Tokenización de Tarjetas. El usuario puede tokenizar más de una tarjeta para futuros pagos.
* Area de Usuario “Mis Tarjetas” para la gestión de tarjetas.
* Devoluciones Totales o Parciales en línea.
* Compatible con WooCommerce Subscriptions.
* Multimoneda. WooCommerce Multilingual. Se puede asociar un terminal por cada moneda. Deberemos para ello tener contratado un terminal en cada moneda. Los pagos se procesarán por el terminal correspondiente a la moneda del pedido. Si sólo se dispone de un terminal configurado en PAYTPV con la opción de multimoneda activada se pasarán los importes en la moneda del pedido y el banco realizará la conversión.