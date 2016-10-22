<?php
/**
 * @filename        Cardsave.php
 * @description     This class is for making payment through Cardsave
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Masuduzzaman
 *
 * @link
 * @created on      July 10, 2012
 * @Dependencies
 * @license
 ***/
  class Cardsave extends PaymentGateway
  {
      var $requesType = 'SALE';

      function Cardsave()
      {
          parent::PaymentGateway();
          $this->requestURL = 'https://gw1.cardsaveonlinepayments.com:4430/';
          $this->testURL = 'https://gw1.cardsaveonlinepayments.com:4430/';
          $this->supportedCurrencies = array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD', 'ZAR');
      }

      function initialize()
      {
          $this->logMessage('Initializing basic parameters...');
          $this->setParam('instId', $this->apiKey);
		  $this->setParam('amount', $this->amount*100);
		  $this->setParam('currency', 'GBP');
      }

      function setCardParameters()
      {
          $this->logMessage('Initializing card parameters...');
      }

      function setBillingInformation()
      {

	   $this->setParam('testMode', '100');
	   $this->setParam('cartId', '101KT0098');
	   $this->setParam('name', 'masud');
	   $this->setParam('address1', ' The Street');
	   $this->setParam('address1', 'My Suburb');
	   $this->setParam('town', 'mytown');
	   $this->setParam('region', 'my region or county');
	   $this->setParam('country', 'GB');
	   $this->setParam('tel', '0123456789');
	   $this->setParam('email', 'enggmasud1983@gmail.com');


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
	  function convertCurrencyCode()
	  {
	  $currencyCode = $this->currencyCode;
	  switch($currencyCode)
          {
			  case 'GBP':
			  return '826';
			  case 'USD':
			  return '840';
			  case 'EUR':
			  return '978';
			  case 'AUD':
			  return '036';
			  case 'CAD':
			  return '124';
		 }

	  }
      function sale()
      {
		$currencyCode = $this->convertCurrencyCode();
	    $xml = new DOMDocument("1.0", "UTF-8");
		$paymentService = $xml->createElement('soap:Envelope');

		$paymentService->appendChild(
		$xml->createAttribute('xmlns:soap'))->appendChild(
		$xml->createTextNode('http://schemas.xmlsoap.org/soap/envelope/'));

		$paymentService->appendChild(
		$xml->createAttribute('xmlns:xsi'))->appendChild(
		$xml->createTextNode('http://www.w3.org/2001/XMLSchema-instance'));

		$paymentService->appendChild(
		$xml->createAttribute('xmlns:xsd'))->appendChild(
		$xml->createTextNode('http://www.w3.org/2001/XMLSchema'));


		$soapBody = $xml->createElement('soap:Body');
		$CardDetailsTransaction = $xml->createElement('CardDetailsTransaction');
		$CardDetailsTransaction->appendChild(
		$xml->createAttribute('xmlns'))->appendChild(
		$xml->createTextNode('https://www.thepaymentgateway.net/'));

		$PaymentMessage = $xml->createElement('PaymentMessage');
		$TransactionDetails = $xml->createElement('TransactionDetails');
	    $PaymentMessage->appendChild($TransactionDetails);

		$TransactionDetails->appendChild(
		$xml->createAttribute('Amount'))->appendChild(
		$xml->createTextNode($this->amount*100));

		$TransactionDetails->appendChild(
		$xml->createAttribute('CurrencyCode'))->appendChild(
		$xml->createTextNode($currencyCode));

		//messageDetails.
		$MessageDetails = $xml->createElement('MessageDetails');
	    $TransactionDetails->appendChild($MessageDetails);
		$MessageDetails->appendChild(
		$xml->createAttribute('TransactionType'))->appendChild(
		$xml->createTextNode('SALE'));

		$TransactionControl = $xml->createElement('TransactionControl');

		$ThreeDSecureOverridePolicy = $xml->createElement('ThreeDSecureOverridePolicy','true');
		$TransactionControl->appendChild($ThreeDSecureOverridePolicy);

		$DuplicateDelay = $xml->createElement('DuplicateDelay','20');
		$TransactionControl->appendChild($DuplicateDelay);
		$EchoCardType = $xml->createElement('EchoCardType','true');
		$TransactionControl->appendChild($EchoCardType);
		$EchoAVSCheckResult = $xml->createElement('EchoAVSCheckResult','true');
		$TransactionControl->appendChild($EchoAVSCheckResult);
		$EchoCV2CheckResult = $xml->createElement('EchoCV2CheckResult','true');
		$TransactionControl->appendChild($EchoCV2CheckResult);
		$EchoAmountReceived = $xml->createElement('EchoAmountReceived','true');
		$TransactionControl->appendChild($EchoAmountReceived);
		$TransactionDetails->appendChild($TransactionControl);

		$OrderID = $xml->createElement('OrderID',$this->invoiceNumber);
		$TransactionDetails->appendChild($OrderID);
		$OrderDescription = $xml->createElement('OrderDescription','desccrrr');
		$TransactionDetails->appendChild($OrderDescription);
		$PaymentMessage->appendChild($TransactionDetails);

		//card details
		$CardDetails = $xml->createElement('CardDetails');
		$CardName = $xml->createElement('CardName',$this->nameOnCard);
		$CardDetails->appendChild($CardName);
		$CardNumber = $xml->createElement('CardNumber',$this->cardNumber);
		$CardDetails->appendChild($CardNumber);

		$ExpiryDate = $xml->createElement('ExpiryDate');
		$ExpiryDate->appendChild(
		$xml->createAttribute('Month'))->appendChild(
		$xml->createTextNode(str_pad($this->expiryMonth, 2, '0', STR_PAD_LEFT)));
		$ExpiryDate->appendChild(
		$xml->createAttribute('Year'))->appendChild(
		$xml->createTextNode(substr($this->expiryYear,-2)));
		$CardDetails->appendChild($ExpiryDate);

		$CV2 = $xml->createElement('CV2',$this->cvv);
		$CardDetails->appendChild($CV2);
		$PaymentMessage->appendChild($CardDetails);

		$CustomerDetails = $xml->createElement('CustomerDetails');
		//billing address
		$billingaddress = $xml->createElement('billingaddress');
		$address1 = $xml->createElement('address1',$this->address1);
		$billingaddress->appendChild($address1);
		$city = $xml->createElement('city',$this->city);
		$billingaddress->appendChild($city);
		$state = $xml->createElement('state',$this->state);
		$billingaddress->appendChild($state);
		$postcode = $xml->createElement('postcode',$this->zip);
		$billingaddress->appendChild($postcode);
		$CustomerDetails->appendChild($billingaddress);

		$EmailAddress = $xml->createElement('EmailAddress',$this->email);
		$CustomerDetails->appendChild($EmailAddress);
		$PhoneNumber = $xml->createElement('PhoneNumber',$this->phone);
		$CustomerDetails->appendChild($PhoneNumber);
		$PaymentMessage->appendChild($CustomerDetails);
		//merchant authentication.
		$MerchantAuthentication = $xml->createElement('MerchantAuthentication');
		$PaymentMessage->appendChild($MerchantAuthentication);
	    $MerchantAuthentication->appendChild(
		$xml->createAttribute('MerchantID'))->appendChild(
		$xml->createTextNode($this->apiUserName));
		$MerchantAuthentication->appendChild(
		$xml->createAttribute('Password'))->appendChild(
		$xml->createTextNode($this->apiKey));

		$CardDetailsTransaction->appendChild($PaymentMessage);
		$soapBody->appendChild($CardDetailsTransaction);

		$paymentService->appendChild($soapBody);
		$xml->appendChild($paymentService);
		$this->requestString = $xml->saveXML();

	    $this->makeAPICall();
		return $this->response;


      }

      function refund()
      {
		$currencyCode = $this->convertCurrencyCode();
		$xml = new DOMDocument("1.0", "UTF-8");
		$paymentService = $xml->createElement('soap:Envelope');

		$paymentService->appendChild(
		$xml->createAttribute('xmlns:soap'))->appendChild(
		$xml->createTextNode('http://schemas.xmlsoap.org/soap/envelope/'));

		$paymentService->appendChild(
		$xml->createAttribute('xmlns:xsi'))->appendChild(
		$xml->createTextNode('http://www.w3.org/2001/XMLSchema-instance'));

		$paymentService->appendChild(
		$xml->createAttribute('xmlns:xsd'))->appendChild(
		$xml->createTextNode('http://www.w3.org/2001/XMLSchema'));
		$soapBody = $xml->createElement('soap:Body');
		$CardDetailsTransaction = $xml->createElement('CardDetailsTransaction');
		$CardDetailsTransaction->appendChild(
		$xml->createAttribute('xmlns'))->appendChild(
		$xml->createTextNode('https://www.thepaymentgateway.net/'));

		$PaymentMessage = $xml->createElement('PaymentMessage');
		$TransactionDetails = $xml->createElement('TransactionDetails');
	    $PaymentMessage->appendChild($TransactionDetails);

		$TransactionDetails->appendChild(
		$xml->createAttribute('Amount'))->appendChild(
		$xml->createTextNode($this->amount*100));

		$TransactionDetails->appendChild(
		$xml->createAttribute('CurrencyCode'))->appendChild(
		$xml->createTextNode($currencyCode));

		$OrderID = $xml->createElement('OrderID',$this->invoiceNumber);
		$TransactionDetails->appendChild($OrderID);

		//messageDetails.
		$MessageDetails = $xml->createElement('MessageDetails');
	    $TransactionDetails->appendChild($MessageDetails);
		$MessageDetails->appendChild(
		$xml->createAttribute('TransactionType'))->appendChild(
		$xml->createTextNode('REFUND'));
		$MessageDetails->appendChild(
		$xml->createAttribute('CrossReference'))->appendChild(
		$xml->createTextNode($this->transactionId));

		$PaymentMessage->appendChild($TransactionDetails);
		//merchant authentication.
		$MerchantAuthentication = $xml->createElement('MerchantAuthentication');
		$PaymentMessage->appendChild($MerchantAuthentication);
	    $MerchantAuthentication->appendChild(
		$xml->createAttribute('MerchantID'))->appendChild(
		$xml->createTextNode($this->apiUserName));
		$MerchantAuthentication->appendChild(
		$xml->createAttribute('Password'))->appendChild(
		$xml->createTextNode($this->apiKey));
		//merchant authentication.

		//card details
		$CardDetails = $xml->createElement('CardDetails');
		$CardName = $xml->createElement('CardName',$this->nameOnCard);
		$CardDetails->appendChild($CardName);
		$CardNumber = $xml->createElement('CardNumber',$this->cardNumber);
		$CardDetails->appendChild($CardNumber);

		$ExpiryDate = $xml->createElement('ExpiryDate');
		$ExpiryDate->appendChild(
		$xml->createAttribute('Month'))->appendChild(
		$xml->createTextNode(str_pad($this->expiryMonth, 2, '0', STR_PAD_LEFT)));
		$ExpiryDate->appendChild(
		$xml->createAttribute('Year'))->appendChild(
		$xml->createTextNode(substr($this->expiryYear,-2)));
		$CardDetails->appendChild($ExpiryDate);
		$CV2 = $xml->createElement('CV2',$this->cvv);
		$CardDetails->appendChild($CV2);
		$PaymentMessage->appendChild($CardDetails);
		//card details
		$CardDetailsTransaction->appendChild($PaymentMessage);
		$soapBody->appendChild($CardDetailsTransaction);

		$paymentService->appendChild($soapBody);
		$xml->appendChild($paymentService);
		$this->requestString = $xml->saveXML();

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

								if($childNode->hasChildNodes()){
								 foreach($childNode->childNodes as $grandchildNodes){

									if($grandchildNodes->hasChildNodes()){
									foreach($grandchildNodes->childNodes as $putichildNodes){

									if ($putichildNodes->nodeType != XML_TEXT_NODE){
									  $responseValues[$putichildNodes->nodeName] = trim($putichildNodes->nodeValue);
								  }

								 }
								 }
								}
								}
							}
                  }
              }


              if($responseValues['StatusCode']==0){
                  $this->response->success = 1;
                  $this->response->ack = ACK_SUCCESS;
                  }
                  else
                  {
                  $this->response->ack = ACK_FAILURE;
				  $this->response->success = 0;
                  $this->response->setError($responseValues['Message']);
                  }

				if(isset($responseValues['AuthCode']))
                {
                    $this->response->authorizationId = $responseValues['AuthCode'];
                    $this->response->transactionId = $responseValues['AuthCode'];
                }


				if(isset($responseValues['AmountReceived']))
				$this->response->amount = $responseValues['AmountReceived']/100;
				else
				$this->response->amount = $this->amount/100;

				if(isset($responseValues['CardType']))
				$this->response->cardType = $responseValues['CardType'];


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
          if(empty($this->apiKey) )
          {
			$this->response->setError('WorldPay Installation ID have not been configured.');
          }

          $this->validateAmount();
      }

	function makeAPICall()
      {
	  $szUserAgent = 'ThePaymentGateway SOAP Library PHP';
	  $this->prepareRequest();
      $this->logMessage($this->requestString);
      $this->logMessage('Initializing cURL...');
	  $cURL = curl_init();


		    	//http settings
		    	$HttpHeader[] = 'SOAPAction:'.'https://www.thepaymentgateway.net/CardDetailsTransaction';
		    	$HttpHeader[] = 'Content-Type: text/xml; charset = utf-8';
		    	$HttpHeader[] = 'Connection: close';

	        	curl_setopt($cURL, CURLOPT_HEADER, false);
	        	curl_setopt($cURL, CURLOPT_HTTPHEADER, $HttpHeader);
	        	curl_setopt($cURL, CURLOPT_POST, true);
	        	curl_setopt($cURL, CURLOPT_URL, 'https://gw1.cardsaveonlinepayments.com:4430/');
	        	curl_setopt($cURL, CURLOPT_USERAGENT, $szUserAgent);
	        	curl_setopt($cURL, CURLOPT_POSTFIELDS, $this->requestString);
	        	curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
	        	curl_setopt($cURL, CURLOPT_ENCODING, "UTF-8");
	        	curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, false);
	        	curl_setopt($cURL, CURLOPT_TIMEOUT, 60);

				$szString = curl_exec($cURL);
				$errorNo = curl_errno($cURL);//test
				$errorMsg = curl_error($cURL);//test
				$header = curl_getinfo($cURL);//test

				$this->m_szLastResponse = $szString;
				$this->response->setRawResponse($szString);

				$szString = str_replace("<soap:Body>", '" "', $szString);
				$szString = str_replace("</soap:Body>", '" "', $szString);
				$this->response->setRawResponse($szString);


				$this->logMessage(urldecode($this->response->getRawResponse()));

          if ($errorNo)
          {
              $this->response->curlErrorNo = $errorNo;
              $this->response->curlErrorMessage = $errorMsg;
              $error = 'cURL Error # ' . $this->response->curlErrorNo . ' : ' . $this->response->curlErrorMessage;

              $this->logMessage($error);

              if($this->showCurlError)
              {
                  $this->response->setError($error);
              }
          }
          else
          {
              curl_close($cURL);
          }
          $this->logMessage('Preparing response...');
          $this->prepareResponse();
          $this->logMessage('Done...');

	}



  }
?>