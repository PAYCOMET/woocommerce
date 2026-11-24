<?php

class PaytpvApi
{

    private int $timeout = 10;
    private string $endpointValidation = "https://api.paycomet.com/gateway/json_product_url_check.php";

    public function __construct() {}

    /*
     * Valida la configuración del terminal
     */
    public function validatePaycomet($merchantCode, $idterminal, $password, $terminales_txt)
    {
        $merchantCode = trim($merchantCode);
        $idterminal = trim($idterminal);
        $password = trim($password);

        $signature = hash('sha512', $merchantCode . $idterminal . $password);

        $arrParams = array(
            'DS_MERCHANT_MERCHANTCODE' => $merchantCode,
            'DS_MERCHANT_TERMINAL' => $idterminal,
            'DS_MERCHANT_MERCHANTSIGNATURE' => $signature,
            'DS_MERCHANT_TERMINALES' => $terminales_txt
        );

        $json = json_encode($arrParams);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->endpointValidation);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);


        $ret = curl_exec($ch);

        if (!$ret) {
            $ret = json_encode(array("DS_RESPONSE" => 2, "DS_ERROR_ID" => "28"));
        }
        curl_close($ch);

        return json_decode($ret, true);
    }
}
