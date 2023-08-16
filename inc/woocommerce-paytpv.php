<?php
	/**
	 * Pasarela PAYCOMET Gateway Class
	 *
	 */
	use Automattic\WooCommerce\Utilities\OrderUtil;
	class woocommerce_paytpv extends WC_Payment_Gateway
	{

        public function write_log( $log )
        {
			if ( true === WP_DEBUG ) {
				if ( is_array($log) || is_object($log)) {
					error_log(print_r($log, true));
				} else {
					error_log($log);
				}
			}
		}

		public function __construct($loadHooks = true)
		{
			$this->id = 'paytpv';
			$this->icon = PAYTPV_PLUGIN_URL . 'images/paycomet.png';
			$this->has_fields = false;
			$this->method_title = 'PAYCOMET';
            $this->method_description = __('Payment gateway for credit card payment. Configuration for PayComet and alternative payment methods.', 'wc_paytpv' );
			$this->supports = array(
				'products',
				'refunds',
				'subscriptions',
				'subscription_cancellation',
				'subscription_suspension',
				'subscription_reactivation',
				'subscription_amount_changes',
				'subscription_date_changes'
			);
			// Load the form fields
			$this->init_form_fields();

			// Load the settings.
			$this->init_settings();

			$this->iframeurl = 'https://api.paycomet.com/gateway/ifr-bankstore';

			// Get setting values
			$this->enabled = $this->settings['enabled'];
			$this->title = $this->settings['title'];
			$this->description = $this->settings['description'];

			if ($this->title == "Pagar con tarjeta") {
				$this->title = __( 'Pay with card', 'wc_paytpv' );
			}
			if ($this->description == "Pague con tarjeta de crédito de la forma más segura") {
				$this->description = __( 'Pay using your credit card in a secure way', 'wc_paytpv' );
			}
			$this->clientcode = $this->settings['clientcode'];
			$this->apiKey = isset($this->settings['apikey'])?$this->settings['apikey']:"";

			$this->paytpv_terminals = get_option('woocommerce_paytpv_terminals',
				array(
					array(
						'term' => $this->get_option('term'),
						'pass' => $this->get_option('pass'),
						'moneda' => $this->get_option('moneda'),
						'dcc' => $this->get_option('dcc')
					)
				)
			);

			$this->disable_offer_savecard = isset($this->settings['disable_offer_savecard']) ? $this->settings['disable_offer_savecard'] : 0;
			$this->payment_paycomet = isset($this->settings['payment_paycomet']) ? $this->settings['payment_paycomet'] : 0;
			$this->jet_id = isset($this->settings['jet_id']) ? $this->settings['jet_id'] : '';
			$this->pan_div_style = (isset($this->settings['pan_div_style']) && $this->settings['pan_div_style'] != "") ? $this->settings['pan_div_style'] : 'width: 290px; padding:0px; height:34px; border: 1px solid #dcd7ca';
			$this->pan_input_style = (isset($this->settings['pan_input_style']) && $this->settings['pan_input_style'] != "") ? $this->settings['pan_input_style'] : 'height: 30px; font-size:18px; padding-top:2px; border:0px;';
			$this->cvc2_div_style = (isset($this->settings['cvc2_div_style']) && $this->settings['cvc2_div_style'] != "") ? $this->settings['cvc2_div_style'] : 'height: 34px; padding:0px;';
			$this->cvc2_input_style = (isset($this->settings['cvc2_input_style']) && $this->settings['cvc2_input_style'] != "") ? $this->settings['cvc2_input_style'] : 'width: 60px; height: 30px; font-size:18px; padding-left:7px; border: 1px solid #dcd7ca;';
			$this->iframe_height = isset($this->settings['iframe_height']) ? $this->settings['iframe_height'] : 440;
			$this->isJetIframeActive = $this->payment_paycomet === '2';

			if ($this->isJetIframeActive){
				$this->has_fields = true;
			}

			// Verificar campos obligatorios para que esté habilitado.
			if ($this->clientcode == "" || $this->paytpv_terminals[0]["term"] == "" || $this->paytpv_terminals[0]["pass"] == "" || ($this->isJetIframeActive && $this->jet_id == "")) {
				$this->enabled = false;
			}

			// Hooks
			if ($loadHooks) {

				add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));

				add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
				//add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'save_terminals_details' ) );
				add_action('woocommerce_api_woocommerce_' . $this->id, array($this, 'check_' . $this->id . '_resquest'));

				add_action('admin_notices', array($this, 'validate_paytpv'));

				// Subscriptions
				add_action('woocommerce_scheduled_subscription_payment_' . $this->id, array( $this, 'scheduled_subscription_payment'), 10, 2 );
				add_filter('wcs_resubscribe_order_created', array( $this, 'store_renewal_order_id'), 10, 4 );

				//JetIframe integration
				if ($this->isJetIframeActive) {
					add_action('woocommerce_review_order_before_submit', array($this, 'addFieldForJetiframeToken'));
					add_action('woocommerce_pay_order_before_submit', array($this, 'addFieldForJetiframeToken'));
					add_filter('woocommerce_pay_order_button_html', array( $this, 'woocommerce_pay_order_button_html_filter'), 10, 4 );
				}
			}
		}

		public function woocommerce_pay_order_button_html_filter( $html ) {
			// The text of the button
			$order_button_text = __('Place order', 'woocommerce');

			return '<input type="submit" class="button alt" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">';
		}

		public function addFieldForJetiframeToken() {
			print '<input type="hidden" id="jetiframe-token" name="jetiframe-token">';
			print '<input type="checkbox" id="savecard_jetiframe" name="savecard_jetiframe" style="display:none">';
			print '<input type="text" id="hiddenCardField" name="hiddenCardField" style="display:none">';
		}

		/**
		 * Loads the my-subscriptions.php template on the My Account page.
		 *
		 * @since 1.0
		 */
		public static function get_my_cards_template()
		{
			$user_id = get_current_user_id();
			$saved_cards = Paytpv::savedClientCards($user_id);
			$operation = 107;

			// Obtenemos el terminal para el pedido
			// El primer terminal configurado
			$gateway = new self();
			$terminal = $gateway->paytpv_terminals[0];
			$term = $terminal["term"];
			$pass = $terminal["pass"];
			$dcc = $terminal["dcc"];
			$order = $user_id;

			$url_paytpv = "";

			if ($gateway->apiKey != '') {
				try {
					$apiRest = new PaycometApiRest($gateway->apiKey);
					$apiResponse = $apiRest->form(
						$operation,
						$gateway->_getLanguange(),
						$term,
						'',
						[
							'terminal' => (int) $term,
							'order' => (string) $order,
							'urlOk' => (string) get_permalink( get_option('woocommerce_myaccount_page_id') ),
							'urlKo' => (string) get_permalink( get_option('woocommerce_myaccount_page_id') ),
						]
					);

					if ($apiResponse->errorCode==0) {
						$url_paytpv = $apiResponse->challengeUrl;
					} else {
						if ($apiResponse->errorCode==1004) {
							$error_txt = __( 'Error: ', 'wc_paytpv' ) . $apiResponse->errorCode;
						} else {
							$error_txt = __( 'An error has occurred. Please verify the data entered and try again', 'wc_paytpv' );
						}
						print '<p>' . $error_txt .'</p>';
						$gateway->write_log('Error ' . $apiResponse->errorCode . " en form");
						exit;
					}
				} catch (exception $e){
					$url_paytpv = "";
				}

			} else {
				print '<p>' . __( 'Error: ', 'wc_paytpv' ) . "1004" .'</p>';
				$gateway->write_log('Error 1004. ApiKey vacía');
				exit;
			}

			$disable_offer_savecard = $gateway->disable_offer_savecard;
			$payment_paycomet = $gateway->payment_paycomet;
			$jet_id = $gateway->jet_id;

			wc_get_template( 'myaccount/my-cards.php',
			array(
				'isJetIframeActive' => ($gateway->isJetIframeActive)?1:0,
				'disable_offer_savecard' => $disable_offer_savecard,
				'saved_cards' => $saved_cards,
				'jet_id' => $jet_id,
				'apiKey' => $gateway->apiKey,
				'term' => $term,
				'pass' => $pass,
				'dcc' => $dcc,
				'clientcode' => $gateway->clientcode,
				'settings' => $gateway->settings,
				'user_id' => get_current_user_id(),
				'url_paytpv'=> $url_paytpv,
				'payment_paycomet'=> $payment_paycomet,
				'pan_div_style' => $gateway->pan_div_style,
				'pan_input_style' => $gateway->pan_input_style,
				'cvc2_div_style' => $gateway->cvc2_div_style,
				'cvc2_input_style' => $gateway->cvc2_input_style
			), '', PAYTPV_PLUGIN_DIR . 'template/' );
		}

		public function validate_paytpv()
		{
			if (empty($this->paytpv_terminals))
		    	echo '<div class="error"><p>'.__('You must define at least one terminal', 'wc_paytpv' ).'</p></div>';
		}

		/**
		 * There are no payment fields for PAYCOMET, but we want to show the description if set.
		 * */
		function payment_fields()
		{

			if ($this->apiKey != '') {
				if ( $this->description)
					echo wpautop( wptexturize( $this->description ) );
				if ($this->isJetIframeActive) {
					//if (defined('DOING_AJAX') && DOING_AJAX) {
						wc_get_template( 'checkout/jetiframe-checkout.php', array('jet_id' => $this->jet_id, 'disable_offer_savecard' => $this->disable_offer_savecard,'pan_div_style' => $this->pan_div_style,'pan_input_style' => $this->pan_input_style,'cvc2_div_style' => $this->cvc2_div_style,'cvc2_input_style' => $this->cvc2_input_style), '', PAYTPV_PLUGIN_DIR . 'template/' );
					//}
				}
			} else {
				$this->write_log('Error 1004. ApiKey vacía');
				print '<p>' . __( 'Error: ', 'wc_paytpv' ) . "1004" .'</p>';
			}
		}

		/**
		 * Admin Panel Options
		 * - Options for bits like 'title' and availability on a country-by-country basis
		 * */
		public function admin_options()
		{
			?>
			<h3><?php _e( 'PAYCOMET Payment Gateway', 'wc_paytpv' ); ?></h3>
			<p>
				<?php _e( '<a href="https://www.paycomet.com">PAYCOMET Online</a> payment gateway for Woocommerce enables credit card payment in your shop. All you need is a PAYCOMET merchant account and access to <a href="https://dashboard.paycomet.com/cp_control">customer area</a>', 'wc_paytpv'  ); ?>
			</p>
			<p>
				<?php _e( 'There you should configure "Tipo de notificación del cobro:" as "Notificación por URL" set ther teh following URL:', 'wc_paytpv'  ); ?> <?php echo add_query_arg( 'tpvLstr', 'notify', add_query_arg( 'wc-api', 'woocommerce_' . $this->id, home_url( '/' ) ) ); ?></p>
			</p>
			<table class="form-table">
				<?php $this->generate_settings_html(); ?>
			</table><!--/.form-table-->
			<?php
		}

		public function process_admin_options()
        {
            $settings = new WC_Admin_Settings();
			$postData = $this->get_post_data();
			$error = false;

			// Si se activa el Módulo se verifican los datos
			if (isset($_REQUEST["woocommerce_paytpv_enabled"]) && $_REQUEST["woocommerce_paytpv_enabled"]==1) {

				// Validate required fields
				if (empty($postData['woocommerce_paytpv_apikey']) ||
				empty($postData['woocommerce_paytpv_clientcode']) ||
				$postData['term'][0] == "" ||
				$postData['pass'][0] == ""
				) {
					$error = true;
					$settings->add_error(__('ERROR: Unable to activate payment method.','wc_paytpv')  . " " . __('Please fill in required fields: API Key, Client Code, Terminal Number, Password.','wc_paytpv'));
				}

				// Validate required fields
				if ($postData['woocommerce_paytpv_payment_paycomet'] == 0 && (!filter_var($postData['woocommerce_paytpv_iframe_height'], FILTER_VALIDATE_INT) ||  $postData['woocommerce_paytpv_iframe_height'] < 440))
				{
					$error = true;
					$settings->add_error(__('ERROR: The height of the iframe must be at least 440.','wc_paytpv'));
				}

				if($postData['woocommerce_paytpv_payment_paycomet'] == 2 && $postData['woocommerce_paytpv_jet_id'] == '')
				{
					$error = true;
					$settings->add_error(__('ERROR: The JetId field must be filled if the payment method is JetIframe','wc_paytpv'));
				}

				// Validate info Paycomet
				if (!$error) {
					$arrValidatePaycomet = $this->validatePaycomet($postData);
					if ($arrValidatePaycomet["error"] != 0) {
						$error = true;
						$settings->add_error(__('ERROR: Unable to activate payment method.','wc_paytpv') . " " . $arrValidatePaycomet["error_txt"]);
					}
				}

			}

			// Si hay error guardamos los datos pero no dejamos habilitar el método de pago
			if ($error) {
				unset($_POST["woocommerce_paytpv_enabled"]);
			}

			$this->save_terminals_details();

            return parent::process_admin_options();
		}

		private function validatePaycomet($postData)
		{
			$api = new PaytpvApi();

			$arrDatos = array();
			$arrDatos["error"] = 0;

			// Validación de los datos en Paycomet
			foreach (array_keys($postData["term"]) as $key) {
				$term = ($postData['term'][$key] == '') ? "" : $postData['term'][$key];
				$terminales_txt = "CES";
				$terminales_info = "Secure";

				$resp = $api->validatePaycomet(
					$postData['woocommerce_paytpv_clientcode'],
					$term,
					$postData['pass'][$key],
					$terminales_txt
				);

				if ($resp["DS_RESPONSE"] != 1) {
					$arrDatos["error"] = 1;
					switch ($resp["DS_ERROR_ID"]) {
						case 1121:  // No se encuentra el cliente
						case 1130:  // No se encuentra el producto
						case 1003:  // Credenciales inválidas
						case 127:   // Parámetro no válido.
							$arrDatos["error_txt"] = __('Check that the Client Code, Terminal and Password are correct','wc_paytpv');
							break;

						case 1337:  // Ruta de notificación no configurada
							$arrDatos["error_txt"] = __('Notification URL is not defined in the product configuration of your account PAYCOMET account.','wc_paytpv');
							break;

						case 28:    // Curl
						case 1338:  // Ruta de notificación no responde correctamente
							$arrDatos["error_txt"] = __('The notification URL defined in the product configuration of your PAYCOMET account does not respond correctly. Verify that it has been defined as: ','wc_paytpv')
							. add_query_arg( 'tpvLstr', 'notify', add_query_arg( 'wc-api', 'woocommerce_' . $this->id, home_url( '/' ) ) );
							break;

						case 1339:  // Configuración de terminales incorrecta
							$arrDatos["error_txt"] = __('Your Product in PAYCOMET account is not set up with the Available Terminals option: ','wc_paytpv') . $terminales_info;
							break;
					}

					return $arrDatos;
				}
			}

			return $arrDatos;
		}

		/**
		 * Save account details table
		 */
		public function save_terminals_details()
		{
			$terminals = array();

			if ( isset( $_POST['term'] ) ) {
				$term   = array_map( 'wc_clean', $_POST['term'] );
				$pass = array_map( 'wc_clean', $_POST['pass'] );
				$moneda           = array_map( 'wc_clean', $_POST['moneda'] );
				$dcc = array_map( 'wc_clean', $_POST['dcc'] );
				foreach ( $term as $i => $name ) {
					if ( ! isset( $term[ $i ] ) ) {
						continue;
					}

					$terminals[] = array(
						'term'   => $term[ $i ],
						'pass' => $pass[ $i ],
						'moneda' => $moneda[ $i ],
						'dcc' => $dcc[ $i ]
					);
				}
			}

			update_option('woocommerce_paytpv_terminals', $terminals);
		}

        public static function load_resources()
        {
			global $hook_suffix;

			wp_register_style( 'lightcase.css', PAYTPV_PLUGIN_URL . 'css/lightcase.css', PAYTPV_VERSION );
			wp_enqueue_style( 'lightcase.css');

			wp_register_style( 'paytpv.css', PAYTPV_PLUGIN_URL . 'css/paytpv.css', PAYTPV_VERSION );
			wp_enqueue_style( 'paytpv.css');

			wp_register_script( 'paytpv.js', PAYTPV_PLUGIN_URL . 'js/paytpv.js', array('jquery'),  PAYTPV_VERSION );
			wp_enqueue_script( 'paytpv.js' );

			wp_register_script( 'lightcase.js', PAYTPV_PLUGIN_URL . 'js/lightcase.js', array('jquery'), PAYTPV_VERSION );
			wp_enqueue_script( 'lightcase.js' );
		}

        public static function load_resources_conf()
        {
			global $hook_suffix;

			wp_register_style( 'paytpv.css', PAYTPV_PLUGIN_URL . 'css/paytpv.css', PAYTPV_VERSION );
			wp_enqueue_style( 'paytpv.css');

			wp_register_script( 'paytpv_conf.js', PAYTPV_PLUGIN_URL . 'js/paytpv_conf.js', array('jquery'),  PAYTPV_VERSION );
			wp_enqueue_script( 'paytpv_conf.js' );
		}

		/**
		 * Initialize Gateway Settings Form Fields
		 */
        function init_form_fields()
        {
			$this->form_fields = array(
				'enabled' => array(
					'title' => __( 'Enable/Disable', 'wc_paytpv' ),
					'label' => __( 'Enable PAYCOMET gateway', 'wc_paytpv' ),
					'type' => 'checkbox',
					'description' => '',
					'default' => 'no'
				),
				'title' => array(
					'title' => __( 'Title', 'wc_paytpv' ),
					'type' => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'wc_paytpv' ),
					'default' => __( 'Pay with card', 'wc_paytpv' ),
                	'desc_tip'    => true
				),
				'description' => array(
					'title' => __( 'Description', 'wc_paytpv' ),
					'type' => 'textarea',
					'class' => 'description',
					'description' => __( 'This controls the description which the user sees during checkout.', 'wc_paytpv' ),
					'default' => __( 'Pay using your credit card in a secure way', 'wc_paytpv' ),
                	'desc_tip'    => true
				),
				'apikey' => array(
					'title' => __('API Key', 'wc_paytpv' ),
					'type' => 'text',
					'class' => 'api_key',
					'description' => __( 'Your API Key from PayComet. Read documentation <a href="https://docs.paycomet.com/es/inicio/configuracion#apikeys">here</a>', 'wc_paytpv' ),
					'default' => ''
				),
				'clientcode' => array(
					'title' => __( 'Client code', 'wc_paytpv' ),
					'type' => 'text',
					'class' => 'clientcode',
					'description' => '',
					'default' => ''
				),
				'paytpv_terminals' => array(
					'type'        => 'paytpv_terminals'
				),
				'payment_paycomet' => array(
					'title' => __( 'Integration with', 'wc_paytpv' ),
					'type' => 'select',
					'label' => '',
					'class' => 'payment_paycomet',
					'description' => __( 'Pay in a Iframe, Paycomet page or JetIframe.', 'wc_paytpv' ),
					'options' => array(
						0 => __('Iframe', 'wc_paytpv'),
						1 => __('Paycomet (Full Screen)', 'wc_paytpv'),
						2 => __('Jet Iframe', 'wc_paytpv'),
					),
					'desc_tip'    => true
				),
				'jet_id' => array(
					'title' => __('JetId', 'wc_paytpv' ),
					'type' => 'text',
					'class' => 'jet_id',
					'description' => __( 'Your JetId from PayComet', 'wc_paytpv' ),
					'default' => '',
					'desc_tip'    => true
				),
				'pan_div_style' => array(
                    'title' => __( 'PAN Div Style', 'wc_paytpv' ),
                    'type' => 'text',
                    'class' => 'jet_id',
                    'description' => __( 'Modify the style of the PAN Div', 'wc_paytpv' ) . ". <br/>" . __( 'Default', 'wc_paytpv' ) . ": height:34px; width: 290px; padding:0px; border: 1px solid #dcd7ca",
                    'default' => 'height:34px; width: 290px; padding:0px; border: 1px solid #dcd7ca',
                    'desc_tip'    => true
                ),
                'pan_input_style' => array(
                    'title' => __( 'PAN Input Style', 'wc_paytpv' ),
                    'type' => 'text',
                    'class' => 'jet_id',
                    'description' => __( 'Modify the style of the PAN Input', 'wc_paytpv' ) . ". <br/>" . __( 'Default', 'wc_paytpv' ) . ": <br/>height: 30px; font-size:18px; padding-top:2px; border:0px;",
                    'default' => 'height: 30px; font-size:18px; padding-top:2px; border:0px;',
                    'desc_tip'    => true
                ),
                'cvc2_div_style' => array(
                    'title' => __( 'CVC2 Div Style', 'wc_paytpv' ),
                    'type' => 'text',
                    'class' => 'jet_id',
                    'description' => __( 'Modify the style of the CVC2 Div', 'wc_paytpv' ) . ". <br/>" . __( 'Default', 'wc_paytpv' ) . ": <br/>height: 34px; padding:0px;",
                    'default' => 'height: 34px; padding:0px;',
                    'desc_tip'    => true
                ),
                'cvc2_input_style' => array(
                    'title' => __( 'CVC2 Input Style', 'wc_paytpv' ),
                    'type' => 'text',
                    'class' => 'jet_id',
                    'description' => __( 'Modify the style of the CVC2 Input', 'wc_paytpv' ) . ". <br/>" . __( 'Default', 'wc_paytpv' ) . ": <br/>height: 30px; width: 60px; font-size:18px; padding-left:7px; border: 1px solid #dcd7ca;",
                    'default' => 'height: 30px; width: 60px; font-size:18px; padding-left:7px; border: 1px solid #dcd7ca;',
                    'desc_tip'    => true
                ),
				'iframe_height' => array(
					'title' => __( 'Iframe Height (px)', 'wc_paytpv' ),
					'type' => 'text',
					'class' => 'iframe_height',
					'description' => __( 'Iframe height in pixels (Min 440)', 'wc_paytpv' ),
					'default' => '440',
					'desc_tip'    => true
				),
				'disable_offer_savecard' => array(
					'title' => __( 'Disable Offer to save card', 'wc_paytpv' ),
					'type' => 'select',
					'label' => '',
					'options' => array(
						0 => __( 'No', 'wc_paytpv' ),
						1 => __( 'Yes', 'wc_paytpv' )
					),
					'default' => '0',
					'desc_tip'    => true
				)
			);
		}

		/**
	 	* generate_account_details_html function.
	 	*/
		public function generate_paytpv_terminals_html()
		{
			ob_start();

			?>
			<tr valign="top">
				<th class="titledesc"><?php _e( 'Terminals', 'wc_paytpv' ); ?></th>
				<td colspan="2" class="forminp" id="paytpv_terminals">
					<table class="tblterminals widefat wc_input_table sortable" style="font-size:80%" cellspacing="0">
						<thead>
							<tr>
								<th class="sort">&nbsp;</th>
								<th><?php _e( 'Terminal Number', 'wc_paytpv' ); ?></th>
								<th><?php _e( 'Password', 'wc_paytpv' ); ?></th>
								<th><?php _e( 'Currency', 'wc_paytpv' ); ?></th>
								<th><?php _e( 'DCC', 'wc_paytpv' ); ?></th>
							</tr>
						</thead>
						<tbody class="accounts">
							<?php
							$i = -1;

							$arrMonedas = get_woocommerce_currencies();
							$arrDCC = array(__( 'No', 'wc_paytpv' ),__( 'Yes', 'wc_paytpv' ));

							// Un terminal por defecto en la moneda de woocommerce
							if (empty($this->paytpv_terminals)){
								$this->paytpv_terminals[0] = array("term"=>"","pass"=>"","moneda"=> get_woocommerce_currency(),"dcc"=>0);
							}

							if ( $this->paytpv_terminals){
								foreach ( $this->paytpv_terminals as $key=>$terminal){
									$i++;

									echo '<tr class="terminal">
										<td class="sort"></td>
										<td><input type="text" value="' . esc_attr( wp_unslash( $terminal['term'] ) ) . '" name="term[]" /></td>
										<td><input class="pass" type="text" value="' . esc_attr( wp_unslash( $terminal['pass'] ) ). '" name="pass[]" /></td>
									';
									echo '<td><select class="moneda" name="moneda[]">
										';
										foreach ($arrMonedas as $key=>$val){
											$selected = ($key==$terminal['moneda'] || ($terminal['moneda']=="" && $key==get_woocommerce_currency()))?"selected":"";
											echo '<option value="'.$key.'" '.$selected.'>'.$val.'</option>';
										}
									echo '</select></td>';
									echo '<td><select class="dcc" name="dcc[]">';
									foreach ($arrDCC as $key=>$val){
										$selected = ($key==$terminal['dcc'] || ($terminal['dcc']=="" && $key==0))?"selected":"";
										echo '<option value="'.$key.'" '.$selected.'>'.$val.'</option>';
									}
									echo '</select></td>';
									echo '</tr>';
								}
							}
							?>
						</tbody>
						<tfoot>
							<tr>
								<th colspan="7"><a href="#" class="add button"><?php _e( '+ Add Terminal', 'wc_paytpv' ); ?></a> <a href="#" class="remove_term button"><?php _e( '- Remove Terminal', 'wc_paytpv' ); ?></a></th>
							</tr>
						</tfoot>
					</table>

				</td>
			</tr>
			<p id="msg_1terminal" style="display:none"><?php print __('Must have at least one terminal configured to process payments.', 'wc_paytpv');?></p>
			<p id="msg_moneda_terminal" style="display:none"><?php print __('There can be two terminals configured with the same currency.', 'wc_paytpv');?></p>
            <?php

			return ob_get_clean();
		}

		/**
		 * Check for PAYCOMET IPN Response
		 * */
        function check_paytpv_resquest()
        {
			if (!isset($_REQUEST['tpvLstr']))
				return;

			if (isset($_REQUEST['Order']) ){
				$datos_order = explode("_",$_REQUEST['Order']); // En los pagos de suscripcion viene {id_order}_{numpago}
				$ref = $datos_order[0];
				try {
					$order = new WC_Order( ( int ) substr( $ref, 0, 8 ) );
				} catch (exception $e) {}
			}

			// Check Notification URL
			if (isset($_REQUEST['ping']) && $_REQUEST['ping'] == 1) {
				die("PING OK");
			}

			// Get Data
			if (isset($_POST['paycomet_data']) && $_POST['paycomet_data'] == 1) {
				global $woocommerce;
				global $wp_version;
				if (isset($_POST["clientcode"]) &&
					$_POST["clientcode"] == $this->clientcode &&
					isset($_POST["terminal"]) &&
					$_POST["terminal"]==$this->paytpv_terminals[0]["term"]
				) {
					$apiKey = ($this->apiKey != '')?1:0;
					$enabled = ($this->enabled == "yes")?1:0;
					$arrDatos = array(
						"m_v" => PAYTPV_VERSION,
						"wp_v" => $wp_version,
						"wc_v" => $woocommerce->version,
						"e" => $enabled,
						"ak" => $apiKey
					);
					exit(json_encode($arrDatos));
				}
			}


			if ( $_REQUEST[ 'tpvLstr' ] == 'pay' && $order->get_status() != 'completed' ) { //PAGO CON TARJETA GUARDADA

				$card = $_POST[ 'card' ];
				$saved_card = PayTPV::savedCard($order->get_user_id(),$card);

				// Obtenemos el terminal para el pedido
				$arrTerminalData = $this->TerminalCurrency($order);
				$importe = $arrTerminalData["importe"];
				$currency_iso_code = $arrTerminalData["currency_iso_code"];
				$term = $arrTerminalData["term"];
				$pass = $arrTerminalData["pass"];
				$paytpv_order_ref = $order->get_id();
				$paytpv_order_ref = str_pad($paytpv_order_ref, 8, "0", STR_PAD_LEFT);

				$secure_pay = 1;

				$URLOK = $this->get_return_url( $order );
				$URLKO = $order->get_cancel_order_url_raw();

				$salida = $URLKO; // Default



				// REST
				if ($this->apiKey != '') {
					$dcc = $arrTerminalData["dcc"];
					$OPERATION = ($dcc == 1)?116 : 1;
					$methodId = 1;
					$userInteraction = 1;
					$scoring = 0;

					$merchantData = $this->getMerchantData($order);

					try {

						$apiRest = new PaycometApiRest($this->apiKey);
						$apiResponse = $apiRest->form(
                            $OPERATION,
                            $this->_getLanguange(),
                            $term,
                            '',
                            [
                                'terminal' => $term,
                                'methods' => [$methodId],
                                'order' => $paytpv_order_ref,
                                'amount' => $importe,
                                'currency' => $currency_iso_code,
                                'idUser' => $saved_card["paytpv_iduser"],
                                'tokenUser' => $saved_card["paytpv_tokenuser"],
                                'userInteraction' => $userInteraction,
                                'secure' => $secure_pay,
                                'merchantData' => $merchantData,
                                'urlOk' => $URLOK,
                                'urlKo' => $URLKO
                            ]
                        );

						if ($apiResponse->errorCode==0) {
							$salida = $apiResponse->challengeUrl;
						} else {
							if ($apiResponse->errorCode==1004) {
								$error_txt = __( 'Error: ', 'wc_paytpv' ) . $apiResponse->errorCode;
							} else {
								$error_txt = __( 'An error has occurred. Please verify the data entered and try again', 'wc_paytpv' );
							}
							wc_add_notice($error_txt, 'error' );
							$this->write_log('Error ' . $apiResponse->errorCode . " en form");
						}

					} catch (exception $e){
						$error_txt = __( 'An error has occurred. Please verify the data entered and try again', 'wc_paytpv' );
						wc_add_notice($error_txt, 'error' );
					}

				} else {
					wc_add_notice(__( 'Error: ', 'wc_paytpv' ) . "1004", 'error' );
					$this->write_log('Error 1004. ApiKey vacía');
					$salida = $URLKO;
				}

				header('Location: '. $salida);
				exit;

				// PAGO NO SEGURO --------------------------------------------------------------------------
				$ip = $this->getIp();

				$userInteraction = 1;

				// REST
				if ($this->apiKey != '') {

					$URLOK = $this->get_return_url($order);
					$URLKO = $order->get_cancel_order_url_raw();

					$methodId = 1;
					$scoring = 0;
					$notifyDirectPayment = 1;

					$merchantData = $this->getMerchantData($order);

					try {

						$apiRest = new PaycometApiRest($this->apiKey);
						$executePurchaseResponse = $apiRest->executePurchase(
							$term,
							$paytpv_order_ref,
							$importe,
							$currency_iso_code,
							$methodId,
							$ip,
							$secure_pay,
							$saved_card["paytpv_iduser"],
							$saved_card["paytpv_tokenuser"],
							$URLOK,
							$URLKO,
							$scoring,
							'',
							'',
							$userInteraction,
							[],
							'',
							'',
							$merchantData,
							$notifyDirectPayment
						);

						$charge["DS_RESPONSE"] 			= ($executePurchaseResponse->errorCode > 0)? 0 : 1;
						$charge["DS_ERROR_ID"] 			= $executePurchaseResponse->errorCode;
						$charge["DS_MERCHANT_AUTHCODE"] = $executePurchaseResponse->authCode ?? '';
						$charge["DS_MERCHANT_AMOUNT"] 	= $executePurchaseResponse->amount ?? 0;
						$charge["DS_CHALLENGE_URL"] 	= $executePurchaseResponse->challengeUrl ?? '';

						if ($executePurchaseResponse->errorCode > 0) {
							$this->write_log('Error ' . $executePurchaseResponse->errorCode . " en executePurchase");
						}

					} catch (Exception $e) {
						$charge["DS_ERROR_ID"] = $executePurchaseResponse->errorCode;
					}

				}  else {
					$charge["DS_RESPONSE"] = 0;
					$charge["DS_ERROR_ID"] = 1004;
					$this->write_log('Error 1004. ApiKey vacía');
				}

				// Si hay challenge redirigimos al cliente a la URL
				if ($charge[ 'DS_CHALLENGE_URL' ] != '') {

					if ( class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
						$order->add_meta_data('PayTPV_IdUser', $saved_card["paytpv_iduser"] );
						$order->add_meta_data('PayTPV_TokenUser', $saved_card["paytpv_tokenuser"] );
						$order->save();
					} else {
						update_post_meta( ( int ) $order->get_id(), 'PayTPV_IdUser', $saved_card["paytpv_iduser"] );
						update_post_meta( ( int ) $order->get_id(), 'PayTPV_TokenUser', $saved_card["paytpv_tokenuser"] );
					}

					$url = urldecode($charge[ 'DS_CHALLENGE_URL' ]);
				// Si es OK
				} else if (( int ) $charge[ 'DS_RESPONSE' ] == 1 ) {
					if ( class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
						$order->add_meta_data('PayTPV_IdUser', $saved_card["paytpv_iduser"] );
						$order->add_meta_data('PayTPV_TokenUser', $saved_card["paytpv_tokenuser"] );
						$order->save();
					} else {
						update_post_meta( ( int ) $order->get_id(), 'PayTPV_IdUser', $saved_card["paytpv_iduser"] );
						update_post_meta( ( int ) $order->get_id(), 'PayTPV_TokenUser', $saved_card["paytpv_tokenuser"] );
					}

					$url = $this->get_return_url( $order );
				// Si es KO
				} else {
					$url = $order->get_cancel_order_url_raw();
				}

				wp_redirect( $url, 303 );
				exit;
			}

			if ($_REQUEST[ 'tpvLstr' ] == 'notify' && isset($_POST["TransactionType"])) {//NOTIFICACIÓN

				switch ($_POST["TransactionType"]){
					// add_User
					case 107:

						$terminal = $this->paytpv_terminals[0];
						$term = $terminal["term"];
						$pass = $terminal["pass"];

						$user_id = $_POST["Order"];
						$DateTime = (isset($_POST[ 'DateTime']))?$_POST[ 'DateTime']:"";
						$sign = (isset($_POST[ 'NotificationHash']))?$_POST[ 'NotificationHash']:"";

						$localSign = hash('sha512',$this->clientcode . $term . $_POST["TransactionType"] . $_POST[ 'Order' ] . $DateTime . md5($pass));

						if ( $_REQUEST[ 'TransactionType' ] == '107' && $_REQUEST[ 'Response' ] == 'OK' && ($sign == $localSign)) {

							if (isset($_REQUEST[ 'IdUser' ])){
								// Save User Card
								$result = $this->saveCard(null, $user_id,$_REQUEST[ 'IdUser' ],$_REQUEST[ 'TokenUser' ],$_POST["TransactionType"]);
							}
						}

						print "PAYCOMET OK";

						exit;

					break;

					// execute_purchase
					case 1:
					case 109:
						$arrTerminalData = $this->TerminalCurrency($order);
						$currency_iso_code = $arrTerminalData["currency_iso_code"];
						$term = $arrTerminalData["term"];
						$pass = $arrTerminalData["pass"];
						if ( class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
							$idUser = $_REQUEST['IdUser'] ?? $order->get_meta('PayTPV_IdUser', true);
							$tokenUser = $_REQUEST['TokenUser'] ?? $order->get_meta('PayTPV_TokenUser', true);
						} else {
							$idUser = $_REQUEST['IdUser'] ?? get_post_meta((int) $order->get_id(), 'PayTPV_IdUser', true);
							$tokenUser = $_REQUEST['TokenUser'] ?? get_post_meta((int) $order->get_id(), 'PayTPV_TokenUser', true);
						}
						$mensaje = $this->clientcode .
								$term .
								$_REQUEST[ 'TransactionType' ] .
								$_REQUEST[ 'Order' ] .
								$_REQUEST[ 'Amount' ] .
								$currency_iso_code;

						$localSign = hash('sha512', $mensaje . md5( $pass ) . $_REQUEST[ 'BankDateTime' ] . $_REQUEST[ 'Response' ] );

						if ( ($_REQUEST[ 'TransactionType' ] == '1' || $_REQUEST[ 'TransactionType' ] == '109')  && $_REQUEST[ 'Response' ] == 'OK' && ($_REQUEST[ 'NotificationHash' ] == $localSign)) {
							// Para las operaciones con tarjeta.
							if (isset($idUser) && $_REQUEST[ 'MethodId' ]==1){
								if ( class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
									$save_card = $order->get_meta('paytpv_savecard', true );
								} else {
									$save_card = get_post_meta( ( int ) $order->get_id(), 'paytpv_savecard', true );
								}


								// Guardamos el token cuando el cliente lo ha marcado y cuando la opción Deshabilitar Almacenar Tarjeta esta desactivada.
								if (isset($save_card) && $save_card=="1" && $this->disable_offer_savecard==0){
									// Save User Card
									$result = $this->saveCard($order, $order->get_user_id(), $idUser, $tokenUser, $_POST["TransactionType"]);
								}

								if ( class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
									$order->add_meta_data('PayTPV_IdUser', $idUser );
									$order->add_meta_data('PayTPV_TokenUser', $tokenUser );
									$order->save();
								} else {
									update_post_meta((int) $order->get_id(), 'PayTPV_IdUser', $idUser);
									update_post_meta((int) $order->get_id(), 'PayTPV_TokenUser', $tokenUser);
								}

								// Si es de una suscripcion actualizamos el token del parent order para usarlo en los pagos sucesivos
								if ( class_exists( 'WC_Subscriptions_Renewal_Order' )) {
									if ( function_exists( 'wcs_get_subscriptions_for_renewal_order' )) {
										$subscriptions = wcs_get_subscriptions_for_renewal_order($order);
										$subscription  = array_pop( $subscriptions );
										if ($subscription && $subscription->get_parent_id()) {
											if ( class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
												$order->add_meta_data('PayTPV_IdUser', $saved_card["paytpv_iduser"] );
												$order->add_meta_data('PayTPV_TokenUser', $saved_card["paytpv_tokenuser"] );
												$order->save();
											} else {
												update_post_meta((int) $subscription->get_parent_id(), 'PayTPV_IdUser', $idUser);
												update_post_meta((int) $subscription->get_parent_id(), 'PayTPV_TokenUser', $tokenUser);
											}
										}
									}
								}
							}

							$order->add_order_note( __( 'PAYCOMET payment completed', 'woocommerce' ) );
							$order->payment_complete($_REQUEST[ 'AuthCode' ]);

							if ( class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
								$order->add_meta_data('PayTPV_Referencia', $_REQUEST[ 'Order' ] );
								$order->save();
							} else {
								update_post_meta( ( int ) $order->get_id(), 'PayTPV_Referencia', $_REQUEST[ 'Order' ] );
							}

							if ($_REQUEST[ 'MethodName' ]) {
								if ( class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
									$order->add_meta_data('PayTPV_MethodName', $_REQUEST[ 'MethodName' ] );
									$order->save();
								} else {
									update_post_meta( ( int ) $order->get_id(), 'PayTPV_MethodName', $_REQUEST[ 'MethodName' ] );
								}
							}

							print "PAYCOMET WC OK";

							exit;
						} else {
							print "PAYCOMET WC KO";
							if($_REQUEST[ 'MethodId' ] == 38){
								$order->update_status( 'cancelled', '', true );
							}

							exit;
						}

					break;
				}
				print "PAYCOMET WC ERROR";

				exit;
			}

			// Save Card in execute_purchase
			if ( $_REQUEST[ 'tpvLstr' ] == 'savecard' ) {//NOTIFICACIÓN
				if ( class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
					$order->add_meta_data('paytpv_savecard', $_POST["paytpv_agree"] );
					$order->save();
				} else {
					update_post_meta( ( int ) $order->get_id(), 'paytpv_savecard', $_POST["paytpv_agree"] );
				}

				exit;
			}

			// Save Card Description
			if ( $_REQUEST[ 'tpvLstr' ] == 'saveDesc' ) {//NOTIFICACIÓN
				$card_desc = $_POST["card_desc"];
				$id_card = $_GET["id"];

				Paytpv::saveCardDesc($id_card,$card_desc);

				$res["resp"] = 0;
				print json_encode($res);
				exit;
			}

			// Remove User Card
			if ( $_REQUEST[ 'tpvLstr' ] == 'removeCard' ) {//NOTIFICACIÓN
				$id_card = $_GET["id"];

				Paytpv::removeCard($id_card);

				$res["resp"] = 0;
				print json_encode($res);
				exit;
			}

			print "PAYCOMET WC ERROR 2";

			exit;
		}

		public function getIp($ref = false)
		{
			// Si llega referencia obtenemos la ip
			if ($ref !== false) {
				if ( class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
					$DS_ORIGINAL_IP = $order->get_meta('_customer_ip_address', true );
				} else {
					$DS_ORIGINAL_IP = get_post_meta( ( int ) $ref, '_customer_ip_address', true );
				}


				if (strpos($DS_ORIGINAL_IP, ":") !== false ) {
					$DS_ORIGINAL_IP = $_SERVER['REMOTE_ADDR'];
				}
			// Si no de remote address
			} else {
				if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
					//check ip from share internet
					$DS_ORIGINAL_IP = $_SERVER['HTTP_CLIENT_IP'];
				} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
					$ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
					$ips = array_map('trim', $ips);
					$DS_ORIGINAL_IP = $ips[0];
				} else {
					$DS_ORIGINAL_IP = $_SERVER['REMOTE_ADDR'];
				}
			}

			if ($DS_ORIGINAL_IP == "" || strpos($DS_ORIGINAL_IP, ":") !== false ) {
				$DS_ORIGINAL_IP = "127.0.0.1";
			}


			return $DS_ORIGINAL_IP;
		}

		/**
		 * Validate user password
		 * */
		public function validPassword($id, $passwd)
		{
			$user = new WP_User( $id);

			if (wp_check_password($passwd, $user->user_pass, $user->ID)){
				return true;
			}

			return false;
		}

		/**
		 * Get PAYCOMET language code
		 * */
		function _getLanguange()
		{
			$lng = substr( get_bloginfo( 'language' ), 0, 2 );
			if ( function_exists( 'qtrans_getLanguage' ) )
				$lng = qtrans_getLanguage();

			if ( defined( 'ICL_LANGUAGE_CODE' ) )
				$lng = ICL_LANGUAGE_CODE;

			switch ( $lng ) {
				case 'en':
					return 'EN';
				case 'fr':
					return 'FR';
				case 'de':
					return 'DE';
				case 'it':
					return 'IT';
				case 'ca':
					return 'CA';
				default:
					return 'ES';
			}
			return 'ES';
		}

		public function isoCodeToNumber($code)
		{
			$isoCodeNumber = 724; // Default value;

			$arrCode = array("AF" => "004", "AX" => "248", "AL" => "008", "DE" => "276", "AD" => "020", "AO" => "024", "AI" => "660", "AQ" => "010", "AG" => "028", "SA" => "682", "DZ" => "012", "AR" => "032", "AM" => "051", "AW" => "533", "AU" => "036", "AT" => "040", "AZ" => "031", "BS" => "044", "BD" => "050", "BB" => "052", "BH" => "048", "BE" => "056", "BZ" => "084", "BJ" => "204", "BM" => "060", "BY" => "112", "BO" => "068", "BQ" => "535", "BA" => "070", "BW" => "072", "BR" => "076", "BN" => "096", "BG" => "100", "BF" => "854", "BI" => "108", "BT" => "064", "CV" => "132", "KH" => "116", "CM" => "120", "CA" => "124", "QA" => "634", "TD" => "148", "CL" => "52", "CN" => "156", "CY" => "196", "CO" => "170", "KM" => "174", "KP" => "408", "KR" => "410", "CI" => "384", "CR" => "188", "HR" => "191", "CU" => "192", "CW" => "531", "DK" => "208", "DM" => "212", "EC" => "218", "EG" => "818", "SV" => "222", "AE" => "784", "ER" => "232", "SK" => "703", "SI" => "705", "ES" => "724", "US" => "840", "EE" => "233", "ET" => "231", "PH" => "608", "FI" => "246", "FJ" => "242", "FR" => "250", "GA" => "266", "GM" => "270", "GE" => "268", "GH" => "288", "GI" => "292", "GD" => "308", "GR" => "300", "GL" => "304", "GP" => "312", "GU" => "316", "GT" => "320", "GF" => "254", "GG" => "831", "GN" => "324", "GW" => "624", "GQ" => "226", "GY" => "328", "HT" => "332", "HN" => "340", "HK" => "344", "HU" => "348", "IN" => "356", "ID" => "360", "IQ" => "368", "IR" => "364", "IE" => "372", "BV" => "074", "IM" => "833", "CX" => "162", "IS" => "352", "KY" => "136", "CC" => "166", "CK" => "184", "FO" => "234", "GS" => "239", "HM" => "334", "FK" => "238", "MP" => "580", "MH" => "584", "PN" => "612", "SB" => "090", "TC" => "796", "UM" => "581", "VG" => "092", "VI" => "850", "IL" => "376", "IT" => "380", "JM" => "388", "JP" => "392", "JE" => "832", "JO" => "400", "KZ" => "398", "KE" => "404", "KG" => "417", "KI" => "296", "KW" => "414", "LA" => "418", "LS" => "426", "LV" => "428", "LB" => "422", "LR" => "430", "LY" => "434", "LI" => "438", "LT" => "440", "LU" => "442", "MO" => "446", "MK" => "807", "MG" => "450", "MY" => "458", "MW" => "454", "MV" => "462", "ML" => "466", "MT" => "470", "MA" => "504", "MQ" => "474", "MU" => "480", "MR" => "478", "YT" => "175", "MX" => "484", "FM" => "583", "MD" => "498", "MC" => "492", "MN" => "496", "ME" => "499", "MS" => "500", "MZ" => "508", "MM" => "104", "NA" => "516", "NR" => "520", "NP" => "524", "NI" => "558", "NE" => "562", "NG" => "566", "NU" => "570", "NF" => "574", "NO" => "578", "NC" => "540", "NZ" => "554", "OM" => "512", "NL" => "528", "PK" => "586", "PW" => "585", "PS" => "275", "PA" => "591", "PG" => "598", "PY" => "600", "PE" => "604", "PF" => "258", "PL" => "616", "PT" => "620", "PR" => "630", "GB" => "826", "EH" => "732", "CF" => "140", "CZ" => "203", "CG" => "178", "CD" => "180", "DO" => "214", "RE" => "638", "RW" => "646", "RO" => "642", "RU" => "643", "WS" => "882", "AS" => "016", "BL" => "652", "KN" => "659", "SM" => "674", "MF" => "663", "PM" => "666", "VC" => "670", "SH" => "654", "LC" => "662", "ST" => "678", "SN" => "686", "RS" => "688", "SC" => "690", "SL" => "694", "SG" => "702", "SX" => "534", "SY" => "760", "SO" => "706", "LK" => "144", "SZ" => "748", "ZA" => "710", "SD" => "729", "SS" => "728", "SE" => "752", "CH" => "756", "SR" => "740", "SJ" => "744", "TH" => "764", "TW" => "158", "TZ" => "834", "TJ" => "762", "IO" => "086", "TF" => "260", "TL" => "626", "TG" => "768", "TK" => "772", "TO" => "776", "TT" => "780", "TN" => "788", "TM" => "795", "TR" => "792", "TV" => "798", "UA" => "804", "UG" => "800", "UY" => "858", "UZ" => "860", "VU" => "548", "VA" => "336", "VE" => "862", "VN" => "704", "WF" => "876", "YE" => "887", "DJ" => "262", "ZM" => "894", "ZW" => "716");
			if (isset($arrCode[$code])) {
				$isoCodeNumber = $arrCode[$code];
			}
			return $isoCodeNumber;
		}

		public function isoCodePhonePrefix($code)
		{
			$isoCodePhonePrefix = "34"; // Default value;

			$arrCode = array("AC" => "247", "AD" => "376", "AE" => "971", "AF" => "93","AG" => "268", "AI" => "264", "AL" => "355", "AM" => "374", "AN" => "599", "AO" => "244", "AR" => "54", "AS" => "684", "AT" => "43", "AU" => "61", "AW" => "297", "AX" => "358", "AZ" => "374", "AZ" => "994", "BA" => "387", "BB" => "246", "BD" => "880", "BE" => "32", "BF" => "226", "BG" => "359", "BH" => "973", "BI" => "257", "BJ" => "229", "BM" => "441", "BN" => "673", "BO" => "591", "BR" => "55", "BS" => "242", "BT" => "975", "BW" => "267", "BY" => "375", "BZ" => "501", "CA" => "1", "CC" => "61", "CD" => "243", "CF" => "236", "CG" => "242", "CH" => "41", "CI" => "225", "CK" => "682", "CL" => "56", "CM" => "237", "CN" => "86", "CO" => "57", "CR" => "506", "CS" => "381", "CU" => "53", "CV" => "238", "CX" => "61", "CY" => "392", "CY" => "357", "CZ" => "420", "DE" => "49", "DJ" => "253", "DK" => "45", "DM" => "767", "DO" => "809", "DZ" => "213", "EC" => "593", "EE" => "372", "EG" => "20", "EH" => "212", "ER" => "291", "ES" => "34", "ET" => "251", "FI" => "358", "FJ" => "679", "FK" => "500", "FM" => "691", "FO" => "298", "FR" => "33", "GA" => "241", "GB" => "44", "GD" => "473", "GE" => "995", "GF" => "594", "GG" => "44", "GH" => "233", "GI" => "350", "GL" => "299", "GM" => "220", "GN" => "224", "GP" => "590", "GQ" => "240", "GR" => "30", "GT" => "502", "GU" => "671", "GW" => "245", "GY" => "592", "HK" => "852", "HN" => "504", "HR" => "385", "HT" => "509", "HU" => "36", "ID" => "62", "IE" => "353", "IL" => "972", "IM" => "44", "IN" => "91", "IO" => "246", "IQ" => "964", "IR" => "98", "IS" => "354", "IT" => "39", "JE" => "44", "JM" => "876", "JO" => "962", "JP" => "81", "KE" => "254", "KG" => "996", "KH" => "855", "KI" => "686", "KM" => "269", "KN" => "869", "KP" => "850", "KR" => "82", "KW" => "965", "KY" => "345", "KZ" => "7", "LA" => "856", "LB" => "961", "LC" => "758", "LI" => "423", "LK" => "94", "LR" => "231", "LS" => "266", "LT" => "370", "LU" => "352", "LV" => "371", "LY" => "218", "MA" => "212", "MC" => "377", "MD"  > "533", "MD" => "373", "ME" => "382", "MG" => "261", "MH" => "692", "MK" => "389", "ML" => "223", "MM" => "95", "MN" => "976", "MO" => "853", "MP" => "670", "MQ" => "596", "MR" => "222", "MS" => "664", "MT" => "356", "MU" => "230", "MV" => "960", "MW" => "265", "MX" => "52", "MY" => "60", "MZ" => "258", "NA" => "264", "NC" => "687", "NE" => "227", "NF" => "672", "NG" => "234", "NI" => "505", "NL" => "31", "NO" => "47", "NP" => "977", "NR" => "674", "NU" => "683", "NZ" => "64", "OM" => "968", "PA" => "507", "PE" => "51", "PF" => "689", "PG" => "675", "PH" => "63", "PK" => "92", "PL" => "48", "PM" => "508", "PR" => "787", "PS" => "970", "PT" => "351", "PW" => "680", "PY" => "595", "QA" => "974", "RE" => "262", "RO" => "40", "RS" => "381", "RU" => "7", "RW" => "250", "SA" => "966", "SB" => "677", "SC" => "248", "SD" => "249", "SE" => "46", "SG" => "65", "SH" => "290", "SI" => "386", "SJ" => "47", "SK" => "421", "SL" => "232", "SM" => "378", "SN" => "221", "SO" => "252", "SO" => "252", "SR"  > "597", "ST" => "239", "SV" => "503", "SY" => "963", "SZ" => "268", "TA" => "290", "TC" => "649", "TD" => "235", "TG" => "228", "TH" => "66", "TJ" => "992", "TK" =>  "690", "TL" => "670", "TM" => "993", "TN" => "216", "TO" => "676", "TR" => "90", "TT" => "868", "TV" => "688", "TW" => "886", "TZ" => "255", "UA" => "380", "UG" =>  "256", "US" => "1", "UY" => "598", "UZ" => "998", "VA" => "379", "VC" => "784", "VE" => "58", "VG" => "284", "VI" => "340", "VN" => "84", "VU" => "678", "WF" => "681", "WS" => "685", "YE" => "967", "YT" => "262", "ZA" => "27","ZM" => "260", "ZW" => "263");
			if (isset($arrCode[$code])) {
				$isoCodePhonePrefix =  $arrCode[$code];
			}
			return $isoCodePhonePrefix;
		}

		public function numPurchaseCustomer($id_customer, $valid=1, $interval=1, $intervalType = "DAY")
		{
			global $wpdb;

			$date_now = new DateTime("now");
			$date_now = $date_now->format("Y-m-d h:m:s");

			if ($valid==1) {
				$post_status = implode("','", array('wc-processing', 'wc-completed') );
			} else {
				$post_status = implode("','", array('wc-processing', 'wc-completed', 'wc-pending') );
			}

			$result = $wpdb->get_row( "SELECT count(*) as num_orders FROM $wpdb->posts
						WHERE post_type = 'shop_order'
						AND post_author = " . $id_customer . "
						AND post_status IN ('{$post_status}')
						AND post_date > '".$date_now . "' -interval " . $interval . " " . $intervalType);

			return $result->num_orders;
		}

		public function firstAddressDelivery($id_customer, $order)
		{
			global $wpdb;

			$date_now = new DateTime("now");
			$date_now = $date_now->format("Y-m-d h:m:s");

			$post_status = implode("','", array('wc-processing', 'wc-completed') );

			$result = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM " . $wpdb->posts . " p INNER JOIN " . $wpdb->postmeta . " pm on p.ID = pm.post_id
					WHERE p.post_type = 'shop_order'
					AND p.post_author = %d
					AND p.post_status IN ('{$post_status}')
					AND p.ID < " . $order->get_id() . "
					AND pm.meta_key = '_shipping_address_1' and pm.meta_value = %s
					order by p.post_date asc limit 1", $id_customer, $order->get_shipping_address_1())
				);
			if ($result) {
				return $result->post_date;
			} else {
				return "";
			}
		}

		public function acctInfo($order)
		{
			$acctInfoData = array();
			$date_now = new DateTime("now");
			$customer = wp_get_current_user();
			$isGuest = !is_user_logged_in();

			try {
				if ($isGuest){
					$acctInfoData["chAccAgeInd"] = "01";
				} else {
					$date_customer = new DateTime(strftime('%Y%m%d', strtotime($customer->user_registered)));
					$diff = $date_now->diff($date_customer);
					$dias = $diff->days;

					if ($dias == 0) {
						$acctInfoData["chAccAgeInd"] = "02";
					} else if ($dias < 30) {
						$acctInfoData["chAccAgeInd"] = "03";
					} else if ($dias < 60) {
						$acctInfoData["chAccAgeInd"] = "04";
					} else {
						$acctInfoData["chAccAgeInd"] = "05";
					}

					$acctInfoData["chAccChange"] = date('Ymd', get_user_meta( get_current_user_id(), 'last_update', true ));

					$date_customer_upd = new DateTime();
					$date_customer_upd->setTimestamp(get_user_meta( get_current_user_id(), 'last_update', true ));

					$diff = $date_now->diff($date_customer_upd);
					$dias_upd = $diff->days;

					if ($dias_upd == 0) {
						$acctInfoData["chAccChangeInd"] = "01";
					} else if ($dias_upd < 30) {
						$acctInfoData["chAccChangeInd"] = "02";
					} else if ($dias_upd < 60) {
						$acctInfoData["chAccChangeInd"] = "03";
					} else {
						$acctInfoData["chAccChangeInd"] = "04";
					}

					$acctInfoData["chAccDate"] = strftime('%Y%m%d', strtotime($customer->user_registered));

					$acctInfoData["nbPurchaseAccount"] = $this->numPurchaseCustomer(get_current_user_id(), 1, 6, "MONTH") <= 9999 ? $this->numPurchaseCustomer(get_current_user_id(), 1, 6, "MONTH") : 9999;

					$acctInfoData["txnActivityDay"] = $this->numPurchaseCustomer(get_current_user_id(), 0, 1, "DAY") <= 999 ? $this->numPurchaseCustomer(get_current_user_id(), 0, 1, "DAY") : 999;
					$acctInfoData["txnActivityYear"] = $this->numPurchaseCustomer(get_current_user_id(), 0, 1, "YEAR") <= 999 ? $this->numPurchaseCustomer(get_current_user_id(), 0, 1, "YEAR") : 999;

					if ( ($customer->first_name != $order->get_billing_first_name()) ||
					($customer->last_name != $order->get_billing_last_name())) {
						$acctInfoData["shipNameIndicator"] = "02";
					} else {
						$acctInfoData["shipNameIndicator"] = "01";
					}
				}

				$firstAddressDelivery = $this->firstAddressDelivery(get_current_user_id(), $order);

				if ($firstAddressDelivery != "") {
					$acctInfoData["shipAddressUsage"] = date("Ymd", strtotime($firstAddressDelivery));

					$date_firstAddressDelivery = new DateTime(strftime('%Y%m%d', strtotime($firstAddressDelivery)));
					$diff = $date_now->diff($date_firstAddressDelivery);
					$dias_firstAddressDelivery = $diff->days;

					if ($dias_firstAddressDelivery == 0) {
						$acctInfoData["shipAddressUsageInd"] = "01";
					} else if ($dias_upd < 30) {
						$acctInfoData["shipAddressUsageInd"] = "02";
					} else if ($dias_upd < 60) {
						$acctInfoData["shipAddressUsageInd"] = "03";
					} else {
						$acctInfoData["shipAddressUsageInd"] = "04";
					}
				}

				$acctInfoData["suspiciousAccActivity"] = "01";
			} catch (exception $e){
				// If exception send empty $acctInfoData
			}

			return $acctInfoData;
		}

        public function threeDSRequestorAuthenticationInfo()
        {
			$logged = is_user_logged_in();

			$threeDSRequestorAuthenticationInfo = array();
			$threeDSRequestorAuthenticationInfo["threeDSReqAuthData"] = "";
			$threeDSRequestorAuthenticationInfo["threeDSReqAuthMethod"] = ($logged) ? "02" : "01";

			return $threeDSRequestorAuthenticationInfo;
		}

		public function getEMV3DS($order)
		{
			$Merchant_EMV3DS = array();

			try {

				$Merchant_EMV3DS["customer"]["id"] = get_current_user_id() ?? '';
				$Merchant_EMV3DS["customer"]["name"] = $order->get_billing_first_name() ?? '';
				$Merchant_EMV3DS["customer"]["surname"] = $order->get_billing_last_name() ?? '';
				$Merchant_EMV3DS["customer"]["email"] = $order->get_billing_email() ?? '';

				// Billing info
				$billing = $order->get_address('billing');
				if ($billing) {
					$Merchant_EMV3DS["billing"]["billAddrCity"] = $order->get_billing_city() ?? '';
					if ($order->get_billing_country() != "") {
						$Merchant_EMV3DS["billing"]["billAddrCountry"] = $this->isoCodeToNumber($order->get_billing_country()) ?? '';
						// billAddrState -> Solo si está definido billAddrCountry
						if ($order->get_billing_state() != "" ) {
							$billAddState = explode("-",$order->get_billing_state());
							$billAddState = end($billAddState);
							$Merchant_EMV3DS["billing"]["billAddrState"] = $billAddState;
						}
					}
					$Merchant_EMV3DS["billing"]["billAddrLine1"] = $order->get_billing_address_1() ?? '';
					$Merchant_EMV3DS["billing"]["billAddrLine2"] = $order->get_billing_address_2() ?? '';
					$Merchant_EMV3DS["billing"]["billAddrPostCode"] = $order->get_billing_postcode() ?? '';

					if ( $order->get_billing_phone() != "" ) {
						if ($order->get_billing_country() != "" && $this->isoCodePhonePrefix($order->get_billing_country()) != "") {
							$arrDatosHomePhone["cc"] = $this->isoCodePhonePrefix($order->get_billing_country()) ?? '';
							$arrDatosHomePhone["subscriber"] = substr(preg_replace('/[^0-9]/', '', $order->get_billing_phone()), 0, 15);

							$Merchant_EMV3DS["customer"]["homePhone"] = $arrDatosHomePhone;
							$Merchant_EMV3DS["customer"]["mobilePhone"] = $arrDatosHomePhone;
						}
					}
				}

				$shipping = $order->get_address('shipping');
				if ($shipping) {
					$Merchant_EMV3DS["shipping"]["shipAddrCity"] = $order->get_shipping_city() ?? '';
					if ($order->get_shipping_country() != "") {
						$Merchant_EMV3DS["shipping"]["shipAddrCountry"] = $this->isoCodeToNumber($order->get_shipping_country()) ?? '';
						// shipAddrState -> Solo si está definido shipAddrCountry
						if ($order->get_shipping_state() != "") {
							$shipAddrState = explode("-",$order->get_shipping_state());
							$shipAddrState = end($shipAddrState);
							$Merchant_EMV3DS["shipping"]["shipAddrState"] = $shipAddrState;
						}
					}
					$Merchant_EMV3DS["shipping"]["shipAddrLine1"] = $order->get_shipping_address_1() ?? '';
					$Merchant_EMV3DS["shipping"]["shipAddrLine2"] = $order->get_shipping_address_2() ?? '';
					$Merchant_EMV3DS["shipping"]["shipAddrPostCode"] = $order->get_shipping_postcode() ?? '';
				}

				// acctInfo
				$Merchant_EMV3DS["acctInfo"] = $this->acctInfo($order);

				// threeDSRequestorAuthenticationInfo
				$Merchant_EMV3DS["threeDSRequestorAuthenticationInfo"] = $this->threeDSRequestorAuthenticationInfo();

				// AddrMatch
				$Merchant_EMV3DS["addrMatch"] = (($order->get_shipping_city() == $order->get_billing_city()) &&
												($order->get_shipping_country() == $order->get_billing_country()) &&
												($order->get_shipping_address_1() == $order->get_billing_address_1()) &&
												($order->get_shipping_address_2() == $order->get_billing_address_2())) ? "Y" : "N";

				$Merchant_EMV3DS["challengeWindowSize"] = 05;
			} catch (exception $e){
				// If exception send empty $Merchant_EMV3DS
			}
			return $Merchant_EMV3DS;
		}

		public function getShoppingCart($order)
		{
			$shoppingCartData = array();

			try {

				$amount=0;
				$i=0;

				// The loop to get the order items which are WC_Order_Item_Product objects since WC 3+
				foreach($order->get_items() as $item) {

					//Get the product ID
					$product_id = $item->get_product_id();

					//Get the WC_Product object
					$product = $item->get_product();
					$terms = get_the_terms($product_id, 'product_cat');
					$arrCategories = array();
					if ($terms && is_array($terms)) {
						foreach ( $terms as $term ) {
							// Categories by slug
							$arrCategories[] = $term->slug;
						}
					}

					if ($product->get_regular_price() == null || $product->get_regular_price() == ""){
						$price = (float)$product->get_price();
					}else{
						$price = (float)$product->get_regular_price();
					}

					if (is_int($item["quantity"])) {
						$shoppingCartData[$i]["sku"] = $product_id;
						$shoppingCartData[$i]["quantity"] = (int) $item["quantity"];
						$shoppingCartData[$i]["unitPrice"] = number_format($price * 100, 0, '.', '');
						$shoppingCartData[$i]["name"] = $item["name"];
						$shoppingCartData[$i]["category"] = $item["category"];
						$shoppingCartData[$i]["articleType"] = ($item["is_virtual"] == 1)?8 : 5;
						$shoppingCartData[$i]["discountValue"] = 0;
						if($product->get_sale_price() > 0) {
							$shoppingCartData[$i]["discountValue"] = number_format(($price - $product->get_sale_price()) * 100, 0, '.', '');
						}
						$amount += ($shoppingCartData[$i]["unitPrice"] - $shoppingCartData[$i]["discountValue"]) * $shoppingCartData[$i]["quantity"];
					} else {
						$quantity = (isset($item["quantity"]))?$item["quantity"]:1;
						$shoppingCartData[$i]["sku"] = $product_id;
						$shoppingCartData[$i]["quantity"] = 1;
						$shoppingCartData[$i]["unitPrice"] = number_format(($price * $quantity) * 100, 0, '.', '');
						$shoppingCartData[$i]["name"] = $item["name"];
						$shoppingCartData[$i]["category"] = $item["category"];
						$shoppingCartData[$i]["articleType"] = ($item["is_virtual"] == 1)?8 : 5;
						$shoppingCartData[$i]["discountValue"] = 0;
						if($product->get_sale_price() > 0) {
							$shoppingCartData[$i]["discountValue"] = number_format(($price - $product->get_sale_price()) * 100, 0, '.', '');
						}
						$amount += ($shoppingCartData[$i]["unitPrice"] - $shoppingCartData[$i]["discountValue"]) * $shoppingCartData[$i]["quantity"];
					}
					$i++;
				}

				// Se calculan los impuestos y gastos de envio
				$tax = number_format((float)$order->get_total() * 100, 0, '.', '') - $amount;
				if($tax > 0) {
					$shoppingCartData[$i]["sku"] = "1";
					$shoppingCartData[$i]["quantity"] = 1;
					$shoppingCartData[$i]["unitPrice"] = $tax;
					$shoppingCartData[$i]["name"] = "Tax";
					$shoppingCartData[$i]["articleType"] = "11";
				}

			} catch (exception $e){
				// If exception send empty $shoppingCartData
			}

			return array("shoppingCart"=>array_values($shoppingCartData));
		}

        public function getMerchantData($order)
        {
			$MERCHANT_EMV3DS = $this->getEMV3DS($order);
			$SHOPPING_CART = $this->getShoppingCart($order);

			$datos = array_merge($MERCHANT_EMV3DS,$SHOPPING_CART);

			return $datos;

		}

        function getOrderPaymentUrl($order)
        {
			// Obtenemos el terminal para el pedido
			$arrTerminalData = $this->TerminalCurrency($order);

			$importe = $arrTerminalData["importe"];
			$currency_iso_code = $arrTerminalData["currency_iso_code"];
			$term = $arrTerminalData["term"];
			$pass = $arrTerminalData["pass"];
			$dcc = $arrTerminalData["dcc"];
			$secure_pay = 1;

			$OPERATION = ($dcc == 1)?116 : 1;

			$MERCHANT_ORDER = str_pad( $order->get_id(), 8, "0", STR_PAD_LEFT );
			$MERCHANT_AMOUNT = $importe;
			$MERCHANT_CURRENCY = $currency_iso_code;
			$URLOK = $this->get_return_url( $order );
			$URLKO = $order->get_cancel_order_url_raw();

			// REST
			if ($this->apiKey != '') {

				$userInteraction = 1;
				$merchantData = $this->getMerchantData($order);
				$url = "";

				try {

					$apiRest = new PaycometApiRest($this->apiKey);
					$apiResponse = $apiRest->form(
						$OPERATION,
						$this->_getLanguange(),
						$arrTerminalData['term'],
						'',
						[
							'terminal' => $term,
							'methods' => [1],
							'order' => $MERCHANT_ORDER,
							'amount' => $MERCHANT_AMOUNT,
							'currency' => $MERCHANT_CURRENCY,
							'userInteraction' => $userInteraction,
							'secure' => $secure_pay,
							'merchantData' => $merchantData,
							'urlOk' => $URLOK,
							'urlKo' => $URLKO
						],
						[]
					);

					if ($apiResponse->errorCode==0) {
						$url = $apiResponse->challengeUrl;
					} else {
						if ($apiResponse->errorCode==1004) {
							$error_txt = __( 'Error: ', 'wc_paytpv' ) . $apiResponse->errorCode;
						} else {
							$error_txt = __( 'An error has occurred. Please verify the data entered and try again', 'wc_paytpv' );
						}
						print '<p>' . $error_txt .'</p>';
						$this->write_log('Error ' . $apiResponse->errorCode . " en form");
					}
				} catch (exception $e){
					$url = "";
				}

			} else {
				$this->write_log('Error 1004. ApiKey vacía');
				print '<p>' . __( 'Error: ', 'wc_paytpv' ) . "1004" .'</p>';
				$url = "";
			}

			return $url;
		}

		function process_payment($order_id)
		{
			$order = new WC_Order($order_id);

			$result = "success";

			if ($this->isJetIframeActive) {
				$result = $this->processJetIFramePayment($order);
			}

			$this->write_log( 'Process payment: ' . $order_id );

			return array(
				'result' => $result,
				'redirect'	=> $this->isJetIframeActive ? $this->jetiframeOkUrl : $order->get_checkout_payment_url( true )
			);
		}

		function processJetIframePayment($order)
		{
			$ip = $this->getIp();
			$arrTerminalData = $this->TerminalCurrency($order);
			$URLOK = $this->get_return_url($order);
			$URLKO = $order->get_cancel_order_url_raw();


			// With token Card
			if ($_POST['hiddenCardField'] != 0) {
				$saved_card = PayTPV::savedCard($order->get_user_id(), $_POST['hiddenCardField']);
				$idUser = $saved_card["paytpv_iduser"];
				$tokenUser = $saved_card["paytpv_tokenuser"];

			// With jetIframe Token
			} else {
				// REST
				if ($this->apiKey != '') {

					$notify = 2; // No notificar HTTP

					$apiRest = new PaycometApiRest($this->apiKey);
					$addUserResponse = $apiRest->addUser(
						$arrTerminalData['term'],
						$_POST['jetiframe-token'],
						$order->get_id(),
						'',
						'ES',
						$notify
					);

					if ($addUserResponse->errorCode>0) {
						if ($addUserResponse->errorCode==1004) {
							$error_txt = __( 'Error: ', 'wc_paytpv' ) . $addUserResponse->errorCode;
						} else {
							$error_txt = __( 'An error has occurred. Please verify the data entered and try again', 'wc_paytpv' );
						}
						wc_add_notice($error_txt, 'error' );
						$this->write_log('Error ' . $addUserResponse->errorCode . " en addUser");
						return "error";
					}

					$idUser = $addUserResponse->idUser;
					$tokenUser = $addUserResponse->tokenUser;
				}
			}

			$savecard_jetiframe = (isset($_POST["savecard_jetiframe"]))?1:0;

			if ( class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
				$order->add_meta_data('paytpv_savecard', $savecard_jetiframe);
				$order->add_meta_data('PayTPV_IdUser', $idUser);
				$order->add_meta_data('PayTPV_TokenUser', $tokenUser);
				$order->save();
			} else {
				update_post_meta((int) $order->get_id(), 'paytpv_savecard', $savecard_jetiframe);
				update_post_meta((int) $order->get_id(), 'PayTPV_IdUser', $idUser);
				update_post_meta((int) $order->get_id(), 'PayTPV_TokenUser', $tokenUser);
			}

			$secure_pay = 1;

			$term = $arrTerminalData['term'];
			$importe = $arrTerminalData["importe"];
			$currency = $arrTerminalData["currency_iso_code"];

			$MERCHANT_ORDER = str_pad( $order->get_id(), 8, "0", STR_PAD_LEFT );

			if ($this->apiKey != '') {
				$methodId = 1;
				$userInteraction = 1;
				$scoring = 0;
				$notifyDirectPayment = 1;

				$merchantData = $this->getMerchantData($order);

				$dcc = $arrTerminalData["dcc"];
				if ($dcc == 1) {

					$OPERATION = 116;

					try {

						$apiRest = new PaycometApiRest($this->apiKey);
						$executePurchaseResponse = $apiRest->form(
                            $OPERATION,
                            $this->_getLanguange(),
                            $term,
                            '',
                            [
                                'terminal' => $term,
                                'methods' => [$methodId],
                                'order' => $MERCHANT_ORDER,
                                'amount' => $importe,
                                'currency' => $currency,
                                'idUser' => $idUser,
                                'tokenUser' => $tokenUser,
                                'userInteraction' => $userInteraction,
                                'secure' => $secure_pay,
                                'merchantData' => $merchantData,
                                'urlOk' => $URLOK,
                                'urlKo' => $URLKO
                            ]
                        );

					} catch (exception $e){
						$error_txt = __( 'An error has occurred. Please verify the data entered and try again', 'wc_paytpv' );
						wc_add_notice($error_txt, 'error' );
					}
				}else{
					$apiRest = new PaycometApiRest($this->apiKey);
					$executePurchaseResponse = $apiRest->executePurchase(
						$term,
						$MERCHANT_ORDER,
						$importe,
						$currency,
						$methodId,
						$ip,
						$secure_pay,
						$idUser,
						$tokenUser,
						$URLOK,
						$URLKO,
						$scoring,
						'',
						'',
						$userInteraction,
						[],
						'',
						'',
						$merchantData,
						$notifyDirectPayment
					);
				}
				$urlReturn = $URLOK;
				if ($executePurchaseResponse->errorCode>0) {
					if ($executePurchaseResponse->errorCode==1004) {
						$error_txt = __( 'Error: ', 'wc_paytpv' ) . $executePurchaseResponse->errorCode;
					} else {
						$error_txt = __( 'An error has occurred. Please verify the data entered and try again', 'wc_paytpv' );
					}
					wc_add_notice($error_txt, 'error' );
					$this->write_log('Error ' . $executePurchaseResponse->errorCode . " en executePurchase");
					$urlReturn = $URLKO;
				}

				$this->jetiframeOkUrl = $executePurchaseResponse->challengeUrl != '' ? $executePurchaseResponse->challengeUrl : $urlReturn;

			} else {
				$this->jetiframeOkUrl = $URLKO;
			}
			return "success";
		}


	    /**
		 * Safe transaction
		 * */
		public function isFirstPurchaseToken($id_customer,$paytpv_iduser)
		{
	    	global $wpdb;
	    	$saved_card = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %d", 'PayTPV_IdUser', $paytpv_iduser ), ARRAY_A );

	        if ( null !== $saved_card ) {
			  return false;
			} else {
			  return true;
			}
	    }

		/**
		 * return array data of order currency
		 */
		public function TerminalCurrency($order)
		{
			$order_currency = $order->get_currency();
			// Get terminal with same order currency
			foreach($this->paytpv_terminals as $terminal) {
				if($terminal["moneda"]==$order_currency)
					$terminal_currency = $terminal;
			}

			// Not exists terminal in user currency
			if (empty($terminal_currency) === true){

				// Search for terminal in merchant default currency
				foreach ( $this->paytpv_terminals as $terminal) {
					if ($terminal["moneda"]==get_woocommerce_currency())
						$terminal_currency = $terminal;
				}

				// If not exists terminal in default currency. Select first terminal defined
				if (empty($terminal_currency) === true){
					$terminal_currency = $this->paytpv_terminals[0];
				}
			}

			$arrTerminalData["term"] = $terminal_currency["term"];
			$arrTerminalData["pass"] = $terminal_currency["pass"];
			$arrTerminalData["dcc"] = $terminal_currency["dcc"];
			$arrTerminalData["currency_iso_code"] = $terminal_currency["moneda"];
			$arrTerminalData["importe"] = number_format($order->get_total() * 100, 0, '.', '');

	        return $arrTerminalData;
		}

		/**
		 * receipt_page
		 * */
		function receipt_page($order_id)
		{
			switch ($this->payment_paycomet) {
				case 0:
					echo '<p>' . __( 'Thanks for your order, please fill the data below to process the payment.', 'wc_paytpv' ) . '</p>';
					break;
				case 1:
					echo '<p>' . __( 'Thanks for your order, please press the button to pay.', 'wc_paytpv' ) . '</p>';
					break;
				default:
					break;
			}

			echo $this->savedCardsHtml($order_id);
		}

		/**
		 * Html saved Cards
		 */
		function savedCardsHtml($order_id)
		{
			$order = new WC_Order( $order_id );
			$saved_cards = Paytpv::savedActiveCards(get_current_user_id());

			// Tarjetas almacenadas
			$store_card = (sizeof($saved_cards) == 0) ? "none" : "";
			print '<form id="form_paytpv" method="post" action="'.add_query_arg(array("wc-api"=> 'woocommerce_' . $this->id)) . '" class="form-inline">
					<div id="saved_cards" style="display:'.$store_card.'">
	                    <div class="form-group">
	                        <label for="card">'.__('Card', 'wc_paytpv' ).':</label>
	                        <select name="card" id="card" onChange="checkCard()" class="form-control">';

			foreach ($saved_cards as $card){
				$card_desc = ($card["card_desc"]!="")?(" - " . $card["card_desc"]):"";
				print 		"<option value='".$card['id']."'>".$card["paytpv_cc"]. $card_desc. "</option>";

			}

            print '      <option value="0">'.__('NEW CARD', 'wc_paytpv' ).'</option></select>
                    </div>
                </div>';

            if (sizeof($saved_cards) > 0) {
				// Pago directo
				print  '<input type="submit" id="direct_pay" value="'.__( 'Pay', 'wc_paytpv' ).'" class="button paycomet_pay">';
				print  '<img src="'.PAYTPV_PLUGIN_URL . 'images/clockpayblue.gif" alt="'.__( 'Wait, please...', 'wc_paytpv' ).'" width="41" height="30" id="clockwait" style="display:none; margin-top:5px;" />';
				print '<input type="hidden" name="tpvLstr" value="pay">';
			}

			print '<input type="hidden" id="order_id" name="Order" value="'.$order_id.'">';

			// Comprobacion almacenar tarjeta
			if (get_current_user_id() > 0 && $this->disable_offer_savecard == 0) {
				print '
				<div id="storingStep" class="box" style="display:'.$store_card.'">
					<label><input type="checkbox" name="savecard" id="savecard" onChange="saveOrderInfoJQ()"> '.__('Save card for future purchases', 'wc_paytpv' ).'. <span class="paytpv-pci"> '.__('Card data is protected by the Payment Card Industry Data Security Standard (PCI DSS)', 'wc_paytpv' ).'.</span></label>';
	        } else {
	        	print '<div id="ifr-paytpv-container" class="box">';
			}

			print  $this->generate_paytpv_form( $order_id );

			print '</div>';
			print '<p id="paycomet-cancel"><a class="button cancel" style="color: white;" href="'.$order->get_cancel_order_url_raw().'">'.__('Cancel order', 'wc_paytpv').'</a></p>';

			print '</form>';

            wc_get_template( 'myaccount/conditions.php', array( ), '', PAYTPV_PLUGIN_DIR . 'template/' );
		}

		public function getIframeUrl()
		{
			return $this->iframeurl . "?";
		}

		/**
		 * Generate the paytpv button link
		 * */
        function generate_paytpv_form($order_id)
        {

			$order = new WC_Order( $order_id );

			// Obtenemos la Url del pago
			$src = $this->getOrderPaymentUrl($order);
			$html = '';

			// Pago Iframe
			if ($this->payment_paycomet == 0) {
				$html .= '<iframe class="ifr-paytpv" id="paytpv_iframe" src="' . $src . '"
	name="paytpv" style="min-width: 320px!important; border-top-width: 0px; border-right-width: 0px; border-bottom-width: 0px; border-left-width: 0px; border-style: initial; border-color: initial; border-image: initial; height: ' . $this->iframe_height . 'px; " marginheight="0" marginwidth="0" scrolling="no" sandbox="allow-top-navigation allow-scripts allow-same-origin allow-forms"></iframe>';
			} else {
				$html .= '<p><a href="' . $src . '" id="paycomet_page" class="button paycomet_pay">'.__( 'Pay', 'wc_paytpv' ).'</a></p>';
			}

			return $html;
		}

		public function saveCard($order, $user_id, $paytpv_iduser, $paytpv_tokenuser, $TransactionType)
		{
			// Si es una operción de add_user o no existe el token asociado al usuario lo guardamos
			if ($TransactionType==107 || !PayTPV::existsCard($paytpv_iduser,$user_id)){
				if ($order!=null) {
					// Obtenemos el terminal para el pedido
					$arrTerminalData = $this->TerminalCurrency($order);
				} else {
					$arrTerminalData = $this->paytpv_terminals[0];
				}

				$term = $arrTerminalData["term"];

				// REST
				if ($this->apiKey != '') {
					$apiRest = new PaycometApiRest($this->apiKey);
					$infoUserResponse = $apiRest->infoUser(
						$paytpv_iduser,
						$paytpv_tokenuser,
						$term
					);

					$result['DS_MERCHANT_PAN'] = $infoUserResponse->pan;
					$result['DS_CARD_BRAND'] = $infoUserResponse->cardBrand;
					$result['DS_CARD_EXPIRYDATE'] = $infoUserResponse->expiryDate;
				}

				return PayTPV::saveCard(
					$user_id,
					$paytpv_iduser,
					$paytpv_tokenuser,
					$result['DS_MERCHANT_PAN'],
					$result['DS_CARD_BRAND'],
					$result['DS_CARD_EXPIRYDATE']
				);

			}else{
				$result["paytpv_iduser"] = $paytpv_iduser;
				$result["paytpv_tokenuser"] = $paytpv_tokenuser;

				return $result;
			}
		}

		/**
		 * Operaciones sucesivas
		 * */

        function scheduled_subscription_payment($amount_to_charge, $order)
        {


			$subscriptions = wcs_get_subscriptions_for_renewal_order($order);
			$subscription  = array_pop( $subscriptions );

			if (false == $subscription->get_parent_id()) { // There is no original order
				$parent_order = null;
			} else {
				$importe =  number_format($amount_to_charge * 100, 0, '.', '');

				// Obtenemos el terminal para el pedido
				$arrTerminalData = $this->TerminalCurrency($order);

				$parent_order = $subscription->get_parent();

				$paytpv_order_ref = $order->get_id();
				$paytpv_order_ref = str_pad($paytpv_order_ref, 8, "0", STR_PAD_LEFT);

				if ( class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
					$payptv_iduser = $order->get_meta('PayTPV_IdUser', true );
					$payptv_tokenuser = $order->get_meta('PayTPV_TokenUser', true );
				} else {
					$payptv_iduser = get_post_meta( ( int ) $parent_order->get_id(), 'PayTPV_IdUser', true );
					$payptv_tokenuser = get_post_meta( ( int ) $parent_order->get_id(), 'PayTPV_TokenUser', true );
				}


				$ip = $this->getIp();

				$userInteraction = 0;

				$merchantData = $this->getMerchantData($order);

				// Añadimos información MIT -> R
				$trxType = "R";
				$scaException = "MIT";

				$dateAux = new \DateTime("now");
				$dateAux->modify('+10 year');
				$recurringExpiry = $dateAux->format('Ymd'); // Fecha actual + 10 años.
				$merchantData["recurringExpiry"] = $recurringExpiry;
				$merchantData["recurringFrequency"] = "1";

				// REST
				if($this->apiKey != '') {

					$methodId = 1;
					$secure = 0;
					$scoring = 0;
					$notifyDirectPayment = 1;

					$apiRest = new PaycometApiRest($this->apiKey);
					$executePurchaseResponse = $apiRest->executePurchase(
						$arrTerminalData['term'],
						$paytpv_order_ref,
						$arrTerminalData["importe"],
						$arrTerminalData["currency_iso_code"],
						$methodId,
						$ip,
						$secure,
						$payptv_iduser,
						$payptv_tokenuser,
						'',
						'',
						$scoring,
						'',
						'',
						$userInteraction,
						[],
						$trxType,
						$scaException,
						$merchantData,
						$notifyDirectPayment
					);

					$charge["DS_RESPONSE"] = ($executePurchaseResponse->errorCode > 0)? 0 : 1;
					$charge["DS_ERROR_ID"] = $executePurchaseResponse->errorCode;

					if ($executePurchaseResponse->errorCode == 0) {
						$charge["DS_MERCHANT_AUTHCODE"] = $executePurchaseResponse->authCode;
						$charge["DS_MERCHANT_AMOUNT"] = $executePurchaseResponse->amount;
					} else {
						$this->write_log('Error ' . $executePurchaseResponse->errorCode . " en executePurchase pago suscripcion");
					}

				} else {
					$charge["DS_RESPONSE"] = 0;
					$charge["DS_ERROR_ID"] = 1004;
				}

				if (( int ) $charge[ 'DS_RESPONSE' ] == 1 ) {
					if ( class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
						$order->add_meta_data('PayTPV_Referencia', $executePurchaseResponse->order);
						$order->add_meta_data('_transaction_id', $executePurchaseResponse->authCode);
						$order->add_meta_data('PayTPV_IdUser', $payptv_iduser);
						$order->add_meta_data('PayTPV_TokenUser', $payptv_tokenuser);
						$order->save();
					} else {
						update_post_meta($order->get_id(), 'PayTPV_Referencia', $executePurchaseResponse->order);
						update_post_meta($order->get_id(), '_transaction_id', $executePurchaseResponse->authCode);
						update_post_meta($order->get_id(), 'PayTPV_IdUser', $payptv_iduser);
						update_post_meta($order->get_id(), 'PayTPV_TokenUser', $payptv_tokenuser);
					}

					WC_Subscriptions_Manager::process_subscription_payments_on_order($order);
				}
			}
		}

		function store_renewal_order_id($order_meta_query, $original_order_id, $renewal_order_id, $new_order_role)
		{
			if ( 'parent' == $new_order_role )
				$order_meta_query .= " AND `meta_key` NOT LIKE 'PayTPV_IdUser' "
						. " AND `meta_key` NOT LIKE 'PayTPV_TokenUser' "
						. " AND `meta_key` NOT LIKE 'PayTPV_Referencia' ";

			return $order_meta_query;
		}

		/**
		 * Can the order be refunded via PayTPV?
		 * @param  WC_Order $order
		 * @return bool
		 */
		public function can_refund_order($order)
		{
			return $order && $order->get_transaction_id();
		}

		/**
		 * Process a refund if supported
		 * @param  int $order_id
		 * @param  float $amount
		 * @param  string $reason
		 * @return  boolean True or false based on success, or a WP_Error object
		 */
        public function process_refund($order_id, $amount = null, $reason = '')
        {
			$order = wc_get_order( $order_id );

			if (!$this->can_refund_order($order)) {
				$this->write_log('Refund Failed: No transaction ID');

				return false;
			}

			$ip = $this->getIp();
			// Obtenemos el terminal para el pedido
			$arrTerminalData = $this->TerminalCurrency($order);
			$currency_iso_code = $arrTerminalData["currency_iso_code"];
			$term = $arrTerminalData["term"];

			$importe = number_format((float)$amount * 100, 0, '.', '');

			if ( class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
				$paytpv_order_ref = $order->get_meta('PayTPV_Referencia', true);
			} else {
				$paytpv_order_ref = get_post_meta((int) $order->get_id(), 'PayTPV_Referencia', true);
			}


			$transaction_id = $order->get_transaction_id();

			// REST
			if($this->apiKey != '') {

				$notifyDirectPayment = 2; // No notificar HTTP

				$apiRest = new PayCometApiRest($this->apiKey);
				$executeRefundReponse = $apiRest->executeRefund(
					$paytpv_order_ref,
					$term,
					$importe,
					$currency_iso_code,
					$transaction_id,
					$ip,
					$notifyDirectPayment
				);

				$result["DS_RESPONSE"] = ($executeRefundReponse->errorCode > 0)? 0 : 1;
				$result["DS_ERROR_ID"] = $executeRefundReponse->errorCode;
				if ($executeRefundReponse->errorCode == 0) {
					$result['DS_MERCHANT_AUTHCODE'] = $executeRefundReponse->authCode;
				}

			} else {
				$charge["DS_RESPONSE"] = 0;
				$charge["DS_ERROR_ID"] = 1004;
				$this->write_log('Error 1004. ApiKey vacía');
			}

			if ((int) $result['DS_RESPONSE'] != 1) {
				$this->write_log('Error ' . $executeRefundReponse->errorCode . ' en executeRefund');
				$order->add_order_note('Refund Failed. Error: ' . $result['DS_ERROR_ID']);

				return false;
			} else {
				$order->add_order_note( sprintf( __('Refunded %s - Refund ID: %s', 'woocommerce'), $amount, $result['DS_MERCHANT_AUTHCODE']));

				return true;
			}
		}
	}
