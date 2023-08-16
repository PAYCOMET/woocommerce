=== PAYCOMET for WooCommerce ===
Contributors: PAYCOMET
Tags: woocommerce, payment, payment gateway, pasarela de pago, suscripciones, pago, tarjeta, multibanco, moneda, ecommerce, e-commerce
Requires at least: 3.0.1
Tested up to: 6.2.2
Requires PHP: 5.6
Stable tag: 5.28
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

= 5.28 =
Fix HPOS. Mejoras de código

= 5.27 =
Compatibilidad con HPOS.

= 5.26 =
Añadir método de pago Waylet. Mejoras de código.

= 5.25 =
Mejoras de código.

= 5.24 =
Cambio envío información de descuento

= 5.23 =
Cambiar estilos del PAN y CVC2 en jetIframe.

= 5.22 =
Recoger/mostrar información de Multibanco. Eliminación Tokens Caducados

= 5.21 =
Mejoras pedidos pendientes de pago

= 5.20 =
Mejoras de código. Control en la obtención precio unitario.

= 5.19 =
Mejoras de código. Llamada al formulario de pago

= 5.18 =
Mejoras en pago JetIframe

= 5.17 =
Añadir métodos de pago Klarna Payments y PayPal

= 5.16 =
Añadir opción de pago por DCC

= 5.15 =
Mejoras de código

= 5.14 =
Mejoras de código

= 5.13 =
Depuración de código

= 5.12 =
Se actualiza el token del pedido padre en suscripciones

= 5.11 =
Mejoras de código

= 5.10 =
Actualizacion logo IC

= 5.9 =
Mejoras en maquetación
Instant Credit: Posibilidad de configurar el Simulador en Test

= 5.8 =
Se elimina la opción de Paypal de los APMs
Se hacen traducibles el Titulo y Descripción de las opciones de pago cuando estén definidos los textos por defecto

= 5.7 =
Mejoras de código en la obtencion de la Ip del cliente

= 5.6 =
Mejoras de código

= 5.5 =
Mejoras en la obtención de la IP

= 5.4 =
Simulador de cuotas en APM Instant Credit

= 5.3 =
Nuevas validaciones de campos PSD2

= 5.2 =
Cambios en estilos
Validación campos PSD2

= 5.1 =
Fix! Se añaden fichero que faltaban a la release

= 5.0 =
¡¡ API Key Obligatoria !! A partir de esta versión es necesario para el funcionamiento del plugin.
APMs se muestran como nuevas opciones de pago.
Eliminar campos no necesarios con PSD2.

= 4.30 =
Cambios de estilos por compatiblidad con temas.

= 4.29 =
Cambio en la anchura minima iframe.

= 4.28 =
Validación de parámetros PSD2

= 4.27 =
Depuración de código en pagos de suscripción

= 4.26 =
Redirección a la Challenge URL para SCA en caso de que se requiera.

= 4.25 =
Validación de parametros PSD2

= 4.24 =
Cambios de css

= 4.23 =
Validación parámetros API REST

= 4.22 =
Depuración de código

= 4.21 =
Se añade integración JetIframe para incluir formulario de pago en el checkout.
Se añade Integración REST y parámetros PSD2. Es necesario configurar el nuevo parámetro API Key. La API Key se genera en el Panel de PAYCOMET.

= 4.20 =
Cambios en la definicion del Iframe de pago para compatibilidad con diversos temas de Wordpress.

= 4.19 =
Cambios en estilos.

= 4.18 =
Se añade la opción de pagar en la página de Paycomet y poder definir la altura del iframe.

= 4.17 =
Compatibilidad WC 4.2

= 4.16 =
Quitada opción de pago con contraseña y añadida opción de deshabilitar guardado de tarjetas

= 4.15 =
Soporte para idioma Catalan

= 4.14 =
Mejoras validaciones funciones API

= 4.13 =
Mejoras para compatibilidad con plugins de terceros

= 4.12 =
Se sube una imagen que faltaba

= 4.11 =
Cambio de logo por el de Paycomet

= 4.10 =
Actualización de la versión

= 4.10 =
Actualización de la versión

= 4.9 =
Depuración de código.

= 4.8 =
Mejoras de código.

= 4.7 =
Actualización librería nusoap.

= 4.6 =
Soporte para pagos de pedidos creados desde el Backoffice.

= 4.5 =
Mejoras en la validación de los datos

= 4.4 =
Se amplia el tamaño del iframe.

= 4.3 =
Depuracion de código.

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