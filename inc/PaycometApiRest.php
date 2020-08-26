<?php
/**
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author     PAYCOMET <info@paycomet.com>
*  @copyright  2019 PAYTPV ON LINE ENTIDAD DE PAGO S.L
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class PaycometApiRest
{
    private $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function form(
        $operationType,
        $language = 'ES',
        $terminal = '',
        $productDescription = '',
        $payment = [],
        $subscription = []
    ) {
        $params = [
            "operationType" => (int) $operationType,
            "language" => (string) $language,
            "terminal" => (int) $terminal,
            "productDescription" => (string) $productDescription,
            "payment" => $payment,
            "subscription" => $subscription
        ];

        return $this->executeRequest('https://rest.paycomet.com/v1/form', $params);
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
    
    public function infoUser(
        $idUser,
        $tokenUser,
        $terminal
    ) {
        $params = [
            'idUser' => (int) $idUser,
            'tokenUser' => (string) $tokenUser,
            'terminal' => (string) $terminal,
        ];
            
        return $this->executeRequest('https://rest.paycomet.com/v1/cards/info', $params);
    }

    public function removeUser(
        $terminal,
        $idUser,
        $tokenUser
    ) {
        $params = [
            'terminal' => (string) $terminal,
            'idUser' => (int) $idUser,
            'tokenUser' => (string) $tokenUser,
        ];
            
        return $this->executeRequest('https://rest.paycomet.com/v1/cards/delete', $params);
    }

    public function executePurchase(
        $terminal,
        $order,
        $amount,
        $currency,
        $methodId,
        $originalIp,
        $secure,
        $idUser = '',
        $tokenUser= '',
        $urlOk = '',
        $ulrKo = '',
        $scoring = '0',
        $productDescription = '',
        $merchantDescription = '',
        $userInteraction = 1,
        $escrowTargets = [],
        $trxType = '',
        $SCAException = '',
        $merchantData = []
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
                'merchantData' => $merchantData
            ]
        ];

        return $this->executeRequest('https://rest.paycomet.com/v1/payments', $params);
    }

    public function createSubscription(
        $startDate,
        $endDate,
        $periodicity,
        $terminal,
        $methodId,
        $order,
        $amount,
        $currency,
        $originalIp,
        $idUser,
        $tokenUser,
        $secure,
        $urlOk = '',
        $urlKo = '',
        $scoring = '',
        $productDescription = '',
        $merchantDescriptor = '',
        $userInteraction = '',
        $escrowTargets = [],
        $trxType = '',
        $SCAException = '',
        $merchantData = []
    ) {
        $params = ["subscription" => [
                    "startDate" => (string) $startDate,
                    "endDate" => (string) $endDate,
                    "periodicity" => (string) $periodicity,
                ],
                ["payment" => [
                    "terminal" => (int) $terminal,
                    "methodId" => (string) $methodId,
                    "order" => (string) $order,
                    "amount" => (string) $amount,
                    "currency" => (string) $currency,
                    "originalIp" => (string) $originalIp,
                    "idUser" => (int) $idUser,
                    "tokenUser" => (string) $tokenUser,
                    "secure" => (string) $secure,
                    "scoring" => (string) $scoring,
                    "productDescription" => (string) $productDescription,
                    "merchantDescriptor" => (string) $merchantDescriptor,
                    "userInteraction" => (string) $userInteraction,
                    "escrowTargets" => $escrowTargets,
                    "trxType" => (string) $trxType,
                    "SCAException" => (string) $SCAException,
                    "urlOk" => (string) $urlOk,
                    "urlKo" => (string) $urlKo,
                    "merchantData" => $merchantData,
                ]
            ]
        ];

        return $this->executeRequest('https://rest.paycomet.com/v1/subscription', $params);
    }

    public function removeSubscription(
        $terminal,
        $order,
        $idUser,
        $tokenUser
    ) {
        $params = ["payment" => [
                'terminal' => (int) $terminal,
                'order' => (string) $order,
                'idUser' => (int) $idUser,
                'tokenUser' => (string) $tokenUser
            ]
        ];

        return $this->executeRequest('https://rest.paycomet.com/v1/subscription/' . $order . '/remove', $params);
    }

    public function executeRefund(
        $order,
        $terminal,
        $amount,
        $currency,
        $authCode,
        $originalIp,
        $tokenUser = '',
        $idUser = ''
    ) {
        $params = [
            "payment" => [
                'terminal' => (int) $terminal,
                'amount' => (string) $amount,
                'currency' => (string) $currency,
                'authCode' => (string) $authCode,
                'originalIp' => (string) $originalIp,
                'tokenUser' => (string) $tokenUser = '',
                'idUser' => (int) $idUser = ''
            ]
        ];

        return $this->executeRequest('https://rest.paycomet.com/v1/payments/' . $order . '/refund', $params);
    }

    private function executeRequest($endpoint, $params)
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