<?php
/**
 * @filename        PayJunction.php
 * @description     This class is for doing transaction with PayJunction gateway http://www.payjunction.com/.
 *                  Knowledge base: http://payjunction.com/trinity/support/main.action?search.knowledgeBaseCategory.kbcParentId=37
 *                  API Guide: http://www.payjunction.com/trinity/support/view.action?knowledgeBase.knbKnowledgeBaseId=585
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Mohammad Sajjad Hossain - info@sajjadhossain.com
 *
 * @link            http://sajjadhossain.com
 * @created on      June 17, 2011
 * @Dependencies    PaymentGateway
 * @license
 ***/
  class PayJunction extends PaymentGateway
  {
      var $requestType = 'sale';

      function PayJunction(){
          parent::PaymentGateway();
          $this->apiVersion = '1.2';
          $this->requestURL = 'https://www.payjunction.com/quick_link';
          $this->testURL = 'https://www.payjunctionlabs.com/quick_link';

          $this->supportedCurrencies = array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD', 'ZAR');
          $this->headers = array('Content-Type: application/x-www-form-urlencoded');
      }

      function initialize(){
          $this->setParam('dc_logon', $this->apiUserName);
          $this->setParam('dc_password', $this->apiKey);
          $this->setParam('dc_transaction_amount', $this->amount);
          $this->setParam('dc_notes', $this->note);
          $this->setParam('dc_version', $this->apiVersion);
      }

      function setCardInformation(){
          $this->setParam('dc_name', $this->nameOnCard);
          $this->setParam('dc_number', $this->cardNumber);
          $this->setParam('dc_expiration_month', $this->expiryMonth);
          $this->setParam('dc_expiration_year', $this->expiryYear);
      }

      function setBillingInformation(){
          $this->setParam('dc_address', trim($this->address1 . ' ' . $this->address2));
          $this->setParam('dc_city', $this->city);
          $this->setParam('dc_state', $this->state);
          $this->setParam('dc_zipcode', $this->zip);
          $this->setParam('dc_country', $this->countryCode);
      }

      /**
      *@desc Sends request for Authorization
      *
      * @return Response type object
      */
      function authorize(){
          $this->requestType = 'authorize';
          $this->validateCreditCardNumber();
          $this->setParam('dc_invoice', $this->invoiceNumber);
          return $this->doRequest();
      }

      /**
      *@desc Sends request for Capture (Prior Authorization)
      */
      function capture(){
          $this->requestType = 'capture';
          $this->setParam('dc_posture', 'capture');
          $this->setParam('dc_transaction_id', $this->transactionId);
          $this->validateTransactionID();
          return $this->doRequest();
      }

      /**
      *@desc Sends request for payment
      *
      * @return Response type object
      */
      function sale(){
          $this->requestType = 'sale';
          $this->validateCreditCardNumber();
          $this->setParam('dc_invoice', $this->invoiceNumber);
          return $this->doRequest();
      }

      /**
      *@desc Sends refund request
      *
      * @return Response type object
      */
      function refund(){
          $this->requestType = 'refund';
          $this->validateCreditCardNumber();
          return $this->doRequest();
      }

      function doRequest(){
          $this->logMessage('Preparing ' . $this->requestType . ' request...');

          $this->validateBasicInput();
          $this->validateAmount();

          if($this->response->hasError()){
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->logMessage('Setting parameters for ' . $this->requestType . ' request...');

          $this->initialize();
          $this->setCardInformation();
          $this->setBillingInformation();

          switch($this->requestType){
              case 'authorize':
                  $this->setParam('dc_transaction_type', 'AUTHORIZATION');
                  break;
              case 'capture':
                  $this->setParam('dc_transaction_type', 'update');
                  break;
              case 'sale':
                  $this->setParam('dc_transaction_type', 'AUTHORIZATION_CAPTURE');
                  break;
              case 'refund':
                  $this->setParam('dc_transaction_type', 'CREDIT');
                  break;

          }
          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc Prepares a Response object based on the raw response sent by Payment Gateway
      */
      function prepareResponse(){
          $responseArray = $this->processNVPResponse(chr(28));

          if(!empty($responseArray['DC_RESPONSE_CODE']) && ($responseArray['DC_RESPONSE_CODE'] == '00' || $responseArray['DC_RESPONSE_CODE'] == '85')){
              $this->response->success = 1;
              $this->response->ack = ACK_SUCCESS;
              $this->response->transactionId = $responseArray['DC_TRANSACTION_ID'];
              $this->response->authorizationId = $responseArray['DC_APPROVAL_CODE'];
              $this->response->amount = $responseArray['DC_BASE_AMOUNT'];
          }
          else if(!empty($responseArray['DC_QUERY_TYPE']) && $responseArray['DC_QUERY_TYPE'] == 'update'){
              if($responseArray['DC_QUERY_STATUS'] == 'true'){
                  $this->response->success = 1;
                  $this->response->ack = ACK_SUCCESS;
                  $this->response->transactionId = $this->transactionId;
                  $this->response->authorizationId = $this->authorizationId;
              }
              else{
                  $this->response->ack = ACK_FAILURE;
                  $this->response->setError('The amount could not be captured');
              }

              $this->response->amount = $this->amount;
          }
          else{
              $this->response->ack = ACK_FAILURE;
              $this->response->setError($responseArray['DC_RESPONSE_MESSAGE']);
              $this->response->amount = $this->amount;
          }

          $this->response->responseCode = $responseArray['DC_RESPONSE_CODE'];
          $this->response->invoiceNumber = $this->invoiceNumber;
          $this->response->transactionType = $this->requestType;
          $this->response->currency = $this->currencyCode;
      }

      /**
      *@desc Basic input validation
      */
      function validateBasicInput(){
          if(empty($this->apiUserName) || empty($this->apiKey)){
              $this->response->setError('PayJunction credentials have not been configured.');
          }
      }
  }
?>