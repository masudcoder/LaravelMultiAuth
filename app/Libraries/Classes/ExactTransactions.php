<?php
/**
 * @filename        ExactTransactions.php
 * @description     This class is for doing transaction with E-xact Transaction gateway http://www.e-xact.com/.
 *                  Developer Guide: https://hostedcheckout.zendesk.com/entries/231260-developer-guide
 *                  API Guide: https://hostedcheckout.zendesk.com/entries/231362-transaction-processing-api-reference-guide
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Mohammad Sajjad Hossain - info@sajjadhossain.com
 *
 * @link            http://sajjadhossain.com
 * @created on      May 19, 2011
 * @Dependencies    PaymentGateway
 * @license
 ***/
  class ExactTransactions extends PaymentGateway
  {
      var $requestType = 'sale';

      function ExactTransactions(){
          parent::PaymentGateway();
          $this->apiVersion = '8';
          $this->requestURL = 'https://api.e-xact.com/transaction';
          $this->testURL = 'https://api-demo.e-xact.com/transaction';
          //$this->testURL = 'https://localhost/paymentLibrary/e-xact_response.txt';
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
          return $this->doRequest('01', 'authorize');
      }

      /**
      *@desc Sends request for Capture (Prior Authorization)
      */
      function capture(){
          $this->requestType = 'capture';
          return $this->doRequest('02', 'capture');
      }

      /**
      *@desc Sends request for payment
      *
      * @return Response type object
      */
      function sale(){
          $this->requestType = 'sale';
          return $this->doRequest('00', 'sale');
      }

      /**
      *@desc Sends refund request
      *
      * @return Response type object
      */
      function refund(){
          $this->requestType = 'refund';
          return $this->doRequest('04', 'refund');
      }

      function doRequest($transactionType, $logEntity){
          $this->logMessage('Preparing ' . $logEntity . ' request...');

          $this->validateBasicInput();
          $this->validateAmount();

          $saleOrAuthorize = $transactionType == '00' || $transactionType == '01';

          if($saleOrAuthorize){
              $this->validateCreditCardNumber();
              $this->validateExpiryDate();
          }
          else{
              $this->validateAuthorizationID();
          }

          if($this->response->hasError()){
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->logMessage('Setting parameters for ' . $logEntity . ' request...');

          $xml = new DOMDocument("1.0", "UTF-8");

          $transaction = $xml->createElement('Transaction');

          $exactID = $xml->createElement('ExactID', $this->apiUserName);
          $transaction->appendChild($exactID);

          $password = $xml->createElement('Password', $this->convertForXML($this->apiKey));
          $transaction->appendChild($password);

          $transactionType = $xml->createElement('Transaction_Type', $transactionType);
          $transaction->appendChild($transactionType);

          $amount = $xml->createElement('DollarAmount', number_format($this->amount, 2, '.', ''));
          $transaction->appendChild($amount);

          $cardNumber = $xml->createElement('Card_Number', $this->cardNumber);
          $transaction->appendChild($cardNumber);

          $expiryDate = $xml->createElement('Expiry_Date', $this->expiryMonth . $this->getTwoDigitExpiryYear());
          $transaction->appendChild($expiryDate);

          $cardHolder = $xml->createElement('CardHoldersName', $this->convertForXML($this->nameOnCard));
          $transaction->appendChild($cardHolder);

          if(in_array($transactionType, array('00', '01'))){
              $referenceNo = $xml->createElement('Reference_No', $this->invoiceNumber);
              $transaction->appendChild($referenceNo);

              $zipCode = $xml->createElement('ZipCode', $this->zip);
              $transaction->appendChild($zipCode);

              $email = $xml->createElement('Client_Email', $this->email);
              $transaction->appendChild($email);
          }

          $authorizationID = $xml->createElement('Authorization_Num', $this->authorizationId);
          $transaction->appendChild($authorizationID);

          $xml->appendChild($transaction);

          $this->requestString = $xml->saveXML();

          $this->makeAPICall();
          return $this->response;
      }

      function prepareResponse(){
          $xml = new DOMDocument();
          if($xml->loadXML($this->response->getRawResponse())){
              $responseValues = array();

              foreach($xml->childNodes as $node){
                  if($node->hasChildNodes()){
                      foreach($node->childNodes as $childNode){
                          if ($childNode->nodeType != XML_TEXT_NODE){
                              $responseValues[$childNode->nodeName] = trim($childNode->nodeValue);
                          }
                      }
                  }
              }

              if($responseValues['Transaction_Approved'] == 'true' && $responseValues['Transaction_Error'] == 'false'){
                  $this->response->success = 1;
                  $this->response->ack = ACK_SUCCESS;
                  $this->response->responseCode = 0;
                  $this->response->amount = $responseValues['DollarAmount'];
                  $this->response->authorizationId = $responseValues['Authorization_Num'];
                  $this->response->transactionId = $responseValues['Authorization_Num'];
              }
              else{
                  $this->response->ack = ACK_FAILURE;
                  $this->response->responseCode = $responseValues['Error_Number'];

                  if(isset($responseValues['EXact_Resp_Code'])){
                      $this->response->reasonCode = $responseValues['EXact_Resp_Code'];
                  }

                  $error = !empty($responseValues['Error_Description']) ? $responseValues['Error_Description'] : $responseValues['EXact_Message'];
                  $this->response->setError($error);
                  $this->response->amount = $this->amount;
              }

              $this->response->avsResponse = $responseValues['AVS'];
          }
          else{
              $this->response->ack = ACK_FAILURE;
              $this->response->setError($this->response->getRawResponse());
              $this->response->amount = $this->amount;
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