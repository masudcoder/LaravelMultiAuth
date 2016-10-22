<?php
/**
 * @filename        TransFirst.php
 * @description     This class is for doing transaction with Transaction Central (TransFirst).
 *                  This class supports HTTPS Post Method - Merchant Hosted with Standard or Email Response.
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Mohammad Sajjad Hossain - info@sajjadhossain.com
 *
 * @link            http://sajjadhossain.com
 * @created on      December 18, 2009
 * @Dependencies    PaymentGateway
 * @license
 ***/
  class TransFirst extends PaymentGateway
  {
      function TransFirst()
      {
          parent::PaymentGateway();
          $this->apiVersion = '3.3';
          $this->supportedCurrencies = array('USD');
      }

      /**
      *@desc Setting basic parameters
      */
      function initialize()
      {
          $this->logMessage('Initializing basic parameters...');
          $this->setParam('MerchantID', $this->apiUserName);
          $this->setParam('RegKey', $this->apiKey);
      }

      /**
      *@desc Setting billing information
      */
      function setBillingInformation()
      {
          $this->logMessage('Initializing billing parameters...');

          $this->setParam('AVSADDR', $this->address1 . ' ' . $this->address2);
          $this->setParam('AVSZIP', $this->zip);
      }

      /**
      *@desc Set card information
      */
      function setCardInformation()
      {
          $this->logMessage('Initializing card parameters...');
          $this->setParam('AccountNo', trim($this->cardNumber));
          $this->setParam('CCMonth', str_pad($this->expiryMonth, 2, '0', STR_PAD_LEFT));
          $this->setParam('CCYear', $this->getTwoDigitExpiryYear());
          $this->setParam('NameonAccount', $this->nameOnCard);
          $this->setParam('CVV2', $this->cvv);
      }

      /**
      *@desc Sends request for payment
      *
      * @return Response type object
      */
      function sale()
      {
          $this->logMessage('Preparing sale request...');

          $this->requestURL = 'https://webservices.primerchants.com/billing/TransactionCentral/processCC.asp?';
          $this->testURL = $this->requestURL;


          $this->validateBasicInput();
          $this->validateCreditCardNumber();
          $this->validateExpiryDate();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...');
              return $this->response;
          }

          $this->logMessage('Setting parameters for sale request...');

          $this->initialize();
          $this->setParam('RefID', $this->invoiceNumber);
          $this->setParam('Amount', $this->amount);

          $this->setCardInformation();
          $this->setBillingInformation();

          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc Sends refund request
      *
      * @return Response type object
      */
      function refund()
      {
          $this->logMessage('Preparing for refund request...');

          $this->requestURL = 'https://webservices.primerchants.com/billing/TransactionCentral/voidcreditcconline.asp';
          $this->testURL = $this->requestURL;

          $this->validateBasicInput();
          $this->validateTransactionID();
          //$this->validateCreditCardNumber();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...');
              return $this->response;
          }
          $this->logMessage('Setting parameters for refund request...');

          $this->initialize();
          $this->setParam('TransID', $this->transactionId);
          $this->setParam('CreditAmount', $this->amount);

          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc Prepares Response object from raw response
      */
      function prepareResponse()
      {
          $responseArray = $this->processNVPResponse();

          if($responseArray)
          {
              foreach($responseArray as $key => $value)
              {
                  if(strstr($key, 'TRANSID'))
                  {
                      $this->response->transactionId = $value;
                      break;
                  }
              }

              if(!isset($responseArray['AUTH']) || is_null($responseArray['AUTH']) || trim($responseArray['AUTH']) == '' || $responseArray['AUTH'] == 'Declined')
              {
                  $this->response->ack = ACK_FAILURE;

                  $code = '';
                  $message = '';
                  if(strstr($responseArray['NOTES'], '/'))
                  {
                      list($code, $message) = explode('/', $responseArray['NOTES']);
                  }
                  else
                  {
                      $message = $responseArray['NOTES'];
                  }
                  $this->response->setError($message);
                  $this->response->responseCode = $code;
              }
              else
              {
                  $this->response->success = 1;
                  $this->response->ack = ACK_SUCCESS;
              }

              $this->response->authorizationId = $responseArray['AUTH'];
              $this->response->avsResponse = isset($responseArray['AVSCODE']) ? $responseArray['AVSCODE'] : '';
              $this->response->invoiceNumber = isset($responseArray['REFNO']) ? $responseArray['REFNO'] : '';
              $this->response->description = $responseArray['NOTES'];
              $this->response->amount = $this->amount;
              $this->response->ccvResponse = isset($responseArray['CVV2RESPONSEMSG']) ? $responseArray['CVV2RESPONSEMSG'] : '';
          }
      }

      /**
      *@desc Basic input validation
      */
      function validateBasicInput()
      {
          if(empty($this->apiUserName) || empty($this->apiKey))
          {
              $this->response->setError('TransFirst login credentials have not been configured.');
          }

          $this->validateAmount();
      }
  }
?>
