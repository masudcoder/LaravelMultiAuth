<?php
/**
 * @filename        VirtualMerchant.php
 * @description     This class is for doing transaction with Vertual Merchant https://www.myvirtualmerchant.com/VirtualMerchant
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Mohammad Sajjad Hossain - info@sajjadhossain.com
 *
 * @link            http://sajjadhossain.com
 * @created on      January 20, 2011
 * @Dependencies    PaymentGateway
 * @license
 ***/
  class VirtualMerchant extends PaymentGateway
  {
      var $requestType = '';

      function VirtualMerchant()
      {
          parent::PaymentGateway();
          $this->apiVersion = '';
          $this->requestURL = 'https://www.myvirtualmerchant.com/VirtualMerchant/process.do';
          $this->testURL = $this->requestURL;
          $this->supportedCurrencies = array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD', 'ZAR');
      }

      /**
      *@desc Setting basic parameters
      */
      function initialize()
      {
          $this->logMessage('Initializing basic parameters...');

          $this->setParam('ssl_merchant_id', $this->apiUserName);
          $this->setParam('ssl_pin', $this->apiKey);
          $this->setParam('ssl_user_id', empty($this->apiSignature) ? $this->apiUserName : $this->apiSignature);

          $this->setParam('ssl_amount', $this->amount);
          $this->setParam('ssl_txn_currency_code', $this->currencyCode);

          $this->setParam('ssl_test_mode', $this->testMode ? 'true' : 'false');
          $this->setParam('ssl_show_form', 'false');
          $this->setParam('ssl_result_format', 'ASCII');
      }

      /**
      *@desc Setting billing information
      */
      function setBillingInformation()
      {
          $this->logMessage('Initializing billing parameters...');

          $this->setParam('ssl_first_name', $this->firstName);
          $this->setParam('ssl_last_name', $this->lastName);
          $this->setParam('ssl_company', $this->company);
          $this->setParam('ssl_avs_address', $this->address1);
          $this->setParam('ssl_address2', $this->address2);
          $this->setParam('ssl_city', $this->city);
          $this->setParam('ssl_state', $this->state);
          $this->setParam('ssl_avs_zip', $this->zip);
          $this->setParam('ssl_country', $this->country);
          $this->setParam('ssl_phone', $this->phone);
          $this->setParam('ssl_email', $this->email);
      }

      /**
      *@desc Setting shipping information
      */
      function setShippingInformation()
      {
          $this->logMessage('Initializing shipping parameters...');

          $this->setParam('ssl_ship_to_first_name', $this->shippingFirstName);
          $this->setParam('ssl_ship_to_last_name', $this->shippingLastName);
          $this->setParam('ssl_ship_to_company', $this->shippingCompany);
          $this->setParam('ssl_ship_to_address1', $this->shippingAddress1);
          $this->setParam('ssl_ship_to_address2', $this->shippingAddress2);
          $this->setParam('ssl_ship_to_city', $this->shippingCity);
          $this->setParam('ssl_ship_to_state', $this->shippingState);
          $this->setParam('ssl_ship_to_zip', $this->shippingZip);
          $this->setParam('ssl_ship_to_country', $this->shippingCountry);
      }

      /**
      *@desc Setting card information
      */
      function setCardInformation()
      {
          $this->logMessage('Initializing card parameters...');
          $this->setParam('ssl_card_number', trim($this->cardNumber));
          $this->setParam('ssl_exp_date', str_pad($this->expiryMonth, 2, '0', STR_PAD_LEFT) . $this->getTwoDigitExpiryYear());
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
          $this->setParam('ssl_transaction_type', 'CCAUTHONLY');
          $this->setParam('ssl_invoice_number', $this->invoiceNumber);
          $this->setBillingInformation();
          $this->setShippingInformation();

          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc Sends request for Capture (Prior Authorization)
      */
      function capture()
      {
          $this->logMessage('Preparing capture request...');
          $this->validateBasicInput();
          $this->validateAmount();
          $this->validateCreditCardNumber();
          $this->validateExpiryDate();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->logMessage('Setting parameters for capture request...');

          $this->initialize();
          $this->setCardInformation();
          $this->setParam('ssl_transaction_type', 'CCFORCE');
          $this->setParam('ssl_approval_code', $this->authorizationId);

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

          $this->logMessage('Setting parameters for sale request...');

          $this->initialize();
          $this->setParam('ssl_transaction_type', 'CCSALE');
          $this->setParam('ssl_invoice_number', $this->invoiceNumber);
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
          $this->validateAmount();
          $this->validateCreditCardNumber();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }
          $this->logMessage('Setting parameters for refund request...');

          $this->initialize();
          $this->setParam('ssl_transaction_type', 'CCCREDIT');
          $this->setParam('ssl_txn_id', $this->transactionId);
          $this->setParam('ssl_invoice_number', $this->invoiceNumber);
          $this->setCardInformation();

          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc Prepares Response object from raw response
      */
      function prepareResponse()
      {
          $initResponseValues = explode("\n", $this->response->rawResponse);
          $responseValues = array();

          foreach($initResponseValues as $respVal)
          {
              list($key, $val) = explode('=', $respVal);
              $responseValues[$key] = trim($val);
          }

          if(!empty($responseValues))
          {
              if(isset($responseValues['ssl_result']) && $responseValues['ssl_result'] == 0)
              {
                  $this->response->success = 1;
                  $this->response->ack = ACK_SUCCESS;
                  $this->response->responseCode = 0;
                  $this->response->transactionId = $responseValues['ssl_txn_id'];
                  $this->response->amount = $responseValues['ssl_amount'];
                  $this->response->authorizationId = $responseValues['ssl_approval_code'];
              }
              else
              {
                  $this->response->ack = ACK_FAILURE;
                  $this->response->responseCode = $responseValues['errorCode'];
                  $this->response->setError($responseValues['errorMessage']);
                  $this->response->amount = $this->amount;
              }

              $this->response->invoiceNumber = $this->invoiceNumber;
              $this->response->transactionType = $this->requestType;
              $this->response->currency = $this->currencyCode;
          }
          else
          {
              $this->response->setError('Invalid response');
          }
      }

      /**
      *@desc Basic input validation
      */
      function validateBasicInput()
      {
          if(empty($this->apiUserName) || empty($this->apiKey))
          {
              $this->response->setError('Virtual Merchant credentials have not been configured.');
          }
      }
  }
?>