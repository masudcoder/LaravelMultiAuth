<?php
/**
 * @filename        FirstData.php
 * @description     This class is for doing transaction with First Data API
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Mohammad Sajjad Hossain - info@sajjadhossain.com
 *
 * @link            http://sajjadhossain.com
 * @created on      August 05, 2009
 * @Dependencies    PaymentGateway
 * @license
 ***/
  class FirstData extends PaymentGateway
  {
      var $xml = '';

      var $orderType = 'SALE';

      var $result = 'Good';

      var $transactionOrigin = 'ECI';
      var $terminalType = 'UNSPECIFIED';

      var $host = 'secure.linkpt.net';
      var $testHost = 'staging.linkpt.net';
      var $requestPort = '1129';

      function FirstData()
      {
          parent::PaymentGateway();
          $this->apiVersion = '';
          $this->testURL = 'https://' . $this->testHost . ':' . $this->requestPort . '/LSGSXML';
          $this->requestURL = 'https://' . $this->host . ':' . $this->requestPort . '/LSGSXML';
          $this->supportedCurrencies = array('USD');
      }

      /**
      *@desc Initializes required variables
      */
      function initialize()
      {
          $this->logMessage('Initializing variables');
          $this->result = $this->testMode ? 'Good' : 'LIVE';
      }

      /**
      *@desc Only authorize
      */
      function authorize()
      {
          $this->printError('authorize() method has not been implemented');
      }

      /**
      *@desc Only capture
      */
      function capture()
      {
          $this->printError('capture() method has not been implemented');
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
              $this->logMessage('Error found...');
              return $this->response;
          }

          $this->logMessage('Setting parameters for sale request...');
          $this->orderType = 'SALE';
          $this->initialize();

          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc For refunding
      */
      function refund()
      {
          $this->logMessage('Preparing refund request...');

          $this->validateBasicInput();
          $this->validateCreditCardNumber();
          $this->validateExpiryDate();
          $this->validateTransactionID();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...');
              return $this->response;
          }

          $this->logMessage('Setting parameters for refund request...');
          $this->orderType = 'CREDIT';
          $this->initialize();

          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc Prepares a Response object based on the raw response sent by Payment Gateway
      */
      function prepareResponse()
      {
          $rawResponse = $this->response->getRawResponse();

          preg_match_all ("/<(.*?)>(.*?)\</", $rawResponse, $result, PREG_SET_ORDER);

          if(strlen($rawResponse) < 2)
          {
              $this->response->setError('Transaction could not be done');
              $this->response->success = 0;
              $this->response->ack = ACK_FAILURE;
          }
          else
          {
              $responseArray = array();

              $i = 0;
              while (isset($result[$i]))
              {
                  $responseArray[$result[$i][1]] = strip_tags($result[$i][0]);
                  $i++;
              }

              $this->response->avsResponse = isset($responseArray['r_avs']) ? $responseArray['r_avs'] : '';
              $this->response->invoiceNumber = isset($responseArray['r_ordernum']) ? $responseArray['r_ordernum'] : '';

              isset($responseArray['r_error']) ? $this->response->setError($responseArray['r_error']) : '';

              if(isset($responseArray['r_approved']))
              {
                  $approve = strtoupper($responseArray['r_approved']);
                  if($approve == 'APPROVED')
                  {
                      $this->response->success = 1;
                      $this->response->ack = ACK_SUCCESS;
                  }
                  else
                  {
                      $this->response->success = 0;
                      $this->response->ack = ACK_FAILURE;

                      if($approve == 'DECLINED')
                      {
                          $this->response->setError('The transaction has been declined');
                      }
                      else
                      {
                          $this->response->setError('The credit card is blocked');
                      }
                  }
              }

              $this->response->transactionId = isset($responseArray['r_code']) ? $responseArray['r_code'] : '';
              //$this->response->transactionId = $this->response->invoiceNumber;

              $this->response->amount = $this->amount;
              $this->response->transactionType = $this->orderType;
          }
      }

      /**
      *@desc This function prepares an XML request
      *
      * @param bool
      * @param bool
      */
      function prepareRequest($addBilling = true, $addShipping = true)
      {
          /**************************
          * ORDEROPTIONS NODE
          ***************************/
          $this->setStartTag('order');
          $this->setStartTag('orderoptions');
          $this->setNode('ordertype', $this->orderType);
          $this->setNode('result', $this->result);
          $this->setEndTag('orderoptions');

          /**************************
          * CREDITCARD NODE
          ***************************/
          $this->setStartTag('creditcard');
          if($this->magData)
          {
             $this->setNode('track', $this->magData);
          }
          else
          {
              $this->setNode('cardnumber', $this->cardNumber);
              $this->setNode('cardexpmonth', $this->expiryMonth);
              $this->setNode('cardexpyear', $this->getTwoDigitExpiryYear());
          }
          $this->setNode('cvmvalue', $this->cvv);
          $this->setEndTag('creditcard');

          /*************************
          * BILLING NODE
          **************************/
          if($addBilling)
          {
              $this->setStartTag('billing');
              $this->setNode('name', trim($this->firstName . ' ' . $this->lastName), true);
              $this->setNode('company', $this->company, true);
              $this->setNode('address1', $this->address1, true);
              $this->setNode('address2', $this->address2, true);
              $this->setNode('city', $this->city, true);
              $this->setNode('state', $this->state, true);
              $this->setNode('zip', $this->zip, true);
              $this->setNode('country', $this->country);
              $this->setNode('email', $this->email, true);
              $this->setNode('phone', $this->phone, true);
              $this->setNode('fax', $this->fax, true);
              $addNum = split(' ', trim($this->address1 . ' ' . $this->address2));
              $this->setNode('addrnum', $addNum[0]);
              $this->setEndTag('billing');
          }

          /*************************
          * SHIPPING NODE
          **************************/
          if($addShipping)
          {
              $this->setStartTag('shipping');
              $this->setNode('name', trim($this->shippingFirstName . ' ' . $this->shippingLastName), true);
              $this->setNode('address1', $this->shippingAddress1, true);
              $this->setNode('address2', $this->shippingAddress2, true);
              $this->setNode('city', $this->shippingCity, true);
              $this->setNode('state', $this->shippingState, true);
              $this->setNode('zip', $this->shippingZip, true);
              $this->setNode('country', $this->shippingCountry);
              $this->setEndTag('shipping');
          }

          /*************************
          * TRANSACTIONDETAILS NODE
          **************************/
          $this->setStartTag('transactiondetails');
          $this->setNode('oid', $this->transactionId);
          $this->setNode('ponumber', $this->invoiceNumber);
          $this->setNode('terminaltype', $this->terminalType);
          $this->setNode('ip', $this->ipAddress);
          $this->setNode('transactionorigin', $this->transactionOrigin);
          $this->setEndTag('transactiondetails');

          /*************************
          * MERCHANTINFO NODE
          **************************/
          $this->setStartTag('merchantinfo');
          $this->setNode('configfile', $this->apiUserName);
          $this->setNode('keyfile', $this->apiKey);
          $this->sslCertificatate = $this->apiKey;
          $this->setNode('host', $this->testMode ? $this->testHost : $this->host);
          $this->setNode('port', $this->requestPort);
          $this->setEndTag('merchantinfo');

          /*************************
          * PAYMENT NODE
          **************************/
          $this->setStartTag('payment');
          $this->setNode('chargetotal', $this->amount);
          $this->setNode('tax', $this->tax);
          //$this->setNode('currency', $this->getCurrency());
          $this->setEndTag('payment');

          /*************************
          * NOTES NODE
          **************************/

          if(!empty($this->note))
          {
              $this->setStartTag('notes');
              $this->setNode('comments', $this->note, true);
              $this->setEndTag('notes');
          }

          $this->setEndTag('order');
      }

      /**
      *@desc Returns the numeric ISO code of the transaction currency
      */
      function getCurrency()
      {
          switch($this->currencyCode)
          {
              case 'USD':
                return '840';
              case 'EUR':
                return '978';
              case 'GBP':
                return '826';
              case 'JPY':
                return '392';
              case 'CAD':
                return '124';
              case 'AUD':
                return '036';
          }
      }

      /**
      *@desc Basic input validation
      */
      function validateBasicInput()
      {
          if(empty($this->apiUserName) || empty($this->apiKey))
          {
              $this->response->setError('First Data\' credentials have not been configured.');
          }

          $this->validateAmount();
      }
  }
?>