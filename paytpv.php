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

		public static function saveCard($user_id, $paytpv_iduser, $paytpv_tokenuser, $paytpv_cc, $paytpv_brand, $paytpv_expirydate){
			global $wpdb;

			$paytpv_cc = '************' . substr($paytpv_cc, -4);

			$saved_cards = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}paytpv_customer WHERE paytpv_brand = '" . $paytpv_brand . "' AND paytpv_cc = '" . $paytpv_cc . "' AND id_customer = '" . $user_id . "'");

			if (count($saved_cards) == 0) {

				if ($user_id>0){
					$insert_prepared = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}paytpv_customer(paytpv_iduser, paytpv_tokenuser, paytpv_cc, paytpv_brand, paytpv_expirydate, id_customer, `date` )
														VALUES(%d, %s, %s, %s, %s, %d, %s)",
													array($paytpv_iduser, $paytpv_tokenuser, $paytpv_cc, $paytpv_brand, $paytpv_expirydate, $user_id, date('Y-m-d H:i:s')) );
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