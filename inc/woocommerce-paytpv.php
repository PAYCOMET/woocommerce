<?php
	/**
	 * Pasarela PayTpv Gateway Class
	 * 
	 */
	class woocommerce_paytpv extends WC_Payment_Gateway {

		private $ws_client;

		private function write_log( $log ) {
			if ( true === WP_DEBUG ) {
				if ( is_array( $log ) || is_object( $log ) ) {
					error_log( print_r( $log, true ) );
				} else {
					error_log( $log );
				}
			}
		}

		public function __construct() {
			$this->id = 'paytpv';
			$this->icon = PAYTPV_PLUGIN_URL . 'images/cards.png';
			$this->has_fields = false;
			$this->method_title = 'PayTPV';
            $this->method_description = __('Payment gateway for credit card payment.', 'wc_paytpv' );
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

			$this->iframeurl = 'https://secure.paytpv.com/gateway/bnkgateway.php';

			// Get setting values
			$this->enabled = $this->settings[ 'enabled' ];
			$this->title = $this->settings[ 'title' ];
			$this->description = $this->settings[ 'description' ];
			$this->environment = $this->settings[ 'environment' ];
			$this->clientcode = $this->settings[ 'clientcode' ];
			$this->paytpv_terminals = get_option( 'woocommerce_paytpv_terminals',
				array(
					array(
						'term'   => $this->get_option( 'term' ),
						'pass' => $this->get_option( 'pass' ),
						'terminales'      => $this->get_option( 'terminales' ),
						'dsecure'      => $this->get_option( 'dsecure' ),
						'moneda'           => $this->get_option( 'moneda' ),
						'tdmin'            => $this->get_option( 'tdmin' )
					)
				)
			);

			$this->commerce_password = $this->settings[ 'commerce_password' ];

			

			// Hooks
			add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'save_terminals_details' ) );
			add_action( 'woocommerce_api_woocommerce_' . $this->id, array( $this, 'check_' . $this->id . '_resquest' ) );
			
			
			add_action('admin_notices', array( $this, 'validate_paytpv' ));


			// Subscriptions
			//add_action( 'woocommerce_scheduled_subscription_payment_' . $this->id, array( $this, 'scheduled_subscription_payment' ), 10, 2 );
			add_action( 'scheduled_subscription_payment_' . $this->id, array( $this, 'scheduled_subscription_payment' ), 10, 3 );
			add_filter( 'woocommerce_subscriptions_renewal_order_meta_query', array( $this, 'store_renewal_order_id' ), 10, 4 );

	

		}

		/**
		 * Loads the my-subscriptions.php template on the My Account page.
		 *
		 * @since 1.0
		 */
		public static function get_my_cards_template() {

			$user_id = get_current_user_id();

			$saved_cards = Paytpv::savedCards($user_id);

			$operation = 107;
			// Obtenemos el terminal para el pedido
			// El primer terminal configurado
			$gateway = new self();
			$terminal = $gateway->paytpv_terminals[0];
			$term = $terminal["term"];
			$pass = $terminal["pass"];
		

			$order = $user_id;

			$secure_pay = 0;
			
			$signature = md5($gateway->clientcode.$term.$operation.$order.md5($pass));
			$fields = array
			(
				'MERCHANT_MERCHANTCODE' => $gateway->clientcode,
				'MERCHANT_TERMINAL' => $term,
				'OPERATION' => $operation,
				'LANGUAGE' => $gateway->_getLanguange(),
				'MERCHANT_MERCHANTSIGNATURE' => $signature,
				'MERCHANT_ORDER' => $order,
				'URLOK' => wc_get_page_permalink( 'myaccount' ),
			    'URLKO' => wc_get_page_permalink( 'myaccount' ),
			    '3DSECURE' => $secure_pay
			);

			$query = http_build_query($fields);

			$url_paytpv = $gateway->getIframeUrl($secure_pay) . $query;
			
			wc_get_template( 'myaccount/my-cards.php', array( 'saved_cards' => $saved_cards, 'user_id' => get_current_user_id(), 'url_paytpv'=> $url_paytpv), '', PAYTPV_PLUGIN_DIR . 'template/' );

						

		}
		
		public function validate_paytpv(){
			if (empty($this->paytpv_terminals))
		    	echo '<div class="error"><p>'.__('You must define at least one terminal', 'wc_paytpv' ).'</p></div>';
		}

		/**
		 * There are no payment fields for PayTpv, but we want to show the description if set.
		 * */
		function payment_fields() {
			if ( $this->description )
				echo wpautop( wptexturize( $this->description ) );
		}

		/**
		 * Admin Panel Options
		 * - Options for bits like 'title' and availability on a country-by-country basis
		 * */
		public function admin_options() {
			?>
			<h3><?php _e( 'PayTpv Payment Gateway', 'wc_paytpv' ); ?></h3>
			<p>
				<?php _e( '<a href="https://PayTpv.com">PayTpv Online</a> payment gateway for Woocommerce enables credit card payment in your shop. Al you need is a PayTpv.com merchant account and access to <a href="https://www.paytpv.com/clientes.php">customer area</a>', 'wc_paytpv'  ); ?>
			</p>
			<p>
				<?php _e( 'There you should configure "Tipo de notificación del cobro:" as "Notificación por URL" set ther teh following URL:', 'wc_paytpv'  ); ?> <?php echo add_query_arg( 'tpvLstr', 'notify', add_query_arg( 'wc-api', 'woocommerce_' . $this->id, home_url( '/' ) ) ); ?></p>
			</p>
			<table class="form-table">
				<?php $this->generate_settings_html(); ?>
			</table><!--/.form-table-->
			<?php

		}




		/**
		 * Save account details table
		 */
		public function save_terminals_details() {		
			
			$terminals = array();

			if ( isset( $_POST['term'] ) ) {

				$term   = array_map( 'wc_clean', $_POST['term'] );
				$pass = array_map( 'wc_clean', $_POST['pass'] );
				$terminales      = array_map( 'wc_clean', $_POST['terminales'] );
				$dsecure      = array_map( 'wc_clean', $_POST['dsecure'] );
				$moneda           = array_map( 'wc_clean', $_POST['moneda'] );
				$tdmin            = array_map( 'wc_clean', $_POST['tdmin'] );

				foreach ( $term as $i => $name ) {
					if ( ! isset( $term[ $i ] ) ) {
						continue;
					}

					$terminals[] = array(
						'term'   => $term[ $i ],
						'pass' => $pass[ $i ],
						'terminales'      => $terminales[ $i ],
						'dsecure'      => $dsecure[ $i ],
						'moneda'           => $moneda[ $i ],
						'tdmin'            => $tdmin[ $i ]
					);
				}
			}


			update_option( 'woocommerce_paytpv_terminals', $terminals );

		}

		

		public static function load_resources() {
			global $hook_suffix;

			wp_register_style( 'lightcase.css', PAYTPV_PLUGIN_URL . 'css/lightcase.css', PAYTPV_VERSION );
			wp_enqueue_style( 'lightcase.css');

			wp_register_style( 'paytpv.css', PAYTPV_PLUGIN_URL . 'css/paytpv.css', PAYTPV_VERSION );
			wp_enqueue_style( 'paytpv.css');

			wp_register_script( 'paytpv.js', PAYTPV_PLUGIN_URL . 'js/paytpv.js', array(),  PAYTPV_VERSION );
			wp_enqueue_script( 'paytpv.js' );	

			wp_register_script( 'lightcase.js', PAYTPV_PLUGIN_URL . 'js/lightcase.js', array('jquery'), PAYTPV_VERSION );
			wp_enqueue_script( 'lightcase.js' );
			
		}


		public static function load_resources_conf() {
			global $hook_suffix;

			wp_register_style( 'paytpv.css', PAYTPV_PLUGIN_URL . 'css/paytpv.css', PAYTPV_VERSION );
			wp_enqueue_style( 'paytpv.css');

			wp_register_script( 'paytpv_conf.js', PAYTPV_PLUGIN_URL . 'js/paytpv_conf.js', array(),  PAYTPV_VERSION );
			wp_enqueue_script( 'paytpv_conf.js' );	

			
		}

		/**
		 * Initialize Gateway Settings Form Fields
		 */
		function init_form_fields() {

		
			$this->form_fields = array(
				'enabled' => array(
					'title' => __( 'Enable/Disable', 'wc_paytpv' ),
					'label' => __( 'Enable PayTpv gateway', 'wc_paytpv' ),
					'type' => 'checkbox',
					'description' => '',
					'default' => 'no'
				),
				'title' => array(
					'title' => __( 'Title', 'wc_paytpv' ),
					'type' => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'wc_paytpv' ),
					'default' => __( 'Credit Card (by PayTpv)', 'wc_paytpv' )
				),
				'description' => array(
					'title' => __( 'Description', 'wc_paytpv' ),
					'type' => 'textarea',
					'class' => 'description',
					'description' => __( 'This controls the description which the user sees during checkout.', 'wc_paytpv' ),
					'default' => __( 'Pay using your credit card in a secure way', 'wc_paytpv' ),
				),
				'environment' => array(
					'title' => __( 'Environment', 'wc_paytpv' ),
					'type' => 'select',
					'description' => __( 'Try PayTPV module without the need of an associate PayTPV account. Terminal number (1,2,3, ... One for each currency) Password: paytpvtest Test Cards: 5325298401138208 / 5540568785541245/5407696658785988 Expiration Date: Month: 5 / Year: 2020CVC2: 123 / 3DSecure: 1234', 'wc_paytpv' ),
					'options' => array(
						0 => __( 'Live Mode', 'wc_paytpv' ),
						1 => __( 'Test Mode', 'wc_paytpv' )
					)
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
				

				'commerce_password' => array(
					'title' => __( 'Request business password on purchases with stored cards', 'wc_paytpv' ),
					'type' => 'select',
					'label' => '',
					
					'options' => array(
						0 => __( 'No', 'wc_paytpv' ),
						1 => __( 'Yes', 'wc_paytpv' )
					)
				),
	

				/*
				'iframe' => array(
					'title' => __( 'Onsite form in embended iframe', 'wc_paytpv' ),
					'label' => '',
					'type' => 'checkbox',
					'default' => 'yes'
				),
				*/
			);
		}



		/**
	 	* generate_account_details_html function.
	 	*/
		public function generate_paytpv_terminals_html() {

			ob_start();

			?>
			<tr valign="top">
				<th class="titledesc"><?php _e( 'Terminals', 'wc_paytpv' ); ?>:</th>
				<td colspan="2" class="forminp" id="paytpv_terminals">
					<table class="tblterminals widefat wc_input_table sortable" style="font-size:80%" cellspacing="0">
						<thead>
							<tr>
								<th class="sort">&nbsp;</th>
								<th><?php _e( 'Terminal Number', 'wc_paytpv' ); ?></th>
								<th><?php _e( 'Password', 'wc_paytpv' ); ?></th>
								<th><?php _e( 'Terminals available', 'wc_paytpv' ); ?></th>
								<th><?php _e( 'Use 3D Secure', 'wc_paytpv' ); ?></th>
								<th><?php _e( 'Currency', 'wc_paytpv' ); ?></th>
								<th><?php _e( 'Use 3D Secure on purchases over', 'wc_paytpv' ); ?></th>
							</tr>
						</thead>
						<tbody class="accounts">
							<?php
							$i = -1;
							
							$arrTerminals = array(__('Secure','wc_paytpv' ),__('Non-Secure','wc_paytpv' ),__('Both','wc_paytpv' ));
							$arrDsecure = array(__( 'No', 'wc_paytpv' ),__( 'Yes', 'wc_paytpv' ));
							$arrMonedas = get_woocommerce_currencies();

							// Un terminal por defecto en la moneda de woocommerce
							if (empty($this->paytpv_terminals)){
								$this->paytpv_terminals[0] = array("term"=>"","pass"=>"","terminales"=>0,"dsecure"=>0,"moneda"=> get_woocommerce_currency(),"tdmin"=>"");
							}

							if ( $this->paytpv_terminals){
								foreach ( $this->paytpv_terminals as $key=>$terminal){
									$i++;

									echo '<tr class="terminal">
										<td class="sort"></td>
										<td><input type="text" value="' . esc_attr( wp_unslash( $terminal['term'] ) ) . '" name="term[]" /></td>
										<td><input class="pass" type="text" value="' . esc_attr( wp_unslash( $terminal['pass'] ) ). '" name="pass[]" /></td>
										<td><select class="term" name="terminales[]" onchange="checkterminales(this);">
										';
									foreach ($arrTerminals as $key=>$val){
										$selected = ($key==$terminal['terminales'])?"selected":"";
										echo '<option value="'.$key.'" '.$selected.'>'.$val.'</option>';
									}
									echo '</select></td>';
									echo '<td><select class="dsecure" name="dsecure[]">
										';
										foreach ($arrDsecure as $key=>$val){
											$selected = ($key==$terminal['dsecure'])?"selected":"";
											echo '<option value="'.$key.'" '.$selected.'>'.$val.'</option>';
										}
									echo '</select></td>';
									echo '<td><select class="moneda" name="moneda[]">
										';
										foreach ($arrMonedas as $key=>$val){
											$selected = ($key==$terminal['moneda'] || ($terminal['moneda']=="" && $key==get_woocommerce_currency()))?"selected":"";
											echo '<option value="'.$key.'" '.$selected.'>'.$val.'</option>';
										}
									echo '</select></td>';
									echo '<td><input class="tdmin" type="number" value="' . esc_attr( $terminal['tdmin'] ) . '" name="tdmin[]" placeholder="0" /></td>
									</tr>';
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
		 * Check for PayTpv IPN Response
		 * */
		function check_paytpv_resquest() {

			if ( !isset( $_REQUEST[ 'tpvLstr' ] ) )
				return;


			if (isset($_REQUEST['Order']) ){
				$datos_order = explode("_",$_REQUEST['Order']); // En los pagos de suscripcion viene {id_order}_{numpago}
				$ref = $datos_order[0];
				$order = new WC_Order( ( int ) substr( $ref, 0, 8 ) );
			}

			if ( $_REQUEST[ 'tpvLstr' ] == 'pay' && $order->status != 'completed' ) { //PAGO CON TARJETA GUARDADA

				$card = $_POST[ 'card' ];
				$saved_card = PayTPV::savedCard($order->user_id,$card);

				// Verificar contraseña usuario.
				if ($this->commerce_password && !$this->validPassword($order->user_id,$_POST['commerce_password'])){
					 if (!$this->validPassword($order->user_id,$_POST['commerce_password'])){

			        	$url = add_query_arg( 'order', $order->id, add_query_arg( 'key', $order->order_key, get_permalink( woocommerce_get_page_id( 'pay' ) ) ) );
			        	

			        	if ( function_exists( 'wc_add_notice' ) ) {
			        		wc_add_notice( __( 'Invalid commerce password', 'wc_paytpv' ), 'error' );
						} else { // WC < 2.1
							$woocommerce->add_error( __( 'Invalid commerce password', 'wc_paytpv' ) );
							$woocommerce->set_messages();
						}

			        	wp_redirect( $url, 303 );
			        	exit;
			        	
			        }
				}
				

				// Obtenemos el terminal para el pedido
				$arrTerminalData = $this->TerminalCurrency($order);
				$importe = $arrTerminalData["importe"];
				$currency_iso_code = $arrTerminalData["currency_iso_code"];
				$term = $arrTerminalData["term"];
				$pass = $arrTerminalData["pass"];
				$paytpv_order_ref = $order->id;

				$secure_pay = $this->isSecureTransaction($order,$arrTerminalData,$card,$saved_card["paytpv_iduser"])?1:0;
				
				// PAGO SEGURO redireccionamos
				if ($secure_pay){

					$URLOK = $this->get_return_url( $order );
					$URLKO = $order->get_cancel_order_url();

					$OPERATION = "109"; //exec_purchase_token

					$signature = md5($this->clientcode.$saved_card["paytpv_iduser"].$saved_card["paytpv_tokenuser"].$term.$OPERATION.$paytpv_order_ref.$importe.$currency_iso_code.md5($pass));
			
					$fields = array
						(
							'MERCHANT_MERCHANTCODE' => $this->clientcode,
							'MERCHANT_TERMINAL' => $term,
							'OPERATION' => $OPERATION,
							'LANGUAGE' => $this->_getLanguange(),
							'MERCHANT_MERCHANTSIGNATURE' => $signature,
							'MERCHANT_ORDER' => $paytpv_order_ref,
							'MERCHANT_AMOUNT' => $importe,
							'MERCHANT_CURRENCY' => $currency_iso_code,
							'IDUSER' => $saved_card["paytpv_iduser"],
							'TOKEN_USER' => $saved_card["paytpv_tokenuser"],
							'3DSECURE' => $secure_pay,
							'URLOK' => $URLOK,
							'URLKO' => $URLKO
						);
						
					$query = http_build_query($fields);

					$salida = $this->getIframeUrl($secure_pay) . $query;

					header('Location: '.$salida);
					exit;
				}
 
				// PAGO NO SEGURO --------------------------------------------------------------------------

			    // Test Mode
				if ($this->environment==1){
					$charge['DS_RESPONSE'] =1;
					$order->add_order_note( __( 'PayTpv payment completed', 'woocommerce' ) );
					$_REQUEST[ 'AuthCode' ] = 'Test_mode';
					$order->payment_complete($_REQUEST[ 'AuthCode' ]);
					update_post_meta( ( int ) $order->id, 'PayTPV_Referencia', __('Test Mode. Not Real Payment', 'wc_paytpv' ) );


				}else{
					$client = $this->get_client();
					$charge = $client->execute_purchase( $order,$saved_card["paytpv_iduser"],$saved_card["paytpv_tokenuser"],$term,$pass,$currency_iso_code,$importe,$paytpv_order_ref);
				}

				if ( ( int ) $charge[ 'DS_RESPONSE' ] == 1 ) {
					
					// Se procesa en la notificacion
					/*
					$order->add_order_note( __( 'PayTpv payment completed', 'woocommerce' ) );
					$order->payment_complete($charge[ 'DS_MERCHANT_AUTHCODE' ]);
					update_post_meta( ( int ) $order->id, 'PayTPV_Referencia', $charge[ 'DS_MERCHANT_ORDER' ]);
					*/

					update_post_meta( ( int ) $order->id, 'PayTPV_IdUser', $saved_card["paytpv_iduser"] );
					update_post_meta( ( int ) $order->id, 'PayTPV_TokenUser', $saved_card["paytpv_tokenuser"] );

					$url = $this->get_return_url( $order );
				}else{
					$url = $order->get_cancel_order_url();

				}
				wp_redirect( $url, 303 );
				
			}

			
			if ( $_REQUEST[ 'tpvLstr' ] == 'notify' && isset($_POST["TransactionType"])) {//NOTIFICACIÓN

				switch ($_POST["TransactionType"]){
					// add_User
					case 107:



						$terminal = $this->paytpv_terminals[0];
						$term = $terminal["term"];
						$pass = $terminal["pass"];

						$user_id = $_POST["Order"];
						$DateTime = (isset($_POST[ 'DateTime']))?$_POST[ 'DateTime']:"";
						$Signature = (isset($_POST[ 'Signature']))?$_POST[ 'Signature']:"";

						$SIGNATURE = md5($this->clientcode . $term . $_POST["TransactionType"] . $_POST[ 'Order' ] . $DateTime . md5($pass));

						if ( $_REQUEST[ 'TransactionType' ] == '107' && $_REQUEST[ 'Response' ] == 'OK' && ($Signature == $SIGNATURE || $this->environment==1)) {
							if (isset($_REQUEST[ 'IdUser' ])){
								// Save User Card
								$result = $this->saveCard(null, $user_id,$_REQUEST[ 'IdUser' ],$_REQUEST[ 'TokenUser' ],$_POST["TransactionType"]);
							}
						}

						// Modo Test 
						if ($this->environment==1){
							$res["urlok"] = wc_get_page_permalink( 'myaccount' );
							print json_encode($res);
							exit;
						}

						print "Notificacion Procesada";
						exit;

					break;

					// execute_purchase
					case 1:
					case 109:
						$arrTerminalData = $this->TerminalCurrency($order);
						$currency_iso_code = $arrTerminalData["currency_iso_code"];
						$term = $arrTerminalData["term"];
						$pass = $arrTerminalData["pass"];

						$AMOUNT = round( $order->get_total() * 100 );
						
						$mensaje = $this->clientcode .
								$term .
								$_REQUEST[ 'TransactionType' ] .
								$_REQUEST[ 'Order' ] .
								$_REQUEST[ 'Amount' ] .
								$currency_iso_code;

						
						$SIGNATURE = md5( $mensaje . md5( $pass ) . $_REQUEST[ 'BankDateTime' ] . $_REQUEST[ 'Response' ] );
						if ( ($_REQUEST[ 'TransactionType' ] == '1' || $_REQUEST[ 'TransactionType' ] == '109')  && $_REQUEST[ 'Response' ] == 'OK' && ($_REQUEST[ 'ExtendedSignature' ] == $SIGNATURE || $this->environment==1)) {

							if (isset($_REQUEST[ 'IdUser' ])){

								$save_card = get_post_meta( ( int ) $order->id, 'paytpv_savecard', true );
								if ($save_card!=="0"){
									// Save User Card
									$result = $this->saveCard($order, $order->user_id, $_REQUEST[ 'IdUser' ],$_REQUEST[ 'TokenUser' ],$_POST["TransactionType"]);
									$paytpv_iduser = $result["paytpv_iduser"];
									$paytpv_tokenuser = $result["paytpv_tokenuser"];
								}else{
									$paytpv_iduser = $_REQUEST[ 'IdUser' ];
									$paytpv_tokenuser = $_REQUEST[ 'TokenUser' ];
								}

								update_post_meta( ( int ) $order->id, 'PayTPV_IdUser', $paytpv_iduser );
								update_post_meta( ( int ) $order->id, 'PayTPV_TokenUser', $paytpv_tokenuser );
								
							}

							$order->add_order_note( __( 'PayTpv payment completed', 'woocommerce' ) );
							$order->payment_complete($_REQUEST[ 'AuthCode' ]);

							update_post_meta( ( int ) $order->id, 'PayTPV_Referencia', $_REQUEST[ 'Order' ] );

							// Modo Test 
							if ($this->environment==1){
								$url = $this->get_return_url( $order );
								$res["urlok"] = $url;
								print json_encode($res);
								exit;
							}
							print "Notificacion Procesada";
						}else{
							print "Notificacion NO Procesada";
						}

					break;
				}
				print "Error";
				exit;
			}

			// Save Card in execute_purchase
			if ( $_REQUEST[ 'tpvLstr' ] == 'savecard' ) {//NOTIFICACIÓN

				update_post_meta( ( int ) $order->id, 'paytpv_savecard', $_POST["paytpv_agree"] );
				exit;
			}

			// Save Card Description
			if ( $_REQUEST[ 'tpvLstr' ] == 'saveDesc' ) {//NOTIFICACIÓN
				$card_desc = $_POST["card_desc"];
				$id_card = $_GET["id"];

				$saved_cards = Paytpv::saveCardDesc($id_card,$card_desc);
				
				$res["resp"] = 0;
				print json_encode($res);
				exit;
			}

			// Remove User Card
			if ( $_REQUEST[ 'tpvLstr' ] == 'removeCard' ) {//NOTIFICACIÓN
				
				$id_card = $_GET["id"];

				$saved_cards = Paytpv::removeCard($id_card);
				
				$res["resp"] = 0;
				print json_encode($res);
				exit;
			}

			// Load Test Mode iframe Payment
			if ( $_REQUEST[ 'tpvLstr' ] == 'testmode' ) {//NOTIFICACIÓN

				$dsecure = (isset($_REQUEST["dsecure"]))?$_REQUEST["dsecure"]:"";
				if ($dsecure==1){
					print wc_get_template( 'payment_3ds_test.php', array( ), '', PAYTPV_PLUGIN_DIR . 'template/' );
				}else{
					print wc_get_template( 'payment_test_mode.php', array( ), '', PAYTPV_PLUGIN_DIR . 'template/' );
				}
				exit;
			}

			// Check Test Mode Card
			if ( $_REQUEST[ 'tpvLstr' ] == 'checkcard' ) {//NOTIFICACIÓN
				
				// add_user
				if ($_POST['TransactionType']==107){
					$secure_pay = 0;
				}else{
					// Obtenemos el terminal para el pedido
					$arrTerminalData = $this->TerminalCurrency($order);	
					$secure_pay = $this->isSecureTransaction($order,$arrTerminalData,0,0)?1:0;
				}

				$res["dsecure"] = $secure_pay;
				if ($secure_pay==1) sleep(2);

				// Test Mode
				$res["checked"] = 0;

				$arrTestCard = array(5325298401138208,5540568785541245,5407696658785988);

				$mm = 5;
				$yy = 20;
				$merchan_cvc2 = 123;

				if (in_array($_POST["merchan_pan"],$arrTestCard) && $_POST["mm"]==$mm && $_POST["yy"]==$yy && $_POST["merchan_cvc2"]==$merchan_cvc2)
					$res["checked"] = 1;

				
				print json_encode($res);
				exit;
			}

			print "Error Notificacion";
			exit;
			
		}

		/**
		 * Validate user password
		 * */
		public function validPassword($id,$passwd){
			
			$user = new WP_User( $id);

			if (wp_check_password($passwd, $user->user_pass, $user->ID)){
				return true;
			}return false;
		}

		/**
		 * Get PayTpv language code
		 * */
		function _getLanguange() {
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
				default:
					return 'ES';
			}
			return 'ES';
		}

		/**
		 * Get PayTpv Args for passing to PP
		 * */
		function get_paytpv_args( $order ) {
			$paytpv_req_args = array( );
			$paytpv_args = array( );
			$paytpv_args = $this->get_paytpv_bankstore_args( $order );
			return array_merge( $paytpv_args, $paytpv_req_args );
		}

		function get_paytpv_bankstore_args( $order ) {

			// Obtenemos el terminal para el pedido
			$arrTerminalData = $this->TerminalCurrency($order);
			
			$importe = $arrTerminalData["importe"];
			$currency_iso_code = $arrTerminalData["currency_iso_code"];
			$term = $arrTerminalData["term"];
			$pass = $arrTerminalData["pass"];

			$secure_pay = $this->isSecureTransaction($order,$arrTerminalData,0,0)?1:0;

			$OPERATION = '1';
			//$URLOK		= add_query_arg('tpvLstr','notify',add_query_arg( 'wc-api', 'woocommerce_'. $this->id, home_url( '/' ) ) );
			$MERCHANT_ORDER = str_pad( $order->id, 8, "0", STR_PAD_LEFT );
			$MERCHANT_AMOUNT = $importe;
			$MERCHANT_CURRENCY = $currency_iso_code;
			$URLOK = $this->get_return_url( $order );
			$URLKO = $order->get_cancel_order_url();
			$paytpv_req_args = array( );
			$mensaje = $this->clientcode . $term . $OPERATION . $MERCHANT_ORDER . $MERCHANT_AMOUNT . $MERCHANT_CURRENCY;
			$MERCHANT_MERCHANTSIGNATURE = md5( $mensaje . md5( $pass ) );

			$paytpv_args = array(
				'MERCHANT_MERCHANTCODE' => $this->clientcode,
				'MERCHANT_TERMINAL' => $term,
				'OPERATION' => $OPERATION,
				'LANGUAGE' => $this->_getLanguange(),
				'MERCHANT_MERCHANTSIGNATURE' => $MERCHANT_MERCHANTSIGNATURE,
				'MERCHANT_ORDER' => $MERCHANT_ORDER,
				'MERCHANT_AMOUNT' => $MERCHANT_AMOUNT,
				'MERCHANT_CURRENCY' => $MERCHANT_CURRENCY,
				'URLOK' => $URLOK,
				'URLKO' => $URLKO,
				'3DSECURE' => $secure_pay
			);
			return array_merge( $paytpv_args, $paytpv_req_args );
		}

		
		
		function process_payment( $order_id ) {
			$this->write_log( 'Process payment: ' . $order_id );
			$order = new WC_Order( $order_id );
			return array(
				'result' => 'success',
				'redirect' => add_query_arg( 'order', $order->id, add_query_arg( 'key', $order->order_key, get_permalink( woocommerce_get_page_id( 'pay' ) ) ) )
			);
		}	


		/**
		 * Safe transaction
		 * */

		public function isSecureTransaction($order,$arrTerminalData,$card,$paytpv_iduser){
			$importe = $order->get_total();

	        $terminales = $arrTerminalData["terminales"];
	        $tdfirst = $arrTerminalData["tdfirst"];
	        $tdmin = $arrTerminalData["tdmin"];
	        // Transaccion Segura:
	        
	        // Si solo tiene Terminal Seguro
	        if ($terminales==0)
	            return true;   

	        // Si esta definido que el pago es 3d secure y no estamos usando una tarjeta tokenizada
	        if ($tdfirst && $card==0){
	        	
	            return true;
	        }

	        // Si se supera el importe maximo para compra segura
	        if ($terminales==2 && ($tdmin>0 && $tdmin < $importe)){
	        	
	            return true;
	          }

	         // Si esta definido como que la primera compra es Segura y es la primera compra aunque este tokenizada
	        if ($terminales==2 && $tdfirst && $card>0 && $this->isFirstPurchaseToken($order->user_id,$paytpv_iduser)){
	            return true;
	        }
	        
	        
	        return false;
	    }


	    /**
		 * Safe transaction
		 * */
	    public function isFirstPurchaseToken($id_customer,$paytpv_iduser){
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
		public function TerminalCurrency($order){
			$order_currency = $order->get_order_currency();
			// PENDIENTE: Aqui habría que buscar un terminal en la moneda del pedido
			foreach ( $this->paytpv_terminals as $terminal){
				if ($terminal["moneda"]==$order_currency)
					$terminal_currency = $terminal;
			}

			// Not exists terminal in user currency
			if (empty($terminal_currency) === true){

				// Search for terminal in merchant default currency
				foreach ( $this->paytpv_terminals as $terminal){
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
			$arrTerminalData["terminales"] = $terminal_currency["terminales"];
			$arrTerminalData["tdfirst"] = $terminal_currency["dsecure"];
			$arrTerminalData["currency_iso_code"] = $terminal_currency["moneda"];
			$arrTerminalData["importe"] = number_format($order->get_total() * 100, 0, '.', '');
			$arrTerminalData["tdmin"] = $terminal_currency["tdmin"];
			
	        return $arrTerminalData;
		}

		/**
		 * receipt_page
		 * */
		function receipt_page( $order_id ) {
			echo '<p>' . __( 'Thanks for your order, please fill the data below to process the payment.', 'wc_paytpv' ) . '</p>';

			echo '<script type="text/javascript" src="'.PAYTPV_PLUGIN_URL . 'js/paytpv.js"></script>';


			echo $this->savedCardsHtml($order_id);
			
		}


		/**
		 * Html saved Cards 
		 */
		function savedCardsHtml($order_id){
			$order = new WC_Order( $order_id );
			$saved_cards = Paytpv::savedCards($order->user_id);

			// Tarjetas almacenadas
			$store_card = (sizeof($saved_cards)==0)?"none":"";
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

            // Contraseña tienda
            if ($this->commerce_password==1){
            	print '<div id="div_commerce_password">'.__( 'Commerce Password', 'wc_paytpv' ).': <input type="password" name="commerce_password" id="commerce_password"></div>';
            }

            if (sizeof($saved_cards)>0){
	        					
				// Pago directo
				print  '<input type="submit" id="direct_pay" value="'.__( 'Pay', 'wc_paytpv' ).'" class="button alt">';
				print  '<img src="'.PAYTPV_PLUGIN_URL . 'images/clockpayblue.gif" alt="'.__( 'Wait, please...', 'wc_paytpv' ).'" width="41" height="30" id="clockwait" style="display:none; margin-top:5px;" />';
				print '<input type="hidden" name="tpvLstr" value="pay">';
				
			}
			print '<input type="hidden" id="order_id" name="Order" value="'.$order_id.'">';


			// Comprobacion almacenar tarjeta
			$store_card = (sizeof($saved_cards)==0)?"":"";
			print '
			<div id="storingStep" class="box" style="display:'.$store_card.'">
                <h4>'.__('STREAMLINE YOUR FUTURE PURCHASES!', 'wc_paytpv' ).'</h4>
                <label class="checkbox"><input type="checkbox" name="savecard" id="savecard" onChange="saveOrderInfoJQ()" checked>'.__('Yes, remember my card accepting the', 'wc_paytpv' ).' <a id="open_conditions" href="#conditions" class="link"> '.__('terms and conditions of the service', 'wc_paytpv' ).'.</a>.</label>';

            print  $this->generate_paytpv_form( $order_id );
            print '</div>';

            print '</form>';

            wc_get_template( 'myaccount/conditions.php', array( ), '', PAYTPV_PLUGIN_DIR . 'template/' );



		}

		public function getIframeUrl($dsecure){
			if ($this->environment==1){
				return add_query_arg(array("tpvLstr"=>"testmode","dsecure"=> $dsecure,"wc-api"=> 'woocommerce_' . $this->id)) . "&";
			}else{
				return $this->iframeurl . "?";
			}
		}


		/**
		 * Generate the paytpv button link
		 * */
		function generate_paytpv_form( $order_id ) {
			global $woocommerce;

			$order = new WC_Order( $order_id );
			$paytpv_args = $this->get_paytpv_args( $order );

			$iframe_url = $this->getIframeUrl(0);
			

			$html = '';
			$html .= '<iframe id="paytpv_iframe" src="' . $iframe_url . '' . http_build_query( $paytpv_args ) . '"
	name="paytpv" style="width: 670px; border-top-width: 0px; border-right-width: 0px; border-bottom-width: 0px; border-left-width: 0px; border-style: initial; border-color: initial; border-image: initial; height: 320px; " marginheight="0" marginwidth="0" scrolling="no"></iframe>';
			
			return $html;
			
		}

		function get_client() {
			if ( !isset( $this->ws_client ) ) {
				require_once PAYTPV_PLUGIN_DIR . '/ws_client.php';
				$this->ws_client = new WS_Client( $this->settings );
			}
			return $this->ws_client;
		}


		public function saveCard($order,$user_id,$paytpv_iduser,$paytpv_tokenuser,$TransactionType){
			// Si es una operción de add_user o no existe el token asociado al usuario lo guardamos
			if ($TransactionType==107 || !PayTPV::existsCard($paytpv_iduser,$user_id)){
				// Live Mode
				if ($this->environment!=1){
					if ($order!=null){
						// Obtenemos el terminal para el pedido
						$arrTerminalData = $this->TerminalCurrency($order);
					}else{
						$arrTerminalData = $this->paytpv_terminals[0];
					}
					
					$term = $arrTerminalData["term"];
					$pass = $arrTerminalData["pass"];
					

					$client = $this->get_client();
					$result = $client->info_user( $paytpv_iduser, $paytpv_tokenuser, $term, $pass);
				// Test Mode
				}else{
					$paytpv_cc = $_POST["merchan_pan"];
					$result = array('DS_MERCHANT_PAN'=>$paytpv_cc,'DS_CARD_BRAND'=>'MASTERCARD');
				}

				return PayTPV::saveCard($this->environment,$user_id,$paytpv_iduser,$paytpv_tokenuser,$result['DS_MERCHANT_PAN'],$result['DS_CARD_BRAND']);

			}else{
				$result["paytpv_iduser"] = $paytpv_iduser;
				$result["paytpv_tokenuser"] = $paytpv_tokenuser;
				return $result;
			}
			
		}

		/**
		 * Operaciones sucesivas
		 * */
		
		function scheduled_subscription_payment( $amount_to_charge, $order, $product_id ) {


			$this->write_log( 'scheduled_subscription_payment: ' . $amount_to_charge . '€ ' . $order->id );
			$client = $this->get_client();
			
			// Obtenemos el numero de pago de la suscripcion
			$subscription_key = WC_Subscriptions_Manager::get_subscription_key( $order->id, $product_id );
			$num_pago = WC_Subscriptions_Manager::get_subscriptions_completed_payment_count( $subscription_key );
			
			$paytpv_order_ref = $order->id . "_" . $num_pago;
			$importe =  number_format($amount_to_charge * 100, 0, '.', '');

			// Obtenemos el terminal para el pedido
			$arrTerminalData = $this->TerminalCurrency($order);
			$currency_iso_code = $arrTerminalData["currency_iso_code"];
			$term = $arrTerminalData["term"];
			$pass = $arrTerminalData["pass"];
			$payptv_iduser = get_post_meta( ( int ) $order->id, 'PayTPV_IdUser', true );
			$payptv_tokenuser = get_post_meta( ( int ) $order->id, 'PayTPV_TokenUser', true );
			

			$result = $client->execute_purchase( $order,$payptv_iduser,$payptv_tokenuser,$term,$pass,$currency_iso_code,$importe,$paytpv_order_ref);

			if ( ( int ) $result[ 'DS_RESPONSE' ] == 1 ) {
				update_post_meta($order->id, 'PayTPV_Referencia', $result[ 'DS_MERCHANT_ORDER' ]);
				update_post_meta($order->id, '_transaction_id', $result['DS_MERCHANT_AUTHCODE']);	

				WC_Subscriptions_Manager::process_subscription_payments_on_order( $order );
			}
		}

		/*
		function scheduled_subscription_payment( $amount_to_charge, $renewal_order ) {

			print "Txerra";
			exit;

			$this->write_log( 'scheduled_subscription_payment: ' . $amount_to_charge . '€ ' . $renewal_order->id );
			$client = $this->get_client();
			
			$paytpv_order_ref = $renewal_order->id;
			$importe =  number_format($amount_to_charge * 100, 0, '.', '');

			// Obtenemos el terminal para el pedido
			$arrTerminalData = $this->TerminalCurrency($renewal_order);
			$currency_iso_code = $arrTerminalData["currency_iso_code"];
			$term = $arrTerminalData["term"];
			$pass = $arrTerminalData["pass"];
			$payptv_iduser = get_post_meta( ( int ) $renewal_order->id, 'PayTPV_IdUser', true );
			$payptv_tokenuser = get_post_meta( ( int ) $renewal_order->id, 'PayTPV_TokenUser', true );
			

			$result = $client->execute_purchase( $renewal_order,$payptv_iduser,$payptv_tokenuser,$term,$pass,$currency_iso_code,$importe,$paytpv_order_ref);

			if ( ( int ) $result[ 'DS_RESPONSE' ] == 1 ) {
				update_post_meta($order->id, 'PayTPV_Referencia', $result[ 'DS_MERCHANT_ORDER' ]);
				update_post_meta($order->id, '_transaction_id', $result['DS_MERCHANT_AUTHCODE']);
				WC_Subscriptions_Manager::process_subscription_payments_on_order( $order );
			}
		}
		*/

		
		function store_renewal_order_id( $order_meta_query, $original_order_id, $renewal_order_id, $new_order_role ) {
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
		public function can_refund_order( $order ) {
			return $order && $order->get_transaction_id() && get_post_meta( ( int ) $order->id, 'PayTPV_IdUser', true );
		}

		/**
		 * Is a test Order
		 * @param  WC_Order $order
		 * @return bool
		 */
		public function isTestOrder( $order ) {
			$token = get_post_meta( ( int ) $order->id, 'PayTPV_TokenUser', true );
			return ($token=="TESTTOKEN")?true:false;
		}

		/**
		 * Process a refund if supported
		 * @param  int $order_id
		 * @param  float $amount
		 * @param  string $reason
		 * @return  boolean True or false based on success, or a WP_Error object
		 */
		public function process_refund( $order_id, $amount = null, $reason = '' ) {
			

			$order = wc_get_order( $order_id );

			if ( ! $this->can_refund_order( $order ) ) {
				$this->log( 'Refund Failed: No transaction ID' );
				return false;
			}

			if ( $this->isTestOrder( $order ) ) {
				$result[ 'DS_RESPONSE' ]  = 1;
				$result[ 'DS_MERCHANT_AUTHCODE'] = 'Test_mode';
			}else{
				$client = $this->get_client();
				// Obtenemos el terminal para el pedido
				$arrTerminalData = $this->TerminalCurrency($order);
				$currency_iso_code = $arrTerminalData["currency_iso_code"];
				$term = $arrTerminalData["term"];
				$pass = $arrTerminalData["pass"];

				$importe = number_format($amount * 100, 0, '.', '');

				$paytpv_order_ref = get_post_meta( ( int ) $order->id, 'PayTPV_Referencia', true );
				$payptv_iduser = get_post_meta( ( int ) $order->id, 'PayTPV_IdUser', true );
				$payptv_tokenuser = get_post_meta( ( int ) $order->id, 'PayTPV_TokenUser', true );
				$transaction_id = $order->get_transaction_id();


				$result = $client->execute_refund($payptv_iduser, $payptv_tokenuser, $paytpv_order_ref, $term,$pass,$currency_iso_code,  $transaction_id, $importe);
			}
			if ( ( int ) $result[ 'DS_RESPONSE' ] != 1 ) {
				$this->log( 'Refund Failed: ' . $result->get_error_message() );
				return false;
			}else{
				$order->add_order_note( sprintf( __( 'Refunded %s - Refund ID: %s', 'woocommerce' ), $amount, $result['DS_MERCHANT_AUTHCODE'] ) );
				return true;
			}

		}

		
	}
