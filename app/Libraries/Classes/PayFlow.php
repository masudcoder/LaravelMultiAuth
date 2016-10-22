<?php
/**
 * @filename        PayFlow.php
 * @description     This class is for doing transaction with PayPal's PayFlow API
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Mohammad Sajjad Hossain - info@sajjadhossain.com
 *
 * @link            http://sajjadhossain.com
 * @created on      August 10, 2009
 * @Dependencies    PaymentGateway
 * @license
 ***/
  class PayFlow extends PaymentGateway
  {
      /**
      *@desc If one or more additional users on the account, this value is the ID
      * of the user authorized to process transactions. If, however, there are no
      * additional user on the account, this variable will have the same value as vendor
      *
      * @var string
      */
      var $user = '';

      var $host = 'payflowpro.paypal.com';
      var $testHost = 'pilot-payflowpro.paypal.com';

      function PayFlow()
      {
          parent::PaymentGateway();

          $this->apiVersion = '';
          $this->requestURL = 'https://payflowpro.paypal.com';
          $this->testURL = 'https://pilot-payflowpro.paypal.com';
          $this->supportedCurrencies = array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD');
      }

      /**
      *@desc Setting basic required information
      */
      function initialize()
      {
          $this->setParam('USER', $this->apiUserName);
          $this->setParam('VENDOR', $this->apiUserName);
          $this->setParam('PARTNER', $this->apiKey);
          $this->setParam('PWD', $this->apiSignature);
          $this->setParam('AMT', $this->amount);
          $this->setParam('CURRENCY', $this->currencyCode);
          $this->setParam('COMMENT1', $this->note);
          $this->setParam('VERBOSITY', 'MEDIUM');
          //For PWC partnership code
          $this->setParam('BUTTONSOURCE', 'PremiumWebCartInc_Cart_EC');
      }

      /**
      *@desc Setting card information
      */
      function setCardInformation()
      {
          $this->setParam('ACCT', $this->cardNumber);

          $this->setParam('EXPDATE', str_pad($this->expiryMonth, 2, '0', STR_PAD_LEFT) . $this->getTwoDigitExpiryYear());
          $this->setParam('CVV2', $this->cvv);
          $this->setParam('NAME', $this->nameOnCard);
      }

      /**
      *@desc Setting billing information
      */
      function setBillingInformation()
      {
          $this->setParam('FIRSTNAME', $this->firstName);
          $this->setParam('LASTNAME', $this->lastName);
          $this->setParam('STREET', trim($this->address1 . ' ' . $this->address2));
          $this->setParam('ZIP', $this->zip);
          $this->setParam('CITY', $this->city);
          $this->setParam('STATE', $this->state);
          $this->setParam('BILLTOCOUNTRY', $this->countryCode);
          $this->setParam('EMAIL', $this->email);
      }

      /**
      *@desc Setting shipping information
      */
      function setShippingInformation()
      {
          $this->setParam('SHIPTOFIRSTNAME', $this->shippingFirstName);
          $this->setParam('SHIPTOLASTNAME', $this->shippingLastName);
          $this->setParam('SHIPTOSTREET', trim($this->shippingAddress1 . ' ' . $this->shippingAddress2));
          $this->setParam('SHIPTOZIP', $this->shippingZip);
          $this->setParam('SHIPTOCITY', $this->shippingCity);
          $this->setParam('SHIPTOSTATE', $this->shippingState);
          $this->setParam('SHIPTOCOUNTRY', $this->shippingCountryCode);
      }

      /**
      *@desc This function is for sending authorization request.
      */
      function authorize()
      {
          $this->printError('authorize() method has not been implemented');
      }

      /**
      *@desc This function is for sending capture request.
      */
      function capture()
      {
          $this->printError('capture() method has not been implemented');
      }

      /**
      *@desc This function is for sending direct payment request.
      */
      function sale()
      {
          $this->logMessage('Preparing sale request...');
          $this->validateBasicInput();
          $this->validateCurrency();
          $this->validateCreditCardNumber();
          $this->validateExpiryDate();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...');
              return $this->response;
          }

          $this->logMessage('Setting parameters for sale request...');
          $this->initialize();
          $this->setCardInformation();
          $this->setBillingInformation();
          $this->setShippingInformation();
          $this->setParam('TRXTYPE', 'S');  // type of transaction
          $this->setParam('TENDER', 'C');   // mode of payment
          $this->setParam('INVNUM', $this->invoiceNumber);

          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc This function is for sending refund request.
      */
      function refund()
      {
          $this->logMessage('Preparing refund request...');
          $this->validateBasicInput();
          $this->validateTransactionID();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...');
              return $this->response;
          }

          $this->logMessage('Setting parameters for refund request...');
          $this->initialize();
          $this->setBillingInformation();
          $this->setParam('INVNUM', $this->invoiceNumber);
          $this->setParam('TRXTYPE', 'C');  // type of transaction
          $this->setParam('TENDER', 'C');   // mode of payment
          $this->setParam('ORIGID', $this->transactionId);

          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc Prepares request string form the parameters
      *
      * @return null
      */
      function prepareRequest()
      {
          if(count($this->params))
          {
              $nameValuePairs = array();

              foreach($this->params as $key => $value)
              {
                  if($value)
                  {
                      $valueLength = strlen($value);
                      $hasAmp = (bool) strstr($value, '&');
                      $value = urlencode(str_replace('"', '', $value));

                      $nameValuePairs[] = $key . ($hasAmp ?  '[' . $valueLength . ']=' . $value : '=' . $value);
                  }
              }
              $this->requestString = implode('&', $nameValuePairs);
          }

      }

      /**
      *@desc This function is for preparing basic response. Gateway classes will have to implement them to work.
      * Gateway classes may use different functions to prepare response but must implement this function.
      */
      function prepareResponse()
      {
          if($this->response->rawResponse)
          {
              $parts = explode('&', $this->response->rawResponse);

              if(count($parts))
              {
                  $responseArray = array();

                  foreach($parts as $part)
                  {
                      list($key, $value) = explode('=', $part);

                      $responseArray[$key] = $value;
                  }

                  $this->response->transactionId = isset($responseArray['PNREF']) ? $responseArray['PNREF'] : '';
                  $this->response->authorizationId = isset($responseArray['AUTHCODE']) ? $responseArray['AUTHCODE'] : '';
                  $this->response->responseCode = isset($responseArray['RESULT']) ? $responseArray['RESULT'] : '';
                  $this->response->ccvResponse = isset($responseArray['CVV2MATCH']) ? $responseArray['CVV2MATCH'] : '';
                  $this->response->reasonText = isset($responseArray['RESPMSG']) ? $responseArray['RESPMSG'] : '';
                  $this->response->avsResponse = isset($responseArray['AVSADDR']) ? $responseArray['AVSADDR'] : '';
                  $this->response->amount = $this->amount;

                  if($this->response->responseCode)
                  {
                      $this->response->success = 0;
                      $this->response->ack = ACK_FAILURE;

                      $responseText = $this->response->reasonText;
                      if(strstr($responseText, ':'))
                      {
                          $temp = explode(':', $responseText);
                          $responseText = $temp[0];
                      }
                      $this->response->setError($this->response->reasonText);
                  }
                  else
                  {
                      $this->response->success = 1;
                      $this->response->ack = ACK_SUCCESS;
                  }
              }
          }
      }

      /**
      *@desc Basic input validation
      */
      function validateBasicInput()
      {
          if(empty($this->apiUserName) || empty($this->apiKey) || empty($this->apiSignature))
          {
              $this->response->setError('PayFlow login credentials have not been configured.');
          }

          $this->validateAmount();
      }
  }
?>