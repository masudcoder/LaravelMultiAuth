<?php
/**
 * @filename        QuickBooksMerchantServices.php
 * @description     This class is for doing transaction with QuickBooks Merchant Service API (http://qbms.developer.intuit.com/sdk/qbms)
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Mohammad Sajjad Hossain - info@sajjadhossain.com
 *
 * @link            http://sajjadhossain.com
 * @created on      October 01, 2010
 * @Dependencies    PaymentGateway
 * @license
 ***/
  class QuickBooksMerchantServices extends PaymentGateway
  {
      function QuickBooksMerchantServices()
      {
          parent::PaymentGateway();
          $this->testURL = 'https://merchantaccount.ptc.quickbooks.com/j/AppGateway';
          $this->requestURL = 'https://merchantaccount.quickbooks.com/j/AppGateway';
          $this->headers = array("Content-Type: application/x-qbmsxml");
      }

      function initialize()
      {
          $this->amount = number_format($this->amount, 2, '.', '');
          $this->setStartTag('SignonMsgsRq');
          $this->setStartTag('SignonDesktopRq');
          $this->setNode('ClientDateTime', date('Y-m-d') . 'T' . date('H:i:s'));
          $this->setNode('ApplicationLogin', $this->apiUserName);
          $this->setNode('ConnectionTicket', $this->apiSignature);
          $this->setNode('Language', 'English');
          $this->setEndTag('SignonDesktopRq');
          $this->setEndTag('SignonMsgsRq');
      }

      function sale()
      {
          $this->logMessage('Preparing sale request...');

          $this->validateBasicInput();
          $this->validateCreditCardNumber();
          $this->validateExpiryDate();
          $this->validateNameOnCard();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->logMessage('Setting parameters for sale request...');
          $this->requestString = '<?xml version="1.0"?><?qbmsxml version="4.1"?>';

          $this->setStartTag('QBMSXML');
          $this->initialize();
          $this->setStartTag('QBMSXMLMsgsRq');
          $this->setStartTag('CustomerCreditCardChargeRq');
          $this->setNode('TransRequestID', $this->invoiceNumber);
          $this->setNode('CreditCardNumber', $this->cardNumber);
          $this->setNode('ExpirationMonth', $this->expiryMonth);
          $this->setNode('ExpirationYear', $this->expiryYear);
          $this->setNode('IsCardPresent', $this->testMode ? 'false' : 'true');
          $this->setNode('Amount', $this->amount);
          $this->setNode('NameOnCard', $this->nameOnCard, true);
          $this->setNode('CreditCardAddress', trim($this->address1 . ' ' . $this->address2), true);
          $this->setNode('CreditCardPostalCode', $this->zip, true);
          $this->setNode('SalesTaxAmount', $this->tax);
          $this->setNode('Comment', $this->note, true);
          $this->setEndTag('CustomerCreditCardChargeRq');
          $this->setEndTag('QBMSXMLMsgsRq');

          $this->setEndTag('QBMSXML');

          $this->makeAPICall();
          return $this->response;
      }

      function refund()
      {
          $this->logMessage('Preparing refund request...');
          $this->validateBasicInput();
          $this->validateTransactionID();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->logMessage('Setting parameters for refund request...');
          $this->requestString = '<?xml version="1.0"?><?qbmsxml version="4.1"?>';
          $this->setStartTag('QBMSXML');
          $this->initialize();
          $this->setStartTag('QBMSXMLMsgsRq');
          $this->setStartTag('CustomerCreditCardTxnVoidOrRefundRq');
          $this->setNode('TransRequestID', time()); //PWC specific value
          $this->setNode('CreditCardTransID', $this->transactionId);
          $this->setNode('Amount', $this->amount);
          $this->setNode('Comment', $this->note, true);
          $this->setEndTag('CustomerCreditCardTxnVoidOrRefundRq');
          $this->setEndTag('QBMSXMLMsgsRq');

          $this->setEndTag('QBMSXML');

          $this->makeAPICall();
          return $this->response;
      }

      function prepareResponse()
      {
          $rawResponse = $this->response->getRawResponse();
          $responseObject = simplexml_load_string($rawResponse);

          $this->response->isTestMode = $this->testMode ? 1 : 0;

          if(isset($responseObject->QBMSXMLMsgsRs))
          {
              $qbMessage = $responseObject->QBMSXMLMsgsRs;

              if(isset($qbMessage->CustomerCreditCardChargeRs))
              {
                  $obj = $qbMessage->CustomerCreditCardChargeRs;

                  $status = array();

                  foreach($obj->attributes() as $key => $value)
                  {
                      if(is_object($value))
                      {
                          $value = (array) $value;
                          $status[$key] = $value[0];
                      }
                      else
                      {
                          $status[$key] = $value;
                      }
                  }

                  if($status['statusCode'] == 0)
                  {
                      $this->response->success = 1;
                      $this->response->ack = ACK_SUCCESS;

                      $transactionId = (array) $obj->CreditCardTransID;
                      $this->response->transactionId = $transactionId[0];

                      $authorizationCode = (array) $obj->AuthorizationCode;
                      $this->response->authorizationId = $authorizationCode[0];

                      $this->response->amount = $this->amount;
                      $this->response->transactionType = 'Charge';
                  }
                  else
                  {
                      $this->response->success = 0;
                      $this->response->ack = ACK_FAILURE;
                      $this->response->setError($status['statusMessage']);
                      $this->response->responseCode = $status['statusCode'];
                      $this->response->reasonCode = $status['statusCode'];
                  }
              }
              else if(isset($qbMessage->CustomerCreditCardTxnVoidOrRefundRs))
              {
                  $obj = $qbMessage->CustomerCreditCardTxnVoidOrRefundRs;

                  $status = array();

                  foreach($obj->attributes() as $key => $value)
                  {
                      if(is_object($value))
                      {
                          $value = (array) $value;
                          $status[$key] = $value[0];
                      }
                      else
                      {
                          $status[$key] = $value;
                      }
                  }

                  if($status['statusCode'] == 0)
                  {
                      $this->response->success = 1;
                      $this->response->ack = ACK_SUCCESS;

                      $transactionId = (array) $obj->CreditCardTransID;
                      $this->response->transactionId = $transactionId[0];

                      $transactionType = (array) $obj->VoidOrRefundTxnType;
                      $this->response->transactionType = $transactionType[0];
                  }
                  else
                  {
                      $this->response->success = 0;
                      $this->response->ack = ACK_FAILURE;
                      $this->response->setError($status['statusMessage']);
                      $this->response->responseCode = $status['statusCode'];
                      $this->response->reasonCode = $status['statusCode'];
                  }
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
              $this->response->setError('QuickBooks Merchant Service has not been configured (Application Login is missing).');
          }

          if(empty($this->apiSignature))
          {
              $this->response->setError('QuickBooks Merchant Service has not been configured (Connection Ticket is missing).');
          }

          $this->validateAmount();
      }
  }
?>