<?php
/**
 * @filename        Response.php
 * @description     This class is for manipulation of response.
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Mohammad Sajjad Hossain - info@sajjadhossain.com
 *
 * @link            http://sajjadhossain.com
 * @created on      July 22, 2009
 * @dependencies
 * @license
 ***/
 
 namespace App\Libraries\Classes;
 
  class Response
  {
      /**
      *@desc This is the response the payment gateway sends. Gateway classes uses this response to prepare customized response.
      *
      * @var string
      */
      var $rawResponse = '';

      #----------------------
      # Response variables
      #----------------------

      /**
      *@desc If transaction is succesful then this variable will contain 1
      *
      * @var int
      */
      var $success = 0;

      /**
      *@desc Tells if the transaction is successful or not. It is a text representation
      *
      * @var string
      */
      var $ack = '';

      /**
      *@desc This authorization id is needed when capturing
      *
      * @var string
      */
      var $authorizationId = '';

      /**
      *@desc Unique ID for the transaction
      *
      * @var string
      */
      var $transactionId = '';

      /**
      *@desc Stores error
      *
      * @var array
      */
      var $errors = array();

      /**
      *@desc Stores cURL error (if any)
      *
      * @var string
      */
      var $curlErrorNo = '';

      /**
      *@desc Stores cURL error message (if any)
      *
      * @var string
      */
      var $curlErrorMessage = '';

      /**
      *@desc Available for: Authorize.net, First Data
      */
      var $avsResponse = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $responseCode = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $responseSubCode = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $reasonCode = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $reasonText = '';
      /**
      *@desc Available for: Authorize.net, First Data
      */
      var $invoiceNumber = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $description = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $amount = 0.00;

      /**
      *@desc Available for PayPal IPN
      */
      var $currency = '';

      /**
      *@desc Available for: Authorize.net
      */
      var $method = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $transactionType = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $customerId = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $billingFirstName = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $billingLastName = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $billingCompany = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $billingAddress = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $billingCity = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $billingState = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $billingZip = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $billingCountry = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $phone = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $fax = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $email = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $shippingFirstName = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $shippingLastName = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $shippingCompany = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $shippingAddress = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $shippingCity = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $shippingState = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $shippingZip = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $shippingCountry = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $tax = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $duty = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $freight = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $taxExempt = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $purchaseOrderNumber = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $md5Hash = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $ccvResponse = '';
      /**
      *@desc Available for: Authorize.net
      */
      var $cavvResponse = '';

      /**
      *@desc Available for: PayPal
      */
      var $correlationId = '';

      var $note = '';

      /**
      *@desc For SagePay, PaymentExpress
      */
      var $securityKey = '';

      /**
      *@desc Date of payment
      */
      var $paymentDate = '';
      /***********************************
       * Variables for Recurring Response *
       ************************************/
      /**
      *@desc If the transaction is a recurring or regular transaction
      */
      var $isRecurring = 0;
      /**
      *@desc Available for: PayPal Recurring Profile
      */
      var $recurringProfileId = '';

      /**
      *@desc Status of the recurring profile. Available for: PayPal
      */
      var $status = '';

      /**
      *@desc Maximum failed transaction after which the recurring will stop
      */
      var $maximumFailedTransactionAllowed = 0;

      /**
      *@desc Recurring start date, standard date format
      */
      var $recurringProfileStartDate = '';

      /**
      *@desc Next recurring billing date
      */
      var $recurringNextBillingDate = '';

      /**
      *@desc Total Number of successful attempts
      */
      var $recurringCyclesCompleted = '';

      /**
      *@desc Total cycles remain
      */
      var $recurringCyclesRemaining = '';

      /**
      *@desc Outstanding balance
      */
      var $recurringOutstandingBalance = '';

      /**
      *@desc Number of failed transaction
      */
      var $recurringFailedCount = '';

      /**
      *@desc Billing unit
      */
      var $recurringBillingUnit = '';

      /**
      *@desc Number of billing periods that make up one billing cycle
      */
      var $recurringBillingFrequency = '';

      /**
      *@desc The number of billing cycles for payment period
      */
      var $recurringBillingCycles = 0;

      /**
      *@desc Billing unit for trial - Day, Month, Week, SemiMonth, Year
      */
      var $recurringTrialBillingUnit = '';

      /**
      *@desc Number of billing periods that make up one trial billing cycle
      */
      var $recurringTrialBillingFrequency = 0;

      /**
      *@desc The number of billing cycles for trial payment period
      */
      var $recurringTrialBillingCycles = 0;

      /**
      *@desc Amount for trial period
      */
      var $recurringTrialAmount = 0;

      /**
      *@desc Date of last recurring payment
      */
      var $recurringLastPaymentDate = '';

      /**
      *@desc Amount of last paid recurring
      */
      var $recurringLastPaidAmount = 0;

      /******************/
      /* For IPN */
      /******************/
      /**
      *@desc IPN status
      */
      var $paymentStatus = '';
      var $receiverEmail = '';
      var $receiver = '';

      var $isTestMode = 0;
      /**
      *@desc Sets raw response received from payment gateway
      *
      * @param string
      */
      function setRawResponse($rawResponse)
      {
          $this->rawResponse = $rawResponse;
      }

      /**
      *@desc Returns the raw response recieved from payment gateway
      *
      * @return string
      */
      function getRawResponse()
      {
          return $this->rawResponse;
      }

      /**
      *@desc Sets error
      *
      * @param string - error text
      */
      function setError($error)
      {
          
          if(is_array($error) && !empty($error))
          {
              foreach($error as $err)
              {
                  $this->errors[] = $err;
              }
          }
          else
          {
              $this->errors[] = $error;
          }
      }

      /**
      *@desc Returns the array of errors
      *
      * @return array
      */
      function getErrors()
      {
          return $this->errors;
      }

      /**
      *@desc Checks if there is any error
      *
      * @return bool
      */
      function hasError()
      {
        
          return !empty($this->errors);
      }

      /**
      *@desc Prepares a custom string with errors
      *
      * @param string - Default valie ''. Possible values - 'ul', 'ol', 'div', 'p'
      *
      * @return string
      */
      function getErrorString($tag = '')
      {
          switch($tag)
          {
              case 'ol':
                $startTag = '<ol><li>';
                $endTag = '</li></ol>';
                $delimiter = '</li><li>';
                break;
              case 'div':
                $startTag = '<div>';
                $endTag = '</div>';
                $delimiter = '</div><div>';
                break;
              case 'p':
                $startTag = '<p>';
                $endTag = '</p>';
                $delimiter = '</p><p>';
                break;
              case 'ul':
                $startTag = '<ul><li>';
                $endTag = '</li></ul>';
                $delimiter = '</li><li>';
                break;
              default:
                $startTag = '';
                $endTag = '';
                $delimiter = '. ';
                break;
          }

          $errorString = trim(implode($delimiter, $this->errors));

          if($errorString)
          {
              return $startTag . $errorString . $endTag;
          }

          return '';
      }
  }
?>
