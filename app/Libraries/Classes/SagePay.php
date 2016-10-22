<?php
/**
 * @filename        SagePay.php
 * @description     This class is for doing transaction with SagePay. This class supports Direct Protocol for transactions.
 *                  API document: SagePayDIRECTProtocolandIntegrationGuidelines.pdf
 *
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Mohammad Sajjad Hossain - info@sajjadhossain.com
 *
 * @link            http://sajjadhossain.com
 * @created on      December 18, 2009
 * @Dependencies    PaymentGateway
 * @license
 ***/
  class SagePay extends PaymentGateway
  {
      var $simulationURL  = '';
      var $enableSimulation = false;

      function SagePay()
      {
          parent::PaymentGateway();
          $this->supportedCurrencies = array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD');
          $this->apiVersion = '2.23';
      }

      function getRequestURL()
      {
          if(!$this->enableSimulation)
          {
              if($this->testMode)
              {
                  if(!empty($this->testURL))
                  {
                      return $this->testURL;
                  }
              }

              return $this->requestURL;
          }
          return $this->simulationURL;
      }

      function initialize()
      {
          $this->logMessage('Initializing basic parameters...');
          $this->setParam('VPSProtocol', $this->apiVersion);
          $this->setParam('Vendor', $this->apiUserName);
          $this->setParam('Amount', round($this->amount, 2));
          $this->setParam('Currency', $this->currencyCode);
      }

      function setCardInformation()
      {
          $this->setParam('CardHolder', $this->nameOnCard);
          $this->setParam('CardNumber', $this->cardNumber);
          $this->setParam('ExpiryDate', str_pad($this->expiryMonth, 2, '0', STR_PAD_LEFT) . $this->getTwoDigitExpiryYear());

          switch($this->cardType)
          {
              case 'Master Card':
                $this->cardType = 'MC';
                break;
              case 'Visa':
                $this->cardType = 'VISA';
                break;
			  case 'American Express':
                $this->cardType = 'AMEX';
                break;
          }

          $this->setParam('CardType', $this->cardType);
          $this->setParam('CV2', $this-> cvv);
      }

      function setBillingInformation()
      {
          $this->setParam('BillingSurname', $this->lastName);
          $this->setParam('BillingFirstnames', $this->firstName);
          $this->setParam('BillingAddress1', $this->address1);
          $this->setParam('BillingAddress2', $this->address2);
          $this->setParam('BillingCity', $this->city);
          $this->setParam('BillingPostCode', $this->zip);
          $this->setParam('BillingCountry', $this->countryCode);

          if($this->countryCode == 'US')
          {
              $this->setParam('BillingState', $this->state);
          }
          $this->setParam('BillingPhone', $this->phone);
      }

      function setShippingInformation()
      {
          $this->setParam('DeliverySurname', $this->shippingLastName);
          $this->setParam('DeliveryFirstnames', $this->shippingFirstName);
          $this->setParam('DeliveryAddress1', $this->shippingAddress1);
          $this->setParam('DeliveryAddress2', $this->shippingAddress2);
          $this->setParam('DeliveryCity', $this->shippingCity);
          $this->setParam('DeliveryPostCode', $this->shippingZip);
          $this->setParam('DeliveryCountry', $this->shippingCountryCode);

          if($this->shippingCountryCode == 'US')
          {
              $this->setParam('DeliveryState', $this->shippingState);
          }
      }

      function sale()
      {
          if($this->isRecuuringTransaction)
          {
              return $this->repeat();
          }
          $this->requestURL = 'https://live.sagepay.com/gateway/service/vspdirect-register.vsp';
          $this->testURL = 'https://test.sagepay.com/gateway/service/vspdirect-register.vsp';
          $this->simulationURL = 'https://test.sagepay.com/simulator/VSPDirectGateway.asp';

          $this->validateBasicInput();
          $this->validateCreditCardNumber();
          $this->validateExpiryDate();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...');
              return $this->response;
          }

          $this->logMessage('Setting parameters for sale request...');

          $this->initialize();
          $this->setParam('TxType', 'PAYMENT');
          $this->setParam('VendorTxCode', $this->invoiceNumber);

          if(strlen($this->note) >= 100)
             $this->setParam('Description', substr($this->note, 0, 96) . '...');
          else 
             $this->setParam('Description', $this->note);

          $this->setCardInformation();
          $this->setBillingInformation();
          $this->setShippingInformation();

          $this->makeAPICall();
          return $this->response;
      }

      function authorize()
      {
          $this->requestURL = 'https://live.sagepay.com/gateway/service/authorise.vsp';
          $this->testURL = 'https://test.sagepay.com/gateway/service/authorise.vsp';
          $this->simulationURL = 'https://test.sagepay.com/simulator/VSPServerGateway.asp?Service=VendorAuthoriseTx';
      }

      function refund()
      {
          $this->requestURL = 'https://live.sagepay.com/gateway/service/refund.vsp';
          $this->testURL = 'https://test.sagepay.com/gateway/service/refund.vsp';
          $this->simulationURL = 'https://test.sagepay.com/simulator/VSPServerGateway.asp?Service=VendorRefundTx';

          $this->logMessage('Preparing for refund request...');
          $this->validateBasicInput();
          $this->validateRefundInput();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...');
              return $this->response;
          }
          $this->logMessage('Setting parameters for refund request...');
          $this->initialize();
          $this->setParam('TxType', 'REFUND');
          $this->setParam('VendorTxCode', $this->invoiceNumber . time());
          if(strlen($this->note) >= 100)
             $this->setParam('Description', substr($this->note, 0, 96) . '...');
          else 
             $this->setParam('Description', $this->note);
          $this->setParam('RelatedVPSTxId', $this->transactionId);
          $this->setParam('RelatedVendorTxCode', $this->invoiceNumber);
          $this->setParam('RelatedSecurityKey', $this->securityCode);
          $this->setParam('RelatedTxAuthNo', $this->authorizationId);

          $this->makeAPICall();
          return $this->response;
      }

      function repeat()
      {
          $this->requestURL = 'https://live.sagepay.com/gateway/service/repeat.vsp';
          $this->testURL = 'https://test.sagepay.com/gateway/service/repeat.vsp';
          $this->simulationURL = 'https://test.sagepay.com/Simulator/VSPServerGateway.asp?Service=VendorRepeatTx';

          $this->logMessage('Preparing for repeat request...');
          $this->validateBasicInput();

          $this->validateRefundInput();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...');
              return $this->response;
          }
          $this->logMessage('Setting parameters for repeat request...');
          $this->initialize();
          $this->setParam('TxType', 'REPEAT');
          $this->setParam('VendorTxCode', $this->invoiceNumber);
          if(strlen($this->note) >= 100)
             $this->setParam('Description', substr($this->note, 0, 96) . '...');
          else 
             $this->setParam('Description', $this->note);
          $this->setParam('RelatedVPSTxId', $this->transactionId);
          $this->setParam('RelatedVendorTxCode', $this->relatedInvoiceNumber);
          $this->setParam('RelatedSecurityKey', $this->securityCode);
          $this->setParam('RelatedTxAuthNo', $this->authorizationId);

          $this->makeAPICall();
          return $this->response;
      }

      function prepareResponse()
      {
          $responseArray = array();

          if($this->response->rawResponse)
          {
              $parts = explode("\n", $this->response->rawResponse);
              if(count($parts))
              {
                  foreach($parts as $part)
                  {
                      if($part)
                      {
                          list($key, $value) = explode('=', $part);
                          $key = strtoupper($key);
                          $responseArray[$key] = trim($value, "\r");
                      }
                  }
              }

              if($responseArray['STATUS'] == 'OK')
              {
                  $this->response->success = 1;
                  $this->response->ack = ACK_SUCCESS;
                  $this->response->transactionId = urldecode($responseArray['VPSTXID']);
                  $this->response->authorizationId = urldecode($responseArray['TXAUTHNO']);
                  $this->response->securityKey = urldecode($responseArray['SECURITYKEY']);
                  $this->response->invoiceNumber = $this->invoiceNumber;
              }
              else
              {
                  $this->response->success = 0;
                  $this->response->ack = ACK_FAILURE;
                  $this->response->setError(urldecode($responseArray['STATUSDETAIL']));
              }
          }
      }

      /**
      *@desc Basic input validation
      */
      function validateBasicInput()
      {
          if(empty($this->apiUserName))
          {
              $this->response->setError('SagePay login credentials have not been configured.');
          }

          $this->validateAmount();
          $this->validateCurrency();
      }

      function validateRefundInput()
      {
          if(empty($this->transactionId))
          {
              $this->response->setError('Transaction ID is missing');
          }
          else if(empty($this->authorizationId))
          {
              $this->response->setError('Authorization ID is missing');
          }
          else if(empty($this->invoiceNumber))
          {
              $this->response->setError('Invoice number is missing');
          }
          else if(empty($this->securityCode))
          {
              $this->response->setError('Security key is missing');
          }
      }
  }
?>
