<?php
/**
 * @filename        MSDPay.php
 * @description     This class is for doing transaction with MSD-PAY
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Md. Alauddin Husain - alauddinkuet@gmail.com
 *                                                
 * @created on      April 19, 2013
 * @Dependencies    PaymentGateway
 * @license
 ***/
  class MSDPay extends PaymentGateway
  {
      /**
      *@desc Type of customer
      */
      var $customerOrganizationType = 'B';

      /**
      *@desc Flag for determining if mails will be sent to customer or not
      */
      var $emailToCustomer = false;

      var $headerText = '';

      var $footerText = '';
                                                                                                                                                            
      var $defaultDuplicateWindow = 0;
      /**
      *@desc Delimiter for response from gareway
      */
      var $responseDelimiter = '|';

      var $requestType = 'AUTH_CAPTURE';

      function MSDPay()
      {
          parent::PaymentGateway();
          $this->apiVersion = '3.1';
          $this->requestURL = 'https://secure.msdpay.com/gateway/transact.dll';
          $this->testURL = 'https://secure.msdpay.com/gateway/transact.dll';
          $this->supportedCurrencies = array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD', 'ZAR');
      }

      /**
      *@desc Setting basic parameters
      */
      function initialize()
      {
          $this->logMessage('Initializing basic parameters...');

          $this->setParam('x_delim_data', 'TRUE');
          $this->setParam('x_delim_char', $this->responseDelimiter);
          $this->setParam('x_relay_response', 'FALSE');
          $this->setParam('x_duplicate_window', $this->defaultDuplicateWindow);
          $this->setParam('x_url', 'FALSE');
          $this->setParam('x_version', $this->apiVersion);
          $this->setParam('x_method', 'CC');
          $this->setParam('x_login', $this->apiUserName);
          $this->setParam('x_tran_key', $this->apiKey);
          $this->setParam('x_email_customer', $this->emailToCustomer);
          $this->setIPAddress();
          $this->setParam('x_customer_ip', $this->ipAddress);
          $this->setParam('x_Customer_Organization_Type', $this->customerOrganizationType);
          $this->setParam('x_description', $this->note);
          $this->setParam('x_header_email_receipt', $this->headerText);
          $this->setParam('x_footer_email_receipt', $this->footerText);

          $this->setParam('x_amount', $this->amount);
          $this->setParam('x_currency_code', $this->currencyCode);
      }

      /**
      *@desc Setting billing information
      */
      function setBillingInformation()
      {
          $this->logMessage('Initializing billing parameters...');

          $this->setParam('x_first_name', $this->firstName);
          $this->setParam('x_last_name', $this->lastName);
          $this->setParam('x_company', $this->company);
          $this->setParam('x_address', trim($this->address1 . ' ' . $this->address2));
          $this->setParam('x_city', $this->city);
          $this->setParam('x_state', $this->state);
          $this->setParam('x_zip', $this->zip);
          $this->setParam('x_country', $this->country);
          $this->setParam('x_phone', $this->phone);
          $this->setParam('x_fax', $this->fax);
          $this->setParam('x_email', $this->email);
      }

      /**
      *@desc Setting shipping information
      */
      function setShippingInformation()
      {
          $this->logMessage('Initializing shipping parameters...');

          $this->setParam('x_ship_to_first_name', $this->shippingFirstName);
          $this->setParam('x_ship_to_last_name', $this->shippingLastName);
          $this->setParam('x_ship_to_company', $this->shippingCompany);
          $this->setParam('x_ship_to_address', $this->shippingAddress1 . ' ' . $this->shippingAddress2);
          $this->setParam('x_ship_to_city', $this->shippingCity);
          $this->setParam('x_ship_to_state', $this->shippingState);
          $this->setParam('x_ship_to_zip', $this->shippingZip);
          $this->setParam('x_ship_to_country', $this->shippingCountry);
      }

      function setCardInformation()
      {
          $this->logMessage('Initializing card parameters...');
          $this->setParam('x_card_num', trim($this->cardNumber));
          $this->setParam('x_exp_date', $this->expiryMonth . $this->expiryYear);

          if ($this->cvv)
          {
              $this->setParam('x_card_code', $this->cvv);
          }
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

          $this->requestType = 'AUTH_ONLY';
          $this->initialize();
          $this->setCardInformation();
          $this->setParam('x_type', 'AUTH_ONLY');
          $this->setParam('x_po_num', $this->invoiceNumber);
          $this->setParam('x_tax', $this->tax);
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

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->logMessage('Setting parameters for capture request...');

          $this->initialize();
          $this->requestType = 'PRIOR_AUTH_CAPTURE';
          $this->setParam('x_type', 'PRIOR_AUTH_CAPTURE');
          $this->setParam('x_trans_id', $this->transactionId);
          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc Sends capture request
      */
      function captureOnly()
      {
          $this->setParam('x_type', 'CAPTURE_ONLY');
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
          $this->requestType = 'AUTH_CAPTURE';
          $this->setParam('x_type', 'AUTH_CAPTURE');
          $this->setParam('x_po_num', $this->invoiceNumber);
          $this->setParam('x_tax', $this->tax);
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
          $this->validateTransactionID();
          $this->validateCreditCardNumber();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }
          $this->logMessage('Setting parameters for refund request...');

          $this->initialize();
          $this->requestType = 'CREDIT';
          $this->setParam('x_type', 'CREDIT');
          $this->setParam('x_trans_id', $this->transactionId);
          $this->setParam('x_po_num', $this->invoiceNumber);
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
              $this->response->responseCode = $responseValues[0];
              $this->response->responseSubCode = $responseValues[1];
              $this->response->reasonCode = $responseValues[2];
              $this->response->reasonText = $responseValues[3];

              if($responseValues[0] == 1)
              {
                  $this->response->success = 1;
                  $this->response->ack = ACK_SUCCESS;
              }
              else
              {
                  $this->response->ack = ACK_FAILURE;
                  $this->response->setError($responseValues[3]);
              }

              $this->response->authorizationId = $responseValues[4];
              $this->response->avsResponse = $responseValues[5];
              $this->response->transactionId = $responseValues[6];
              $this->response->invoiceNumber = $responseValues[7];
              $this->response->description = $responseValues[8];
              $this->response->amount = $responseValues[9];
              $this->response->method = $responseValues[10];
              $this->response->transactionType = $responseValues[11];
              $this->response->customerId = $responseValues[12];
              $this->response->billingFirstName = $responseValues[13];
              $this->response->billingLastName = $responseValues[14];
              $this->response->billingCompany = $responseValues[15];
              $this->response->billingAddress = $responseValues[16];
              $this->response->billingCity = $responseValues[17];
              $this->response->billingState = $responseValues[18];
              $this->response->billingZip = $responseValues[19];
              $this->response->billingCountry = $responseValues[20];
              $this->response->phone = $responseValues[21];
              $this->response->fax = $responseValues[22];
              $this->response->email = $responseValues[23];
              $this->response->shippingFirstName = $responseValues[24];
              $this->response->shippingLastName = $responseValues[25];
              $this->response->shippingCompany = $responseValues[26];
              $this->response->shippingAddress = $responseValues[27];
              $this->response->shippingCity = $responseValues[28];
              $this->response->shippingState = $responseValues[29];
              $this->response->shippingZip = $responseValues[30];
              $this->response->shippingCountry = $responseValues[31];
              $this->response->tax = $responseValues[32];
              $this->response->duty = $responseValues[33];
              $this->response->freight = $responseValues[34];
              $this->response->taxExempt = $responseValues[35];
              $this->response->purchaseOrderNumber = $responseValues[36];
              $this->response->md5Hash = $responseValues[37];
              $this->response->ccvResponse = $responseValues[38];
              $this->response->cavvResponse = $responseValues[39];
          }
          else
          {
              $this->response->amount = $this->amount;
              $this->response->invoiceNumber = $this->invoiceNumber;
              $this->response->ack = ACK_FAILURE;
              $this->response->transactionType = $this->requestType;
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
              $this->response->setError('MSD-PAY login credentials have not been configured.');
          }
      }

      /**
      *@desc Prepares request string form the parameters
      *
      * @return null
      */
      function prepareRequest()
      {
          if(count($this->params))
          {
              $nameValuePairs = array();

              foreach($this->params as $key => $value)
              {
                  if($value || $key == 'x_duplicate_window')
                  {
                      $nameValuePairs[] = $key .'=' . urlencode($value);
                  }
              }
              $this->requestString = implode('&', $nameValuePairs);
          }
      }
  }
?>
