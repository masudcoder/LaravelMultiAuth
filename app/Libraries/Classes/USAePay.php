<?php
/**
 * @filename        USAePay.php
 * @description     This class is for doing transaction with Usaepay
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Ali Tareque Chowdhury
 *
 * @link
 * @created on      October 4, 2010
 * @Dependencies    PaymentGateway
 * @license
 ***/
  class USAePay extends PaymentGateway
  {
      /**
      *@desc Delimiter for response from gateway
      */
       var $responseDelimiter = '&';

     function USAePay()
      {
          parent::PaymentGateway();
          $this->apiVersion = '1.6.0';
          $this->requestURL = 'https://www.usaepay.com/gate';
          $this->testURL = 'https://sandbox.usaepay.com/gate';
          $this->supportedCurrencies = array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD');
      }

      /**
      *@desc Setting basic parameters
      */
      function initialize()
      {
          $this->logMessage('Initializing basic parameters...');

          $this->setParam('UMkey', $this->apiKey);
          $this->setParam('UMamount', $this->amount);

          if($this->testMode)
          {
              $this->setParam('UMtestmode', 'true');
          }
      }

      /**
      *@desc Setting billing information
      */
      function setBillingInformation()
      {
          $this->logMessage('Initializing billing parameters...');

          $this->setParam('UMname', $this->firstName . ' ' . $this->lastName);
          $this->setParam('UMbillcompany', $this->company);
          $this->setParam('UMbillstreet', $this->address1);
          $this->setParam('UMbillstreet2', $this->address2);
          $this->setParam('UMbillcity', $this->city);
          $this->setParam('UMbillstate', $this->state);
          $this->setParam('UMbillzip', $this->zip);
          $this->setParam('UMbillcountry', $this->country);
          $this->setParam('UMbillphone', $this->phone);
          $this->setParam('UMfax', $this->fax);
          $this->setParam('UMemail', $this->email);
      }

      /**
      *@desc Setting shipping information
      */
      function setShippingInformation()
      {
          $this->logMessage('Initializing shipping parameters...');

          $this->setParam('UMshipfname', $this->shippingFirstName);
          $this->setParam('UMshiplname', $this->shippingLastName);
          $this->setParam('UMshipcompany', $this->shippingCompany);
          $this->setParam('UMshipstreet', $this->shippingAddress1);
          $this->setParam('UMshipstreet2', $this->shippingAddress2);
          $this->setParam('UMshipcity', $this->shippingCity);
          $this->setParam('UMshipstate', $this->shippingState);
          $this->setParam('UMshipzip', $this->shippingZip);
          $this->setParam('UMshipcountry', $this->shippingCountry);
      }

      function setCardInformation()
      {
          $this->logMessage('Initializing card parameters...');
          $this->setParam('UMcard', trim($this->cardNumber));
          $this->setParam('UMexpir', $this->expiryMonth . $this->expiryYear);
          if ($this->cvv)
          {
              $this->setParam('UMcvv2', $this->cvv);
          }
      }

      /**
      *@desc Sends request for Authorization
      *
      * @return Response type object
      */
      function authorize()
      {
          $this->logMessage('Preparing sale request...');

          $this->validateBasicInput();
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
          $this->setCardInformation();
          $this->setParam('UMcommand', 'authonly');
          $this->setParam('UMinvoice', $this->invoiceNumber);
          $this->setParam('UMtax', $this->tax);
          $this->setBillingInformation();
          $this->setShippingInformation();

          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc Sends capture request
      */
      function capture()
      {
          $this->setParam('UMcommand', 'capture');
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
          $this->setParam('UMcommand', 'sale');
          $this->setParam('UMinvoice', $this->invoiceNumber);
          $this->setParam('UMtax', $this->tax);
          $this->setCardInformation();
          $this->setBillingInformation();
          $this->setShippingInformation();

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
          $this->validateTransactionID();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }
          $this->logMessage('Setting parameters for refund request...');
          $this->initialize();
          $this->setParam('UMcommand', 'refund');
          $this->setParam('UMrefNum', $this->transactionId);
          $this->setParam('UMinvoice', $this->invoiceNumber);
          $this->setCardInformation();
          $this->setBillingInformation();

          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc Prepares Response object from raw response
      */
      function prepareResponse()
      {
          $responseValues = explode($this->responseDelimiter, $this->response->rawResponse);

          if(!empty($responseValues))
          {
              $response = array();
              foreach($responseValues as $item)
              {
                  $value = explode('=', $item);
                  $response[$value[0]] = urldecode($value[1]);
              }

              if($response['UMstatus'] == 'Approved')
              {
                  $this->response->success = 1;
                  $this->response->ack = ACK_SUCCESS;
              }
              else
              {
                  $this->response->ack = ACK_FAILURE;
                  $this->response->setError($response['UMerror']);
              }

              $this->response->responseCode = $response['UMerrorcode'];
              $this->response->authorizationId = $response['UMauthCode'];
              $this->response->avsResponse = $response['UMavsResult'];
              $this->response->transactionId = $response['UMrefNum'];
              $this->response->invoiceNumber = $response['UMbatchRefNum'];
              $this->response->ccvResponse = $response['UMcvv2Result'];
              $this->response->amount = $this->amount;
          }
      }

      /**
      *@desc Basic input validation
      */
      function validateBasicInput()
      {
          if(empty($this->apiKey))
          {
              $this->response->setError('USAePay API Key has not been configured.');
          }

          $this->validateAmount();
      }
  }
?>