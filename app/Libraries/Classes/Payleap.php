<?php
/**
 * @filename        Payleap.php
 * @description     This class is for making payment through Payleap
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Masuduzzaman
 *
 * @link
 * @created on      April 26, 2012
 * @Dependencies
 * @license
 ***/
  class Payleap extends PaymentGateway
  {
      var $requesType = 'SALE';

      function Payleap()
      {
          parent::PaymentGateway();
          $this->requestURL = 'https://secure1.payleap.com/TransactServices.svc/ProcessCreditCard';
          $this->testURL = 'https://uat.payleap.com/TransactServices.svc/ProcessCreditCard';
          $this->supportedCurrencies = array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD', 'ZAR');
      }

      function initialize()
      {
          $this->logMessage('Initializing basic parameters...');
          $this->setParam('UserName', $this->apiUserName);
          $this->setParam('Password', $this->apiKey);

      }

      function setCardParameters()
      {
          $this->logMessage('Initializing card parameters...');
          $this->setParam('CardNum', $this->cardNumber);
          $this->setParam('ExpDate',str_pad($this->expiryMonth, 2, '0', STR_PAD_LEFT).substr($this->expiryYear,-2));
          $this->setParam('CVNum', $this->cvv);          
          $this->setParam('NameOnCard',$this->nameOnCard);
          $this->setParam('Amount', $this->amount);
          if($this->magData)
		     $this->setParam('MagData', $this->magData);
      }

      function setBillingInformation()
      {
          $this->logMessage('Initializing billing parameters...');
          $extdata = '<Invoice><InvNum>'.$this->invoiceNumber.'</InvNum><BillTo><Name>'.$this->firstName.'  '.$this->lastName.'</Name><Address><Street>'.$this->address1.'</Street><City>'.$this->city.'</City><State>'.$this->state.'</State><Zip>'.$this->zip.'</Zip><Country>'.$this->country.'</Country></Address><Email>'.$this->email.'</Email><Phone>'.$this->phone.'</Phone></BillTo></Invoice>';
          $this->setParam('ExtData', $extdata);
      }

      function authorize()
      {
          $this->logMessage('Preparing authorize request...');
          $this->validateBasicInput();
          $this->validateCreditCardNumber();
          $this->validateExpiryDate();

          if($this->response->hasError())
          {
              return $this->response;
          }

          $this->requesType = 'AUTH';
          $this->logMessage('Setting parameters for authorize request...');
          $this->initialize();
          $this->setParam('TransType','AUTH');
          $this->setCardParameters();
          $this->setBillingInformation();
          $this->makeAPICall();
          return $this->response;
      }

      function capture()
      {
          $this->logMessage('Preparing capture request...');
          $this->validateBasicInput();
          $this->validateTransactionID();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->requesType = 'Capture';

          $this->logMessage('Setting parameters for capture request...');
          $this->initialize();
          $this->setParam('TransType','Capture');
          $this->setParam('PNRef',$this->transactionId);
          $this->setCardParameters();
          $this->setBillingInformation();

          $this->makeAPICall();
          return $this->response;
      }

      function sale()
      {
          $this->logMessage('Preparing sale request...');
          $this->validateBasicInput();
          $this->validateCreditCardNumber();
          $this->validateExpiryDate();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->requesType = 'SALE';
          $this->logMessage('Setting parameters for sale request...');
          $this->initialize();
          $this->setParam('TransType','SALE');
          $this->setCardParameters();
          $this->setBillingInformation();
          $this->makeAPICall();
          return $this->response;
      }

      function refund()
      {
          $this->logMessage('Preparing refund request...');
          $this->validateBasicInput();
          $this->validateCreditCardNumber();

          $this->validateTransactionID();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          $this->logMessage('Setting parameters for refund request...');
          $this->requesType = 'RETURN';
          $this->initialize();
          $this->setParam('TransType','RETURN');
          $this->setCardParameters();
          $this->setParam('PNRef',$this->transactionId);
          $this->makeAPICall();
          return $this->response;
      }
       function prepareResponse(){

          $xml = new DOMDocument();
          if($xml->loadXML($this->response->getRawResponse())){
              $responseValues = array();

              foreach($xml->childNodes as $node){
                  if($node->hasChildNodes()){
                      foreach($node->childNodes as $childNode){
                          if ($childNode->nodeType != XML_TEXT_NODE){
                              $responseValues[$childNode->nodeName] = trim($childNode->nodeValue);
                          }
                      }
                  }
              }


              if($responseValues['RespMSG'] == 'Approved'){
                  $this->response->success = 1;
                  $this->response->ack = ACK_SUCCESS;
                  }

                  else
                  {
                     $this->response->ack = ACK_FAILURE;
                     $this->response->success = 0;
                     $this->response->setError($responseValues['RespMSG']);
                  }
			   
              $this->response->authorizationId = $responseValues['AuthCode'];
              $this->response->transactionId = $responseValues['PNRef'];
              $this->response->amount = $this->amount;
          }
          else{
              $this->response->ack = ACK_FAILURE;
              $this->response->setError($this->response->getRawResponse());
          }
      }

      /**
      *@desc Basic input validation
      */
      function validateBasicInput()
      {
          if(empty($this->apiUserName) || empty($this->apiKey) )
          {
              $this->response->setError('Payleap login credentials have not been configured.');
          }

          $this->validateAmount();
      }
	  
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
		  //$this->requestString = str_replace('a%2Ab','',$this->requestString);
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
		  
		 // this below line is used only for samurai payment gateway.
		 //curl_setopt($ch, CURLOPT_USERPWD,'902375b3e1fa4df61e3ae54b:89762ea19b767892e5733e0e');

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

          $this->headers[] = "Content-Length: " . strlen($this->requestString);
          curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);

          if($this->testMode)
          {
              curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
              $this->response->isTestMode = 1;
          }
          else
          {
              if($this->ignorePeerVarification)
              {
                  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
              }
              else
              {
                  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
                  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
              }
          }

          curl_setopt($ch,CURLOPT_USERAGENT, $this->userAgent);
          curl_setopt($ch, CURLOPT_TIMEOUT, 60);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($ch, CURLOPT_VERBOSE, ($this->debug ? 1 : 0));

          $this->logMessage('Getting response...');
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
          $this->logMessage('Done...');
      }
  }
?>