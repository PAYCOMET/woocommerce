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


		public static function savedCards($user_id){
			global $wpdb;
			
			$saved_cards = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}paytpv_customer WHERE id_customer = ". $user_id  . " order by date desc", ARRAY_A);
			
			return $saved_cards;
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


		public static function saveCard($environment,$user_id,$paytpv_iduser,$paytpv_tokenuser,$paytpv_cc,$paytpv_brand){
			global $wpdb;

			$paytpv_cc = '************' . substr($paytpv_cc, -4);

			// Test Mode
			// First 100.000 paytpv_iduser for Test_Mode
			if ($environment==1){
				$paytpv_iduser = self::get_Customer();
				$paytpv_tokenuser = "TESTTOKEN";
				
			}

			$insert_prepared = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}paytpv_customer(paytpv_iduser, paytpv_tokenuser, paytpv_cc, paytpv_brand, id_customer, `date` )
												VALUES(%d, %s, %s,%s, %d, %s)",
			                                   array($paytpv_iduser, $paytpv_tokenuser, $paytpv_cc, $paytpv_brand, $user_id, date('Y-m-d H:i:s')) );
			$wpdb->query( $insert_prepared );


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