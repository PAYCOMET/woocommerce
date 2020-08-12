<?php

class PaycometApiRest
{
    public $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function addUser(
        $terminal,
        $jetToken,
        $order,
        $productDescription = '',
        $language = 'ES'
    ) {
        $params = [
            "terminal" => (int) $terminal,
            "jetToken" => (string) $jetToken,
            "order" => (string) $order,
            "productDescription" => (string) $productDescription,
            "language" => (string) $language
        ];

        return $this->executeRequest('https://rest.paycomet.com/v1/cards', $params);
    }

    public function executePurchase(
        $terminal,
        $order,
        $amount,
        $currency,
        $methodId,
        $originalIp,
        $secure,
        $idUser,
        $tokenUser,
        $urlOk = '',
        $ulrKo = '',
        $scoring = '0',
        $productDescription = '',
        $merchantDescription = '',
        $userInteraction = 1,
        $escrowTargets = [],
        $trxType = '',
        $SCAException = '',
        $merchantData = ''
    ) {
        $params = ["payment" => [
                'terminal' => (int) $terminal,
                'order' => (string) $order,
                'amount' => (string) $amount,
                'currency' => (string) $currency,
                'methodId' => (string) $methodId,
                'originalIp' => (string) $originalIp,
                'secure' => (string) $secure,
                'idUser' => (int) $idUser,
                'tokenUser' => (string) $tokenUser,
                'scoring' => (string) $scoring,
                'productDescription' => (string) $productDescription,
                'merchantDescription' => (string) $merchantDescription,
                'userInteraction' => (int) $userInteraction,
                'escrowTargets' => $escrowTargets,
                'trxType' => (string) $trxType,
                'SCAException' => (string) $SCAException,
                'urlOk' => (string) $urlOk,
                'ulrKo' => (string) $ulrKo,
                'merchantData' => $merchantData,
            ]
        ];

        return $this->executeRequest('https://rest.paycomet.com/v1/payments', $params);
    }

    private function executeRequest(string $endpoint, array $params)
    {
        $jsonParams = json_encode($params);

        $curl = curl_init();

        curl_setopt_array($curl, array(
                CURLOPT_URL => $endpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $jsonParams,
                CURLOPT_HTTPHEADER => array(
                    "PAYCOMET-API-TOKEN: $this->apiKey",
                    "Content-Type: application/json"
            ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);

        return json_decode($response);
    }
}