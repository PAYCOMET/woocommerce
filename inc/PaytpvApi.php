<?php

class PaytpvApi
{
    public function __construct()
    {
    }


    public function validatePaycomet($merchantCode, $idterminal, $password, $terminales_txt)
    {

        $merchantCode = trim($merchantCode);
        $idterminal = trim($idterminal);
        $password = trim($password);

        $endpoint = "https://api.paycomet.com/gateway/json_product_url_check.php";
        $signature = hash('sha512', $merchantCode . $idterminal . $password);        

        $arrParams = array(
            'DS_MERCHANT_MERCHANTCODE' => $merchantCode,
            'DS_MERCHANT_TERMINAL' => $idterminal,
            'DS_MERCHANT_MERCHANTSIGNATURE' => $signature,
            'DS_MERCHANT_TERMINALES' => $terminales_txt
        );

        
        $json = json_encode($arrParams);
        $ch = curl_init();
        

        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_VERBOSE, true);


        $ret = curl_exec($ch);
        
        if (!$ret) {
            $ret = json_encode(array("DS_RESPONSE" => 2, "DS_ERROR_ID" => "28"));
        }
        curl_close($ch);

        return json_decode($ret, true);
    }
}
