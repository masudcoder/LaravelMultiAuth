<?php
/**
 * @filename        OptimalPay.php
 * @description     This class is for making payment through OptimalPay Gateway
 * @version         1.0
 * @package         PaymentLibrary.classes
 * @author          Masuduzzaman
 *
 * @link
 * @created on      November 21, 2011
 * @Dependencies
 * @license
 ***/
  class OptimalPayments extends PaymentGateway
  {
      var $requesType = 'SALES';

      function OptimalPayments()
      {
		  parent::PaymentGateway();
		  $this->requestURL = "https://webservices.optimalpayments.com/creditcardWS/CreditCardServlet/v1";
          $this->testURL = "https://webservices.test.optimalpayments.com/creditcardWS/CreditCardServlet/v1";
		  $this->supportedCurrencies = array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD', 'ZAR');
      }

      function initialize()
      {
          $this->logMessage('Initializing basic parameters...');



      }

      function setCardParameters()
      {
          $this->logMessage('Initializing card parameters...');
          $this->setParam('ccnum', $this->cardNumber);
          $this->setParam('ccmo',str_pad($this->expiryMonth, 2, '0', STR_PAD_LEFT));
          $this->setParam('ccyr',$this->expiryYear);
      }

      function setBillingInformation()
      {
          $this->logMessage('Initializing billing parameters...');
          $this->setParam('FNAME', $this->firstName);
          $this->setParam('LNAME', $this->lastName);
          $this->setParam('BADDR1', trim($this->address1 . ' ' . $this->address2));
          $this->setParam('BCITY', $this->city);
          $this->setParam('BSTATE', $this->state);
          $this->setParam('BZIP1', $this->zip);
          $this->setParam('BCUST_EMAIL', $this->email);
      }

      function authorize()
      {
       $this->logMessage('Preparing sale request...');
       $this->requesType = 'AUTH';
       $this->setParam('txnMode', 'ccAuthorize');
       $this->setParam('txnRequest', '<ccAuthRequestV1 xmlns="http://www.optimalpayments.com/creditcard/xmlschema/v1"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://www.optimalpayments.com/creditcard/xmlschema/v1">
                <merchantAccount>
                <accountNum>'.$this->apiUserName.'</accountNum>
                <storeID>'.$this->apiKey.'</storeID>
                <storePwd>'.$this->apiSignature.'</storePwd>
                </merchantAccount>
                <merchantRefNum>Ref-12345</merchantRefNum>
                <amount>'.number_format($this->amount, 2, '.', '').'</amount>
                <card>
                <cardNum>'.$this->cardNumber.'</cardNum>
                <cardExpiry>
                <month>'.str_pad($this->expiryMonth, 2, '0', STR_PAD_LEFT).'</month>
                <year>'.substr($this->expiryYear,-2).'</year>
                </cardExpiry>
                </card>
                <billingDetails>
                <firstName>'.$this->firstName.'</firstName>
                <lastName>'.$this->lastName.'</lastName>
                <street>'.$this->address1.'</street>
                <city>'.$this->city.'</city>
                <state>'.$this->state.'</state>
                <country>US</country>
                <zip>'.$this->zip.'</zip>
                <phone>'.$this->phone.'</phone>
                <email>'.$this->email.'</email>
                </billingDetails>
                </ccAuthRequestV1>');
       $this->makeAPICall();
       return $this->response;

      }

      function capture()
      {
          $this->logMessage('Setting parameters for capture request...');
          $this->requesType = 'capture';
          $this->setParam('txnMode', 'ccSettlement');
          $this->setParam('txnRequest', '<ccPostAuthRequestV1 xmlns="http://www.optimalpayments.com/creditcard/xmlschema/v1"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.optimalpayments.com/creditcard/xmlschema/v1">
            <merchantAccount>
            <accountNum>'.$this->apiUserName.'</accountNum>
            <storeID>'.$this->apiKey.'</storeID>
            <storePwd>'.$this->apiSignature.'</storePwd>
            </merchantAccount>
            <confirmationNumber>'.$this->transactionId.'</confirmationNumber>
            <merchantRefNum>Ref-12345</merchantRefNum>
            <amount>'.number_format($this->amount, 2, '.', '').'</amount>
            </ccPostAuthRequestV1>');
          $this->makeAPICall();
          return $this->response;
      }

      function sale()
      {
	   $this->logMessage('Preparing sale request...');
       $this->requesType = 'SALE';
	   $this->setParam('txnMode', 'ccPurchase');
       $this->setParam('txnRequest', '<ccAuthRequestV1 xmlns="http://www.optimalpayments.com/creditcard/xmlschema/v1"
				xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
				xsi:schemaLocation="http://www.optimalpayments.com/creditcard/xmlschema/v1">
				<merchantAccount>
				<accountNum>'.$this->apiUserName.'</accountNum>
				<storeID>'.$this->apiKey.'</storeID>
				<storePwd>'.$this->apiSignature.'</storePwd>
				</merchantAccount>
				<merchantRefNum>Ref-12345</merchantRefNum>
				<amount>'.number_format($this->amount, 2, '.', '').'</amount>
				<card>
				<cardNum>'.$this->cardNumber.'</cardNum>
				<cardExpiry>
				<month>'.str_pad($this->expiryMonth, 2, '0', STR_PAD_LEFT).'</month>
				<year>'.substr($this->expiryYear,-2).'</year>
				</cardExpiry>
				</card>
				<billingDetails>
				<firstName>'.$this->firstName.'</firstName>
				<lastName>'.$this->lastName.'</lastName>
				<street>'.$this->address1.'</street>
				<city>'.$this->city.'</city>
				<state>'.$this->state.'</state>
				<country>US</country>
				<zip>'.$this->zip.'</zip>
				<phone>'.$this->phone.'</phone>
				<email>'.$this->email.'</email>
				</billingDetails>
				</ccAuthRequestV1>');
	   $this->makeAPICall();
       return $this->response;
      }

      function refund()
      {
          $this->logMessage('Preparing refund request...');
          $this->validateTransactionID();
          $this->logMessage('Setting parameters for refund request...');
          $this->requesType = 'RETURN';
          $this->setParam('txnMode', 'ccCancelSettle');
          $this->setParam('txnRequest', '<ccCancelRequestV1 xmlns="http://www.optimalpayments.com/creditcard/xmlschema/v1"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.optimalpayments.com/creditcard/xmlschema/v1">
            <merchantAccount>
            <accountNum>'.$this->apiUserName.'</accountNum>
            <storeID>'.$this->apiKey.'</storeID>
            <storePwd>'.$this->apiSignature.'</storePwd>
            </merchantAccount>
            <confirmationNumber>'.$this->transactionId.'</confirmationNumber>
            </ccCancelRequestV1>');
          $this->makeAPICall();
          return $this->response;
      }

      function prepareResponse()
      {
       $xml = new DOMDocument();

          if($xml->loadXML($this->response->getRawResponse())){
             $responseValues = array();
             foreach($xml->childNodes as $node){
                  if($node->hasChildNodes()){
                      foreach($node->childNodes as $childNode)
                           {
                                  if ($childNode->nodeType != XML_TEXT_NODE)
                                  {
                                  $responseValues[$childNode->nodeName] = trim($childNode->nodeValue);
                                  }
                                  else
                                  {
                                            if($childNode->hasChildNodes())
                                            {
                                                foreach($childNode->childNodes as $grandchildNode)
                                                {
                                                    if ($grandchildNode->nodeType != XML_TEXT_NODE)
                                                      {
                                                      $responseValues[$grandchildNode->nodeName] = trim($grandchildNode->nodeValue);
                                                      }

                                                }
                                            }
                                  }

                            }
                  }
              }
          }

           if($responseValues['code']==0)
              {
                  $this->response->success = 1;
                  $this->response->ack = ACK_SUCCESS;
              }
              else
              {
                 $this->response->ack = ACK_FAILURE;
                  $this->response->success = 0;
                  $this->response->setError($responseValues['detail']);
              }

          $this->response->transactionId = $responseValues['confirmationNumber'];
          $this->response->transactionType = $this->requesType;
          $this->response->description = $responseValues['description'];
          $this->response->amount = number_format($this->amount, 2, '.', '');
          if(isset($responseValues['authCode']))
          $this->response->authorizationId = $responseValues['authCode'];
     }

      /**
      *@desc Basic input validation
      */
      function validateBasicInput()
      { //die('basic');
          if(empty($this->apiUserName) || empty($this->apiKey) )
          {
              $this->response->setError('Quantum gateway login credentials have not been configured.');
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
/*		  $this->requestString = '<txnMode>ccPurchase</txnMode>
										<txnRequest>
				<ccAuthRequestV1 xmlns="http://www.optimalpayments.com/creditcard/xmlschema/v1"
				xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
				xsi:schemaLocation="http://www.optimalpayments.com/creditcard/xmlschema/v1">
				<merchantAccount>
				<accountNum>89992140</accountNum>
				<storeID>test</storeID>
				<storePwd>test</storePwd>
				</merchantAccount>
				<merchantRefNum>Ref-12345</merchantRefNum>
				<amount>16.00</amount>
				<card>
				<cardNum>4530910000012345</cardNum>
				<cardExpiry>
				<month>11</month>
				<year>2012</year>
				</cardExpiry>
				<cardType>VI</cardType>
				<cvdIndicator>1</cvdIndicator>
				<cvd>111</cvd>
				</card>
				<authentication>
				<indicator>05</indicator>
				<cavv>AAABB4WZlQAAAAAAcJmVENiWiV+=</cavv>
				<xid>Q2prWUI2RFNBc3FOTXNlem50eWY=</xid>
				</authentication>
				<billingDetails>
				<cardPayMethod>WEB</cardPayMethod>
				<firstName>Jane</firstName>
				<lastName>Jones</lastName>
				<street>123 Main Street</street>
				<city>LA</city>
				<state>CA</state>
				<country>US</country>
				<zip>90210</zip>
				<phone>555-555-5555</phone>
				<email>janejones@emailserver.com</email>
				</billingDetails>
				<shippingDetails>
				<carrier>FEX</carrier>
				<shipMethod>T</shipMethod>
				<firstName>Jane</firstName>
				<lastName>Jones</lastName>
				<street>44 Main Street</street>
				<city>LA</city>
				<state>CA</state>
				<country>US</country>
				<zip>90210</zip>
				<phone>555-555-5555</phone>
				<email>janejones@emailserver.com</email>
				</shippingDetails>
				<recurring>
				<recurringIndicator>I</recurringIndicator>
				<originalConfirmationNumber>115147689</originalConfirmationNumber>
				</recurring>
				<customerIP>127.0.0.1</customerIP>
				<productType>M</productType>
				<addendumData>
				<tag>CUST_ACCT_OPEN_DATE</tag>
				<value>20041012</value>
				</addendumData>
				<addendumData>
				<tag>MERCHANT_COUNTRY_CODE</tag>
				<value>US</value>
				</addendumData>
				<addendumData>
				<tag>SERVICE_REQUEST_CURRENCY</tag>
				<value>on</value>
				</addendumData>
				</ccAuthRequestV1>
				</txnRequest>';
*/



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

          $this->headers[] = "Content-Length: " . strlen($this->requestString);
          curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);

          if($this->testMode)
          {
              curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
              $this->response->isTestMode = 1;
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