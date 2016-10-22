<?php
/**
 * @filename        BeanStream.php
 * @description     This class is for doing transaction with BeanStream.com
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Md. Alauddin Husain- alauddinkuet@gmail.com
 *
 * @created on      June 15, 2010
 * @Dependencies    PaymentGateway
 * @license
 ***/
  class BeanStream extends PaymentGateway
  {
      /**
      *@desc Delimiter for response from gareway
      */
      var $responseDelimiter = '&';

      function BeanStream()
      {
          parent::PaymentGateway();
          $this->requestURL = 'https://www.beanstream.com/scripts/process_transaction.asp';
          $this->testURL = 'https://www.beanstream.com/scripts/process_transaction.asp';
          $this->supportedCurrencies = array('USD', 'CAD');
      }

      /**
      *@desc Setting basic parameters
      */
      function initialize()
      {
          $this->logMessage('Initializing basic parameters...');
          $this->setParam('requestType', 'BACKEND');
          $this->setParam('merchant_id', $this->apiKey);
          $this->setParam('username', $this->apiUserName);
          $this->setParam('password', $this->apiSignature);
          $this->setParam('ordEmailAddress', $this->email);
          $this->setParam('ordPhoneNumber', $this->phone);
          $this->setParam('trnOrderNumber', $this->invoiceNumber);
          $this->setParam('trnAmount', $this->amount);
      }

      /**
      *@desc Setting billing information
      */
      function setBillingInformation()
      {
          $this->logMessage('Initializing billing parameters...');

          $this->setParam('ordName', $this->firstName . ' ' . $this->lastName);
          $this->setParam('ordAddress1', $this->address1);
          $this->setParam('ordAddress2', $this->address2);
          $this->setParam('ordCity', $this->city);

          if(in_array($this->countryCode, array('US', 'CA'))){
              $this->setParam('ordProvince', $this->state);
          }
          else{
              $this->setParam('ordProvince', '--');
          }
          $this->setParam('ordPostalCode', $this->zip);
          $this->setParam('ordCountry', $this->countryCode);
          $this->setParam('ordPhoneNumber', $this->phone);
          $this->setParam('ordEmailAddress', $this->email);
      }

      function setCardInformation()
      {
          $this->logMessage('Initializing card parameters...');

          $this->setParam('trnCardOwner', trim($this->nameOnCard));
          $this->setParam('trnCardNumber', trim($this->cardNumber));
          $this->setParam('trnExpMonth', $this->expiryMonth);
          $this->setParam('trnExpYear', $this->getTwoDigitExpiryYear());
      }

      function setShippingInformation()
      {
          $this->setParam('shipName', $this->shippingFirstName . ' ' . $this->shippingLastName);
          $this->setParam('shipAddress1', $this->shippingAddress1);
          $this->setParam('shipAddress2', $this->shippingAddress2);
          $this->setParam('shipCity', $this->shippingCity);
          if(in_array($this->countryCode, array('US', 'CA'))){
              $this->setParam('shipProvince', $this->shippingState);
          }
          else{
              $this->setParam('shipProvince', '--');
          }
          $this->setParam('shipPostalCode', $this->shippingZip);
          $this->setParam('shipCountry', $this->shippingCountryCode);
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
          $this->validateCreditCardNumber();
          $this->validateExpiryDate();
          if($this->response->hasError())
          {
              $this->logMessage('Error found...');
              return $this->response;
          }

          $this->logMessage('Setting parameters for sale request...');

          $this->initialize();
          $this->setParam('trnType', 'P');
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
          $this->validateCreditCardNumber();
          $this->validateExpiryDate();
          if($this->response->hasError())
          {
              $this->logMessage('Error found...');
              return $this->response;
          }

          $this->logMessage('Setting parameters for refund request...');

          $this->initialize();
          $this->setParam('trnType', 'R');
          $this->setParam('adjId', $this->transactionId);
          $this->setCardInformation();
          $this->setBillingInformation();
          $this->setShippingInformation();
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

              if($response['trnApproved'] == 1)
              {
                  $this->response->success = 1;
                  $this->response->ack = ACK_SUCCESS;
              }
              else
              {
                  $this->response->ack = ACK_FAILURE;
                  $this->response->setError($response['messageText']);
              }

              $this->response->authorizationId = $response['authCode'];
              $this->response->reasonCode = $response['messageId'];
              $this->response->reasonText = $response['messageText'];

              $this->response->avsResponse     = $response['avsMessage'];
              $this->response->transactionId   = $response['trnId'];
              $this->response->invoiceNumber   = $response['trnOrderNumber'];
              $this->response->transactionType = $response['trnType'];
              $this->response->avsPostalMatch  = $response['avsPostalMatch'];

              $this->response->amount   = $response['trnAmount'];
              $this->response->method   = $response['paymentMethod'];
              $this->response->cardType = $response['cardType'];
              $this->response->avsId    = $response['avsId'];

              $this->response->errorType    = $response['errorType'];
              $this->response->errorFields  = $response['errorFields'];
              $this->response->avsProcessed = $response['avsProcessed'];
              $this->response->avsResult    = $response['avsResult'];
              $this->response->avsAddrMatch = $response['avsAddrMatch'];
              $this->response->trnDate      = $response['trnDate'];
          }
      }

      /**
      *@desc Basic input validation
      */
      function validateBasicInput()
      {
          if(empty($this->apiUserName))
          {
              $this->response->setError('BeanStream login credentials have not been configured.');
          }

          $this->validateAmount();
      }
  }
?>