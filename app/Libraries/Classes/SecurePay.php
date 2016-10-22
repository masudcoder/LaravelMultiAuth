<?php
/**
 * @filename        SecurePay.php
 * @description     Payment gateway
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Masuduzzaman
 *
 * @created on      May 10, 2012
 * @Dependencies    PaymentGateway
 * @license
 ***/
  class SecurePay extends PaymentGateway
  {
      var $requestType = 'Payment';

      function SecurePay(){
          parent::PaymentGateway();
          $this->apiVersion = 'xml-4.2';
          $this->requestURL = 'https://api.securepay.com.au/xmlapi/payment';
          $this->testURL = 'https://test.securepay.com.au/xmlapi/payment';
          $this->supportedCurrencies = array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD', 'ZAR');
          $this->headers = array('Content-Type: application/xml; charset=UTF-8',
                                'Accept: application/xml');
      }

      /**
      *@desc Sends request for Authorization
      *
      * @return Response type object
      */
      function authorize(){
          $this->requestType = 'authorize';

      }

      /**
      *@desc Sends request for Capture (Prior Authorization)
      */
      function capture(){
          $this->requestType = 'capture';

      }

      /**
      *@desc Sends request for payment
      *
      * @return Response type object
      */
      function sale(){
	  	$xml = new DOMDocument("1.0", "UTF-8");
		$transaction = $xml->createElement('SecurePayMessage');

		// message info
		$messageInfo = $xml->createElement('MessageInfo');
		$apiVersion = $xml->createElement('apiVersion','xml-4.2');
		$messageInfo->appendChild($apiVersion);
		$transaction->appendChild($messageInfo);

		// merchant info
		$merchantInfo = $xml->createElement('MerchantInfo');
		$merchantID = $xml->createElement('merchantID',$this->apiUserName);
		$merchantInfo->appendChild($merchantID);
		$password = $xml->createElement('password',$this->apiKey);
		$merchantInfo->appendChild($password);
		$transaction->appendChild($merchantInfo);

	   //request type
		$requestType = $xml->createElement('RequestType','Payment');
		$transaction->appendChild($requestType);

	    //Payment
		$payment = $xml->createElement('Payment');
		$txnList = $xml->createElement('TxnList');
		$txnList->appendChild(
		$xml->createAttribute('count'))->appendChild(
		$xml->createTextNode('1'));

		$txn = $xml->createElement('Txn');
		$txn->appendChild(
		$xml->createAttribute('ID'))->appendChild(
		$xml->createTextNode('1'));

		$txnType = $xml->createElement('txnType','0');
		$txn->appendChild($txnType);
		$txnSource = $xml->createElement('txnSource','23');
		$txn->appendChild($txnSource);
		$amount = $xml->createElement('amount',$this->amount*100);
		$txn->appendChild($amount);
		$currency = $xml->createElement('currency',$this->currencyCode);
		$txn->appendChild($currency);

		$purchaseOrderNo = $xml->createElement('purchaseOrderNo',$this->invoiceNumber);
		$txn->appendChild($purchaseOrderNo);

		//creditCardInfo
		$CreditCardInfo = $xml->createElement('CreditCardInfo');
		$cardNumber = $xml->createElement('cardNumber',$this->cardNumber);
		$CreditCardInfo->appendChild($cardNumber);
		$cvv = $xml->createElement('cvv',$this->cvv);
		$CreditCardInfo->appendChild($cvv);
		$expiryDate = $xml->createElement('expiryDate',str_pad($this->expiryMonth, 2, '0', STR_PAD_LEFT).'/'.substr($this->expiryYear,-2));
		$CreditCardInfo->appendChild($expiryDate);
		$txn->appendChild($CreditCardInfo);

		$txnList->appendChild($txn);
		$payment->appendChild($txnList);
		$transaction->appendChild($payment);
		$xml->appendChild($transaction);

		$this->requestString = $xml->saveXML();

        $this->makeAPICall();
		return $this->response;
      }

      /**
      *@desc Sends refund request
      *
      * @return Response type object
      */
      function refund(){
	    $xml = new DOMDocument("1.0", "UTF-8");
		$transaction = $xml->createElement('SecurePayMessage');

		// message info
		$messageInfo = $xml->createElement('MessageInfo');
		$apiVersion = $xml->createElement('apiVersion','xml-4.2');
		$messageInfo->appendChild($apiVersion);
		$transaction->appendChild($messageInfo);

		// merchant info
		$merchantInfo = $xml->createElement('MerchantInfo');
		$merchantID = $xml->createElement('merchantID',$this->apiUserName);
		$merchantInfo->appendChild($merchantID);
		$password = $xml->createElement('password',$this->apiKey);
		$merchantInfo->appendChild($password);
		$transaction->appendChild($merchantInfo);

	   //request type
		$requestType = $xml->createElement('RequestType','Payment');
		$transaction->appendChild($requestType);

	    //Payment
		$payment = $xml->createElement('Payment');
		$txnList = $xml->createElement('TxnList');
		$txnList->appendChild(
		$xml->createAttribute('count'))->appendChild(
		$xml->createTextNode('1'));

		$txn = $xml->createElement('Txn');
		$txn->appendChild(
		$xml->createAttribute('ID'))->appendChild(
		$xml->createTextNode('1'));

		$txnType = $xml->createElement('txnType','4');
		$txn->appendChild($txnType);
		$txnSource = $xml->createElement('txnSource','23');
		$txn->appendChild($txnSource);
		$txnID = $xml->createElement('txnID',$this->transactionId);
		$txn->appendChild($txnID);
		$amount = $xml->createElement('amount',$this->amount*100);
		$txn->appendChild($amount);
		$currency = $xml->createElement('currency',$this->currencyCode);
		$txn->appendChild($currency);
		$purchaseOrderNo = $xml->createElement('purchaseOrderNo',$this->invoiceNumber);
		$txn->appendChild($purchaseOrderNo);

		//creditCardInfo
		$CreditCardInfo = $xml->createElement('CreditCardInfo');
		$cardNumber = $xml->createElement('cardNumber',$this->cardNumber);
		$CreditCardInfo->appendChild($cardNumber);
		$cvv = $xml->createElement('cvv',$this->cvv);
		$CreditCardInfo->appendChild($cvv);
		$expiryDate = $xml->createElement('expiryDate',str_pad($this->expiryMonth, 2, '0', STR_PAD_LEFT).'/'.substr($this->expiryYear,-2));
		$CreditCardInfo->appendChild($expiryDate);
		$txn->appendChild($CreditCardInfo);

		$txnList->appendChild($txn);
		$payment->appendChild($txnList);
		$transaction->appendChild($payment);
		$xml->appendChild($transaction);

		$this->requestString = $xml->saveXML();

        $this->makeAPICall();
		return $this->response;

      }

	 function prepareResponse(){
           $xml = new DOMDocument();
           if($xml->loadXML($this->response->getRawResponse())){
            $responseValues = array();

			foreach($xml->childNodes as $node)
			{
               if($node->hasChildNodes())
			   {
                  foreach($node->childNodes as $childNode)
				  {
					  if($childNode->hasChildNodes())
					  {
						foreach($childNode->childNodes as $grandchildNode)
						{
							if($grandchildNode->hasChildNodes())
					           {
							    foreach($grandchildNode->childNodes as $putichildNode)
								{
									if($putichildNode->hasChildNodes())
									{
										foreach($putichildNode->childNodes as $sutichildNode)
										{
											// child name= txnType,amount,txnID
											if ($sutichildNode->nodeType != XML_TEXT_NODE)
											{
												$responseValues[$sutichildNode->nodeName] = trim($sutichildNode->nodeValue);
											}
										}
									}
									}
								}
						}

					  }

                    }
                  }
              }



              if($responseValues['approved']=='Yes'){
                  $this->response->success = 1;
                  $this->response->ack = ACK_SUCCESS;
              }
              else{
                  $this->response->ack = ACK_FAILURE;
                  $this->response->setError($responseValues['responseText']);
              }

            $this->response->responseCode = $responseValues['responseCode'];
            $this->response->transactionId = $responseValues['txnID'];
            $this->response->amount = $responseValues['amount']/100;
            $this->response->currency = $responseValues['currency'];
            $this->response->invoiceNumber   = $responseValues['purchaseOrderNo'];
            $this->response->cardType = $responseValues['cardType'];


          }
          else{
              $this->response->ack = ACK_FAILURE;


          }

      }

      /**
      *@desc Basic input validation
      */
      function validateBasicInput(){
          if(empty($this->apiUserName) || empty($this->apiKey)){
              $this->response->setError('E-xact Transactions credentials have not been configured.');
          }
      }
  }
?>