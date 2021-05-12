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

    public function executePurchaseToken(
        $merchantCode,
        $merchantTerminal,
        $merchantOrder,
        $merchantAmount,
        $merchantCurrency,
        $idUser,
        $tokenUser,
        $merchantPassword,
        $urlOk = '',
        $urlKo = '',
        $language = 'ES',
        $description = '',
        $descriptor = '',
        $dsecure = 1,
        $merchantScoring = '',
        $merchantData = '',
        $scaException = '',
        $merchantTrxType = '',
        $escrow = ''
    ) {
        $operation = 109;
        $signature = hash('sha512', $merchantCode . $idUser . $tokenUser . $merchantTerminal . $operation . $merchantOrder . $merchantAmount . $merchantCurrency . md5($merchantPassword));
        
        $params = [
            'MERCHANT_MERCHANTCODE' => $merchantCode,
            'MERCHANT_TERMINAL' => $merchantTerminal,
            'OPERATION' => $operation,
            'LANGUAGE' => $language,
            'MERCHANT_MERCHANTSIGNATURE' => $signature,
            'MERCHANT_ORDER' => $merchantOrder,
            'MERCHANT_PRODUCTDESCRIPTION' => $description,
            'MERCHANT_DESCRIPTOR' => $descriptor,
            'MERCHANT_AMOUNT' => $merchantAmount,
            'MERCHANT_CURRENCY' => $merchantCurrency,
            'IDUSER' => $idUser,
            'TOKEN_USER' => $tokenUser,
            '3DSECURE' => $dsecure,
            'MERCHANT_SCORING' => $merchantScoring,
            'MERCHANT_DATA' => $merchantData,
            'MERCHANT_SCA_EXCEPTION' => $scaException,  
            'MERCHANT_TRX_TYPE' => $merchantTrxType,
            'ESCROW_TARGETS' => $escrow,
            'URLOK' => $urlOk,
            'URLKO' => $urlKo
        ];

        $query = http_build_query($params);
        $vhash = hash('sha512', md5($query . md5($merchantPassword)));
        $endpoint = 'https://api.paycomet.com/gateway/ifr-bankstore?' . $query . "&VHASH=" . $vhash;

        return $endpoint;
    }
}
