<?php
/**
 * @filename        Stripe.php
 * @description     This class is for making payment through Stripe
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Alauddin (alauddinkuet@gmail.com)
 *
 * @link
 * @created on      November 11, 2014
 * @Dependencies
 * @license
 ***/
  class Stripe extends PaymentGateway
  {
      var $requesType = 'SALES';
      var $doNotSendReciept = array(6395, 6642); // Merchant Row Ids who do not want to send email from stripe gateway
      function Stripe()
      {
          parent::PaymentGateway();
          $this->apiVersion = '1.17.4';
          $this->requestURL = 'https://api.stripe.com/v1/';          
          $this->testURL = 'https://api.stripe.com/v1/';
          $this->supportedCurrencies = array('USD','GBP', 'EUR', 'CAD', 'AUD');
      }

      function sale()
      {
        $this->logMessage('Preparing sale request...');         
        $this->requesType = 'SALES';
        $this->logMessage('Setting parameters for sale request...');
        if(isset($_POST['gateway_error']) && $_POST['gateway_error'])
        {
            $this->response->amount = $this->amount;
            $this->response->reasonCode = $_POST['gateway_error_code'];
            $this->response->setError($_POST['gateway_error']);
            $this->response->ack = ACK_FAILURE;
            $this->response->success = 0;
            return $this->response;
        }
        $this->setParam('amount', ($this->amount * 100));
        $params =  array(
                    'amount'        => $this->amount * 100,
                    'currency'      => $this->currencyCode,
                    'card'          => array(
                    'number'    => $this->cardNumber,
                    'exp_month' => $this->expiryMonth,
                    'exp_year'  => $this->expiryYear
                    ),
                    'description'   => $this->note
                    );
        if($this->cvv)
           $params['card']['cvc'] = $this->cvv;
      
        if(isset($this->customVariable2->value) && !in_array($this->customVariable2->value, $this->doNotSendReciept))
           $params['receipt_email'] = $this->email; 
           
        $this->customRequestString = $this->encode($params);
        $this->headers = array('X-Stripe-Client-User-Agent: ' . json_encode(array('bindings_version' => $this->apiVersion,
            'lang' => 'php',
            'lang_version' => phpversion(),
            'publisher' => 'stripe',
            'uname' => php_uname())),
            'User-Agent: Stripe/v1 PhpBindings/' . $this->apiVersion,
                     'Authorization: Bearer ' . $this->apiKey);

        $this->requestURL = $this->requestURL . 'charges';
        $this->testURL    = $this->testURL . 'charges';
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
          
          $this->headers = array('X-Stripe-Client-User-Agent: ' . json_encode(array('bindings_version' => $this->apiVersion,
            'lang' => 'php',
            'lang_version' => phpversion(),
            'publisher' => 'stripe',
            'uname' => php_uname())),
            'User-Agent: Stripe/v1 PhpBindings/' . $this->apiVersion,
                     'Authorization: Bearer ' . $this->apiKey);

          $this->requestURL = $this->requestURL . 'charges/' . $this->authorizationId . '/refunds';
          $this->testURL = $this->testURL . 'charges/' . $this->authorizationId . '/refunds';
          $this->setParam('amount', ($this->amount * 100));
          $this->makeAPICall();
          return $this->response;
      }

      function prepareResponse()
      {
         $this->logMessage('Getting RowResponse'); 
         $this->logMessage($this->response->rawResponse);
         $response = json_decode($this->response->rawResponse);
         $this->logMessage(print_r($response, 1));
         
         if(!isset($response->error))
         {   
            if($response->object == 'charge')
            {
                if($response->paid && !$response->failure_code)
                { 
                   $this->response->success = 1;
                   $this->response->ack = ACK_SUCCESS;
                }
                else
                {
                   $this->response->success = 0;
                   $this->response->ack = ACK_FAILURE;
                }
            }
            elseif($response->object == 'refund')
            {
                if(!$response->reason)
                { 
                   $this->response->success = 1;
                   $this->response->ack = ACK_SUCCESS;
                }
                else
                {
                   $this->response->success = 0;
                   $this->response->ack = ACK_FAILURE;
                }
            }
            $this->response->reasonCode = $response->failure_code;
            $this->response->reasonText = $response->failure_message;
            $this->response->setError($response->failure_message);
            $this->response->authorizationId = $response->id;
            $this->response->transactionId   = $response->balance_transaction;
            $this->response->transactionType = $response->object;
            $this->response->amount   = ($response->amount/100);
            $this->response->cardType = $response->card->brand;
         }
         else
         {
            $this->response->ack = ACK_FAILURE;
            $this->response->setError($response->error->message);
            $this->response->reasonCode = $response->error->type;
            $this->response->reasonText = $response->error->message;
         }
      }

 
      /**
      * @param array $arr An map of param keys to values.
      * @param string|null $prefix (It doesn't look like we ever use $prefix...)
      *   
      * @returns string A querystring, essentially.
      */
      public static function encode($arr, $prefix=null)
      {
         if (!is_array($arr))
           return $arr;
         $r = array();
         foreach ($arr as $k => $v) 
         {
           if (is_null($v))
             continue;

           if ($prefix && $k && !is_int($k))
             $k = $prefix."[".$k."]";
           else if ($prefix)
             $k = $prefix."[]";

           if (is_array($v)) 
           {
              $r[] = self::encode($v, $k, true);
           } 
           else 
           {
              $r[] = urlencode($k)."=".urlencode($v);
           }
        }
        return implode("&", $r);
     }
  }
?>