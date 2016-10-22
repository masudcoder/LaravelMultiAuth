<?php
/**
 * @filename        MonerisESelectPlusCanada.php
 * @description     This class is for doing transaction with Moneris eSelect Plus Canada.
 *
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Mohammad Sajjad Hossain - info@sajjadhossain.com
 *
 * @link            http://sajjadhossain.com
 * @created on      October 10, 2011
 * @Dependencies    PaymentGateway
 * @license
 ***/

  class MonerisESelectPlusCanada extends PaymentGateway
  {
      var $requestType = 1; //sale

      function MonerisESelectPlusCanada()
      {
          parent::PaymentGateway();
          $this->apiVersion = 'PHP - 2.5.0';
          $this->requestURL = 'https://www3.moneris.com/gateway2/servlet/MpgRequest';
          $this->testURL = 'https://esqa.moneris.com/gateway2/servlet/MpgRequest';
      }

      /**
      *@desc For authorize and capture
      */
      function sale()
      {
          $this->logMessage('Preparing sale request...');

          $this->validateBasicInput();
          $this->validateCreditCardNumber();
          $this->validateExpiryDate();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->requestType = 1;
          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc For authorization
      */
      function authorize()
      {
          $this->logMessage('Preparing authorize request...');

          $this->validateBasicInput();
          $this->validateCreditCardNumber();
          $this->validateExpiryDate();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->requestType = 2;
          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc Only capture
      */
      function capture()
      {
          $this->validateBasicInput();
          $this->validateTransactionID();

          $this->logMessage('Preparing capture request...');

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->requestType = 4;
          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc For refunding
      */
      function refund()
      {
          $this->validateBasicInput();
          $this->validateTransactionID();
          $this->validateInvoiceNumber();

          $this->logMessage('Preparing refund request...');
          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->requestType = 3;
          $this->makeAPICall();
          return $this->response;
      }

      function prepareRequest()
      {
          $this->requestString = '<?xml version="1.0" encoding="utf-8"?>';

          $this->setStartTag('request');
          $this->setNode('store_id', $this->apiUserName);
          $this->setNode('api_token', $this->apiKey);

          if($this->requestType == 1 || $this->requestType == 2) //1 = sale, 2 = preAuth
          {
              if($this->requestType == 1)
              {
                  $tagEncloser = 'purchase';
                  $this->response->transactionType = 'sale';
              }
              else
              {
                  $tagEncloser = 'preauth';
                  $this->response->transactionType = 'preauth';
              }

              $this->setStartTag($tagEncloser);
              $this->setNode('order_id', $this->invoiceNumber);
              $this->setNode('amount', number_format($this->amount, 2, '.', ''));
              $this->setNode('pan', $this->cardNumber);
              $this->setNode('expdate', $this->getTwoDigitExpiryYear() . $this->expiryMonth);
              $this->setNode('crypt_type', 7);

              $this->setStartTag('cust_info');
              $this->setNode('email', $this->email);

              $this->setStartTag('billing');
              $this->setNode('first_name', $this->firstName, true);
              $this->setNode('last_name', $this->lastName, true);
              $this->setNode('company_name', $this->company, true);
              $this->setNode('address', trim($this->address1 . ' ' . $this->address2), true);
              $this->setNode('city', $this->city, true);
              $this->setNode('province', $this->state, true);
              $this->setNode('postal_code', $this->zip, true);
              $this->setNode('country', $this->country, true);
              $this->setNode('phone_number', $this->phone, true);
              $this->setEndTag('billing');

              $this->setEndTag('cust_info');

              $this->setEndTag($tagEncloser);
          }
          else if($this->requestType == 3) //3 = refund
          {
              $this->response->transactionType = 'refund';

              $this->setStartTag('refund');
              $this->setNode('order_id', $this->invoiceNumber);
              $this->setNode('amount', number_format($this->amount, 2, '.', ''));
              $this->setNode('txn_number', $this->transactionId);
              $this->setNode('crypt_type', 7);
              $this->setEndTag('refund');
          }
          else if($this->requestType == 4) // 4 = capture
          {
              $this->response->transactionType = 'capture';

              $this->setStartTag('completion');
              $this->setNode('order_id', $this->invoiceNumber);
              $this->setNode('comp_amount', number_format($this->amount, 2, '.', ''));
              $this->setNode('txn_number', $this->transactionId);
              $this->setNode('crypt_type', 7);
              $this->setEndTag('completion');
          }

          $this->setEndTag('request');
      }

      function prepareResponse()
      {
          $rawData = $this->processXMLData();

          if(!empty($rawData))
          {
              $responseArray = array();

              foreach($rawData as $data)
              {
                  if($data['level'] == 3 && $data['type'] == 'complete')
                  {
                      $responseArray[$data['tag']] = empty($data['value']) || $data['value'] == 'null' ? '' : $data['value'];
                  }
              }

              if(empty($responseArray))
              {
                  $this->response->success = 0;
                  $this->response->ack = ACK_FAILURE;
              }
              else
              {
                  if(!empty($responseArray['RESPONSECODE']) && $responseArray['RESPONSECODE'] < 50)
                  {
                      $this->response->success = 1;
                      $this->response->ack = ACK_SUCCESS;
                  }
                  else
                  {
                      $this->response->success = 0;
                      $this->response->ack = ACK_FAILURE;
                      $this->response->setError($responseArray['MESSAGE']);
                  }

                  $this->response->responseCode = $responseArray['RESPONSECODE'];
                  $this->response->correlationId = $responseArray['REFERENCENUM'];
                  $this->response->transactionId = $responseArray['TRANSID'];
                  $this->response->authorizationId = $responseArray['AUTHCODE'];
                  $this->response->invoiceNumber = $this->invoiceNumber;
                  $this->response->amount = $this->amount;
                  $this->response->currency = $this->currencyCode;
              }
          }
      }

      /**
      *@desc Basic input validation
      */
      function validateBasicInput()
      {
          if(empty($this->apiUserName) || empty($this->apiKey))
          {
              $this->response->setError('Moneris eSelect Plus credentials have not been configured.');
          }

          $this->validateAmount();
      }
  }
?>