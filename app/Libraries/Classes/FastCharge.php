<?php
/**
 * @filename        FastCharge.php
 * @description     This class is for doing transaction with FastCharge.com.
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Mohammad Sajjad Hossain - info@sajjadhossain.com
 *
 * @link            http://sajjadhossain.com
 * @created on      October 10, 2010
 * @Dependencies    PaymentGateway
 * @license
 ***/
  class FastCharge extends PaymentGateway
  {
      var $transactionType = '';

      function FastCharge()
      {
          parent::PaymentGateway();
          $this->requestURL = 'https://trans.secure-fastcharge.com/cgi-bin/process.cgi';
          $this->testURL = 'https://trans.secure-fastcharge.com/cgi-bin/process.cgi';
          $this->supportedCurrencies = array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD');
      }

      /**
      *@desc Setting basic parameters
      */
      function initialize()
      {
          $this->logMessage('Initializing basic parameters...');

          $this->setParam('acctid', $this->apiUserName);
          $this->setParam('amount', number_format($this->amount, 2, '.', ''));
          $this->setParam('ci_memo', $this->note);
          $this->setParam('usepost', 1);
      }

      function setCardInformation()
      {
          $this->logMessage('Initializing card parameters...');
          $this->setParam('ccname', $this->nameOnCard);
          $this->setParam('ccnum', trim($this->cardNumber));
          $this->setParam('expmon', $this->expiryMonth);
          $this->setParam('expyear', $this->expiryYear);
      }

      /**
      *@desc Setting billing information
      */
      function setBillingInformation()
      {
          $this->logMessage('Initializing billing parameters...');

          $this->setParam('ci_companyname', $this->company);
          $this->setParam('ci_billaddr1', $this->address1);
          $this->setParam('ci_billaddr2', $this->address2);
          $this->setParam('ci_billcity', $this->city);
          $this->setParam('ci_billstate', $this->state);
          $this->setParam('ci_billzip', $this->zip);
          $this->setParam('ci_billcountry', $this->countryCode);
          $this->setParam('ci_phone', $this->phone);
          $this->setParam('ci_email', $this->email);
      }

      /**
      *@desc Setting shipping information
      */
      function setShippingInformation()
      {
          $this->logMessage('Initializing shipping parameters...');

          $this->setParam('ci_shipaddr1', $this->shippingAddress1);
          $this->setParam('ci_shipaddr2', $this->shippingAddress2);
          $this->setParam('ci_shipcity', $this->shippingCity);
          $this->setParam('ci_shipstate', $this->shippingState);
          $this->setParam('ci_shipzip', $this->shippingZip);
          $this->setParam('ci_shipcountry', $this->shippingCountryCode);
      }

      /**
      *@desc Sends request for Authorization
      *
      * @return Response type object
      */
      function authorize(){}

      /**
      *@desc Sends capture request
      */
      function capture(){}

      /**
      *@desc Sends request for payment
      *
      * @return Response type object
      */
      function sale()
      {
          $this->transactionType = 'sale';
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
          $this->setParam('action', 'ns_quicksale_cc');
          $this->setParam('merchantordernumber', $this->invoiceNumber);

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
          $this->transactionType = 'refund';
          $this->logMessage('Preparing for refund request...');

          $this->validateBasicInput();
          $this->validateHistoryID();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }
          $this->logMessage('Setting parameters for refund request...');

          $this->initialize();
          $this->setParam('action', 'EXTACH_REFUND');
          $this->setParam('historykeyid', $this->securityCode);
          $this->setParam('orderkeyid', $this->invoiceNumber);

          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc Prepares Response object from raw response
      */
      function prepareResponse()
      {
          $rawResponseValues = explode("\n", $this->response->rawResponse);

          if(!empty($rawResponseValues))
          {
              $cnt = count($rawResponseValues) - 1;
              $responseValues = array();

              for($i = 1; $i < $cnt; $i++)
              {
                  list($key, $value) = explode('=', $rawResponseValues[$i]);
                  $key = strtolower($key);
                  $responseValues[$key] = trim($value);
              }

              if($responseValues['result'] == 1 && $responseValues['status'] == 'Accepted')
              {
                  $this->response->success = 1;
                  $this->response->ack = ACK_SUCCESS;
              }
              else
              {
                  $this->response->ack = ACK_FAILURE;
                  $this->response->responseCode = isset($responseValues['rcode']) ? $responseValues['rcode'] : '';

                  if(isset($responseValues['reason']))
                  {
                      $temp = explode(':', $responseValues['reason']);
                      $temp = isset($temp[2]) ? $temp[2] : '';
                      $this->response->setError($temp);
                  }
              }

              $this->response->transactionType = $this->transactionType;
              $this->response->amount = $this->amount;
              $this->response->transactionId = isset($responseValues['transid']) ? $responseValues['transid'] : '';
              $this->response->authorizationId = isset($responseValues['authcode']) ? $responseValues['authcode'] : '';
              $this->response->invoiceNumber = isset($responseValues['orderid']) ? $responseValues['orderid'] : '';
              $this->response->securityKey = isset($responseValues['historyid']) ? $responseValues['historyid'] : '';
          }
      }

      /**
      *@desc Basic input validation
      */
      function validateBasicInput()
      {
          if(empty($this->apiUserName))
          {
              $this->response->setError('FastCharge.com login credentials have not been configured.');
          }

          $this->validateAmount();
      }

      /**
      *@desc Checks if History ID has been saved or not
      */
      function validateHistoryID()
      {
          if(empty($this->securityCode))
          {
              $this->response->setError('History ID is missing');
          }
      }
  }
?>