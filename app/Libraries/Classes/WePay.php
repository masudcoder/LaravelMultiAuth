<?php
/**
 * @filename        WePay.php
 * @description     This class is for making payment through WePay Custom Checkout
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Alauddin (alauddinkuet@gmail.com)
 *
 * @link
 * @created on      Feb 08, 2015
 * @Dependencies
 * @license
 ***/
class WePay extends PaymentGateway{
    //Production
	//public static $client_id = 71380;
    //public static $client_secret = 'b2a63d59d9';
    
    //Staging
    public static $client_id = 174978; 
	public static $client_secret = '27ea73e98a';
    var $token = '';
    var $requestUrl = 'https://wepayapi.com/v2/';
    var $testUrl = 'https://stage.wepayapi.com/v2/';
    var $requestType = 'sale';
    
    const SALES  = 1;
    const REFUND = 2;
    const REGISTER = 3;// Register Merchant Account
    const CREATE = 4;  // Create Merchant Account
    const CONFIRM = 5; // Send confirmation after merchant account create 
    
    function WePay()
    {     
       parent::PaymentGateway();
       $this->apiVersion = '0.2.1';
       $this->userAgent = 'WePay v2 PHP SDK v' . $this->apiVersion . ' Client id:' . self::$client_id;
       $this->requestURL = '';          
       $this->testURL = '';
       $this->supportedCurrencies = array('USD','GBP', 'EUR', 'CAD', 'AUD');
       $this->verifiy_ssl = false;
       $this->appendHeaderLength = false;
       $this->headers = array("Content-Type: application/json");  
    }

    function registerAccount()
    {
        $this->logMessage('Preparing Register Account request...');         
        $params =  array(
            'client_id'        => self::$client_id,
            'client_secret'    => self::$client_secret,
            'email'            => $this->email,
            'scope'            => 'manage_accounts,collect_payments,view_user,manage_subscriptions,preapprove_payments,send_money',
            'first_name'       => $this->firstName,
            'last_name'        => $this->lastName,
            'original_ip'      => $this->ipAddress,
            'tos_acceptance_time' => 1209600,
            'original_device'  => $_SERVER['HTTP_USER_AGENT']
        );
        
        $this->requestURL = $this->requestUrl . 'user/register';
        $this->testURL = $this->testUrl . 'user/register';

        $this->customRequestString = json_encode($params);

        $this->requestType = self::REGISTER; 
        $this->makeAPICall(); 
        return $this->response;
    }
    
    function createAccount()
    {
        $this->logMessage('Preparing Create Account request...'); 
        $this->headers[] = "Authorization: Bearer $this->token";        
        $params =  array(
            'name'             => $this->firstName . ' ' . $this->lastName,
            'description'       => $this->note
        );

        $this->requestURL = $this->requestUrl . 'account/create';
        $this->testURL = $this->testUrl . 'account/create';
        
        $this->customRequestString = json_encode($params);

        $this->requestType = self::CREATE; 
        $this->makeAPICall();  
        return $this->response;
    }
    
    function sendMerchantAccountConfirmation()
    {
        $this->logMessage('Preparing Send Confirmation request...'); 

        $this->requestURL = $this->requestUrl . 'user/resend_confirmation';
        $this->testURL = $this->testUrl . 'user/resend_confirmation';
        
        unset($this->customRequestString);
        unset($this->requestString);

        $this->requestType = self::CONFIRM; 
        $this->makeAPICall();  
        return $this->response;
    }
    
    function sale()
    {
        $this->logMessage('Preparing Sale request...');         
        $this->headers[] = "Authorization: Bearer " . $this->apiKey;        
        $params =  array(
            'account_id'          => $this->apiUserName,
            'amount'              => $this->amount,
            'currency'            => $this->currencyCode,
            'short_description'   => $this->note,
            'type'                => 'GOODS',
            'payment_method_id'   => $this->customVariable1->value,
            'payment_method_type' => 'credit_card',
            'fee_payer'           => 'payee'
        );
        
        $this->requestURL = $this->requestUrl . 'checkout/create';
        $this->testURL = $this->testUrl . 'checkout/create';

        $this->customRequestString = json_encode($params);

        $this->requestType = self::SALES; 
        $this->makeAPICall(); 
        return $this->response;
    }

    function refund()
    {
        $this->logMessage('Preparing Sale request...');         
        $this->headers[] = "Authorization: Bearer " . $this->apiKey;        
        $params =  array(
            'checkout_id'          => $this->transactionId,
            'amount'              => $this->amount,
            'refund_reason'       => $this->note ? $this->note : 'Customer wants refund.'
        );
        
        $this->requestURL = $this->requestUrl . 'checkout/refund';
        $this->testURL = $this->testUrl . 'checkout/refund';

        $this->customRequestString = json_encode($params);

        $this->requestType = self::REFUND; 
        $this->makeAPICall(); 
        return $this->response;
    }

    function prepareResponse()
    {
         $this->logMessage('Getting RowResponse'); 
         $this->logMessage($this->response->rawResponse);
         
         $response = json_decode($this->response->rawResponse);
         $this->logMessage(print_r($response, 1));
         if($response->error_code || $response->error || $response->error_description)
         {
             $this->response->success = 0;
             $this->response->ack = ACK_FAILURE;
             $this->response->reasonCode = $response->error_code. ':' . $response->error;
             $this->response->reasonText = $response->error_description;
             $this->response->setError($response->error_description);
         }
         else
         {   
             $this->response->success = 1;
             $this->response->ack = ACK_SUCCESS;
             if($this->requestType == self::REGISTER)
             {
                 $this->response->user_id      = $response->user_id;
                 $this->response->access_token = $response->access_token;
                 $this->response->token_type   = $response->token_type;
                 $this->response->expires_in   = $response->expires_in;
             }
             elseif($this->requestType == self::CREATE)
             {
                 $this->response->account_id = $response->account_id;
                 $this->response->owner_user_id = $response->owner_user_id;
                 $this->response->type = $response->type;
                 $this->response->create_time = $response->create_time;
                 $this->response->incoming_payments_status = $response->statuses->incoming_payments_status;
                 $this->response->outgoing_payments_status = $response->statuses->outgoing_payments_status;
             }
             elseif($this->requestType == self::SALES)
             {
                 $this->response->transactionType = self::SALES;
                 $this->response->authorizationId = $response->checkout_uri;
                 $this->response->transactionId   = $response->checkout_id;
                 $this->response->amount   = $response->amount;
                 $this->response->description   = 'Gross:' . $response->gross . ',fee:' . $response->gross . ', app_fee:' . $response->app_fee. ', fee_payer:' . $response->fee_payer;
             }
             elseif($this->requestType == self::REFUND)
             {
                 $this->response->transactionType = self::REFUND;
             }
         }
    }
}
