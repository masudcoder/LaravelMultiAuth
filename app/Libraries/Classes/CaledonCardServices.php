<?php
/**
 * @filename        CaledonCardServices.php
 * @description     This class is for doing transaction with Caledoncard.com
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Mohammad Sajjad Hossain - info@sajjadhossain.com
 *
 * @link            http://sajjadhossain.com
 * @created on      January 04, 2011
 * @Dependencies    PaymentGateway
 * @license
 ***/
  class CaledonCardServices extends PaymentGateway
  {
      function CaledonCardServices()
      {
          parent::PaymentGateway();
          $this->apiVersion = '';
          $this->requestURL = 'https://lt3a.caledoncard.com/';
          $this->testURL = 'https://lt3a.caledoncard.com/';
          $this->supportedCurrencies = array('USD', 'CAD');
          $this->requestMethod = 'GET';
      }

      /**
      *@desc Setting basic parameters
      */
      function initialize()
      {
          $this->logMessage('Initializing basic parameters...');

          $this->setParam('TERMID', $this->apiUserName);
          $amt = (int) ($this->amount * 100); // Converting to cent
          $this->setParam('AMT', $amt);
      }

      function setCardInformation()
      {
          $this->logMessage('Initializing card parameters...');
          $this->setParam('CARD', trim($this->cardNumber));                                                           
          $this->setParam('EXP', str_pad($this->expiryMonth, 2, '0', TR_PAD_LEFT) . $this->getTwoDigitExpiryYear());
      }

      /**
      *@desc Sends request for Authorization
      *
      * @return Response type object
      */
      function authorize()
      {
          $this->logMessage('Preparing authorize request...');

          $this->validateBasicInput();
          $this->validateAmount();
          $this->validateCurrency();
          $this->validateCreditCardNumber();
          $this->validateExpiryDate();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->logMessage('Setting parameters for authorize request...');

          $this->initialize();
          $this->setParam('TYPE', 'P');
          $this->setParam('REF', $this->invoiceNumber);
          $this->setCardInformation();

          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc Sends request for Capture (Prior Authorization)
      */
      function capture()
      {
          $this->logMessage('Preparing capture request...');
          $this->validateBasicInput();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->logMessage('Setting parameters for capture request...');

          $this->initialize();
          $this->setParam('TYPE', 'C');
          $this->setParam('AUTH', $this->transactionId);
          $this->setParam('REF', $this->invoiceNumber);
          $this->setParam('CARD', '0');
          $this->setParam('EXP', '0000');

          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc Sends request for payment
      *
      * @return Response type object
      */
      function sale()
      {
          $this->logMessage('Preparing sale request...');

          $this->validateBasicInput();
          $this->validateAmount();
          $this->validateCurrency();
          $this->validateCreditCardNumber();
          $this->validateExpiryDate();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->logMessage('Setting parameters for sale request...');

          $this->initialize();
          $this->setParam('TYPE', 'S');
          $this->setParam('REF', $this->invoiceNumber);
          $this->setCardInformation();

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
          $this->validateBasicInput();
          $this->validateAmount();
          $this->validateTransactionID();
          $this->validateCreditCardNumber();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }
          $this->logMessage('Setting parameters for refund request...');

          $this->initialize();
          $this->setParam('TYPE', 'R');
          $this->setParam('REF', $this->invoiceNumber);
          $this->setParam('AUTH', $this->transactionId);
          $this->setCardInformation();

          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc Prepares Response object from raw response
      */
      function prepareResponse()
      {
          $parts = explode('&', html_entity_decode($this->response->rawResponse));

          if(!empty($parts))
          {
              $cnt = count($parts);

              $responseValues = array();

              for($i= 0; $i < $cnt; $i++)
              {
                  list($key, $value) = explode('=', $parts[$i]);
                  $responseValues[trim($key)] = trim($value);
              }

              if($responseValues['CODE'] == '0000')
              {
                  $this->response->success = 1;
                  $this->response->ack = ACK_SUCCESS;
              }
              else
              {
                  $this->response->ack = ACK_FAILURE;
                  $this->response->setError($responseValues['TEXT']);
              }

              if(!empty($responseValues['AUTH']))
              {
                  $this->response->transactionId = $responseValues['AUTH'];
              }
              $this->response->responseCode = $responseValues['CODE'];
              $this->response->amount = $this->amount;
              $this->response->invoiceNumber = $this->invoiceNumber;
              $this->response->currency = $this->currencyCode;
          }
      }

      /**
      *@desc Basic input validation
      */
      function validateBasicInput()
      {
          if(empty($this->apiUserName))
          {
              $this->response->setError('Caledon Card Services login credentials have not been configured.');
          }
      }
  }
?>
