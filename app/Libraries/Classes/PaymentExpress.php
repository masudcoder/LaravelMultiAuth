<?php
/**
 * @filename        PaymentExpress.php
 * @description     This class is for doing transaction with Payment Express gateway http://www.paymentexpress.com/.
 *                  Developer Guide: http://www.paymentexpress.com/technical_resources.html
 *                  API Guide: http://www.paymentexpress.com/technical_resources/ecommerce_nonhosted/pxpost.html
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Mohammad Sajjad Hossain - info@sajjadhossain.com
 *
 * @link            http://sajjadhossain.com
 * @created on      June 17, 2011
 * @Dependencies    PaymentGateway
 * @license
 ***/
  class PaymentExpress extends PaymentGateway
  {
      var $requestType = 'sale';
      var $responseValues;
      var $try = 0;

      function PaymentExpress(){
          parent::PaymentGateway();
          $this->apiVersion = '';
          $this->requestURL = 'https://sec.paymentexpress.com/pxpost.aspx';
          $this->testURL = $this->requestURL;

          $this->supportedCurrencies = array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD', 'ZAR');
          $this->headers = array('Content-Type: application/xml; charset=UTF-8',
                                'Accept: application/xml');
      }

      /**
      *@desc Sends request for Authorization
      *
      * @return Response type object
      */
      function authorize(){
          $this->requestType = 'authorize';
          return $this->doRequest('Auth');
      }

      /**
      *@desc Sends request for Capture (Prior Authorization)
      */
      function capture(){
          $this->requestType = 'capture';
          $this->logMessage('Preparing capture request...');

          $this->validateBasicInput();
          $this->validateAmount();

          if(empty($this->securityCode)){
              $this->response->setError('DPS transaction reference missing');
          }

          if($this->response->hasError()){
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $xml = new DOMDocument("1.0", "UTF-8");

          $transaction = $xml->createElement('Txn');

          $exactID = $xml->createElement('PostUsername', $this->apiUserName);
          $transaction->appendChild($exactID);

          $password = $xml->createElement('PostPassword', $this->convertForXML($this->apiKey));
          $transaction->appendChild($password);

          $amount = $xml->createElement('Amount', round($this->amount, 2));
          $transaction->appendChild($amount);

          $currency = $xml->createElement('InputCurrency', $this->currencyCode);
          $transaction->appendChild($currency);

          $transactionType = $xml->createElement('TxnType', 'Complete');
          $transaction->appendChild($transactionType);

          $dpsTransactionReference = $xml->createElement('DpsTxnRef', $this->securityCode);
          $transaction->appendChild($dpsTransactionReference);

          $xml->appendChild($transaction);

          $this->requestString = $xml->saveXML();

          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc Sends request for payment
      *
      * @return Response type object
      */
      function sale(){
          $this->requestType = 'sale';
          return $this->doRequest('Purchase');
      }

      /**
      *@desc Sends refund request
      *
      * @return Response type object
      */
      function refund(){
          $this->requestType = 'refund';
          $this->logMessage('Preparing refund request...');

          $this->validateBasicInput();
          $this->validateAmount();
          if(empty($this->securityCode)){
              $this->response->setError('DPS transaction reference missing');
          }

          if($this->response->hasError()){
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $xml = new DOMDocument("1.0", "UTF-8");

          $transaction = $xml->createElement('Txn');

          $exactID = $xml->createElement('PostUsername', $this->apiUserName);
          $transaction->appendChild($exactID);

          $password = $xml->createElement('PostPassword', $this->convertForXML($this->apiKey));
          $transaction->appendChild($password);

          $amount = $xml->createElement('Amount', round($this->amount, 2));
          $transaction->appendChild($amount);

          $currency = $xml->createElement('InputCurrency', $this->currencyCode);
          $transaction->appendChild($currency);

          $transactionType = $xml->createElement('TxnType', 'Refund');
          $transaction->appendChild($transactionType);

          $dpsTransactionReference = $xml->createElement('DpsTxnRef', $this->securityCode);
          $transaction->appendChild($dpsTransactionReference);

          $xml->appendChild($transaction);

          $this->requestString = $xml->saveXML();

          $this->makeAPICall();
          return $this->response;
      }

      function sendRequestForStatus($reason){
          $this->logMessage('Sending request for status (' . $reason . ')...');

          $xml = new DOMDocument("1.0", "UTF-8");

          $transaction = $xml->createElement('Txn');

          $exactID = $xml->createElement('PostUsername', $this->apiUserName);
          $transaction->appendChild($exactID);

          $password = $xml->createElement('PostPassword', $this->convertForXML($this->apiKey));
          $transaction->appendChild($password);

          $transactionType = $xml->createElement('TxnType', 'Status');
          $transaction->appendChild($transactionType);

          $transactionID = $xml->createElement('TxnId', $this->invoiceNumber);
          $transaction->appendChild($transactionID);

          $xml->appendChild($transaction);

          $this->requestString = $xml->saveXML();

          $this->makeAPICall();
      }

      function doRequest($transactionType){
          $this->logMessage('Preparing ' . $this->requestType . ' request...');

          $this->validateBasicInput();
          $this->validateAmount();
          $this->validateCreditCardNumber();
          $this->validateExpiryDate();

          if($this->response->hasError()){
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->logMessage('Setting parameters for ' . $this->requestType . ' request...');

          $xml = new DOMDocument("1.0", "UTF-8");

          $transaction = $xml->createElement('Txn');

          $exactID = $xml->createElement('PostUsername', $this->apiUserName);
          $transaction->appendChild($exactID);

          $password = $xml->createElement('PostPassword', $this->convertForXML($this->apiKey));
          $transaction->appendChild($password);

          $cardHolder = $xml->createElement('CardHolderName', $this->convertForXML($this->nameOnCard));
          $transaction->appendChild($cardHolder);

          $cardNumber = $xml->createElement('CardNumber', $this->cardNumber);
          $transaction->appendChild($cardNumber);

          $amount = $xml->createElement('Amount', round($this->amount, 2));
          $transaction->appendChild($amount);

          $expiryDate = $xml->createElement('DateExpiry', $this->expiryMonth . $this->getTwoDigitExpiryYear());
          $transaction->appendChild($expiryDate);

          $currency = $xml->createElement('InputCurrency', $this->currencyCode);
          $transaction->appendChild($currency);

          $transactionType = $xml->createElement('TxnType', $transactionType);
          $transaction->appendChild($transactionType);

          $transactionID = $xml->createElement('TxnId', $this->invoiceNumber);
          $transaction->appendChild($transactionID);

          $merchantReference = $xml->createElement('MerchantReference', $this->invoiceNumber);
          $transaction->appendChild($merchantReference);

          $avsAction = $xml->createElement('AvsAction', 1);
          $transaction->appendChild($avsAction);

          $avsPostCode = $xml->createElement('AvsPostCode', $this->zip);
          $transaction->appendChild($avsPostCode);

          $avsStreetAddress = $xml->createElement('AvsStreetAddress', trim($this->address1 . ' ' . $this->address2));
          $transaction->appendChild($avsStreetAddress);

          $xml->appendChild($transaction);

          $this->requestString = $xml->saveXML();

          $this->makeAPICall();
          return $this->response;
      }

      function prepareResponseArray($nodes){
          foreach($nodes as $node){
              if($node->hasChildNodes()){
                  foreach($node->childNodes as $childNode){
                      if ($childNode->nodeType != XML_TEXT_NODE){
                          if($childNode->nodeName == 'Transaction'){
                              $temp = array();
                              foreach($childNode->childNodes as $grandChildNode){
                                  if ($grandChildNode->nodeType != XML_TEXT_NODE){
                                      $temp[$grandChildNode->nodeName] = trim($grandChildNode->nodeValue);
                                  }
                              }
                              $this->responseValues[$childNode->nodeName] = $temp;
                          }
                          else{
                              $this->responseValues[$childNode->nodeName] = trim($childNode->nodeValue);
                          }
                      }
                  }
              }
          }
      }

      /**
      *@desc Prepares a Response object based on the raw response sent by Payment Gateway
      */
      function prepareResponse(){
          $this->responseValues = array();

          $xml = new DOMDocument();
          if($xml->loadXML($this->response->getRawResponse())){
              $this->prepareResponseArray($xml->childNodes);

              if($this->responseValues['Transaction']['StatusRequired'] == 1){
                  $this->sendRequestForStatus('StatusRequired = 1');
              }
              else if($this->responseValues['Success'] == '1' && $this->responseValues['ResponseText'] == 'APPROVED'){
                  $this->response->success = 1;
                  $this->response->ack = ACK_SUCCESS;
                  $this->response->responseCode = $this->responseValues['ReCo'];
                  $this->response->amount = $this->responseValues['Transaction']['Amount'];
                  $this->response->authorizationId = $this->responseValues['Transaction']['AuthCode'];
                  $this->response->transactionId = $this->responseValues['Transaction']['TransactionId'];
                  $this->response->securityKey = $this->responseValues['DpsTxnRef'];
              }
              else{
                  $this->response->ack = ACK_FAILURE;
                  $this->response->responseCode = $this->responseValues['ReCo'];
                  $this->response->setError($this->responseValues['HelpText']);
                  $this->response->amount = $this->amount;
              }
          }
          else{
              if($try == 0){
                  $this->sendRequestForStatus('no response');
              }
              else{
                  $this->response->ack = ACK_FAILURE;
                  $this->response->setError($this->response->getRawResponse());
                  $this->response->amount = $this->amount;
              }
          }

          $this->response->invoiceNumber = $this->invoiceNumber;
          $this->response->transactionType = $this->requestType;
          $this->response->currency = $this->currencyCode;
      }

      /**
      *@desc Basic input validation
      */
      function validateBasicInput(){
          if(empty($this->apiUserName) || empty($this->apiKey)){
              $this->response->setError('E-xact Transactions credentials have not been configured.');
          }
      }
  }
?>