<?php
/**
 * @filename        NetworkMerchants.php
 * @description     This class is for doing transaction with Network Merchants (NMI)
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Mohammad Sajjad Hossain - info@sajjadhossain.com
 *
 * @link            http://sajjadhossain.com
 * @created on      March 30, 2011
 * @Dependencies    PaymentGateway
 * @license
 ***/
  class NetworkMerchants extends PaymentGateway
  {
      function NetworkMerchants()
      {
          parent::PaymentGateway();

          $this->apiVersion = '';
          $this->requestURL = 'https://secure.networkmerchants.com/api/transact.php';
          $this->testURL = $this->requestURL;
          //$this->testURL = 'http://localhost/post_grabber/grab.php';
          $this->supportedCurrencies = array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD');
      }

      /**
      *@desc Setting basic parameters
      */
      function initialize()
      {
          $this->setParam('username', $this->apiUserName);
          $this->setParam('password', $this->apiKey);
          $this->setParam('amount', $this->amount);
          $this->setIPAddress();
          $this->setParam('ipaddress', $this->ipAddress);
      }

      /**
      *@desc Setting billing information
      */
      function setBillingInformation()
      {
          $this->logMessage('Initializing billing parameters...');

          $this->setParam('firstname', $this->firstName);
          $this->setParam('lastname', $this->lastName);
          $this->setParam('company', $this->company);
          $this->setParam('address1', $this->address1);
          $this->setParam('address2', $this->address2);
          $this->setParam('city', $this->city);
          $this->setParam('state', $this->state);
          $this->setParam('zip', $this->zip);
          $this->setParam('country', $this->countryCode);
          $this->setParam('phone', $this->phone);
          $this->setParam('email', $this->email);
      }

      /**
      *@desc Setting shipping information
      */
      function setShippingInformation()
      {
          $this->logMessage('Initializing shipping parameters...');

          $this->setParam('shipping_firstname', $this->shippingFirstName);
          $this->setParam('shipping_lastname', $this->shippingLastName);
          $this->setParam('shipping_company', $this->shippingCompany);
          $this->setParam('shipping_address1', $this->shippingAddress1);
          $this->setParam('shipping_address2', $this->shippingAddress2);
          $this->setParam('shipping_city', $this->shippingCity);
          $this->setParam('shipping_state', $this->shippingState);
          $this->setParam('shipping_zip', $this->shippingZip);
          $this->setParam('shipping_country', $this->shippingCountryCode);
      }

      /**
      *@desc Setting card information
      */
      function setCardInformation()
      {
          $this->logMessage('Initializing card parameters...');
          $this->setParam('ccnumber', trim($this->cardNumber));
          $this->setParam('ccexp', $this->expiryMonth . $this->getTwoDigitExpiryYear());
          if($this->cvv) 
            $this->setParam('cvv', $this->cvv);
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
          $this->setCardInformation();
          $this->setBillingInformation();
          $this->setShippingInformation();
          $this->setParam('type', 'auth');
          $this->setParam('orderid', $this->invoiceNumber);

          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc Sends request for Capture
      */
      function capture()
      {
          $this->logMessage('Preparing capture request...');
          $this->validateBasicInput();
          $this->validateAmount();
          $this->validateCurrency();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->initialize();
          $this->setParam('type', 'capture');
          $this->setParam('transactionid', $this->transactionId);

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

          $this->logMessage('Setting parameters for authorize request...');

          $this->initialize();
          $this->setCardInformation();
          $this->setBillingInformation();
          $this->setShippingInformation();
          $this->setParam('type', 'sale');
          $this->setParam('orderid', $this->invoiceNumber);

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
          $this->validateCurrency();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->initialize();
          $this->setParam('type', 'refund');
          $this->setParam('transactionid', $this->transactionId);

          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc Prepares Response object from raw response
      */
      function prepareResponse()
      {
          $responseArray = $this->processNVPResponse();

          if(!empty($responseArray))
          {
              if($responseArray['RESPONSE'] == 1)
              {
                  $this->response->success = 1;
                  $this->response->ack = ACK_SUCCESS;
              }
              else
              {
                  $this->response->success = 0;
                  $this->response->ack = ACK_FAILURE;

                  if($responseArray['RESPONSE'] == 2)
                  {
                      $message = 'Transaction has been declined';
                  }
                  else
                  {
                      $message = $responseArray['RESPONSETEXT'];
                  }

                  $this->response->setError($message);
              }

              $this->response->transactionId = $responseArray['TRANSACTIONID'];
              $this->response->authorizationId = $responseArray['AUTHCODE'];
              $this->response->responseCode = $responseArray['RESPONSE_CODE'];
              $this->response->avsResponse = $responseArray['AVSRESPONSE'];
              $this->response->ccvResponse = $responseArray['CVVRESPONSE'];
              $this->response->invoiceNumber = $this->invoiceNumber;
              $this->response->amount = $this->amount;
              $this->response->transactionType = $responseArray['TYPE'];
              $this->response->currency = $this->currencyCode;
          }
      }

      /**
      *@desc Basic input validation
      */
      function validateBasicInput()
      {
          if(empty($this->apiUserName) || empty($this->apiKey))
          {
              $this->response->setError('Network Merchants credentials have not been configured.');
          }
      }
  }
?>