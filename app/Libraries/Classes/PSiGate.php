<?php
/**
 * @filename        PSiGate.php
 * @description     This class is for doing transaction with PSiGate
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Mohammad Sajjad Hossain - info@sajjadhossain.com
 *
 * @link            http://sajjadhossain.com
 * @created on      February 10, 2011
 * @Dependencies    PaymentGateway
 * @license
 ***/
  class PSiGate extends PaymentGateway
  {
      var $cardAction = 0; // sale

      function PSiGate()
      {
          parent::PaymentGateway();
          $this->apiVersion = '';
          $this->requestURL = 'https://secure.psigate.com:7934/Messenger/XMLMessenger';
          $this->testURL = 'https://dev.psigate.com:7989/Messenger/XMLMessenger';
          $this->supportedCurrencies = array('USD', 'CAD');
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

          $this->cardAction = 0; //sale
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
          $this->cardAction = 1; //preauth/authorize
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

          $this->cardAction = 2; //postauth/capture
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

          $this->logMessage('Preparing refund request...');
          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->cardAction = 3; //credit/refund
          $this->makeAPICall();
          return $this->response;
      }

      function prepareRequest()
      {
          $this->requestString = '<?xml version="1.0" encoding="UTF-8"?>';
          $this->setStartTag('Order');
          $this->setNode('StoreID', $this->apiUserName, true);
          $this->setNode('Passphrase', $this->apiKey, true);
          $this->setNode('Subtotal', number_format($this->amount, 2, '.', ''));
          $this->setNode('PaymentType', 'CC');
          $this->setNode('CardAction', $this->cardAction);

          /*if($this->testMode)
          {
              //$this->setNode('TestResult', 'R'); //random
              //$this->setNode('TestResult', 'D'); //declined
              //$this->setNode('TestResult', 'A'); //approved
          }*/

          if($this->cardAction == 0 || $this->cardAction == 1)
          {
              $this->setNode('CardNumber', $this->cardNumber);
              $this->setNode('CardExpMonth', $this->expiryMonth);
              $this->setNode('CardExpYear', $this->getTwoDigitExpiryYear());

              $this->setNode('Bname', trim($this->firstName . ' ' . $this->lastName), true);
              $this->setNode('Bcompany', $this->company, true);
              $this->setNode('Baddress1', $this->address1, true);
              $this->setNode('Baddress2', $this->address2, true);
              $this->setNode('Bcity', $this->city, true);
              $this->setNode('Bprovince', $this->state, true);
              $this->setNode('Bpostalcode', $this->zip, true);
              $this->setNode('Bcountry', $this->country, true);
              $this->setNode('Phone', $this->phone, true);
              $this->setNode('Email', $this->email, true);

              $this->setNode('Comments', $this->invoiceNumber, true);
              $this->setNode('CustomerIP', $this->ipAddress, true);
          }
          else if($this->cardAction == 2 || $this->cardAction == 3)
          {
              $this->setNode('OrderID', $this->transactionId, true);
          }

          $this->setEndTag('Order');
      }

      function prepareResponse()
      {
          $rawResponse = $this->response->getRawResponse();

          if(empty($rawResponse))
          {
              $this->response->success = 0;
              $this->response->ack = ACK_FAILURE;
          }
          else
          {
              $responseArray = array();

              $xmlData = $this->processXMLData();

              foreach($xmlData as $data)
              {
                  if($data['level'] == 2 && isset($data['value']))
                  {
                      $responseArray[$data['tag']] = $data['value'];
                  }
              }

              $approved = trim($responseArray['APPROVED']);

              if($approved == 'APPROVED')
              {
                  $this->response->success = 1;
                  $this->response->ack = ACK_SUCCESS;
                  $this->response->transactionId = $responseArray['ORDERID'];
                  $this->response->transactionType = $responseArray['TRANSACTIONTYPE'];
              }
              else if($approved == 'ERROR' || $approved == 'DECLINED')
              {
                  $this->response->success = 0;
                  $this->response->ack = ACK_FAILURE;

                  if($approved == 'DECLINED' && empty($responseArray['ERRMSG']))
                  {
                      $this->response->setError('The transaction has been declined');
                  }
                  else
                  {
                      $this->response->setError($responseArray['ERRMSG']);
                  }
              }

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
          if(empty($this->apiUserName) || empty($this->apiKey))
          {
              $this->response->setError('PSiGate credentials have not been configured.');
          }

          $this->validateAmount();
      }

      /**
      *@desc Overriding the setNode function
      */
      function setNode($node, $value, $convert = false)
      {
          if(!empty($node))
          {
              $value = $convert ? $this->convertForXML($value) : $value;
              $this->requestString .= "<$node>$value</$node>";
          }
      }
  }
?>