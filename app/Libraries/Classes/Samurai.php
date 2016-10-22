<?php
/**
 * @filename        Samurai.php
 * @description     This class is for making payment through Quantum Gateway (Non-interactive method)
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Masuduzzaman
 *
 * @link
 * @created on      November 21, 2011
 * @Dependencies
 * @license
 ***/
  class Samurai extends PaymentGateway
  {
      
	  var $responseDelimiter = ' ';

      function SAMURAI()
      {
          parent::PaymentGateway();
          $this->testURL = 'https://api.samurai.feefighters.com/v1/payment_methods';
		  $this->requestURL = 'https://api.samurai.feefighters.com/v1/payment_methods';		   
		  $this->supportedCurrencies = array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD', 'ZAR');
      }

      function initialize()
      {	       
          //$this->setParam('merchant_key', $this->apiKey);		  
		  $this->setParam('merchant_key', $this->apiUserName);	
		  $this->setParam('redirect_url','https://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']);			  	  
		  	    	   
      }

      function setCardParameters()
      {          
          $this->setParam('credit_card[card_number]', $this->cardNumber);
		  $this->setParam('credit_card[cvv]', $this->cvv);	  
          $this->setParam('credit_card[expiry_month]',str_pad($this->expiryMonth, 2, '0', STR_PAD_LEFT));
		  $this->setParam('credit_card[expiry_year]',$this->expiryYear);
      }

      function setBillingInformation()
      {
          $this->logMessage('Initializing billing parameters...');          
		  $this->setParam('credit_card[first_name]', $this->firstName);
          $this->setParam('credit_card[last_name]', $this->lastName);	
		  $this->setParam('credit_card[address_1]', trim($this->address1 . ' ' . $this->address2));
          $this->setParam('credit_card[city]', $this->city);
          $this->setParam('credit_card[state]', $this->state);
          $this->setParam('credit_card[zip]', $this->zip);      
      }   
     

	  function gettoken()
	  {
	      $this->logMessage('Preparing Token request samurai...');
          $this->validateBasicInput();
          $this->validateCreditCardNumber();
          $this->validateExpiryDate();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }
          
         $this->logMessage('Setting parameters for token request...');	 
		 $this->initialize();
		 $this->setCardParameters();
         $this->setBillingInformation();    
		 
		 $this->makeAPICall();
         return $this->response;
	  
	  }
	  
	   function authorize()
       {
		  $response = $this->gettoken();	
		  $token = (explode('=',$response->rawResponse));
		  $t2 = explode('"',$token[2]);	

          if($this->response->hasError())
          {
              return $this->response;
          }
		  
		  $this->testURL = 'https://api.samurai.feefighters.com/v1/processors/'.$this->apiSignature.'/authorize.xml';
		  $this->requestURL = 'https://api.samurai.feefighters.com/v1/processors/'.$this->apiSignature.'/authorize.xml';
          
          $this->logMessage('Setting parameters for authorize request...');
          $this->setParam('transaction[amount]', $this->amount);
		  $this->setParam('transaction[currency_code]', $this->currencyCode);		 
          $this->setParam('transaction[type]','authorize');
		  $this->setParam('transaction[payment_method_token]', trim($t2[0]));
		
		  $this->makeAPICall();
          return $this->response;
      }
	  
	  function capture()
      {
          $this->logMessage('Preparing capture request...');
          $this->validateTransactionID();
          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }

          
          $this->logMessage('Setting parameters for capture request...');
          $this->initialize();
		 
    	  $this->testURL = 'https://api.samurai.feefighters.com/v1/transactions/'.$this->transactionId.'/capture.xml';
		  $this->requestURL = 'https://api.samurai.feefighters.com/v1/transactions/'.$this->transactionId.'/capture.xml';
		  
          $this->setParam('transaction[amount]', $this->amount);
          

          $this->makeAPICall();
          return $this->response;
      }
	  
      function sale()
      {      
		$response = $this->gettoken();	
		$token = (explode('=',$response->rawResponse));
		$t2 = explode('"',$token[2]);		
		$this->logMessage('Preparing sale request...');
          
        if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }         
		 		  
         $this->logMessage('Setting parameters for sale request...');
		 $this->testURL = 'https://api.samurai.feefighters.com/v1/processors/'.$this->apiSignature.'/purchase.xml';
		 $this->requestURL = 'https://api.samurai.feefighters.com/v1/processors/'.$this->apiSignature.'/purchase.xml';		
 		 $this->setParam('transaction[amount]', $this->amount);
		 $this->setParam('transaction[currency_code]', $this->currencyCode);
		 $this->setParam('transaction[type]', 'purchase');
		 $this->setParam('transaction[payment_method_token]', trim($t2[0]));
		   
         $this->makeAPICall();
         return $this->response;
      }

      function refund()
      {
          $this->logMessage('Preparing refund request...');
                    
          $this->validateTransactionID();

          if($this->response->hasError())
          {
              $this->logMessage('Error found...<br />' . $this->response->getErrorString('ul'));
              return $this->response;
          }
		  $this->testURL = 'https://api.samurai.feefighters.com/v1/transactions/'.$this->transactionId.'/reverse.xml';
		  $this->requestURL = 'https://api.samurai.feefighters.com/v1/transactions/'.$this->transactionId.'/reverse.xml';
		  
          $this->logMessage('Setting parameters for refund request...');
          $this->setParam('transaction[amount]', $this->amount);
		  
          $this->makeAPICall();
          return $this->response;
      }

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
			 			 
			  if(isset($responseArray['TRANSACTION_TOKEN']))
			  {
			  $this->response->success = 1;
              $this->response->ack = ACK_SUCCESS;          
              $this->response->transactionId = $responseArray['TRANSACTION_TOKEN'];
              $this->response->amount = $responseArray['AMOUNT'];
              $this->response->transactionType = $responseArray['TRANSACTION_TYPE'];
			  }			  
          }
		  
          $this->response->currency = $this->currencyCode;
	    
      }

      /**
      *@desc Basic input validation
      */
      function validateBasicInput()
      {
          if(empty($this->apiKey) )
          {
              $this->response->setError('merchant key has not been configured.');
          }

          
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
		  
		  curl_setopt($ch, CURLOPT_USERPWD,''.$this->apiUserName.':'.$this->apiKey.'');

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
              curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
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