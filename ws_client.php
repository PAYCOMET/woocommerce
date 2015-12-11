<?php

if ( !class_exists( 'nusoap_client' ) ) {
	require_once(dirname( __FILE__ ) . '/lib/nusoap.php');
}

class WS_Client {

	var $client = null;
	var $config = null;

	private function write_log($log){
      if ( true === WP_DEBUG ) {
          if ( is_array( $log ) || is_object( $log ) ) {
              error_log( print_r( $log, true ));
          } else {
              error_log( $log);
          }
      }
	}

	public function __construct( array $config = array( ), $proxyhost = '', $proxyport = '', $proxyusername = '', $proxypassword = '' ) {
		$useCURL = isset( $_POST[ 'usecurl' ] ) ? $_POST[ 'usecurl' ] : '0';
		$this->config = $config;
		$this->client = new nusoap_client( 'https://secure.paytpv.com/gateway/xml_bankstore.php', false,
						$proxyhost, $proxyport, $proxyusername, $proxypassword );
		$err = $this->client->getError();
		if ( $err ) {
			$this->write_log($err);
			$this->write_log('Debug: '.$client->getDebug());
			exit();
		}
		$this->client->setUseCurl( $useCURL );
	}

	function execute_purchase( $order, $DS_IDUSER,$DS_TOKEN_USER,$DS_MERCHANT_TERMINAL,$DS_MERCHANT_PASS,$DS_MERCHANT_CURRENCY='EUR',$amount,$ref='' ) {

		$DS_MERCHANT_MERCHANTCODE = $this->config[ 'clientcode' ];
		$DS_MERCHANT_AMOUNT = $amount;

		$DS_MERCHANT_ORDER = str_pad( $ref, 8, "0", STR_PAD_LEFT );
		
		$DS_MERCHANT_MERCHANTSIGNATURE = sha1( $DS_MERCHANT_MERCHANTCODE . $DS_IDUSER . $DS_TOKEN_USER . $DS_MERCHANT_TERMINAL . $DS_MERCHANT_AMOUNT . $DS_MERCHANT_ORDER . $DS_MERCHANT_PASS );
		$DS_ORIGINAL_IP = get_post_meta( ( int ) $order->id, '_customer_ip_address', true );
		if ($DS_ORIGINAL_IP=="::1")	$DS_ORIGINAL_IP = "127.0.0.1";

		$p = array(
			'DS_MERCHANT_MERCHANTCODE' => $DS_MERCHANT_MERCHANTCODE,
			'DS_MERCHANT_TERMINAL' => $DS_MERCHANT_TERMINAL,
			'DS_IDUSER' => $DS_IDUSER,
			'DS_TOKEN_USER' => $DS_TOKEN_USER,
			'DS_MERCHANT_AMOUNT' => ( string ) $DS_MERCHANT_AMOUNT,
			'DS_MERCHANT_ORDER' => ( string ) $DS_MERCHANT_ORDER,
			'DS_MERCHANT_CURRENCY' => $DS_MERCHANT_CURRENCY,
			'DS_MERCHANT_MERCHANTSIGNATURE' => $DS_MERCHANT_MERCHANTSIGNATURE,
			'DS_ORIGINAL_IP' => $DS_ORIGINAL_IP,
			'DS_MERCHANT_PRODUCTDESCRIPTION' => '',
			'DS_MERCHANT_OWNER' => ''
		);
		$this->write_log("Petición execute_purchase:\n".print_r($p,true));
		$res = $this->client->call( 'execute_purchase', $p, '', '', false, true );
		$this->write_log("Respuesta execute_purchase:\n".print_r($res,true));
		return $res;
	}


	function info_user( $DS_IDUSER, $DS_TOKEN_USER,$DS_MERCHANT_TERMINAL,$DS_MERCHANT_PASS ) {
		$DS_MERCHANT_MERCHANTCODE = $this->config[ 'clientcode' ];
		$DS_MERCHANT_MERCHANTSIGNATURE = sha1( $DS_MERCHANT_MERCHANTCODE . $DS_IDUSER . $DS_TOKEN_USER . $DS_MERCHANT_TERMINAL . $DS_MERCHANT_PASS );
		$DS_ORIGINAL_IP = $_SERVER['REMOTE_ADDR'];
		if ($DS_ORIGINAL_IP=="::1")	$DS_ORIGINAL_IP = "127.0.0.1";
		$p = array(
			'DS_MERCHANT_MERCHANTCODE' => $DS_MERCHANT_MERCHANTCODE,
			'DS_MERCHANT_TERMINAL' => $DS_MERCHANT_TERMINAL,
			'DS_IDUSER' => $DS_IDUSER,
			'DS_TOKEN_USER' => $DS_TOKEN_USER,
			'DS_MERCHANT_MERCHANTSIGNATURE' => $DS_MERCHANT_MERCHANTSIGNATURE,
			'DS_ORIGINAL_IP' => $DS_ORIGINAL_IP
		);

		
		
		$this->write_log("Petición info_user:\n".print_r($p,true));
		$res = $this->client->call( 'info_user', $p, '', '', false, true );
		$this->write_log("Respuesta info_user:\n".print_r($res,true));
		return $res;
	}

	function execute_refund($DS_IDUSER, $DS_TOKEN_USER, $ref, $DS_MERCHANT_TERMINAL,$DS_MERCHANT_PASS,$DS_MERCHANT_CURRENCY,  $DS_MERCHANT_AUTHCODE, $DS_MERCHANT_AMOUNT) {

		
		$DS_MERCHANT_MERCHANTCODE = $this->config[ 'clientcode' ];
		$DS_MERCHANT_ORDER = str_pad( $ref, 8, "0", STR_PAD_LEFT );
		$DS_MERCHANT_MERCHANTSIGNATURE = sha1($DS_MERCHANT_MERCHANTCODE . $DS_IDUSER . $DS_TOKEN_USER . $DS_MERCHANT_TERMINAL . $DS_MERCHANT_AUTHCODE . $DS_MERCHANT_ORDER . $DS_MERCHANT_PASS);
		$DS_ORIGINAL_IP = get_post_meta( ( int ) $ref, '_customer_ip_address', true );
		if ($DS_ORIGINAL_IP=="::1")	$DS_ORIGINAL_IP = "127.0.0.1";

		$p = array(

			'DS_MERCHANT_MERCHANTCODE' => $DS_MERCHANT_MERCHANTCODE,
			'DS_MERCHANT_TERMINAL' => $DS_MERCHANT_TERMINAL,
			'DS_IDUSER' => $DS_IDUSER,
			'DS_TOKEN_USER' => $DS_TOKEN_USER,
			'DS_MERCHANT_AUTHCODE' => $DS_MERCHANT_AUTHCODE,
			'DS_MERCHANT_ORDER' => $DS_MERCHANT_ORDER,
			'DS_MERCHANT_CURRENCY' => $DS_MERCHANT_CURRENCY,
			'DS_MERCHANT_MERCHANTSIGNATURE' => $DS_MERCHANT_MERCHANTSIGNATURE,
			'DS_ORIGINAL_IP' => $DS_ORIGINAL_IP,
			'DS_MERCHANT_AMOUNT' => $DS_MERCHANT_AMOUNT

		);

		$this->write_log("Petición execute_refund:\n".print_r($p,true));

		$res = $this->client->call( 'execute_refund', $p, '', '', false, true );

		$this->write_log("Respuesta execute_refund:\n".print_r($res,true));

		return $res;

	}

}

/**
 * @author mikel
 *
 */
class CreditCard {

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var long
	 */
	protected $pan;

	/**
	 * @var unknown_type
	 */
	protected $exp;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var int
	 */
	protected $cvv;

	public function getType() {
		return $this->type;
	}

	public function getName() {
		return $this->name;
	}

	public function getPan() {
		return $this->pan;
	}

	public function getExp() {
		return $this->exp;
	}

	public function getCvv() {
		return $this->cvv;
	}

	public function setType( $type ) {
		$this->type = $type;
		return $this;
	}

	public function setName( $name ) {
		$this->name = $name;
		return $this;
	}

	public function setPan( $pan ) {
		$this->pan = $pan;
		return $this;
	}

	public function setExp( $exp ) {
		$this->exp = $exp;
		return $this;
	}

	public function setCvv( $cvv ) {
		$this->cvv = $cvv;
		return $this;
	}

}
