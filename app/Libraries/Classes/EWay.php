<?php
/**
 * @filename        EWay.php
 * @description     This class is for doing transaction with eWay API
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Mohammad Sajjad Hossain - info@sajjadhossain.com
 *
 * @link            http://sajjadhossain.com
 * @created on      December 01, 2009
 * @Dependencies    PaymentGateway
 * @license
 ***/
  class EWay extends PaymentGateway
  {
      var $orderType = 'SALE';

      function EWay()
      {
          parent::PaymentGateway();
          $this->apiVersion = '';
          $this->supportedCurrencies = array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD', 'ZAR');
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
          $this->testURL = 'https://www.eway.com.au/gateway/xmltest/testpage.asp';
          $this->requestURL = 'https://www.eway.com.au/gateway/xmlpayment.asp';
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
          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc For refunding
      */
      function refund()
      {
          $this->testURL = 'https://www.eway.com.au/gateway/xmlpaymentrefund.asp';
          $this->requestURL = 'https://www.eway.com.au/gateway/xmlpaymentrefund.asp';
          $this->logMessage('Preparing refund request...');
          $this->orderType = 'CREDIT';

          $this->validateBasicInput();
          $this->validateExpiryDate();
          $this->validateTransactionID();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->logMessage('Setting parameters for refund request...');

          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc Prepares a Response object based on the raw response sent by Payment Gateway
      */
      function prepareResponse()
      {
          $rawResponse = $this->response->getRawResponse();

          if(strlen($rawResponse) < 2)
          {
              $this->response->setError('Transaction could not be done');
              $this->response->success = 0;
              $this->response->ack = ACK_FAILURE;
          }
          else
          {
              $responseArray = array();

              $xmlData = $this->processXMLData();

              foreach($xmlData as $data)
              {
                  if($data['level'] == 2 && isset($data['value']))
                  {
                      $responseArray[$data['tag']] = $data['value'];
                  }
              }

              if($responseArray['EWAYTRXNSTATUS'] == 'True')
              {
                  $this->response->success = 1;
                  $this->response->ack = ACK_SUCCESS;
              }
              else
              {
                  $this->response->success = 0;
                  $this->response->ack = ACK_FAILURE;

                  if(strstr($responseArray['EWAYTRXNERROR'], ','))
                  {
                      list($code, $error) = explode(',', $responseArray['EWAYTRXNERROR']);
                      $this->response->reasonCode = $code;
                      $this->response->setError($error);
                  }
                  else
                  {
                      $this->response->setError($responseArray['EWAYTRXNERROR']);
                  }
              }

              $this->response->transactionId = $responseArray['EWAYTRXNNUMBER'];
              $this->response->authorizationId = $responseArray['EWAYAUTHCODE'];
              $this->response->amount = $responseArray['EWAYRETURNAMOUNT'] / 100;
              $this->response->transactionType = $this->orderType;
              $this->response->ccvResponse = $this->cvv;
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
          $this->setStartTag('ewaygateway');

          $this->setNode('CustomerID', $this->apiUserName);
          $this->amount = $this->amount * 100; //converting to cent
          $this->setNode('TotalAmount', $this->amount); //mandatory field

          if($this->orderType == 'CREDIT')
          {
              $this->setNode('OriginalTrxnNumber', $this->transactionId);
              $this->setNode('RefundPassword', $this->apiKey);
          }
          else
          {
              $this->setNode('TrxnNumber', $this->transactionId);

              $this->setNode('CustomerFirstName', $this->firstName, true);
              $this->setNode('CustomerLastName', $this->lastName, true);
              $this->setNode('CustomerEmail', $this->email, true);
              $this->setNode('CustomerAddress', trim($this->address1 . ' ' . $this->address2), true);
              $this->setNode('CustomerPostcode', $this->zip, true);
              $this->setNode('CustomerInvoiceDescription', $this->note, true);
              $this->setNode('CustomerInvoiceRef', $this->invoiceNumber);

              $this->setNode('CardHoldersName', $this->nameOnCard); //mandatory field
              $this->setNode('CardNumber', $this->cardNumber); //mandatory field
          }

          $this->setNode('CardExpiryMonth', str_pad($this->expiryMonth, 2, '0', STR_PAD_LEFT)); //mandatory field
          $this->setNode('CardExpiryYear', $this->getTwoDigitExpiryYear()); //mandatory field
          $this->setNode('CVN', $this->cvv); //mandatory field
          $this->setNode('Option1', '');
          $this->setNode('Option2', '');
          $this->setNode('Option3', '');

          $this->setEndTag('ewaygateway');
      }

      /**
      *@desc Basic input validation
      */
      function validateBasicInput()
      {
          if(empty($this->apiUserName))
          {
              $this->response->setError('eWay Customer ID has not been configured.');
          }

          if($this->orderType == 'CREDIT' && empty($this->apiKey))
          {
              $this->response->setError('eWay Refund Password has not been configured.');
          }

          $this->validateAmount();
      }

      /**
      *@desc For creating a node
      *
      * @param string
      * @param string
      */
      function setNode($node, $value, $convert = false)
      {
          if(!empty($node))
          {
              $node = 'eway' . $node;
              $value = $convert ? $this->convertForXML($value) : $value;
              $value = !empty($value) ? htmlentities(trim($value), ENT_QUOTES, 'UTF-8') : '';
              $this->requestString .= "<$node>$value</$node>";
          }
      }

      /**
      *@desc For creating a start tag
      *
      * @param string
      */
      function setStartTag($node)
      {
          if(!empty($node))
          {
              $this->requestString .= "<$node>";
          }
      }

      /**
      *@desc For creating an end tag
      *
      * @param string
      */
      function setEndTag($node)
      {
          if(!empty($node))
          {
              $this->requestString .= "</$node>";
          }
      }
  }
?>