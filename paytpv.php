<?php
	class PayTPV {

		public static function get_option( $name, $default = false ) {
			$option = get_option( 'paytpv' );

			if ( false === $option ) {
				return $default;
			}

			if ( isset( $option[$name] ) ) {
				return $option[$name];
			} else {
				return $default;
			}
		}

		public static function update_option( $name, $value ) {
			$option = get_option( 'paytpv' );
			$option = ( false === $option ) ? array() : (array) $option;
			$option = array_merge( $option, array( $name => $value ) );
			update_option( 'paytpv', $option );
		}


		public static function savedActiveCards($user_id){
			global $wpdb;

			$saved_cards = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}paytpv_customer WHERE id_customer>0 and id_customer = ". $user_id  ." order by date desc", ARRAY_A);

			foreach ($saved_cards as $key => $card){

				$expiryDate = $card["paytpv_expirydate"];
				if($expiryDate == ""){
					Paytpv::fillExpirydate($card["paytpv_iduser"], $card["paytpv_tokenuser"], $card["id"], $expiryDate);
				}

				// If expired
				if ((int)date("Ym") > (int)str_replace("/", "", $expiryDate)) {
					unset($saved_cards[$key]);
				}

			}

			return $saved_cards;
		}

		public static function savedClientCards($user_id){
			global $wpdb;

			$saved_cards_validated = [];
        	$saved_cards_validated["valid"] = [];
        	$saved_cards_validated["invalid"] = [];

			$saved_cards = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}paytpv_customer WHERE id_customer>0 and id_customer = ". $user_id  . " order by date desc", ARRAY_A);

			foreach ($saved_cards as $card){
				$expiryDate = $card["paytpv_expirydate"];
				if($expiryDate == ""){
					Paytpv::fillExpirydate($card["paytpv_iduser"], $card["paytpv_tokenuser"], $card["id"], $expiryDate);
				}

				// If not expired
				if ((int)date("Ym") < (int)str_replace("/", "", $expiryDate)) {
					$card['paytpv_expirydate'] = $expiryDate;
					$saved_cards_validated["valid"][] = $card;
				} else {
					if ($expiryDate == "1900/01") {
						$card['paytpv_expirydate'] = "";
					}
					$saved_cards_validated["invalid"][] = $card;
				}
			}

			return $saved_cards_validated;
		}

		public static function fillExpirydate($idUser, $tokenUser, $id, &$expiryDate){
			global $wpdb;

			$paytpv_terminals = get_option('woocommerce_paytpv_terminals');
			$term=$paytpv_terminals[0]["term"];

			$apiRest = new PaycometApiRest(get_option('woocommerce_paytpv_settings')['apikey']);
			$infoUserResponse = $apiRest->infoUser(
				$idUser,
				$tokenUser,
				$term
			);

			if ($infoUserResponse->errorCode == 1001) {
				$expiryDate = '1900/01';
			}else{
				$expiryDate = $infoUserResponse->expiryDate;
			}
			$update_prepared = $wpdb->prepare( "UPDATE {$wpdb->prefix}paytpv_customer
												SET paytpv_expirydate=%s WHERE id=%d",$expiryDate,$id);
			$wpdb->query( $update_prepared );

		}

		public static function savedCard($user_id,$id_card){
			global $wpdb;

			$saved_card = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}paytpv_customer WHERE id_customer = %d AND id = %d", $user_id, $id_card ), ARRAY_A );

			return $saved_card;
		}

		public static function saveCardDesc($id_card,$card_desc){
			global $wpdb;

			$saved_card = $wpdb->get_row( $wpdb->prepare( "update {$wpdb->prefix}paytpv_customer set card_desc = %s WHERE id_customer = %d AND id = %d", $card_desc, get_current_user_id(),$id_card ), ARRAY_A );

			return $saved_card;
		}

		public static function existsCOF($paytpv_iduser,$paytpv_tokenuser){
			global $wpdb;

			$tokenCOF = $wpdb->get_row( $wpdb->prepare( "SELECT tokenCOF FROM {$wpdb->prefix}paytpv_customer WHERE paytpv_iduser = %d AND paytpv_tokenuser = %d  ", $paytpv_iduser, $paytpv_tokenuser ), ARRAY_A );

			return $tokenCOF;
		}

		public static function saveCOF($tokenCOF,$paytpv_iduser,$paytpv_tokenuser){
            global $wpdb;

            $saved_card = $wpdb->get_row( $wpdb->prepare( "update {$wpdb->prefix}paytpv_customer set tokenCOF = %s WHERE paytpv_iduser = %d AND paytpv_tokenuser = %d ", $tokenCOF, $paytpv_iduser, $paytpv_tokenuser), ARRAY_A );

            return $saved_card;
        }

		public static function get_my_cards_template($id) {
		
			$paytpv_terminals = get_option('woocommerce_paytpv_terminals');
			$term=$paytpv_terminals[0]["term"];
			
			$apiRest = new PaycometApiRest(get_option('woocommerce_paytpv_settings')['apikey']);
			$apiResponse = $apiRest->form(
				1,
				'ES',
				$term,
				'',
				[
					'terminal' => (int) $term,
					'methods' => [1],
					'order' => $id . "_" . rand() . "_tokenization",
					'amount' => '50',
					'currency' => 'EUR',
					'secure' => 1,
					'urlOk' => (string) get_permalink( get_option('woocommerce_myaccount_page_id') ),
					'urlKo' => (string) get_permalink( get_option('woocommerce_myaccount_page_id') ),
				]
			);
			if ($apiResponse->errorCode==0) {
				$url_paytpv = $apiResponse->challengeUrl;
			}else{
				$url_paytpv=true;
			}

			return $url_paytpv;
		}

		public static function get_my_cards_template_expired($id) {
		
			$paytpv_terminals = get_option('woocommerce_paytpv_terminals');
			$term=$paytpv_terminals[0]["term"];
			
			$apiRest = new PaycometApiRest(get_option('woocommerce_paytpv_settings')['apikey']);
			$apiResponse = $apiRest->form(
				107,
				'ES',
				$term,
				'',
				[
					'terminal' => (int) $term,
					'order' => $id . "_" . rand() . "_tokenization",
					'urlOk' => (string) get_permalink( get_option('woocommerce_myaccount_page_id') ),
					'urlKo' => (string) get_permalink( get_option('woocommerce_myaccount_page_id') ),
				]
			);
			if ($apiResponse->errorCode==0) {
				$url_paytpv = $apiResponse->challengeUrl;
			}else{
				$url_paytpv=true;
			}

			return $url_paytpv;
		}

		public static function checkCardExistence($user_id, $id_card, $paytpv_cc, $paytpv_brand){
			global $wpdb;

			$paytpv_cc = '************' . substr($paytpv_cc, -4);

			$saved_cards = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}paytpv_customer WHERE paytpv_brand = '" . $paytpv_brand . "' AND paytpv_cc = '" . $paytpv_cc . "' AND id_customer = '" . $user_id . "' AND id != '" . $id_card . "'");

			if (count($saved_cards) == 0) {
				return true;
			}else{
				return false;
			}
		}

		public static function oldSavedCard($id_card){
			global $wpdb;

			$saved_card = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}paytpv_customer WHERE id = %d", $id_card ), ARRAY_A );

			return $saved_card;
		}

		public static function removeCardTokenization($id_card){
			global $wpdb;

			$saved_card = $wpdb->get_row( $wpdb->prepare( "delete from {$wpdb->prefix}paytpv_customer WHERE id = %d", $id_card ), ARRAY_A );

			return $saved_card;
		}

		public static function subscriptionsWithCard($paytpv_iduser){
			global $wpdb;

			$orders = $wpdb->get_results( $wpdb->prepare( "SELECT t1.order_id FROM {$wpdb->prefix}wc_orders_meta t1 WHERE t1.meta_value = %d AND (SELECT t2.id FROM {$wpdb->prefix}wc_orders t2 WHERE t2.parent_order_id=t1.order_id  limit 1)", $paytpv_iduser ), ARRAY_A );

			return $orders;
		}

		public static function replaceIdUser($order,$paytpv_iduser){
            global $wpdb;

            $idUserUpdated = $wpdb->get_row( $wpdb->prepare( "update {$wpdb->prefix}wc_orders_meta set meta_value = %s  WHERE order_id = %d and meta_key='PayTPV_IdUser'", $paytpv_iduser,$order ), ARRAY_A );

            return $idUserUpdated;
        }

		public static function replaceTokenUser($order,$paytpv_tokenuser){
            global $wpdb;

            $tokenUserUpdated = $wpdb->get_row( $wpdb->prepare( "update {$wpdb->prefix}wc_orders_meta set meta_value = %s  WHERE order_id = %d and meta_key='Paytpv_TokenUser'", $paytpv_tokenuser,$order ), ARRAY_A );

            return $tokenUserUpdated;
        }

		public static function removeCard($id_card){
			global $wpdb;

			$saved_card = $wpdb->get_row( $wpdb->prepare( "delete from {$wpdb->prefix}paytpv_customer WHERE id_customer = %d AND id = %d", get_current_user_id(),$id_card ), ARRAY_A );

			return $saved_card;
		}

		public static function existsCard($paytpv_iduser,$user_id){
			global $wpdb;

			$card = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}paytpv_customer WHERE id_customer = %d AND paytpv_iduser = %d", $user_id, $paytpv_iduser ), ARRAY_A );

			if ( null !== $card ) {
				return true;
			}else{
				return false;

			}
		}

		public static function saveCard($user_id, $paytpv_iduser, $paytpv_tokenuser, $paytpv_cc, $paytpv_brand, $paytpv_expirydate, $paytpv_cof){
			global $wpdb;

			$paytpv_cc = '************' . substr($paytpv_cc, -4);

			$saved_cards = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}paytpv_customer WHERE paytpv_brand = '" . $paytpv_brand . "' AND paytpv_cc = '" . $paytpv_cc . "' AND id_customer = '" . $user_id . "'");
			
			if (count($saved_cards) == 0) {

				if ($user_id>0){
					$insert_prepared = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}paytpv_customer(paytpv_iduser, paytpv_tokenuser, paytpv_cc, paytpv_brand, paytpv_expirydate, id_customer, `date`, tokenCOF )
														VALUES(%d, %s, %s, %s, %s, %d, %s, %s)",
													array($paytpv_iduser, $paytpv_tokenuser, $paytpv_cc, $paytpv_brand, $paytpv_expirydate, $user_id, date('Y-m-d H:i:s'), $paytpv_cof) );
					$wpdb->query( $insert_prepared );
				}
			}

			$result["paytpv_iduser"] = $paytpv_iduser;
			$result["paytpv_tokenuser"] = $paytpv_tokenuser;

			return $result;
		}


		public static function get_Customer(){
			global $wpdb;
			$mylink = $wpdb->get_row( "SELECT max(paytpv_iduser) as 'max_iduser' from {$wpdb->prefix}paytpv_customer WHERE paytpv_iduser<100000" );

			if ( null !== $mylink ) {
				$paytpv_iduser = $mylink->max_iduser+1;
			}else{
				$paytpv_iduser = 1;
			}
			return $paytpv_iduser;
		}


	}

?>