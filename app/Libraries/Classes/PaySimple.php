<?php
/**
 * @filename        PaySimple.php
 * @description     This class is for doing transaction with PaySimple's XML API
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Mohammad Sajjad Hossain - info@sajjadhossain.com
 *
 * @link            http://sajjadhossain.com
 * @created on      January 03, 2011
 * @Dependencies    PaymentGateway
 * @license
 ***/
  class PaySimple extends PaymentGateway
  {
      var $sandBoxEndPoint = 'https://sandbox-api.paysimple.com/3.00/paysimpleapi/xml/';
      var $productionEndPoint = 'https://api.paysimple.com/3.00/paysimpleapi/xml/';

      var $requestType = 'sale';

      function PaySimple()
      {
          parent::PaymentGateway();
          $this->apiVersion = '3.00';
          $this->supportedCurrencies = array('USD', 'GBP', 'EUR', 'ZAR');
          $headers = array('Content-Type: application/xml; charset=utf-8', 'Accept: application/xml', 'User-Agent: MSH Payment Processor');
          $this->setHeaders($headers);
          $this->verifiy_ssl = false;
      }

      function setBasicInfo()
      {
          $this->setNode('userkey', $this->apiUserName);
          $this->setNode('apikey', $this->apiKey);
      }

      function setCustomerInformation()
      {
          $attr = array('xmlns' => 'http://api.paysimple.com',
                        'xmlns:i' => 'http://www.w3.org/2001/XMLSchema-instance');

          $this->setStartTag('customer', $attr);
          $this->setNode('BillingAddress1', $this->address1, true);
          $this->setNode('BillingAddress2', $this->address2, true);
          $this->setNode('BillingCity', $this->city, true);
          $isoCountry = new ISOCountry();
          $this->setNode('BillingCountryCode', $isoCountry->getISOThreeCharCode($this->countryCode));
          $this->setNode('BillingPostalCode', $this->zip);
          $this->setNode('BillingState', $this->getStateId($this->state));
          $this->setNode('CompanyName', $this->company, true);
          $this->setNode('Email', $this->email);
          $this->setNode('Fax', $this->fax);
          $this->setNode('FirstName', $this->firstName, true);
          $this->setNode('LastName', $this->lastName, true);
          $this->setNode('MiddleName', '');
          $this->setNode('Notes', $this->note, true);
          $this->setNode('Phone', $this->phone);
          $this->setNode('ShippingAddress1', $this->shippingAddress1, true);
          $this->setNode('ShippingAddress2', $this->shippingAddress2, true);
          $this->setNode('ShippingCity', $this->shippingCity, true);
          $this->setNode('ShippingCountryCode', $isoCountry->getISOThreeCharCode($this->shippingCountryCode));
          $this->setNode('ShippingPostalCode', $this->shippingZip);
          $this->setNode('ShippingSameAsBilling', 'false');
          $this->setNode('ShippingState', $this->getStateId($this->shippingState));
          $this->setNode('WebSite', '');
          $this->setEndTag('customer');
      }

      function setCardInformation()
      {
          $attr = array('xmlns' => 'http://api.paysimple.com',
                        'xmlns:i' => 'http://www.w3.org/2001/XMLSchema-instance');

          $this->setStartTag('customerAccount', $attr);
          $this->setNode('ApiConsumerData', '');
          $this->setNode('PsReferenceId', '0');
          $this->setNode('CustomerId', '0');
          $this->setNode('AccountNumber', $this->cardNumber);
          $this->setNode('CCExpiry', $this->expiryMonth . '/' . $this->expiryYear);

          if($this->cardType == 'Master Card' || $this->cardType == 'MasterCard')
          {
              $cardType = 'Master';
          }
          else if($this->cardType == 'American Express')
          {
              $cardType = 'Amex';
          }
          else
          {
              $cardType = $this->cardType;
          }

          $this->setNode('CCType', $cardType);
          $this->setEndTag('customerAccount');
      }

      /**
      *@desc Only authorize
      */
      function authorize()
      {
          $this->response->setError('Method is not supported');
          return $this->response;
      }

      /**
      *@desc Only capture
      */
      function capture()
      {
          $this->response->setError('Method is not supported');
          return $this->response;
      }

      /**
      *@desc For authorize and capture
      */
      function sale()
      {
          $this->testURL = $this->sandBoxEndPoint . 'addcustomerandmakeccpayment';
          $this->requestURL = $this->productionEndPoint . 'addcustomerandmakeccpayment';
          $this->logMessage('Preparing sale request...');

          $this->validateBasicInput();
          $this->validateAmount();
          $this->validateCreditCardNumber();
          $this->validateExpiryDate();

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
          $this->testURL = $this->sandBoxEndPoint . 'reversepayment';
          $this->requestURL = $this->productionEndPoint . 'reversepayment';
          $this->logMessage('Preparing refund request...');

          $this->validateBasicInput();
          $this->validateTransactionID();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->logMessage('Setting parameters for refund request...');
          $this->requestType = 'refund';
          $this->makeAPICall();
          return $this->response;
      }

      /**
      *@desc Prepares a Response object based on the raw response sent by Payment Gateway
      */
      function prepareResponse()
      {
          $rawResponse = str_replace('a:string', 'a', $this->response->getRawResponse());

          if(strstr($rawResponse, 'Request Error'))
          {
              $this->response->success = 0;
              $this->response->ack = ACK_FAILURE;
              $this->response->setError('Request error: Server returned an exception.');
          }
          else
          {
              $response = simplexml_load_string($rawResponse);

              if($response->IsSuccess != 'false')
              {
                  if(!empty($response->PsObject->PsObject[2]))
                  {
                      $paymentInfo = $response->PsObject->PsObject[2];
                  }
                  else
                  {
                      $paymentInfo = $response->PsObject->PsObject;
                  }
                  $status = (string) $paymentInfo->Status;

                  if($status == 'Authorized')
                  {
                      $this->response->success = 1;
                      $this->response->ack = ACK_SUCCESS;
                      $this->response->transactionId = (string) $paymentInfo->PsReferenceId;
                      $this->response->authorizationId = (string) $paymentInfo->TraceNumber;
                      $customerAccountId = (string) $paymentInfo->CustomerAccountId;
                      $customerId = (string) $paymentInfo->CustomerId;
                      $this->response->securityKey = $customerAccountId . '|' . $customerId;
                  }
                  else if($status == 'ReversePosted')
                  {
                      $this->response->success = 1;
                      $this->response->ack = ACK_SUCCESS;
                  }
                  else
                  {
                      $this->response->success = 0;
                      $this->response->ack = ACK_FAILURE;
                      $this->response->setError($responseArray['RESPONSE']);
                  }
              }
              else
              {
                  $this->response->success = 0;
                  $this->response->ack = ACK_FAILURE;
                  $error = (string) $response->ErrorMessage->a;
                  $this->response->setError($error);
              }
          }

          $this->response->invoiceNumber = $this->invoiceNumber;
          $this->response->amount = $this->amount;
          $this->response->transactionType = $this->requestType;
          $this->response->currency = $this->currencyCode;
      }

      /**
      *@desc This function prepares an XML request
      *
      * @param bool
      * @param bool
      */
      function prepareRequest()
      {
          if($this->requestType == 'sale')
          {
              $this->setStartTag('AddCustomerAndMakeCCPayment', array('xmlns' => 'http://api.paysimple.com'));
              $this->setBasicInfo();
              $this->setCustomerInformation();
              $this->setCardInformation();
              $this->setNode('amount', number_format($this->amount, 2, '.', ''));

              $attr = array('xmlns' => 'http://api.paysimple.com',
                            'xmlns:a' => 'http://schemas.datacontract.org/2004/07/PaySimple.Api.ApiObjects',
                            'xmlns:i' => 'http://www.w3.org/2001/XMLSchema-instance');
              $this->setStartTag('detail', $attr);
              $this->setNode('a:CustomData', '', false, array('xmlns:b' => 'http://schemas.microsoft.com/2003/10/Serialization/Arrays'));
              $this->setNode('a:Description', $this->note, true);
              $this->setNode('a:InvoiceNumber', $this->invoiceNumber);
              $this->setNode('a:OrderId', $this->invoiceNumber);
              $this->setNode('a:PurchaseOrderNumber', '');
              $this->setEndTag('detail');

              $this->setEndTag('AddCustomerAndMakeCCPayment');
          }
          else if($this->requestType == 'refund')
          {
              $this->setStartTag('ReversePayment', array('xmlns' => 'http://api.paysimple.com'));
              $this->setBasicInfo();
              $this->setNode('paymentId',$this->transactionId);
              $this->setEndTag('ReversePayment');
          }
      }

      /**
      *@desc Basic input validation
      */
      function validateBasicInput()
      {
          if(empty($this->apiUserName) || empty($this->apiKey))
          {
              $this->response->setError('Virtual Card Services Terminal ID has not been configured.');
          }
      }

      function getStateId($code)
      {
          $states = array('AK' => 1,
                            'AL' => 2,
                            'AR' => 3,
                            'AZ' => 4,
                            'CA' => 5,
                            'CO' => 6,
                            'CT' => 7,
                            'DC' => 8,
                            'DE' => 9,
                            'FL' => 10,
                            'GA' => 11,
                            'HI' => 12,
                            'IA' => 13,
                            'ID' => 14,
                            'IL' => 15,
                            'IN' => 16,
                            'KS' => 17,
                            'KY' => 18,
                            'LA' => 19,
                            'MA' => 20,
                            'MD' => 21,
                            'ME' => 22,
                            'MI' => 23,
                            'MN' => 24,
                            'MO' => 25,
                            'MS' => 26,
                            'MT' => 27,
                            'NC' => 28,
                            'ND' => 29,
                            'NE' => 30,
                            'NH' => 31,
                            'NJ' => 32,
                            'NM' => 33,
                            'NV' => 34,
                            'NY' => 35,
                            'OH' => 36,
                            'OK' => 37,
                            'OR' => 38,
                            'PA' => 39,
                            'RI' => 40,
                            'SC' => 41,
                            'SD' => 42,
                            'TN' => 43,
                            'TX' => 44,
                            'UT' => 45,
                            'VA' => 46,
                            'VT' => 47,
                            'WA' => 48,
                            'WI' => 49,
                            'WV' => 50,
                            'WY' => 51,
                            'PR' => 52,
                            'AB' => 53,
                            'BC' => 54,
                            'MB' => 55,
                            'NB' => 56,
                            'NL' => 57,
                            'NT' => 58,
                            'NS' => 59,
                            'NU' => 60,
                            'ON' => 61,
                            'PE' => 62,
                            'QC' => 63,
                            'SK' => 64,
                            'YT' => 65,
                            'Other' => 66,
                            'MP' => 67,
                            'GU' => 68,
                            'VI' => 69,
                            'MH' => 70,
                            'AS' => 71,
                            'AE' => 72,
                            'PW' => 73,
                            'AP' => 74,
                            'FM' => 75,
                            'AA' => 76);
          return !empty($states[$code]) ? $states[$code] : $states['Other'];
      }

      function setNode($node, $value, $convert = false, $attribute = '')
      {
          if(!empty($node))
          {
              if(!empty($attribute) && is_array($attribute))
              {
                  $temp = array();
                  foreach($attribute as $key => $val)
                  {
                      $temp[] = $key . '="' . $val . '"';
                  }
                  $attribute = implode(' ', $temp) . ' ';
              }

              if($value == '')
              {
                  $this->requestString .= "<$node $attribute/>";
              }
              else
              {
                  $value = $convert ? $this->convertForXML($value) : $value;
                  $this->requestString .= "<$node $attribute>$value</$node>";
              }
          }
      }
  }
?>