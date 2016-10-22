<?php
/**
 * @filename        PaymentGateway.php
 * @description     This class is the base class and has generic functions. Classes for each gateway must extend this class
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Md. Alauddin Husain - alauddinkuet@gmail.com
 *
 * @created on      July 22, 2009
 * @Dependencies
 * @license
 ***/
  namespace App\Libraries\Classes;
  use Log;
  //use App\Libraries\Classes\Response;
  class PaymentGateway
  {
      /**
      *@desc Cntents of the "User-Agent: " header to be used in a HTTP request.
      */
      var $userAgent = 'PWC Payment Processing Library';

      /**
      *@desc Request method to be used. Possible values 'POST' and 'GET'
      */
      var $requestMethod = 'POST';

       /**
      *@desc URL of the gateway to which the request will be sent
      *
      * @var string
      */
      var $requestURL = '';

      /**
      *@desc Test URL of the gateway to which the request will be sent
      *
      * @var string
      */
      var $testURL = '';

      /**
      *@desc Version of API
      *
      * @var string
      */
      var $apiVersion = '';

      /**
      *@desc API user name or ID
      *
      *@var string
      */
      var $apiUserName = '';

      /**
      *@desc API key
      *
      *@var string
      */
      var $apiKey = '';

      /**
      *@desc API signature
      *
      *@var string
      */
      var $apiSignature = '';

      /**
      *@desc Turn on/off test mode
      *
      *@var bool
      */
      var $testMode = false;

      /**
      *@desc Turn on/off debug mode
      * @var bool
      */
      var $debug = true;

      /**
      *@desc If true cURL error will be shown with other errors
      * @var bool
      */
      var $showCurlError = false;

      /**
      *@desc Name on the credit card
      *
      *@var string
      */
      var $nameOnCard = '';

      /**
      *@desc Credit Card number
      *
      *@var string
      */
      var $cardNumber = 0;

      /**
      *@desc Month of expiration of the credit card
      *
      *@var int
      */
      var $expiryMonth = 0;

      /**
      *@desc Year of expiration of the credit card
      *
      *@var int
      */
      var $expiryYear = 0;

      /**
      *@desc Supported cards
      *
      *@var int
      */
      var $cardType = '';

      /**
      *@desc Amount to be charged
      *
      *@var float
      */
      var $amount = 0.00;

      /**
      *@desc Code for the currency
      *
      *@var string
      */
      var $currencyCode = 'USD';

      /**
      *@desc Supported currencies
      *
      * @var array
      */
      var $supportedCurrencies = array();

      /**
      *@desc Card verification number (optional)
      *
      *@var int
      */
      var $cvv = '';

      /**
      *@desc Code of country
      *
      *@var string
      */
      var $countryCode = 'US';

      /**
      *@desc Order number or invoice number
      *
      *@var string
      */
      var $invoiceNumber = '';

      /**
      *@desc For repeat transactions the invoice number of main order
      *
      * @var string
      */
      var $relatedInvoiceNumber = '';

      /**
      *@desc First name of the customer
      *
      *@var string
      */
      var $firstName = '';

      /**
      *@desc Last name of the customer
      *
      *@var string
      */
      var $lastName = '';

      /**
      *@desc Company name of the customer
      *
      *@var string
      */
      var $company = '';

      /**
      *@desc Billing address of the customer
      *
      *@var string
      */
      var $address1 = '';
      var $address2 = '';

      /**
      *@desc Billing city of the customer
      *
      *@var string
      */
      var $city = '';

      /**
      *@desc Billing state of the customer
      *
      *@var string
      */
      var $state = '';

      /**
      *@desc Billing zip code of the customer
      *
      *@var string
      */
      var $zip = '';

      /**
      *@desc Billing country of the customer
      *
      *@var string
      */
      var $country = '';

      /**
      *@desc Billing country code of the customer
      *
      *@var string
      */
      var $billingCountryCode = '';

      /**
      *@desc Shipping first name of the customer
      *
      *@var string
      */
      var $shippingFirstName = '';

      /**
      *@desc Shipping last name of the customer
      *
      *@var string
      */
      var $shippingLastName = '';

      /**
      *@desc Company name for shipping address
      *
      *@var string
      */
      var $shippingCompany = '';

      /**
      *@desc Shipping address of the customer
      *
      *@var string
      */
      var $shippingAddress1 = '';
      var $shippingAddress2 = '';

      /**
      *@desc Shipping city of the customer
      *
      *@var string
      */
      var $shippingCity = '';

      /**
      *@desc Shipping state of the customer
      *
      *@var string
      */
      var $shippingState = '';

      /**
      *@desc Shipping zip code of the customer
      *
      *@var string
      */
      var $shippingZip = '';

      /**
      *@desc Shipping country of the customer
      *
      *@var string
      */
      var $shippingCountry = '';

      /**
      *@desc Shipping country code of the customer
      *
      *@var string
      */
      var $shippingCountryCode = '';

      /**
      *@desc Phone number of the customer
      *
      *@var string
      */
      var $phone = '';

      /**
      *@desc Fax number of the customer
      *
      *@var string
      */
      var $fax = '';

      /**
      *@desc Email address of the customer
      *
      *@var string
      */
      var $email = '';

      /**
      *@desc Tax Amount
      *
      *@var float
      */
      var $tax = 0.00;

      /**
      *@desc IP Address of the client
      *
      *@var string
      */
      var $ipAddress = false;

      /**
      *@desc Response
      *
      *@var Response type oobject
      */
      var $response = null;

      /**
      *@desc Parameters for transaction
      *
      *@var array
      */
      var $params = array();

      /**
      *@desc Request prepared based on parameters
      *
      *@var string
      */
      var $requestString = '';

      /**
      *@desc Proxy host if using proxy
      *
      *@var string
      */
      var $proxyHost = '';

      /**
      *@desc Proxy port if using proxy
      *
      *@var string
      */
      var $proxyPort = '';

      /**
      *@desc Transaction ID
      *
      *@var string
      */
      var $transactionId = '';

      /**
      *@desc Authorization ID
      *
      *@var string
      */
      var $authorizationId = '';

      /**
      *@desc Used for SagePay refund
      *
      * @var string
      */
      var $securityCode = '';

      /**
      *@desc Comment/note (PayPal, Authorize.Net)
      *
      * @var string
      */
      var $note = '';

      /**
      *@desc Capture Complete Type. Possible values 'Complete' and 'NotComplete' (PayPal)
      */
      var $completeType = 'Complete';

      var $refundType = 'Partial';

      /**
      *@desc For storing text for debuging
      * @var string
      */
      var $debugText = '';

      /**
      *@desc For use when using cURL
      */
      var $sslCertificatate = '';

      /**
      *@desc header information
      */
      var $headers = '';

      /**
      *@desc header length used for mobile beanstream
      */
      var $appendHeaderLength = TRUE;

      /**
      *@desc Shipping charge
      */
      var $shippingAmount = 0;

      /*************************/
      # Variables for recurring #
      /*************************/

      /**
      *@desc Defines if the transaction is a recurring transaction or not
      */
      var $isRecuuringTransaction = false;

      /**
      *@desc Recurring profile ID
      */
      var $profileId = '';

      /**
      *@desc Recurring start date, Format: (YYYY-MM-DDThh:mm:ssZ). T separates date and time, Z end the date
      */
      var $startDate = '';

      /**
      *@desc Recurring start duration
      */
      var $startDuration = 0;

      /**
      *@desc Maximum failed transaction after which the recurring will stop
      */
      var $maximumFailedTransactionAllowed = '4';

      /**
      *@desc Billing unit - Day, Month, Week, SemiMonth, Year
      */
      var $billingUnit = 'Day';

      /**
      *@desc Number of billing periods that make up one billing cycle
      */
      var $billingFrequency = 0;

      /**
      *@desc The number of billing cycles for payment period
      */
      var $billingCycles = 0;

      /**
      *@desc Billing unit for trial - Day, Month, Week, SemiMonth, Year
      */
      var $trialBillingUnit = '';

      /**
      *@desc Number of billing periods that make up one trial billing cycle
      */
      var $trialBillingFrequency = 0;

      /**
      *@desc The number of billing cycles for trial payment period
      */
      var $trialBillingCycles = 0;

      /**
      *@desc Amount for trial period
      */
      var $trialAmount = 0;

      /**
      *@desc Custom variables
      */
      var $customVariable1, $customVariable2, $customVariable3, $customVariable4;

      /**
      *@desc specify weather we need to verify the ssl cert
      */
      var $verifiy_ssl = true;

      function __construct()
      {
          // Initializing response object
          $this->response = new Response();
      }

      /**
      *@desc Sends request to the payment gateway using and cURL
      *
      * @return array - Returns an array with raw response and cURL error and message
      */
      function makeAPICall()
      {          
        
		
          $url = $this->getRequestURL();
              
          if(empty($url))
          {
              $this->logMessage('Request URL is missing.');
              $this->response->setError('Request URL has not been set');
          }

          if($this->response->hasError())
          {
              $this->logMessage('Request could not be made because of errors.');
              return;
          }

          $this->logMessage('Preparing request...');
          $this->prepareRequest();
         
          $this->logMessage($this->requestString);
		  
		  

          $this->logMessage('Initializing cURL...');
          $ch = curl_init();

          if($this->requestMethod == 'POST')
          {
              $this->logMessage('Sending Request to : ' . $url);
              curl_setopt($ch, CURLOPT_URL, $url);
              curl_setopt($ch, CURLOPT_POST, 1);
              curl_setopt($ch, CURLOPT_POSTFIELDS, $this->requestString);
          }
          else
          {
              $url .= $this->requestString; //the URL should contain '?' if it is needed
              $this->logMessage('Sending Request to : ' . $url);
              curl_setopt($ch, CURLOPT_URL, $url);
              curl_setopt($ch, CURLOPT_POST, 0);
          }

          if ($this->proxyHost && $this->proxyPort)
          {
              curl_setopt($ch, CURLOPT_PROXY, $this->proxyHost . ':' . $this->proxyPort);
          }

          if(!empty($this->sslCertificatate))
          {
              $this->logMessage('Using SSL Certificate...' . $this->sslCertificatate);
              curl_setopt ($ch, CURLOPT_SSLCERT, $this->sslCertificatate);
          }

          if(empty($this->headers))
          {
              $this->headers = array();
          }
          if($this->appendHeaderLength)
          {
            $this->headers[] = "Content-Length: " . strlen($this->requestString);
          }
          curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);

          if($this->testMode)
          {
              curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
             // $this->response->isTestMode = 1;
          }
          else if(!$this->verifiy_ssl)
          {
              $this->logMessage('SSL in not verified as using internet secure gateway');
              curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
          }
          else
          {
              curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
          }

          curl_setopt($ch,CURLOPT_USERAGENT, $this->userAgent);
          curl_setopt($ch, CURLOPT_TIMEOUT, 60);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($ch, CURLOPT_VERBOSE, ($this->debug ? 1 : 0));
            
          $this->logMessage('Getting response...');
          $r = curl_exec($ch);
         
          
          $this->response->setRawResponse(curl_exec($ch));

          $this->logMessage(urldecode($this->response->getRawResponse()));

          if (curl_errno($ch))
          {
              $this->response->curlErrorNo = curl_errno($ch);
              $this->response->curlErrorMessage = curl_error($ch);
              $error = 'cURL Error # ' . $this->response->curlErrorNo . ' : ' . $this->response->curlErrorMessage;

              $this->logMessage($error);

              if($this->showCurlError)
              {
                  $this->response->setError($error);
              }
          }
          else
          {
              curl_close($ch);
          }
          $this->logMessage('Preparing response...');
          $this->prepareResponse();
          $this->logMessage("Done...\n----------------------------------------------------");
      }

      /**
      *@desc Stores keys and values
      *
      * @param string
      * @param string
      *
      * @return void
      */
      function setParam($key, $value)
      {
          !empty($key) ? ($this->params[$key] = $value) : '';
      }

      /**
      *@desc For creating a node
      *
      * @param string
      * @param string
      * @param bool
      *
      * @return void
      */
      function setNode($node, $value, $convert = false)
      {
          if(!empty($node) && !empty($value))
          {
              $value = $convert ? $this->convertForXML($value) : $value;
              $this->requestString .= "<$node>$value</$node>";
          }
      }

      /**
      *@desc For creating a start tag
      *
      * @param string
      * @param array
      *
      * @return void
      */
      function setStartTag($node, $attributes = '')
      {
          if(!empty($node))
          {
              if(!empty($attributes))
              {
                  $temp = array();
                  foreach($attributes as $key => $value)
                  {
                      $temp[] = $key . '="' . $value . '"';
                  }
                  $attributes = implode(' ', $temp);
                  $this->requestString .= "<$node $attributes>";
              }
              else
              {
                  $this->requestString .= "<$node>";
              }
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

      /**
      *@desc Sets headers for the request
      * @param array An array of headers
      * @param bool Resets previously set headers. Default is true.
      */
      function setHeaders($headers, $reset = true)
      {
          if(empty($this->headers))
          {
              $this->headers = $headers;
          }
          else if(is_array($this->headers))
          {
              $this->headers = array_merge($this->headers, $headers);
          }
      }
      /**
      *@desc Prepares request string form the parameters
      *
      * @return null
      */
      function prepareRequest()
      {
          if(!empty($this->params))
          {
              $nameValuePairs = array();

              foreach($this->params as $key => $value)
              {
                  if($value)
                  {
                      $nameValuePairs[] = $key .'=' . urlencode($value);
                      //$this->logMessage($key .'=' . urlencode($value));
                  }
              }
              $this->requestString = implode('&', $nameValuePairs);
          }
      }

      function resetParamList()
      {
          $this->params = array();
      }

      /**
      *@desc Returns the URL to which the request will be sent
      *
      * @return string
      */
      function getRequestURL()
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

      /**
      *@desc Prints the error
      *
      * @return null
      */
      function printError($error)
      {
          $html = '<div style="width:400px; margin:150px auto; border:2px solid #CC0000;">
                    <div style="background:#CC0000; padding:2px 5px; font-family:Arial, Helvetica, sans-serif; font-size:12px; font-weight:bold; color:#FFFFFF;">ERROR!</div>
                    <div style="margin:10px; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#000000;">'.$error.'</div>
                   </div>';
          $html = $this->wrapInHtmlMarkUp($html, 'Payment Library - Error!');
          die($html);
      }

      function wrapInHtmlMarkUp($content, $title = '')
      {
          $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                   <html xmlns="http://www.w3.org/1999/xhtml">
                        <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <title>' . $title . '</title>
                        </head>
                        <body>' . $content . '</body></html>';
          return $html;
      }
      /**
      *@desc Gets and sets the client IP Address
      *
      * @return null
      */
      function setIPAddress()
      {
          if ($this->ipAddress !== false)
          {
              return $this->ipAddress;
          }

          if (isset($_SERVER['REMOTE_ADDR']) AND isset($_SERVER['HTTP_CLIENT_IP']))
          {
              $this->ipAddress = $_SERVER['HTTP_CLIENT_IP'];
          }
          else if (isset($_SERVER['REMOTE_ADDR']))
          {
              $this->ipAddress = $_SERVER['REMOTE_ADDR'];
          }
          else if (isset($_SERVER['HTTP_CLIENT_IP']))
          {
              $this->ipAddress = $_SERVER['HTTP_CLIENT_IP'];
          }
          else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
          {
              $this->ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
          }

          if ($this->ipAddress === false)
          {
              $this->ipAddress = '0.0.0.0';
          }
          else if (strstr($this->ipAddress, ','))
          {
              $parts = explode(',', $this->ipAddress);
              $this->ipAddress = end($parts);
          }
      }

      function processNVPResponse($fieldSeperator = '&')
      {
          $responseArray = array();

          if($this->response->rawResponse)
          {
              $parts = explode($fieldSeperator, $this->response->rawResponse);
              if(count($parts))
              {
                  foreach($parts as $part)
                  {
                      list($key, $value) = explode('=', $part);
                      $key = strtoupper($key);
                      $responseArray[$key] = $value;
                  }
              }
          }

          return empty($responseArray) ? false : $responseArray;
      }

      function processXMLData()
      {
          $xmlParser = xml_parser_create();
          $index = '';
          xml_parse_into_struct($xmlParser,  $this->response->rawResponse, $xmlData, $index);
          xml_parser_free($xmlParser);
          return $xmlData;
      }

      /**
      *@desc Checks if card number is not empty
      */
      function validateCreditCardNumber()
      {
          if(!$this->testMode)
          {
              $cardValidator = new CardValidator();
              $cardValidator->cardNumber = $this->cardNumber;
              $cardValidator->cardType = $this->cardType;

              if(!$cardValidator->isValid())
              { 
                  $this->response->setError($cardValidator->message);
              }
          }
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

          $expiryDate = mktime ( 0, 0, 0, ($this->expiryMonth + 1), 1, $this->expiryYear );
          if(!($expiryDate > time ()))
          {   
              $this->response->setError('The credit card has expired');
          }
      }

      /**
      *@desc Checks if card holder name is not empty
      */
      function validateNameOnCard()
      {
          if(empty($this->nameOnCard))
          {
              $this->response->setError('Name on card (Card Holder Name) is missing');
          }
      }

      function validateCardSecurityCode()
      {
          if(empty($this->cvv))
          {
              $this->response->setError('Card Security Code is missing');
          }
          else if(!is_numeric($this->cvv))
          {
              $this->response->setError('Invalid Card Security Code');
          }
          else
          {
              $len = strlen($this->cvv);

              if($len < 3 || $len > 4)
              {
                  $this->response->setError('Invalid Card Security Code');
              }
          }
      }

      /**
      *@desc Checks if amount is not empty
      */
      function validateAmount()
      {
          if(empty($this->amount) || $this->amount < 0)
          {
              $this->response->setError('Invalid amount.');
          }
      }

      /**
      *@desc Checks if transaction ID has been saved or not
      */
      function validateTransactionID()
      {
          if(empty($this->transactionId))
          {
              $this->response->setError('Transaction ID is missing');
          }
      }

      /**
      *@desc Checks if authorization ID has been saved or not
      */
      function validateAuthorizationID()
      {
          if(empty($this->authorizationId))
          {
              $this->response->setError('Authorization ID is missing');
          }
      }

      /**
      *@desc Checks if the currency code is missing or not or supported by the gateway
      */
      function validateCurrency()
      {                                   
          if(empty($this->currencyCode))
          {
              $this->response->setError('Currency code is missing');
          }
          else
          {
              if(!in_array($this->currencyCode, $this->supportedCurrencies))
              {
                  $this->response->setError('The currency code "' . $this->currencyCode . '" is not supported by the gateway');
              }
          }
      }

      function validateInvoiceNumber()
      {
          if(empty($this->invoiceNumber))
          {
              $this->response->setError('Invoice Number is missing');
          }
      }

      /**
      *@desc This function is for sending authorization request. Gateway classes will have to implement them to work.
      */
      function authorize()
      {
          $this->printError('authorize() method has not been implemented');
      }

      /**
      *@desc This function is for sending capture request. Gateway classes will have to implement them to work.
      */
      function capture()
      {
          $this->printError('capture() method has not been implemented');
      }

      /**
      *@desc This function is for sending direct payment request. Gateway classes will have to implement them to work.
      */
      function sale()
      {
          $this->printError('sale() method has not been implemented');
      }

      /**
      *@desc This function is for sending refund request. Gateway classes will have to implement them to work.
      */
      function refund()
      {
          $this->printError('refund() method has not been implemented');
      }

      function createRecurringProfile()
      {
          $this->printError('createRecurringProfile() method has not been implemented');
      }

      function getRecurringProfileDetails()
      {
          $this->printError('getRecurringProfileDetails() method has not been implemented');
      }

      /*function saleWithRecurring()
      {
          $this->printError('getRecurringProfileDetails() method has not been implemented');
      }*/

      /**
      *@desc This function is for preparing basic response. Gateway classes will have to implement them to work.
      * Gateway classes may use different functions to prepare response but must implement this function.
      */
      function prepareResponse()
      {
          $this->printError('prepareResponse() method has not been implemented');
      }

      /**
      *@desc Enables test mode
      */
      function enableTestMode()
      {
          $this->testMode = true;
      }

      /**
      *@desc Enables debugging
      */
      function enableDebugging()
      {
          $this->debug = true;
      }

      /**
      *@desc For logging debug messages
      *
      * @param string
      */
      function logMessage($message)
      {   
          if($this->debug)
          {
             Log::info($message);
          }
      }

      function getTwoDigitExpiryYear()
      {
          $len = strlen($this->expiryYear);

          return $len > 2 ? substr($this->expiryYear, $len - 2) : $this->expiryYear;
      }

      /**
      *@desc Copy of xml_convert function of CI XML helper
      * Convert Reserved XML characters to Entities
      *
      * @access   public
      * @param    string
      * @return   string
      */
      function convertForXML($str)
      {
          $temp = '__TEMP_AMPERSANDS__';

          // Replace entities to temporary markers so that
          // ampersands won't get messed up
          $str = preg_replace("/&#(\d+);/", "$temp\\1;", $str);
          $str = preg_replace("/&(\w+);/",  "$temp\\1;", $str);

          $str = str_replace(array("&","<",">","\"", "'", "-"),
                       array("&amp;", "&lt;", "&gt;", "&quot;", "&#39;", "&#45;"),
                       $str);

          // Decode the temp markers back to entities
          $str = preg_replace("/$temp(\d+);/","&#\\1;",$str);
          $str = preg_replace("/$temp(\w+);/","&\\1;", $str);

          return $str;
      }
  }
?>
