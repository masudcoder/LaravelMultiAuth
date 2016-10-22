<?php
/**
 * @filename        VirtualCardServices.php
 * @description     This class is for doing transaction with Virtual Card Services Host to Host XML API
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Mohammad Sajjad Hossain - info@sajjadhossain.com
 *
 * @link            http://sajjadhossain.com
 * @created on      January 03, 2011
 * @Dependencies    PaymentGateway
 * @license
 ***/
  class VirtualCardServices extends PaymentGateway
  {
      var $requestType = 'sale';

      function VirtualCardServices()
      {
          parent::PaymentGateway();
          $this->apiVersion = '';
          $this->supportedCurrencies = array('USD', 'GBP', 'EUR', 'ZAR');
      }

      /**
      *@desc Only authorize
      */
      function authorize()
      {
          $this->testURL = 'https://www.vcs.co.za/vvonline/ccxmlauth.asp';
          $this->requestURL = 'https://www.vcs.co.za/vvonline/ccxmlauth.asp';
          $this->logMessage('Preparing authorize only request...');

          $this->validateBasicInput();
          $this->validateAmount();
          $this->validateCreditCardNumber();
          $this->validateExpiryDate();
          $this->validateNameOnCard();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->logMessage('Setting parameters for sale request...');
          $this->requestType = 'authorize';
          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc Only capture
      */
      function capture()
      {
          $this->testURL = 'https://www.vcs.co.za/vvonline/ccxmlsettle.asp';
          $this->requestURL = 'https://www.vcs.co.za/vvonline/ccxmlsettle.asp';
          $this->logMessage('Preparing capture only request...');

          $this->validateBasicInput();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->logMessage('Setting parameters for capture request...');
          $this->requestType = 'capture';
          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc For authorize and capture
      */
      function sale()
      {
          $this->testURL = 'https://www.vcs.co.za/vvonline/ccxmlauth.asp';
          $this->requestURL = 'https://www.vcs.co.za/vvonline/ccxmlauth.asp';
          $this->logMessage('Preparing sale request...');

          $this->validateBasicInput();
          $this->validateAmount();
          $this->validateCreditCardNumber();
          $this->validateExpiryDate();
          $this->validateNameOnCard();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->logMessage('Setting parameters for sale request...');
          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc For refunding
      */
      function refund()
      {
          $this->testURL = 'https://www.vcs.co.za/vvonline/ccxmlauth.asp';
          $this->requestURL = 'https://www.vcs.co.za/vvonline/ccxmlauth.asp';
          $this->logMessage('Preparing refund request...');

          $this->validateBasicInput();
          $this->validateAmount();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->logMessage('Setting parameters for refund request...');
          $this->requestType = 'refund';
          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc Prepares a Response object based on the raw response sent by Payment Gateway
      */
      function prepareResponse()
      {
          $rawResponse = $this->response->getRawResponse();

          $responseArray = array();

          $xmlData = $this->processXMLData();

          foreach($xmlData as $data)
          {
              if($data['level'] == 2 && isset($data['value']))
              {
                  $responseArray[$data['tag']] = $data['value'];
              }
          }

          $isApproved = strpos($responseArray['RESPONSE'], 'APPROVED') == 6;

          if($isApproved)
          {
              $this->response->success = 1;
              $this->response->ack = ACK_SUCCESS;
              $this->response->transactionId = substr($responseArray['RESPONSE'], 0, 6);
          }
          else
          {
              $this->response->success = 0;
              $this->response->ack = ACK_FAILURE;
              $this->response->setError($responseArray['RESPONSE']);
          }

          $this->response->invoiceNumber = $this->invoiceNumber;
          $this->response->amount = $this->amount;
          $this->response->transactionType = $this->requestType;
          $this->response->currency = $this->currencyCode;
      }

      /**
      *@desc This function prepares an XML request
      *
      * @param bool
      * @param bool
      */
      function prepareRequest()
      {
          $this->requestString = 'xmlmessage=' . urlencode('<?xml version="1.0" ?>');

          $this->invoiceNumber = str_replace('-', '', $this->invoiceNumber);

          if($this->requestType == 'sale' || $this->requestType == 'authorize')
          {
              $this->setStartTag('AuthorisationRequest');
              $this->setNode('UserId', $this->apiUserName);
              $this->setNode('Reference', $this->invoiceNumber, true);
              $this->setNode('Description', 'Payment processing for ' . $this->invoiceNumber, true);
              $this->setNode('Amount', $this->amount);
              $this->setNode('CardholderName', $this->nameOnCard, true);
              $this->setNode('CardNumber', $this->cardNumber);
              $this->setNode('ExpiryMonth', $this->expiryMonth);
              $this->setNode('ExpiryYear', $this->expiryYear);
              $this->setNode('Currency', $this->currencyCode);
              $this->setNode('CellNumber', $this->phone);
              $this->setNode('CardPresent', 'N');
              $this->setNode('DelaySettlement', $this->requestType == 'sale' ? 'N' : 'Y');
              $this->setEndTag('AuthorisationRequest');
          }
          else if($this->requestType == 'capture')
          {
              $this->setStartTag('SettlementRequest');
              $this->setNode('UserId', $this->apiUserName);
              $this->setNode('Reference', $this->invoiceNumber, true);
              $this->setNode('SettlementDate', date('Y/m/d', time()));
              $this->setEndTag('SettlementRequest');
          }
          else if($this->requestType == 'refund')
          {
              $this->setStartTag('RefundRequest');
              $this->setNode('UserId', $this->apiUserName);
              $this->setNode('Reference', $this->invoiceNumber, true);
              $this->setNode('Description', 'Refund', true);
              $this->setNode('Amount', $this->amount, true);
              $this->setEndTag('RefundRequest');
          }
      }

      /**
      *@desc Basic input validation
      */
      function validateBasicInput()
      {
          if(empty($this->apiUserName))
          {
              $this->response->setError('Virtual Card Services Terminal ID has not been configured.');
          }
      }
  }
?>