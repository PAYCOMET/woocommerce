const mygateway_data = window.wc.wcSettings.getSetting( 'mygateway_data', {} );
const mygateway_label = window.wp.htmlEntities.decodeEntities( mygateway_data.title )
	|| window.wp.i18n.__( 'Bizum', 'mygateway' );
const mygateway_content = ( mygateway_data ) => {
	return window.wp.htmlEntities.decodeEntities( mygateway_data.description || '' );
};
const MyGateway = {
	name: 'Bizum',
	label: mygateway_label,
	content: Object( window.wp.element.createElement )( mygateway_content, null ),
	edit: Object( window.wp.element.createElement )( mygateway_content, null ),
	canMakePayment: () => true,
	placeOrderButtonLabel: window.wp.i18n.__( 'Continue', 'Bizum' ),
	ariaLabel: mygateway_label,
	supports: {},
};
console.log(Object.values(MyGateway));
window.wc.wcBlocksRegistry.registerPaymentMethod( MyGateway );


