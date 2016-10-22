<?php
/**
 * @filename        FirstDataGlobalE4.php
 * @description     This class is for making payment through First Data Global E4
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Alauddin (alauddinkuet@gmail.com)
 *
 * @link
 * @created on      November 11, 2014
 * @Dependencies
 * @license
 ***/
  class FirstDataGlobalE4 extends PaymentGateway
  {
        /**
        * Transaction types
        */
        const TRAN_PURCHASE = '00';
        const TRAN_PREAUTH = '01';
        const TRAN_PREAUTHCOMPLETE = '02';
        const TRAN_FORCEDPOST = '03';
        const TRAN_REFUND = '04';
        const TRAN_PREAUTHONLY = '05';
        const TRAN_PAYPALORDER = '07';
        const TRAN_VOID = '13';
        const TRAN_TAGGEDPREAUTHCOMPLETE = '32';
        const TRAN_TAGGEDVOID = '33';
        const TRAN_TAGGEDREFUND = '34';
        const TRAN_CASHOUT = '83';
        const TRAN_ACTIVATION = '85';
        const TRAN_BALANCEINQUIRY = '86';
        const TRAN_RELOAD = '88';
        const TRAN_DEACTIVATION = '89';

      function FirstDataGlobalE4()
      {
          parent::PaymentGateway();
          $this->apiVersion = '1.17.4';
          $this->requestURL = 'https://api.globalgatewaye4.firstdata.com/transaction/v11';          
          $this->testURL = 'https://api.demo.globalgatewaye4.firstdata.com/transaction/v11';
          $this->supportedCurrencies = array('USD','GBP', 'EUR', 'CAD', 'AUD');
      }

      function sale()
      {
        $this->logMessage('Preparing sale request...');         
        $this->logMessage('Setting parameters for sale request...');

        $params =  array(
                    'gateway_id'    => $this->apiUserName,  
                    'password'      => $this->apiKey,  
                    'amount'        => $this->amount,
                    'currency'      => $this->currencyCode,  
                    'cardholder_name' => $this->nameOnCard,
                    'cc_number'    => $this->cardNumber,
                    'cc_expiry'    => $this->expiryMonth . $this->getTwoDigitExpiryYear(),
                    'reference_no' => $this->invoiceNumber,
                    'transaction_type' => self::TRAN_PURCHASE,  
                    );
        if($this->cvv)
           $params['cavv'] = $this->cvv;

        $this->customRequestString = json_encode($params);
        $this->headers = array('Content-Type: application/json; charset=UTF-8;','Accept: application/json');

        $this->makeAPICall();  
        return $this->response;
      }

      function refund()
      {
          $this->logMessage('Preparing refund request...');

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->logMessage('Setting parameters for refund request...');
          
          $params =  array(
                    'gateway_id'    => $this->apiUserName,  
                    'password'      => $this->apiKey,  
                    'cardholder_name' => $this->nameOnCard,
                    'cc_number'    => $this->cardNumber,
                    'cc_expiry'    => $this->expiryMonth . $this->getTwoDigitExpiryYear(),
                    'amount'        => $this->amount,
                    'transaction_tag'   => $this->transactionId,  
                    'authorization_num' => $this->authorizationId,  
                    'transaction_type' => self::TRAN_REFUND,  
                    );

          $this->customRequestString = json_encode($params);
          $this->headers = array('Content-Type: application/json; charset=UTF-8;','Accept: application/json');

          $this->makeAPICall();  
          return $this->response;
      }

      function prepareResponse()
      {
         $this->logMessage('Getting RowResponse'); 
         $this->logMessage($this->response->rawResponse);
         
         if(count(explode(':', $this->response->rawResponse)) > 5)  //Response is not JSON
         {
             $response = json_decode($this->response->rawResponse, 1);
             $this->logMessage(print_r($response, 1));
             if($response['transaction_approved'])
             {
                 if($response['transaction_type'] == self::TRAN_PURCHASE)
                 {
                    if($response['bank_resp_code'] == 100 && $response['bank_message'] == 'Approved')
                    { 
                       $this->response->success = 1;
                       $this->response->ack = ACK_SUCCESS;
                    }
                    else
                    {
                       $this->response->success = 0;
                       $this->response->ack = ACK_FAILURE;
                    }
                    $this->response->transactionType = self::TRAN_PURCHASE;

                 }
                 elseif($response['transaction_type'] == self::TRAN_REFUND)
                 {
                    if($response['bank_resp_code'] == 100 && $response['bank_message'] == 'Approved')
                    { 
                       $this->response->success = 1;
                       $this->response->ack = ACK_SUCCESS;
                    }
                    else
                    {
                       $this->response->success = 0;
                       $this->response->ack = ACK_FAILURE;
                    }
                    $this->response->transactionType = self::TRAN_REFUND;
                 }
             
                 $this->response->reasonCode = $response['bank_resp_code'];
                 $this->response->reasonText = $response['exact_message'];
                 $this->response->setError($response['exact_message']);
                 $this->response->authorizationId = $response['authorization_num'];
                 $this->response->transactionId   = $response['transaction_tag'];
                 $this->response->amount   = $response['amount'];
                 $this->response->cardType = $response['credit_card_type'];
             }
         }
         else
         {
            $this->response->ack = ACK_FAILURE;
            $this->response->setError($this->response->rawResponse. 'May be Gateway setup error.');
            $this->response->reasonCode = 1001;
            $this->response->reasonText = $this->response->rawResponse . '. May be Gateway setup error.';
         }
      }
  }
?>