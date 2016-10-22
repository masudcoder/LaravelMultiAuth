<?php
/**
 * @filename        PayPalAdvanced.php
 * @description     This class is for making payment through PayPal Advanced
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Mohammad Sajjad Hossain - info@sajjadhossain.com
 *
 * @link            http://sajjadhossain.com
 * @created on      July 22, 2009
 * @Dependencies
 * @license
 ***/
  class PayPalAdvanced extends PaymentGateway
  {
      function PayPalAdvanced()
      {
          parent::PaymentGateway();
          $this->apiVersion = '52.0';
          $this->requestURL = 'https://api-3t.paypal.com/nvp';
          $this->testURL = 'https://api-3t.sandbox.paypal.com/nvp';
          $this->supportedCurrencies = array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD', 'ZAR');
      }

      function initialize()
      {
          $this->setParam('VERSION', $this->apiVersion);
          $this->setParam('PWD', $this->apiKey);
          $this->setParam('USER', $this->apiUserName);
          $this->setParam('SIGNATURE', $this->apiSignature);
          $this->setParam('AMT', $this->amount);
          $this->setParam('CURRENCYCODE', $this->currencyCode);
          //For PWC partnership code
          $this->setParam('BUTTONSOURCE', 'PremiumWebCartInc_Cart_DP');
      }

      function setCardParameters()
      {
          switch($this->cardType)
          {
              case 'Master Card':
                $this->cardType = 'MasterCard';
                break;
              case 'American Express':
                $this->cardType = 'Amex';
                break;
          }
          $this->setParam('CREDITCARDTYPE', $this->cardType);
          $this->setParam('ACCT', $this->cardNumber);
          $this->setParam('EXPDATE', str_pad($this->expiryMonth, 2, '0', STR_PAD_LEFT) . $this->expiryYear);
          $this->setParam('CVV2', $this->cvv);
      }

      function setBillingInformation()
      {
          $this->setParam('FIRSTNAME', $this->firstName);
          $this->setParam('LASTNAME', $this->lastName);
          $this->setParam('STREET', trim($this->address1 . ' ' . $this->address2));
          $this->setParam('CITY', $this->city);
          $this->setParam('STATE', $this->state);
          $this->setParam('ZIP', $this->zip);
          $this->setParam('COUNTRYCODE', $this->countryCode);
          $this->setParam('EMAIL', $this->email);
      }

      function setShippingInformation()
      {
          $this->setParam('SHIPTONAME', trim($this->shippingFirstName . ' ' . $this->shippingLastName));
          $this->setParam('SHIPTOSTREET', $this->shippingAddress1);
          $this->setParam('SHIPTOSTREET2', $this->shippingAddress2);
          $this->setParam('SHIPTOCITY', $this->shippingCity);
          $this->setParam('SHIPTOSTATE', $this->shippingState);
          $this->setParam('SHIPTOZIP', $this->shippingZip);
          $this->setParam('SHIPTOCOUNTRY', $this->shippingCountryCode);
      }

      function authorize()
      {
          $this->validateBasicInput();
          $this->validateCurrency();
          $this->validateCreditCardNumber();
          $this->validateExpiryDate();

          if($this->response->hasError())
          {
              return $this->response;
          }

          $this->setParam('PAYMENTACTION', 'Authorization');
          $this->setParam('METHOD', 'doDirectPayment');
          $this->initialize();
          $this->setCardParameters();
          $this->setBillingInformation();
          $this->setShippingInformation();

          $this->makeAPICall();
          return $this->response;
      }

      function capture()
      {
          $this->validateBasicInput();
          $this->validateTransactionID();

          if($this->response->hasError())
          {
              return $this->response;
          }

          $this->setParam('METHOD', 'DOCapture');
          $this->initialize();
          $this->setParam('AUTHORIZATIONID', $this->transactionId);
          $this->setParam('COMPLETETYPE', $this->completeType);
          $this->setParam('NOTE', $this->note);

          $this->makeAPICall();
          return $this->response;
      }

      function sale()
      {
          $this->validateBasicInput();
          $this->validateCurrency();
          $this->validateCreditCardNumber();
          $this->validateExpiryDate();

          if($this->response->hasError())
          {
              return $this->response;
          }

          $this->setParam('PAYMENTACTION', 'Sale');
          $this->setParam('METHOD', 'doDirectPayment');
          $this->initialize();
          $this->setCardParameters();
          $this->setBillingInformation();
          $this->setShippingInformation();

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
              return $this->response;
          }

          $this->logMessage('Setting parameters for refund request...');
          $this->setParam('METHOD', 'RefundTransaction');
          $this->initialize();
          $this->setBillingInformation();
          $this->setParam('TRANSACTIONID', $this->transactionId);
          $this->setParam('NOTE', $this->note ? $this->note : $this->refundType . ' refund');
          $this->setParam('REFUNDTYPE', $this->refundType);

          $this->makeAPICall();
          return $this->response;
      }

      function createRecurringProfile()
      {
          $this->logMessage('Preparing request for creating recurring profile...');

          $this->validateBasicInput();
          $this->validateCreditCardNumber();
          $this->validateExpiryDate();
          $this->validateRequiredParamsForRecurring();

          if($this->response->hasError())
          {
              return $this->response;
          }

          $this->logMessage('Setting parameters...');
          $this->setParam('METHOD', 'CreateRecurringPaymentsProfile');
          $this->initialize();
          $this->setCardParameters();
          $this->setParam('SUBSCRIBERNAME', trim($this->firstName . ' ' . $this->lastName));
          $this->setParam('PROFILESTARTDATE', $this->startDate);
          $this->setParam('PROFILEREFERENCE', $this->invoiceNumber);
          $this->setParam('DESC', $this->note);
          $this->setParam('MAXFAILEDPAYMENTS', $this->maximumFailedTransactionAllowed);
          $this->setParam('BILLINGPERIOD', $this->billingUnit);
          $this->setParam('BILLINGFREQUENCY', $this->billingFrequency);
          $this->setParam('TOTALBILLINGCYCLES', $this->billingCycles);
          $this->setParam('TRIALBILLINGPERIOD', $this->trialBillingUnit);
          $this->setParam('TRIALBILLINGFREQUENCY', $this->trialBillingFrequency);
          $this->setParam('TRIALTOTALBILLINGCYCLES', $this->trialBillingCycles);
          $this->setParam('TRIALAMT', $this->trialAmount);
          $this->setParam('SHIPPINGAMT', $this->shippingAmount);
          $this->setParam('TAXAMT', $this->tax);
          $this->setParam('CURRENCYCODE', $this->currencyCode);

          $this->setParam('SHIPTONAME', trim($this->shippingFirstName . ' ' . $this->shippingLastName));
          $this->setParam('SHIPTOSTREET', $this->shippingAddress1);
          $this->setParam('SHIPTOSTREET2', $this->shippingAddress2);
          $this->setParam('SHIPTOCITY', $this->shippingCity);
          $this->setParam('SHIPTOSTATE', $this->shippingState);
          $this->setParam('SHIPTOZIP', $this->shippingZip);
          $this->setParam('SHIPTOCOUNTRY', $this->shippingCountryCode);

          $this->setParam('EMAIL', $this->email);
          $this->setParam('COUNTRYCODE', $this->countryCode);
          $this->setParam('BUSINESS', $this->company);
          $this->setParam('FIRSTNAME', $this->firstName);
          $this->setParam('LASTNAME', $this->lastName);

          $this->setParam('STREET', $this->address1);
          $this->setParam('STREET2', $this->address2);
          $this->setParam('CITY', $this->city);
          $this->setParam('STATE', $this->state);
          $this->setParam('ZIP', $this->zip);
          $this->setParam('COUNTRYCODE', $this->countryCode);
          $this->setParam('PHONENUM', $this->phone);

          $this->makeAPICall();
          return $this->response;
      }

      function getRecurringProfileDetails()
      {
          $this->logMessage('Preparing request for creating recurring profile...');

          if(empty($this->profileId))
          {
              $this->response->setError('Profile ID is missing');
              return $this->response;
          }

          $this->logMessage('Setting parameters...');
          $this->setParam('METHOD', 'GetRecurringPaymentsProfileDetails');
          $this->initialize();
          $this->setParam('ProfileID', $this->profileId);
          $this->makeAPICall();
          return $this->response;
      }

      function prepareResponse()
      {
          $responseArray = $this->processNVPResponse();

          if($responseArray)
          {
              $this->response->amount = $this->amount;

              if(isset($responseArray['TRANSACTIONID']))
              {
                  $this->response->transactionId = urldecode($responseArray['TRANSACTIONID']);
              }

              if(isset($responseArray['AUTHORIZATIONID']))
              {
                  $this->response->authorizationId = urldecode($responseArray['AUTHORIZATIONID']);
              }

              if(isset($responseArray['CVV2MATCH']))
              {
                  $this->response->ccvResponse = $responseArray['CVV2MATCH'];
              }

              if(isset($responseArray['AVSCODE']))
              {
                  $this->response->avsResponse = $responseArray['AVSCODE'];
              }

              if(isset($responseArray['CORRELATIONID']))
              {
                  $this->response->correlationId = urldecode($responseArray['CORRELATIONID']);
              }

              if(isset($responseArray['PROFILEID']))
              {
                  $this->response->recurringProfileId = urldecode($responseArray['PROFILEID']);
              }

              if(isset($responseArray['STATUS']))
              {
                  $this->response->status = urldecode($responseArray['STATUS']);
              }

              if(isset($responseArray['DESC']))
              {
                  $this->response->note = urldecode($responseArray['DESC']);
              }

              if(isset($responseArray['MAXFAILEDPAYMENTS']))
              {
                  $this->response->maximumFailedTransactionAllowed = urldecode($responseArray['MAXFAILEDPAYMENTS']);
              }

              if(isset($responseArray['SUBSCRIBERNAME']))
              {
                  $nameParts = explode(' ', urldecode($responseArray['SUBSCRIBERNAME']));
                  $totalParts = count($nameParts);

                  $this->response->shippingFirstName = $nameParts[0];

                  if($totalParts == 2)
                  {
                      $this->response->shippingLastName = $nameParts[1];
                  }
                  else if($totalParts > 2)
                  {
                      $this->response->shippingFirstName = $nameParts[0];

                      for($i = 1; $i < $totalParts; $i++)
                      {
                          $this->response->shippingLastName .= $nameParts[$i];
                      }
                  }
              }

              if(isset($responseArray['PROFILESTARTDATE']))
              {
                  $this->response->recurringProfileStartDate = urldecode($responseArray['PROFILESTARTDATE']);
              }

              if(isset($responseArray['PROFILEREFERENCE']))
              {
                  $this->response->invoiceNumber = urldecode($responseArray['PROFILEREFERENCE']);
              }

              if(isset($responseArray['NEXTBILLINGDATE']))
              {
                  $this->response->recurringNextBillingDate = urldecode($responseArray['NEXTBILLINGDATE']);
              }

              if(isset($responseArray['NUMCYCLESCOMPLETED']))
              {
                  $this->response->recurringCyclesCompleted = urldecode($responseArray['NUMCYCLESCOMPLETED']);
              }

              if(isset($responseArray['NUMCYCLESREMAINING']))
              {
                  $this->response->recurringCyclesRemaining = urldecode($responseArray['NUMCYCLESREMAINING']);
              }

              if(isset($responseArray['OUTSTANDINGBALANCE']))
              {
                  $this->response->recurringOutstandingBalance = urldecode($responseArray['OUTSTANDINGBALANCE']);
              }

              if(isset($responseArray['FAILEDPAYMENTCOUNT']))
              {
                  $this->response->recurringFailedCount = $responseArray['FAILEDPAYMENTCOUNT'];
              }

              if(isset($responseArray['BILLINGPERIOD']))
              {
                  $this->response->recurringBillingUnit = urldecode($responseArray['BILLINGPERIOD']);
              }

              if(isset($responseArray['BILLINGFREQUENCY']))
              {
                  $this->response->recurringBillingFrequency = $responseArray['BILLINGFREQUENCY'];
              }

              if(isset($responseArray['TOTALBILLINGCYCLES']))
              {
                  $this->response->recurringBillingCycles = $responseArray['TOTALBILLINGCYCLES'];
              }

              if(isset($responseArray['LASTPAYMENTDATE']))
              {
                  $this->response->recurringLastPaymentDate = urldecode($responseArray['LASTPAYMENTDATE']);
              }

              if(isset($responseArray['LASTPAYMENTAMT']))
              {
                  $this->response->recurringLastPaidAmount = urldecode($responseArray['LASTPAYMENTAMT']);
              }

              if(strtolower($responseArray['ACK']) == 'failure')
              {
                  $this->response->success = 0;
                  $this->response->ack = ACK_FAILURE;

                  $count = 0;

                  while (isset($responseArray['L_SHORTMESSAGE' . $count]))
                  {
                      $errorCode = $responseArray['L_ERRORCODE' . $count];
                      $shortMessage = $responseArray['L_SHORTMESSAGE' . $count];
                      $longMessage  = $responseArray['L_LONGMESSAGE' . $count];

                      $this->response->setError($errorCode . ': ' . urldecode($shortMessage . ' (' . $longMessage . ')'));

                      $count++;
                  }
              }
              else
              {
                  $this->response->success = 1;
                  $this->response->ack = ACK_SUCCESS;
              }
          }
      }

      /**
      *@desc Basic input validation
      */
      function validateBasicInput()
      {
          if(empty($this->apiUserName) || empty($this->apiKey) || empty($this->apiSignature))
          {
              $this->response->setError('PayPal Advanced login credentials have not been configured.');
          }

          $this->validateAmount();
      }

      /**
      *@desc Checks if expiry date is not empty
      */
      function validateExpiryDate()
      {
          if(empty($this->expiryMonth) || empty($this->expiryYear))
          {
              $this->response->setError('Expiration date is missing');
          }
      }

      function validateRequiredParamsForRecurring()
      {
          if(empty($this->note))
          {
              $this->response->setError('Description is missing');
          }

          if(empty($this->billingUnit))
          {
              $this->response->setError('Billing period/unit is missing');
          }
          else if(!in_array($this->billingUnit, array('Day', 'Week', 'SemiMonth', 'Month', 'Year')))
          {
              $this->response->setError('Invalid billing period/unit');
          }
          else if($this->billingUnit == 'Week' && $this->billingFrequency > 52)
          {
              $this->response->setError('Invalid billing frequency ('.$this->billingFrequency.')');
          }
          else if($this->billingUnit == 'Month' && $this->billingFrequency > 12)
          {
              $this->response->setError('Invalid billing frequency ('.$this->billingFrequency.')');
          }

          if(empty($this->startDate))
          {
              $this->response->setError('Recurring start date is missing');
          }
      }
  }
?>